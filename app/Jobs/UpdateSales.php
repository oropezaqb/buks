<?php

namespace App\Jobs;

use App\Models\Customer;
use App\Models\Account;
use App\Models\Product;
use App\Models\Document;
use App\Models\SubsidiaryLedger;
use App\Models\Transaction;
use App\Models\CreditNoteLine;
use App\Models\InvoiceItemLine;
use App\Models\SalesReturn;
use App\Models\Invoice;
use App\Jobs\RecordSalesReturn;

    /**
     * @SuppressWarnings(PHPMD.ElseExpression)
     * @SuppressWarnings(PHPMD.LongVariable)
     */

class UpdateSales
{
    public function updateSales($date)
    {
        $company = \Auth::user()->currentCompany->company;
        $salesForUpdate = \DB::table('transactions')->where('company_id', $company->id)
            ->where('date', '>=', $date)->orderBy('date', 'asc')->get();
        $this->deleteJournalEntries($salesForUpdate);
        foreach ($salesForUpdate as $saleForUpdate) {
            $transactions = Transaction::all();
            $transaction = $transactions->find($saleForUpdate->id);
            if ($transaction->type == 'sale') {
                $invoice = $transaction->transactable;
                $input = array();
                $row = 0;
                $input['customer_id'] = $invoice->customer_id;
                $input['date'] = $invoice->date;
                $input['invoice_number'] = $invoice->invoice_number;
                foreach ($invoice->itemLines as $itemLine) {
                    $input['item_lines']["'product_id'"][$row] = $itemLine->product_id;
                    $input['item_lines']["'description'"][$row] = $itemLine->description;
                    $input['item_lines']["'quantity'"][$row] = $itemLine->quantity;
                    $input['item_lines']["'amount'"][$row] = $itemLine->amount;
                    $input['item_lines']["'output_tax'"][$row] = $itemLine->output_tax;
                    $row += 1;
                }
                $createInvoice = new CreateInvoice();
                $createInvoice->recordSales($invoice, $input);
                $account = Account::where('title', 'Accounts Receivable')->firstOrFail();
                $document = Document::firstOrCreate(['name' => 'Invoice'], ['company_id' => $company->id]);
                $createInvoice->recordJournalEntry(
                    $invoice,
                    $input,
                    $account,
                    $document,
                    $invoice->invoice_number,
                    "To record sale of goods on account.",
                    1
                );
            }
            if ($transaction->type == 'sales_receipt') {
                $salesReceipt = $transaction->transactable;
                $input = array();
                $row = 0;
                $input['customer_id'] = $salesReceipt->customer_id;
                $input['date'] = $salesReceipt->date;
                $input['invoice_number'] = $salesReceipt->invoice_number;
                foreach ($salesReceipt->itemLines as $itemLine) {
                    $input['item_lines']["'product_id'"][$row] = $itemLine->product_id;
                    $input['item_lines']["'description'"][$row] = $itemLine->description;
                    $input['item_lines']["'quantity'"][$row] = $itemLine->quantity;
                    $input['item_lines']["'amount'"][$row] = $itemLine->amount;
                    $input['item_lines']["'output_tax'"][$row] = $itemLine->output_tax;
                    $row += 1;
                }
                $createInvoice = new CreateInvoice();
                $createInvoice->recordSales($salesReceipt, $input);
                $document = Document::firstOrCreate(['name' => 'Sales Receipt'], ['company_id' => $company->id]);
                $createInvoice->recordJournalEntry(
                    $salesReceipt,
                    $input,
                    $salesReceipt->account,
                    $document,
                    $salesReceipt->number,
                    "To record sale of goods for cash.",
                    1
                );
            }
            if ($transaction->type == 'sales_return') {
                $this->recordSalesReturn($transaction);
            }
            if ($transaction->type == 'inventory_qty_adj') {
                $inventoryQtyAdj = $transaction->transactable;
                $input = array();
                $row = 0;
                $createInventoryQtyAdj = new CreateInventoryQtyAdj();
                $createInventoryQtyAdj->updateSalesAndPurchases($inventoryQtyAdj);
                $createInventoryQtyAdj->recordJournalEntry($inventoryQtyAdj);
            }
        }
    }
    public function recordSalesReturn($transaction)
    {
                $creditNote = $transaction->transactable;
                $input = array();
                $row = 0;
                $input['customer_id'] = $creditNote->invoice->customer_id;
                $input['date'] = $creditNote->date;
                $input['number'] = $creditNote->number;
        foreach ($creditNote->lines as $itemLine) {
            $input['item_lines']["'product_id'"][$row] = $itemLine->product_id;
            $input['item_lines']["'description'"][$row] = $itemLine->description;
            $input['item_lines']["'quantity'"][$row] = $itemLine->quantity;
            $input['item_lines']["'amount'"][$row] = $itemLine->amount;
            $input['item_lines']["'output_tax'"][$row] = $itemLine->output_tax;
            $row += 1;
        }
                $recordSalesReturn = new RecordSalesReturn();
                $recordSalesReturn->record($creditNote, $input);
                $account = Account::where('title', 'Accounts Receivable')->firstOrFail();
                $company = \Auth::user()->currentCompany->company;
                $document = Document::firstOrCreate(['name' => 'Credit Note'], ['company_id' => $company->id]);
                $createInvoice = new CreateInvoice();
                $createInvoice->recordJournalEntry(
                    $creditNote,
                    $input,
                    $account,
                    $document,
                    $creditNote->number,
                    "To record return of goods from a customer.",
                    -1
                );
                $createCreditNote = new CreateCreditNote();
                $createCreditNote->recordPurchases($creditNote);
    }
    public function deleteJournalEntries($salesForUpdate)
    {
        foreach ($salesForUpdate as $saleForUpdate) {
            $transactions = Transaction::all();
            $transaction = $transactions->find($saleForUpdate->id);
            $invoice = $transaction->transactable;
            if (is_object($invoice->journalEntry)) {
                $invoice->journalEntry->delete();
            }
            $this->deleteSales($transaction, $invoice);
        }
    }
    public function deleteSales($transaction, $invoice)
    {
        if ($transaction->type == 'sale') {
            if (is_object($invoice->sales)) {
                $sales = $invoice->sales;
                foreach ($sales as $sale) {
                    $sale->delete();
                }
            }
        }
        if ($transaction->type == 'sales_return') {
            if (is_object($invoice->salesReturns)) {
                $salesReturns = $invoice->salesReturns;
                foreach ($salesReturns as $salesReturn) {
                    $salesReturn->delete();
                }
            }
            if (is_object($invoice->purchases)) {
                $purchases = $invoice->purchases;
                foreach ($purchases as $purchase) {
                    $purchase->delete();
                }
            }
        }
    }
}
