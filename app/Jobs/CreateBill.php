<?php

namespace App\Jobs;

use App\Models\Supplier;
use App\Models\Account;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\Document;
use App\Models\JournalEntry;
use App\Models\Posting;
use App\Models\SubsidiaryLedger;
use App\Models\BillItemLine;
use App\Models\BillCategoryLine;
use App\Models\PurchaseReturn;

class CreateBill
{
    public function recordPurchases($bill)
    {
        if (!is_null(request("item_lines.'product_id'"))) {
            $count = count(request("item_lines.'product_id'"));
            for ($row = 0; $row < $count; $row++) {
                $product = Product::find(request("item_lines.'product_id'.".$row));
                if ($product->track_quantity) {
                    $company = \Auth::user()->currentCompany->company;
//                    $quantityReturned = $bill->purchaseReturns
//                        ->where('product_id', $product->id)->sum('quantity');
                    $quantity = request("item_lines.'quantity'.".$row);
//                    $amountReturned = $bill->purchaseReturns
//                        ->where('product_id', $product->id)->sum('amount');
                    $amount = request("item_lines.'amount'.".$row);
                    $purchase = new Purchase([
                        'company_id' => $company->id,
                        'date' => request('bill_date'),
                        'product_id' => request("item_lines.'product_id'.".$row),
                        'quantity' => $quantity,
                        'amount' => $amount
                    ]);
                    $bill->purchases()->save($purchase);
                }
            }
        }
    }
    public function recordJournalEntry($bill)
    {
        $company = \Auth::user()->currentCompany->company;
        $document = Document::firstOrCreate(['name' => 'Bill', 'company_id' => $company->id]);
        $payableAccount = Account::where('title', 'Accounts Payable')->firstOrFail();
        $taxAccount = Account::where('title', 'Input VAT')->firstOrFail();
        $supplier = Supplier::all()->find(request('supplier_id'));
        $payableSubsidiary = SubsidiaryLedger::where('name', $supplier->name)
            ->firstOrCreate(['name' => $supplier->name, 'company_id' => $company->id]);
        $journalEntry = new JournalEntry([
            'company_id' => $company->id,
            'date' => request('bill_date'),
            'document_type_id' => $document->id,
            'document_number' => $bill->bill_number,
            'explanation' => 'To record purchase of goods on account.'
        ]);
        $bill->journalEntry()->save($journalEntry);
        $payableAmount = 0;
        $taxAmount = 0;
        if (!is_null(request("category_lines.'account_id'"))) {
            $count = count(request("category_lines.'account_id'"));
            for ($row = 0; $row < $count; $row++) {
                $inputTax = 0;
                if (!is_null(request("category_lines.'input_tax'.".$row))) {
                    $inputTax = request("category_lines.'input_tax'.".$row);
                }
                $posting = new Posting([
                    'company_id' => $company->id,
                    'journal_entry_id' => $journalEntry->id,
                    'account_id' => request("category_lines.'account_id'.".$row),
                    'debit' => request("category_lines.'amount'.".$row)
                ]);
                $posting->save();
                $payableAmount -= request("category_lines.'amount'.".$row) + $inputTax;
                $taxAmount += $inputTax;
            }
        }
        if (!is_null(request("item_lines.'product_id'"))) {
            $count = count(request("item_lines.'product_id'"));
            for ($row = 0; $row < $count; $row++) {
                $inputTax = 0;
                if (!is_null(request("item_lines.'input_tax'.".$row))) {
                    $inputTax = request("item_lines.'input_tax'.".$row);
                }
                $product = Product::find(request("item_lines.'product_id'.".$row));
                $posting = new Posting([
                    'company_id' => $company->id,
                    'journal_entry_id' => $journalEntry->id,
                    'account_id' => $product->inventoryAccount->id,
                    'debit' => request("item_lines.'amount'.".$row)
                ]);
                $posting->save();
                $payableAmount -= request("item_lines.'amount'.".$row) + $inputTax;
                $taxAmount += $inputTax;
            }
        }
        if ($taxAmount > 0) {
            $posting = new Posting([
                'company_id' => $company->id,
                'journal_entry_id' => $journalEntry->id,
                'account_id' => $taxAccount->id,
                'debit' => $taxAmount
            ]);
            $posting->save();
        }
        $posting = new Posting([
            'company_id' => $company->id,
            'journal_entry_id' => $journalEntry->id,
            'account_id' => $payableAccount->id,
            'debit' => $payableAmount,
            'subsidiary_ledger_id' => $payableSubsidiary->id
        ]);
        $posting->save();
    }
    public function deleteBillDetails($bill)
    {
        foreach ($bill->categoryLines as $categoryLine) {
            $categoryLine->delete();
        }
        foreach ($bill->itemLines as $itemLine) {
            $itemLine->delete();
        }
        foreach ($bill->purchases as $purchase) {
            $purchase->delete();
        }
    }
    public function updateLines($bill)
    {
        if (!is_null(request("category_lines.'account_id'"))) {
            $count = count(request("category_lines.'account_id'"));
            for ($row = 0; $row < $count; $row++) {
                $inputTax = 0;
                if (!is_null(request("category_lines.'input_tax'.".$row))) {
                    $inputTax = request("category_lines.'input_tax'.".$row);
                }
                $categoryLine = new BillCategoryLine([
                    'bill_id' => $bill->id,
                    'account_id' => request("category_lines.'account_id'.".$row),
                    'description' => request("category_lines.'description'.".$row),
                    'amount' => request("category_lines.'amount'.".$row),
                    'input_tax' => $inputTax
                ]);
                $categoryLine->save();
            }
        }
        if (!is_null(request("item_lines.'product_id'"))) {
            $count = count(request("item_lines.'product_id'"));
            for ($row = 0; $row < $count; $row++) {
                $inputTax = 0;
                if (!is_null(request("item_lines.'input_tax'.".$row))) {
                    $inputTax = request("item_lines.'input_tax'.".$row);
                }
                $itemLine = new BillItemLine([
                    'bill_id' => $bill->id,
                    'product_id' => request("item_lines.'product_id'.".$row),
                    'description' => request("item_lines.'description'.".$row),
                    'quantity' => request("item_lines.'quantity'.".$row),
                    'amount' => request("item_lines.'amount'.".$row),
                    'input_tax' => $inputTax
                ]);
                $itemLine->save();
            }
        }
    }
    public function recordTransaction($bill)
    {
        $company = \Auth::user()->currentCompany->company;
        $transaction = new Transaction([
            'company_id' => $company->id,
            'type' => 'purchase',
            'date' => request('date')
        ]);
        $bill->transaction()->save($transaction);
    }
}
