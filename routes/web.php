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
});

Route::middleware(['auth', 'web'])->group(function () {
    Route::resource('queries', QueryController::class)->middleware(CheckCurrentCompany::class);

    Route::post('/queries/{query}/run', [QueryController::class, 'run'])
      ->name('queries.run')->middleware(CheckCurrentCompany::class);
});
