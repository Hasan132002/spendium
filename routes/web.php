<?php

declare(strict_types=1);

use App\Http\Controllers\Backend\ActionLogController;
use App\Http\Controllers\Backend\DashboardController;
use App\Http\Controllers\Backend\ModulesController;
use App\Http\Controllers\Backend\RolesController;
use App\Http\Controllers\Backend\UsersController;
use App\Http\Controllers\Backend\SettingsController;
use App\Http\Controllers\Backend\ProfilesController;
use App\Http\Controllers\Backend\TranslationController;
use App\Http\Controllers\Backend\UserLoginAsController;
use App\Http\Controllers\Backend\LocaleController;
use App\Http\Controllers\Backend\SaleOrderController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Backend\AllAppController;
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', 'HomeController@redirectAdmin')->name('index');
Route::get('/home', 'HomeController@index')->name('home');
Route::get('/action-log', [ActionLogController::class, 'index'])->name('actionlog.index');

/**
 * Admin routes.
 */
Route::group(['prefix' => 'admin', 'as' => 'admin.', 'middleware' => ['auth']], function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::resource('roles', RolesController::class);

    // Modules Routes.
    Route::get('/modules', [ModulesController::class, 'index'])->name('modules.index');
    Route::post('/modules/toggle-status/{module}', [ModulesController::class, 'toggleStatus'])->name('modules.toggle-status');
    Route::post('/modules/upload', [ModulesController::class, 'upload'])->name('modules.upload');
    Route::delete('/modules/{module}', [ModulesController::class, 'destroy'])->name('modules.delete');

    // Sale Order Routes
    Route::get('/sale-orders', [SaleOrderController::class, 'index'])->name('sale-orders.index');
    Route::get('/sale-orders/view/{id}', [SaleOrderController::class, 'view'])->name('sale-orders.view');

    // Route::get('/sale-orders/create', [SaleOrderController::class, 'create'])->name('sale-orders.create');
    Route::get('/sale-orders/create/{id?}', [SaleOrderController::class, 'create'])->name('sale-orders.create');
    Route::put('/sale-orders/update/{id}', [SaleOrderController::class, 'update'])->name('sale-orders.update');

    Route::post('/sale-orders', [SaleOrderController::class, 'store'])->name('sale-orders.store');
    Route::get('/sale-orders/{docEntry}/edit', [SaleOrderController::class, 'create'])->name('sale-orders.edit');
    Route::put('/sale-orders/{docEntry}', [SaleOrderController::class, 'update'])->name('sale-orders.update');

    Route::delete('/sale-orders/{id}', [SaleOrderController::class, 'destroy'])->name('sale-orders.destroy');

    
    // Settings Routes.
    Route::get('/settings', [SettingsController::class, 'index'])->name('settings.index');
    Route::post('/settings', [SettingsController::class, 'store'])->name('settings.store');

    // Translation Routes
    Route::get('/translations', [TranslationController::class, 'index'])->name('translations.index');
    Route::post('/translations', [TranslationController::class, 'update'])->name('translations.update');
    Route::post('/translations/create', [TranslationController::class, 'create'])->name('translations.create');

    // Login as & Switch back
    Route::resource('users', UsersController::class);
    Route::get('users/{id}/login-as', [UserLoginAsController::class, 'loginAs'])->name('users.login-as');
    Route::post('users/switch-back', [UserLoginAsController::class, 'switchBack'])->name('users.switch-back');


    Route::get('/auth/profile', [AllAppController::class, 'profile']);

    // ✅ Budget
    // Route::get('/budget/family', [AllAppController::class, 'familyBudget']);

Route::get('/budget/family', [AllAppController::class, 'familyBudget'])->name('budget.family');
    Route::get('/budget/assigned', [AllAppController::class, 'assignedBudgets']);

    // ✅ Expenses
    Route::get('/expenses/my', [AllAppController::class, 'myExpenses']);
    Route::get('/expenses/family', [AllAppController::class, 'familyExpenses']);

    // ✅ Family
    Route::get('/family/members', [AllAppController::class, 'Members']);

    // ✅ Categories
    Route::get('/categories/all', [AllAppController::class, 'categories']);

    // ✅ Fund Requests
    Route::get('/fund-request/my', [AllAppController::class, 'myRequests']);
    Route::get('/fund-request/funds/all', [AllAppController::class, 'FamilyRequests']);

    // ✅ Loan Categories
    Route::get('/loan-categories', [AllAppController::class, 'loanCategories']);

    // ✅ Loans
    Route::get('/loans', [AllAppController::class, 'loans']);
    Route::get('/loans/{id}', [AllAppController::class, 'Loan']);

    // ✅ Loan Repayments
    Route::get('/loan-repayments/loan/{loan_id}', [AllAppController::class, 'loanRepayments']);

    // ✅ Loan Contributions
    Route::get('/loan-contributions/my', [AllAppController::class, 'myContributions']);

    // ✅ Goals (Family & Personal)
    Route::get('/goals/family', [AllAppController::class, 'familyGoals']);
    Route::get('/goals/personal', [AllAppController::class, 'myGoals']);
    Route::get('/goals/{id}/progress', [AllAppController::class, 'goalProgress']);

    // ✅ Savings
    Route::get('/savings/my', [AllAppController::class, 'Savings']);
    Route::get('/savings/history', [AllAppController::class, 'savingsHistory']);
    Route::get('/savings/end-of-month', [AllAppController::class, 'endOfMonthRolloverView']); 

    // ✅ Posts & Comments
   Route::get('/posts', [AllAppController::class, 'Posts']);
Route::get('/posts/{post}', [AllAppController::class, 'post']);
Route::get('/my-posts', [AllAppController::class, 'myPosts']);
Route::get('/posts/{post}/comments', [AllAppController::class, 'comments']);

Route::get('/users/{user}/followers', [AllAppController::class, 'followers']);
Route::get('/users/{user}/followings', [AllAppController::class, 'followings']);
Route::get('/users/{user}/profile-stats', [AllAppController::class, 'profileStats']);


});



/**
 * Profile routes.
 */
Route::group(['prefix' => 'profile', 'as' => 'profile.', 'middleware' => ['auth']], function () {
    Route::get('/edit', [ProfilesController::class, 'edit'])->name('edit');
    Route::put('/update', [ProfilesController::class, 'update'])->name('update');
});

Route::get('/locale/{lang}', [LocaleController::class, 'switch'])->name('locale.switch');


