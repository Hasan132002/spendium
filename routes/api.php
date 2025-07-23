<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\WhatsappCallbackController;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\FamilyController;
use App\Http\Controllers\API\ExpenseController;
use App\Http\Controllers\API\BudgetController;
use App\Http\Controllers\API\CategoryController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\ReactionController;
use App\Http\Controllers\FollowController;
use App\Http\Controllers\AIController;

use App\Http\Controllers\API\{
    FundRequestController,
    LoanCategoryController,
    LoanController,
    LoanRepaymentController,
    LoanContributionController,
    GoalController,
    SavingsController
};
/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// Route::middleware('auth:api')->get('/user', function (Request $request) {
//     return $request->user();
// });

// API endpoint to get translations for a specific language
Route::get('/translations/{lang}', function (string $lang) {
    $path = resource_path("lang/{$lang}.json");

    if (!file_exists($path)) {
        return response()->json(['error' => 'Language not found'], 404);
    }

    $translations = json_decode(file_get_contents($path), true);
    return response()->json($translations);
});

Route::post('/whatsapp/callback', [WhatsappCallbackController::class, 'handle']);

Route::post('/store-embedding', [AIController::class, 'storeEmbedding']);


// Auth Routes for Android App
Route::group(['prefix' => 'auth'], function () {
    Route::post('register', [AuthController::class, 'register']);
    Route::post('login', [AuthController::class, 'login']);
    Route::post('forgot-password', [AuthController::class, 'forgotPassword']);
    Route::post('verify-otp', [AuthController::class, 'verifyOtp']);
    Route::post('reset-password', [AuthController::class, 'resetPassword']);
    Route::get('/users/non-fathers', [AuthController::class, 'getNonFatherUsers']);

    Route::middleware('auth:api')->group(function () {
        Route::post('change-password', [AuthController::class, 'changePassword']);
        Route::post('logout', [AuthController::class, 'logout']);
        Route::get('profile', [AuthController::class, 'profile']);
    });
});

Route::middleware('auth:api')->group(function () {

    Route::prefix('budget')->group(function () {
        Route::post('/family', [BudgetController::class, 'createFamilyBudget']);
        Route::post('/assign', [BudgetController::class, 'assignToMember']);
        Route::get('/family', [BudgetController::class, 'familyBudget']);
        Route::get('/assigned', [BudgetController::class, 'assignedBudgets']);
    });

    Route::prefix('expenses')->group(function () {
        Route::post('/add', [ExpenseController::class, 'store']);
        Route::get('/my', [ExpenseController::class, 'myExpenses']);
        Route::get('/family', [ExpenseController::class, 'familyExpenses']);
        Route::post('/approve/{id}', [ExpenseController::class, 'approveExpense']);
    });


    Route::prefix('family')->group(function () {
        Route::post('/create', [FamilyController::class, 'create']);
        Route::post('/invite', [FamilyController::class, 'inviteMember']);
        Route::post('/accept-invitation', [FamilyController::class, 'acceptInvitation']);
        Route::get('/my-invitations', [FamilyController::class, 'showMyInvitations']);

        Route::get('/members', [FamilyController::class, 'listMembers']);
        Route::get('/hasFamily', [FamilyController::class, 'hasCreatedFamily']);
    });

    Route::prefix('categories')->group(function () {
        Route::post('/add', [CategoryController::class, 'store']);
        Route::get('/all', [CategoryController::class, 'index']);
        Route::put('/update/{id}', [CategoryController::class, 'update']);
        Route::delete('/delete/{id}', [CategoryController::class, 'destroy']);
    });

    Route::prefix('fund-request')->group(function () {
        Route::post('/ask', [FundRequestController::class, 'ask']);
        Route::get('/my', [FundRequestController::class, 'myRequests']);
        Route::post('/approve/{id}', [FundRequestController::class, 'approve']); // optional amount
        Route::post('/decline/{id}', [FundRequestController::class, 'decline']);
        Route::get('/funds/all', [FundRequestController::class, 'allFamilyRequests']);
    });
    Route::prefix('loan-categories')->group(function () {
        Route::get('/', [LoanCategoryController::class, 'index']);
        Route::post('/create', [LoanCategoryController::class, 'store']);
        Route::put('/update/{id}', [LoanCategoryController::class, 'update']);
        Route::delete('/delete/{id}', [LoanCategoryController::class, 'destroy']);
    });

    // Loans (Father-only)
    Route::prefix('loans')->group(function () {
        Route::post('/create', [LoanController::class, 'store']);
        Route::get('/', [LoanController::class, 'index']);
        Route::get('/{id}', [LoanController::class, 'show']);
    });

    // Repayments (Father-only)
    Route::prefix('loan-repayments')->group(function () {
        Route::post('/add', [LoanRepaymentController::class, 'store']);
        Route::get('/loan/{loan_id}', [LoanRepaymentController::class, 'byLoan']);
    });

    // Contributions (Child/Mother)
    Route::prefix('loan-contributions')->group(function () {
        Route::post('/offer', [LoanContributionController::class, 'store']);
        Route::get('/my', [LoanContributionController::class, 'myContributions']);
        Route::post('/approve/{id}', [LoanContributionController::class, 'approve']);
        Route::post('/decline/{id}', [LoanContributionController::class, 'decline']);
    });

    // Goals (Family)
    Route::prefix('goals/family')->group(function () {
        Route::get('/', [GoalController::class, 'familyGoals']);
        Route::post('/create', [GoalController::class, 'createFamilyGoal']);
    });

    // Personal Goals (All Members)
    Route::prefix('goals/personal')->group(function () {
        Route::get('/', [GoalController::class, 'myGoals']);
        Route::post('/create', [GoalController::class, 'createMyGoal']);
        Route::put('/update/{id}', [GoalController::class, 'updateMyGoal']);
        Route::delete('/delete/{id}', [GoalController::class, 'deleteMyGoal']);
    });

    // Contribute to Goal
    Route::post('/goals/contribute', [GoalController::class, 'contributeToGoal']);
    Route::get('/goals/{id}/progress', [GoalController::class, 'goalProgress']);

    // Savings APIs
    Route::prefix('savings')->group(function () {
        Route::get('/my', [SavingsController::class, 'mySavings']);
        Route::post('/add', [SavingsController::class, 'addToSavings']);
        Route::get('/history', [SavingsController::class, 'savingsHistory']);
        Route::post('/transfer-to-goal', [SavingsController::class, 'transferToGoal']);
        Route::get('/end-of-month', [SavingsController::class, 'endOfMonthRollover']);
    });

    Route::get('posts', [PostController::class, 'index']);
    Route::get('posts/{post}', [PostController::class, 'show']);
    Route::post('posts', [PostController::class, 'store']);
    Route::post('posts/{post}', [PostController::class, 'update']);
    Route::delete('posts/{post}', [PostController::class, 'destroy']);

    Route::get('my-posts', [PostController::class, 'myPosts']);

    // Comment Routes
    Route::post('posts/{post}/comments', [CommentController::class, 'store']);
    Route::get('posts/{post}/comments', [CommentController::class, 'index']);

    // Reaction Routes
    Route::post('posts/{post}/like', [ReactionController::class, 'togglePostReaction']);
    Route::post('comments/{comment}/like', [ReactionController::class, 'toggleCommentReaction']);
    Route::post('/users/{user}/follow', [FollowController::class, 'toggleFollow']);
    Route::post('/users/{user}/unfollow', [FollowController::class, 'unfollow']);

    // List followers and followings
    Route::get('/users/{user}/followers', [FollowController::class, 'followers']);
    Route::get('/users/{user}/followings', [FollowController::class, 'followings']);
    // Route::get('/profile-stats/{id}', [FollowController::class, 'profileStats']);
    Route::get('users/{user}/profile-stats', [FollowController::class, 'profileStats']);


    
});
