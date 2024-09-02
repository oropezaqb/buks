<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\CompanyUserController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\AbilityController;
use App\Http\Controllers\CurrentCompanyController;
use App\Http\Controllers\ApplicationController;
use App\Http\Controllers\LineItemController;
use App\Http\Controllers\AccountController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\ReportLineItemController;
use App\Http\Controllers\JournalEntryController;
use App\Http\Controllers\SubsidiaryLedgerController;
use App\Http\Controllers\QueryController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\BillController;
use App\Http\Controllers\PurchaseController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\SalesReceiptController;
use App\Http\Controllers\ReceivedPaymentController;
use App\Http\Controllers\AjaxController;
use App\Http\Controllers\CreditNoteController;
use App\Http\Controllers\AjaxCNController;
use App\Http\Controllers\SupplierCreditController;
use App\Http\Controllers\AjaxSCController;
use Illuminate\Support\Facades\Route;
use App\Http\Middleware\CheckCurrentCompany;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';

Route::middleware(['auth', 'web'])->group(function () {
    Route::get('/suppliers/import', 'SupplierController@import')->name('suppliers.import')
        ->middleware(CheckCurrentCompany::class);
    Route::post('/suppliers/upload', 'SupplierController@upload')->name('suppliers.upload')
        ->middleware(CheckCurrentCompany::class);
    Route::get('/products/import', 'ProductController@import')->name('products.import')
        ->middleware(CheckCurrentCompany::class);
    Route::post('/products/upload', 'ProductController@upload')->name('products.upload')
        ->middleware(CheckCurrentCompany::class);
    Route::post('/received_payments/ajax-request', [AjaxController::class, 'store'])
        ->middleware(CheckCurrentCompany::class);
    Route::post('/credit_notes/getinvoice', [AjaxCNController::class, 'getInvoice'])
        ->middleware(CheckCurrentCompany::class);
    Route::post('/credit_notes/getamounts', [AjaxCNController::class, 'getAmounts'])
        ->middleware(CheckCurrentCompany::class);
    Route::post('/supplier_credits/getdocument', [AjaxSCController::class, 'getDocument'])
        ->middleware(CheckCurrentCompany::class);
    Route::post('/supplier_credits/getamounts', [AjaxSCController::class, 'getAmounts'])
        ->middleware(CheckCurrentCompany::class);
});

Route::middleware(['auth', 'web'])->group(function () {
    Route::resource('companies', CompanyController::class);
    Route::resource('company_users', CompanyUserController::class)->middleware(CheckCurrentCompany::class);
    Route::resource('roles', RoleController::class)->middleware(CheckCurrentCompany::class);
    Route::resource('abilities', AbilityController::class)->middleware(CheckCurrentCompany::class);
    Route::resource('current_company', CurrentCompanyController::class);
    Route::resource('applications', ApplicationController::class);
    Route::resource('line_items', LineItemController::class)->middleware(CheckCurrentCompany::class);
    Route::resource('accounts', AccountController::class)->middleware(CheckCurrentCompany::class);
    Route::resource('documents', DocumentController::class)->middleware(CheckCurrentCompany::class);
    Route::resource('subsidiary_ledgers', SubsidiaryLedgerController::class)->middleware(CheckCurrentCompany::class);
    Route::resource('report_line_items', ReportLineItemController::class)->middleware(CheckCurrentCompany::class);
    Route::resource('journal_entries', JournalEntryController::class)->middleware(CheckCurrentCompany::class);
    Route::resource('suppliers', SupplierController::class)->middleware(CheckCurrentCompany::class);
    Route::resource('products', ProductController::class)->middleware(CheckCurrentCompany::class);
    Route::resource('bills', BillController::class)->middleware(CheckCurrentCompany::class);
    Route::resource('purchases', PurchaseController::class)->middleware(CheckCurrentCompany::class);
    Route::resource('customers', CustomerController::class)->middleware(CheckCurrentCompany::class);
    Route::resource('invoices', InvoiceController::class)->middleware(CheckCurrentCompany::class);
    Route::resource('sales_receipts', SalesReceiptController::class)->middleware(CheckCurrentCompany::class);
    Route::resource('received_payments', ReceivedPaymentController::class)->middleware(CheckCurrentCompany::class);
    Route::resource('credit_notes', CreditNoteController::class)->middleware(CheckCurrentCompany::class);
    Route::resource('supplier_credits', SupplierCreditController::class)->middleware(CheckCurrentCompany::class);
});

Route::middleware(['auth', 'web'])->group(function () {
    Route::resource('queries', QueryController::class)->middleware(CheckCurrentCompany::class);

    Route::post('/queries/{query}/run', [QueryController::class, 'run'])
      ->name('queries.run')->middleware(CheckCurrentCompany::class);
});
