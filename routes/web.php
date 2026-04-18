<?php

use App\Http\Controllers\AccountController;
use App\Http\Controllers\BillsController;
use App\Http\Controllers\BudgetController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CreditCardBillController;
use App\Http\Controllers\OpenBillsController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\TransactionController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('dashboard');
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::resource('accounts', AccountController::class)->except(['show']);
    Route::resource('categories', CategoryController::class)->except(['show']);
    Route::resource('transactions', TransactionController::class);
    Route::delete('/transactions/{transaction}/installments', [TransactionController::class, 'destroyInstallmentGroup'])->name('transactions.installments.destroy');
    Route::resource('budgets', BudgetController::class)->only(['index', 'store', 'destroy']);
    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
    Route::get('/reports/extrato', [ReportController::class, 'extrato'])->name('reports.extrato');

    // Faturas
    Route::get('/faturas', [BillsController::class, 'index'])->name('bills.index');

    // Contas em Aberto
    Route::get('/contas-em-aberto', [OpenBillsController::class, 'index'])->name('open-bills.index');

    // Fatura de cartão de crédito
    Route::get('/accounts/{account}/bill', [CreditCardBillController::class, 'index'])->name('credit-card-bills.index');
    Route::get('/accounts/{account}/bill/months', [CreditCardBillController::class, 'months'])->name('credit-card-bills.months');
    Route::get('/accounts/{account}/bill/statement', [CreditCardBillController::class, 'statement'])->name('credit-card-bills.statement');
    Route::post('/accounts/{account}/bill/close', [CreditCardBillController::class, 'close'])->name('credit-card-bills.close');
    Route::patch('/credit-card-bills/{bill}/pay', [CreditCardBillController::class, 'pay'])->name('credit-card-bills.pay');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
