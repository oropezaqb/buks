<?php

namespace App\Jobs;

use App\Models\Supplier;
use App\Models\Account;
use App\Models\Product;
use App\Models\Document;
use App\Models\SubsidiaryLedger;
use App\Models\Transaction;
use App\Models\SupplierCreditCLine;
use App\Models\SupplierCreditILine;
use App\Models\InvoiceItemLine;
use App\Models\SalesReturn;
use App\Models\Invoice;
use App\Models\JournalEntry;
use App\Models\Posting;
use App\Models\Purchase;
use App\Models\Bill;
use App\Models\BillItemLine;
use App\Models\BillCategoryLine;
use App\Models\PurchaseReturn;

    /**
     * @SuppressWarnings(PHPMD.ElseExpression)
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
     */

class CreateSupplierCredit
{
    public function determineCAmounts($purchasableDoc, $docId, $accountId, $supplierCreditId)
    {
        $company = \Auth::user()->currentCompany->company;
        $document = null;
        $amounts = array();
        switch ($purchasableDoc) {
            case 'Bill':
                $document = Bill::where('company_id', $company->id)->where('id', $docId)->first();
                $amounts = $this->determineCBillAmounts(
                    $document,
                    $purchasableDoc,
                    $docId,
                    $accountId,
                    $supplierCreditId
                );
                break;
            case 'Cheque':
                $document = Cheque::where('company_id', $company->id)->where('id', $docId)->first();
                $amounts = $this->determineCChequeAmounts(
                    $document,
                    $purchasableDoc,
                    $docId,
                    $accountId,
                    $supplierCreditId
                );
                break;
            default:
                $document = null;
        }
        return $amounts;
    }
    public function determineCBillAmounts($document, $purchasableDoc, $docId, $accountId, $supplierCreditId)
    {
        $account = Account::find($accountId);
        $amountPurchased = BillCategoryLine::where('bill_id', $document->id)
            ->where('account_id', $account->id)->sum('amount');
        $taxPurchased = BillCategoryLine::where('bill_id', $document->id)
            ->where('account_id', $account->id)->sum('input_tax');
        $amtReturnForThisSC = 0;
        if (!is_null($supplierCreditId)) {
            $amtReturnForThisSC = SupplierCreditCLine::where('supplier_credit_id', $supplierCreditId)
                ->where('purchasable_type', 'App\Models\Bill')
                ->where('purchasable_id', $docId)
                ->where('account_id', $account->id)->sum('amount');
        }
        $amountReturned = SupplierCreditCLine::where('purchasable_type', 'App\Models\Bill')
            ->where('purchasable_id', $docId)
            ->where('account_id', $account->id)->sum('amount');
        $taxReturnForThisSC = 0;
        if (!is_null($supplierCreditId)) {
            $taxReturnForThisSC = SupplierCreditCLine::where('supplier_credit_id', $supplierCreditId)
                ->where('purchasable_type', 'App\Models\Bill')
                ->where('purchasable_id', $docId)
                ->where('account_id', $account->id)->sum('input_tax');
        }
        $taxReturned = SupplierCreditCLine::where('purchasable_type', 'App\Models\Bill')
            ->where('purchasable_id', $docId)
            ->where('account_id', $account->id)->sum('input_tax');
        $amountUnreturned = $amountPurchased - ($amountReturned - $amtReturnForThisSC);
        $taxUnreturned = $taxPurchased - ($taxReturned - $taxReturnForThisSC);
        $amounts = array();
        $amounts['amount_unreturned'] = $amountUnreturned;
        $amounts['tax_unreturned'] = $taxUnreturned;
        $amounts['amount'] = $amountUnreturned;
        $amounts['tax'] = $taxUnreturned;
        return $amounts;
    }
    public function determineAmounts($purchasableDoc, $docId, $productId, $quantity, $supplierCreditId)
    {
        $company = \Auth::user()->currentCompany->company;
        $document = null;
        $amounts = array();
        switch ($purchasableDoc) {
            case 'Bill':
                $document = Bill::where('company_id', $company->id)->where('id', $docId)->first();
                $amounts = $this->determineBillAmounts(
                    $document,
                    $purchasableDoc,
                    $docId,
                    $productId,
                    $quantity,
                    $supplierCreditId
                );
                break;
            case 'Cheque':
                $document = Cheque::where('company_id', $company->id)->where('id', $docId)->first();
                $amounts = $this->determineChequeAmounts(
                    $document,
                    $purchasableDoc,
                    $docId,
                    $productId,
                    $quantity,
                    $supplierCreditId
                );
                break;
            default:
                $document = null;
        }
        return $amounts;
    }
    public function determineBillAmounts($document, $purchasableDoc, $docId, $productId, $quantity, $supplierCreditId)
    {
        $product = Product::find($productId);
        $quantityPurchased = BillItemLine::where('bill_id', $document->id)
            ->where('product_id', $product->id)->sum('quantity');
        $amountPurchased = BillItemLine::where('bill_id', $document->id)
            ->where('product_id', $product->id)->sum('amount');
        $taxPurchased = BillItemLine::where('bill_id', $document->id)
            ->where('product_id', $product->id)->sum('input_tax');
        $qtyReturnForThisSC = 0;
        if (!is_null($supplierCreditId)) {
            $qtyReturnForThisSC = SupplierCreditILine::where('supplier_credit_id', $supplierCreditId)
                ->where('purchasable_type', 'App\Models\Bill')
                ->where('purchasable_id', $docId)
                ->where('product_id', $product->id)->sum('quantity');
        }
        $quantityReturned = SupplierCreditILine::where('purchasable_type', 'App\Models\Bill')
            ->where('purchasable_id', $docId)
            ->where('product_id', $product->id)->sum('quantity');
        $amtReturnForThisSC = 0;
        if (!is_null($supplierCreditId)) {
            $amtReturnForThisSC = SupplierCreditILine::where('supplier_credit_id', $supplierCreditId)
                ->where('purchasable_type', 'App\Models\Bill')
                ->where('purchasable_id', $docId)
                ->where('product_id', $product->id)->sum('amount');
        }
        $amountReturned = SupplierCreditILine::where('purchasable_type', 'App\Models\Bill')
            ->where('purchasable_id', $docId)
            ->where('product_id', $product->id)->sum('amount');
        $taxReturnForThisSC = 0;
        if (!is_null($supplierCreditId)) {
            $taxReturnForThisSC = SupplierCreditILine::where('supplier_credit_id', $supplierCreditId)
                ->where('purchasable_type', 'App\Models\Bill')
                ->where('purchasable_id', $docId)
                ->where('product_id', $product->id)->sum('input_tax');
        }
        $taxReturned = SupplierCreditILine::where('purchasable_type', 'App\Models\Bill')
            ->where('purchasable_id', $docId)
            ->where('product_id', $product->id)->sum('input_tax');
        $quantityUnreturned = $quantityPurchased - ($quantityReturned - $qtyReturnForThisSC);
        $amountUnreturned = $amountPurchased - ($amountReturned - $amtReturnForThisSC);
        $taxUnreturned = $taxPurchased - ($taxReturned - $taxReturnForThisSC);
        $amounts = array();
        $amounts['amount'] = 0;
        $amounts['tax'] = 0;
        $amounts['amount_unreturned'] = $amountUnreturned;
        $amounts['tax_unreturned'] = $taxUnreturned;
        $amounts['quantity_unreturned'] = $quantityUnreturned;
        if (($quantity > 0) && ($quantity < $quantityUnreturned)) {
            $amounts['amount'] = round(($amountUnreturned / $quantityUnreturned) * $quantity, 2);
            $amounts['tax'] = round(($taxUnreturned / $quantityUnreturned) * $quantity, 2);
        }
        if ($quantity == $quantityUnreturned) {
            $amounts['amount'] = $amountUnreturned;
            $amounts['tax'] = $taxUnreturned;
        }
        return $amounts;
    }
    public function deletePurchaseReturns($supplierCredit, $document)
    {
//        $purchaseReturns = PurchaseReturn::where('returnablepurc_type', 'App\SupplierCredit')->get();
        foreach ($supplierCredit->purchaseReturns as $purchaseReturn) {
            $purchaseReturn->delete();
        }
    }
    public function savePurchaseReturns($supplierCredit, $document)
    {
        $count = count(request("item_lines.'product_id'"), 1);
        if ($count > 0) {
            for ($row = 0; $row < $count; $row++) {
                $product = Product::find(request("item_lines.'product_id'.".$row));
                if ($product->track_quantity && !is_null(request("item_lines.'amount'.".$row)) &&
                    is_numeric(request("item_lines.'amount'.".$row))) {
                    $company = \Auth::user()->currentCompany->company;
                    $purchaseReturn = new PurchaseReturn([
                        'company_id' => $company->id,
                        'date' => request('date'),
                        'product_id' => $product->id,
                        'quantity' => request("item_lines.'quantity'.".$row),
                        'amount' => request("item_lines.'amount'.".$row),
                        'returnablepurc_type' => 'App\Models\SupplierCredit',
                        'returnablepurc_id' => $supplierCredit->id
                    ]);
                    $document->purchaseReturns()->save($purchaseReturn);
                }
            }
        }
    }
    public function deletePurchases($supplierCredit, $document)
    {
        if (!is_null($document)) {
            foreach ($document->purchases as $purchase) {
                $purchase->delete();
            }
        }
    }
    public function updatePurchases($supplierCredit, $document)
    {
        if (!is_null($document->itemLines)) {
            foreach ($document->itemLines as $itemLine) {
                $product = $itemLine->product;
                if ($product->track_quantity) {
                    $company = \Auth::user()->currentCompany->company;
                    $quantityReturned = $document->purchaseReturns
                        ->where('product_id', $product->id)->sum('quantity');
                    $quantity = $itemLine->quantity - $quantityReturned;
                    $amountReturned = $document->purchaseReturns
                        ->where('product_id', $product->id)->sum('amount');
                    $amount = $itemLine->amount - $amountReturned;
                    $purchase = new Purchase([
                        'company_id' => $company->id,
                        'date' => $document->bill_date,
                        'product_id' => $product->id,
                        'quantity' => $quantity,
                        'amount' => $amount
                    ]);
                    $document->purchases()->save($purchase);
                }
            }
        }
    }
    public function recordJournalEntry($supplierCredit)
    {
        $company = \Auth::user()->currentCompany->company;
        $document = Document::firstOrCreate(['name' => 'Supplier Credit', 'company_id' => $company->id]);
        $payableAccount = Account::where('title', 'Accounts Payable')->firstOrFail();
        $taxAccount = Account::where('title', 'Input VAT')->firstOrFail();
        $supplier = $supplierCredit->purchasable->supplier;
        $payableSubsidiary = SubsidiaryLedger::where('name', $supplier->name)
            ->firstOrCreate(['name' => $supplier->name, 'company_id' => $company->id]);
        $journalEntry = new JournalEntry([
            'company_id' => $company->id,
            'date' => request('date'),
            'document_type_id' => $document->id,
            'document_number' => $supplierCredit->number,
            'explanation' => 'To record return of goods back to a supplier.'
        ]);
        $supplierCredit->journalEntry()->save($journalEntry);
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
                    'debit' => -request("category_lines.'amount'.".$row)
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
                    'debit' => -request("item_lines.'amount'.".$row)
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
                'debit' => -$taxAmount
            ]);
            $posting->save();
        }
        $posting = new Posting([
            'company_id' => $company->id,
            'journal_entry_id' => $journalEntry->id,
            'account_id' => $payableAccount->id,
            'debit' => -$payableAmount,
            'subsidiary_ledger_id' => $payableSubsidiary->id
        ]);
        $posting->save();
    }
}
