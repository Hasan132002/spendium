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
use App\Http\Controllers\Backend\FamilyMemberController;
use App\Http\Controllers\Backend\NotificationController;
use App\Http\Controllers\Backend\IncomeController;
use App\Http\Controllers\Backend\ReportController;
use App\Http\Controllers\Auth\FamilyInviteController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Backend\AllAppController;
use App\Http\Controllers\AIController;
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

    // ✅ Reports / Analytics
    Route::group(['prefix' => 'reports', 'as' => 'reports.'], function () {
        Route::get('/', [ReportController::class, 'index'])->name('index');
        Route::get('/export/expenses', [ReportController::class, 'exportExpenses'])->name('export-expenses');
        Route::get('/export/incomes', [ReportController::class, 'exportIncomes'])->name('export-incomes');
    });

    // ✅ Incomes
    Route::group(['prefix' => 'incomes', 'as' => 'incomes.'], function () {
        Route::get('/my', [IncomeController::class, 'myIncomes'])->name('my');
        Route::get('/family', [IncomeController::class, 'familyIncomes'])->name('family');
        Route::get('/create', [IncomeController::class, 'create'])->name('create');
        Route::post('/', [IncomeController::class, 'store'])->name('store');
        Route::delete('/{id}', [IncomeController::class, 'destroy'])->name('destroy');
    });

    // ✅ Notifications
    Route::group(['prefix' => 'notifications', 'as' => 'notifications.'], function () {
        Route::get('/', [NotificationController::class, 'index'])->name('index');
        Route::get('/latest', [NotificationController::class, 'latest'])->name('latest');
        Route::get('/{id}/read', [NotificationController::class, 'markRead'])->name('mark-read');
        Route::post('/mark-all-read', [NotificationController::class, 'markAllRead'])->name('mark-all-read');
        Route::delete('/{id}', [NotificationController::class, 'destroy'])->name('destroy');
    });

    // ✅ Family Management (new controller with full CRUD + invitations)
    Route::group(['prefix' => 'family', 'as' => 'family.'], function () {
        Route::get('/members', [FamilyMemberController::class, 'index'])->name('members.index');
        Route::get('/members/invite', [FamilyMemberController::class, 'showInviteForm'])->name('members.invite');
        Route::post('/members/invite', [FamilyMemberController::class, 'storeInvite'])->name('members.store-invite');
        Route::get('/members/{member}/edit', [FamilyMemberController::class, 'showChangeRoleForm'])->name('members.change-role-form');
        Route::put('/members/{member}', [FamilyMemberController::class, 'changeRole'])->name('members.change-role');
        Route::delete('/members/{member}', [FamilyMemberController::class, 'remove'])->name('members.remove');
        Route::post('/invitations/{invitation}/resend', [FamilyMemberController::class, 'resendInvite'])->name('invitations.resend');
        Route::delete('/invitations/{invitation}', [FamilyMemberController::class, 'revokeInvite'])->name('invitations.revoke');
        Route::post('/transfer-head', [FamilyMemberController::class, 'transferHead'])->name('transfer-head');
    });

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


//  Route::get('/ai-assistant', [App\Http\Controllers\AIController::class, 'index'])->name('ai.index');
//     Route::post('/ai-assistant/ask', [App\Http\Controllers\AIController::class, 'ask'])->name('ai.ask');
 Route::get('/ai/chat', [AIController::class, 'chatView'])->name('ai.chat');
    Route::post('/ai/ask', [AIController::class, 'askAI'])->name('ai.ask');
});



/**
 * Profile routes.cd
 */
Route::group(['prefix' => 'profile', 'as' => 'profile.', 'middleware' => ['auth']], function () {
    Route::get('/edit', [ProfilesController::class, 'edit'])->name('edit');
    Route::put('/update', [ProfilesController::class, 'update'])->name('update');
});

Route::get('/locale/{lang}', [LocaleController::class, 'switch'])->name('locale.switch');

/**
 * Public family invitation acceptance routes (no auth).
 */
Route::get('/family/invite/{token}', [FamilyInviteController::class, 'show'])->name('family.invite.show');
Route::post('/family/invite/{token}', [FamilyInviteController::class, 'accept'])->name('family.invite.accept');


