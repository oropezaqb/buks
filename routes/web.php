<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\CompanyUserController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\AbilityController;
use App\Http\Controllers\CurrentCompanyController;
use App\Http\Controllers\ApplicationController;
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
});
