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
use App\Http\Controllers\API\BudgetController;
use App\Http\Controllers\API\ExpenseController;
use App\Http\Controllers\API\FamilyController;
use App\Http\Controllers\API\CategoryController;
use App\Http\Controllers\API\FundRequestController;
use App\Http\Controllers\API\LoanCategoryController;
use App\Http\Controllers\API\LoanController;
use App\Http\Controllers\API\LoanRepaymentController;
use App\Http\Controllers\API\LoanContributionController;
use App\Http\Controllers\API\GoalController;
use App\Http\Controllers\API\SavingsController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\FollowController;
use App\Http\Controllers\API\AuthController;
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


    Route::get('/auth/profile', [AuthController::class, 'profile']);

    // ✅ Budget
    Route::get('/budget/family', [BudgetController::class, 'familyBudget']);
    Route::get('/budget/assigned', [BudgetController::class, 'assignedBudgets']);

    // ✅ Expenses
    Route::get('/expenses/my', [ExpenseController::class, 'myExpenses']);
    Route::get('/expenses/family', [ExpenseController::class, 'familyExpenses']);

    // ✅ Family
    Route::get('/family/members', [FamilyController::class, 'listMembers']);

    // ✅ Categories
    Route::get('/categories/all', [CategoryController::class, 'index']);

    // ✅ Fund Requests
    Route::get('/fund-request/my', [FundRequestController::class, 'myRequests']);
    Route::get('/fund-request/funds/all', [FundRequestController::class, 'allFamilyRequests']);

    // ✅ Loan Categories
    Route::get('/loan-categories', [LoanCategoryController::class, 'index']);

    // ✅ Loans
    Route::get('/loans', [LoanController::class, 'index']);
    Route::get('/loans/{id}', [LoanController::class, 'show']);

    // ✅ Loan Repayments
    Route::get('/loan-repayments/loan/{loan_id}', [LoanRepaymentController::class, 'byLoan']);

    // ✅ Loan Contributions
    Route::get('/loan-contributions/my', [LoanContributionController::class, 'myContributions']);

    // ✅ Goals (Family & Personal)
    Route::get('/goals/family', [GoalController::class, 'familyGoals']);
    Route::get('/goals/personal', [GoalController::class, 'myGoals']);
    Route::get('/goals/{id}/progress', [GoalController::class, 'goalProgress']);

    // ✅ Savings
    Route::get('/savings/my', [SavingsController::class, 'mySavings']);
    Route::get('/savings/history', [SavingsController::class, 'savingsHistory']);
    Route::get('/savings/end-of-month', [SavingsController::class, 'endOfMonthRollover']);

    // ✅ Posts & Comments
    Route::get('/posts', [PostController::class, 'index']);
    Route::get('/posts/{post}', [PostController::class, 'show']);
    Route::get('/my-posts', [PostController::class, 'myPosts']);
    Route::get('/posts/{post}/comments', [CommentController::class, 'index']);

    // ✅ Follows
    Route::get('/users/{user}/followers', [FollowController::class, 'followers']);
    Route::get('/users/{user}/followings', [FollowController::class, 'followings']);
    Route::get('/users/{user}/profile-stats', [FollowController::class, 'profileStats']);


});



/**
 * Profile routes.
 */
Route::group(['prefix' => 'profile', 'as' => 'profile.', 'middleware' => ['auth']], function () {
    Route::get('/edit', [ProfilesController::class, 'edit'])->name('edit');
    Route::put('/update', [ProfilesController::class, 'update'])->name('update');
});

Route::get('/locale/{lang}', [LocaleController::class, 'switch'])->name('locale.switch');


