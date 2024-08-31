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
        foreach ($salesForUpdate as $saleForUpdate) {
            $transactions = Transaction::all();
            $transaction = $transactions->find($saleForUpdate->id);
            $invoice = $transaction->transactable;
            if (is_object($invoice->journalEntry)) {
                $invoice->journalEntry->delete();
            }
            $this->deleteSales($transaction, $invoice);
        }
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
                $createInvoice->recordJournalEntry($invoice, $input);
            }
            if ($transaction->type == 'sales_return') {
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
                $createInvoice = new CreateCreditNote();
                $createInvoice->recordJournalEntry($creditNote, $input);
                $createInvoice->recordPurchases($creditNote);
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
