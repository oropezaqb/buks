<?php

namespace App\Jobs;

use App\Models\Customer;
use App\Models\Account;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\Sale;
use App\Models\Document;
use App\Models\JournalEntry;
use App\Models\Posting;
use App\Models\SubsidiaryLedger;
use App\Models\Transaction;
use App\Models\SalesReceiptItemLine;
use App\Jobs\CreateInvoice;

    /**
     * @SuppressWarnings(PHPMD.ElseExpression)
     */

class CreateSalesReceipt
{
    public function recordSales($salesReceipt, $input)
    {
        $createInvoice = new CreateInvoice();
        $count = count($input['item_lines']["'product_id'"], 1);
        if ($count > 0) {
            for ($row = 0; $row < $count; $row++) {
                $product = Product::find($input['item_lines']["'product_id'"][$row]);
                if ($product->track_quantity) {
                    $numberRecorded = 0;
                    do {
                        $company = \Auth::user()->currentCompany->company;
                        $purchase = $createInvoice->determinePurchaseSold($company, $product);
                        if (is_object($purchase)) {
                            $numberUnrecorded = $input['item_lines']["'quantity'"][$row] - $numberRecorded;
                            $quantity = $createInvoice->determineQuantitySold($company, $purchase, $numberUnrecorded);
                            $amount = $createInvoice->determineAmountSold($company, $purchase, $numberUnrecorded);
                            $sale = new Sale([
                                'company_id' => $company->id,
                                'purchase_id' => $purchase->id,
                                'date' => $input['date'],
                                'product_id' => $product->id,
                                'quantity' => $quantity,
                                'amount' => $amount
                            ]);
                            $salesReceipt->sales()->save($sale);
                            $numberRecorded += $quantity;
                        } else {
                            break;
                        }
                    } while ($numberRecorded < $input['item_lines']["'quantity'"][$row]);
                }
            }
        }
    }
    public function recordTransaction($salesReceipt)
    {
        $company = \Auth::user()->currentCompany->company;
        $transaction = new Transaction([
            'company_id' => $company->id,
            'type' => 'sales_receipt',
            'date' => request('date')
        ]);
        $salesReceipt->transaction()->save($transaction);
    }
    public function updateSales($salesForUpdate)
    {
        foreach ($salesForUpdate as $saleForUpdate) {
            $transactions = Transaction::all();
            $transaction = $transactions->find($saleForUpdate->id);
            $salesReceipt = $transaction->transactable;
            if (is_object($salesReceipt->journalEntry)) {
                foreach ($salesReceipt->journalEntry->postings as $posting) {
                    $posting->delete();
                }
                $salesReceipt->journalEntry->delete();
            }
            if (is_object($salesReceipt->sales)) {
                $sales = $salesReceipt->sales;
                foreach ($sales as $sale) {
                    $sale->delete();
                }
            }
        }
        foreach ($salesForUpdate as $saleForUpdate) {
            $transactions = Transaction::all();
            $transaction = $transactions->find($saleForUpdate->id);
            $salesReceipt = $transaction->transactable;
            $input = array();
            $row = 0;
            $input['customer_id'] = $salesReceipt->customer_id;
            $input['date'] = $salesReceipt->date;
            $input['number'] = $salesReceipt->number;
            $input['account_id'] = $salesReceipt->account_id;
            foreach ($salesReceipt->itemLines as $itemLine) {
                $input['item_lines']["'product_id'"][$row] = $itemLine->product_id;
                $input['item_lines']["'description'"][$row] = $itemLine->description;
                $input['item_lines']["'quantity'"][$row] = $itemLine->quantity;
                $input['item_lines']["'amount'"][$row] = $itemLine->amount;
                $input['item_lines']["'output_tax'"][$row] = $itemLine->output_tax;
                $row += 1;
            }
            $createSalesReceipt = new CreateSalesReceipt();
            $createSalesReceipt->recordSales($salesReceipt, $input);
            $createSalesReceipt->recordJournalEntry($salesReceipt, $input);
        }
    }
    public function updateLines($salesReceipt)
    {
        if (!is_null(request("item_lines.'product_id'"))) {
            $count = count(request("item_lines.'product_id'"));
            for ($row = 0; $row < $count; $row++) {
                $outputTax = 0;
                if (!is_null(request("item_lines.'output_tax'.".$row))) {
                    $outputTax = request("item_lines.'output_tax'.".$row);
                }
                $itemLine = new SalesReceiptItemLine([
                    'sales_receipt_id' => $salesReceipt->id,
                    'product_id' => request("item_lines.'product_id'.".$row),
                    'description' => request("item_lines.'description'.".$row),
                    'quantity' => request("item_lines.'quantity'.".$row),
                    'amount' => request("item_lines.'amount'.".$row),
                    'output_tax' => $outputTax
                ]);
                $itemLine->save();
            }
        }
    }
    public function deleteSalesReceiptDetails($salesReceipt)
    {
        foreach ($salesReceipt->itemLines as $itemLine) {
            $itemLine->delete();
        }
        foreach ($salesReceipt->sales as $sale) {
            $sale->delete();
        }
    }
}
