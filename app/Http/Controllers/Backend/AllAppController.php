<?php


declare(strict_types=1);

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Models\{Family, Budget, Expense, Category, FundRequest, FamilyMember,Loan, LoanCategory, LoanRepayment, LoanContribution, Goal, Saving, SavingsTransaction, Post, User, BudgetTransaction};
use Carbon\Carbon;

class AllAppController extends Controller
{

    public function familyBudget(Request $request)
    {
        $user = auth()->user();
        $family = Family::where('father_id', $user->id)->first();
        if (!$family) {
            return redirect()->back()->with('error', 'No family found for this user.');
        }

        $query = Budget::with(['user', 'category', 'family'])
            ->where('family_id', $family->id);

        if ($request->filled('family')) {
            $query->whereHas('user', function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->family . '%');
            });
        }

        $budgets = $query->get();

        return view('dashboard.family_budgets', compact('budgets', 'family'));
    }



public function assignedBudgets()
{
    $user = auth()->user();

    if (!$user) {
        return redirect()->route('login')->with('error', 'Please login to access this page.');
    }

    $family = Family::where('father_id', $user->id)->first();

    if ($family) {
        $budgets = Budget::with(['user:id,name,email', 'category:id,name'])
            ->where('family_id', $family->id)
            ->whereNotNull('user_id')
            ->get();
    } else {
        $budgets = Budget::with('category:id,name')
            ->where('user_id', $user->id)
            ->get();
    }

    return view('dashboard.assigned_budgets', compact('budgets'));
}

public function MyExpenses()
{
    $user = auth()->user();

    if (!$user) {
        return redirect()->route('login')->with('error', 'Please login to continue.');
    }

    $expenses = Expense::where('user_id', $user->id)
        ->with(['category:id,name', 'budget'])
        ->get();

    return view('dashboard.my_expenses', compact('expenses'));
}


   public function FamilyExpenses()
{
    $user = auth()->user();

    if (!$user) {
        return redirect()->route('login')->with('error', 'Please login to continue.');
    }

    $family = Family::where('father_id', $user->id)->first();

    if (!$family) {
        return redirect()->back()->with('error', 'No family found for this user.');
    }

    $memberIds = FamilyMember::where('family_id', $family->id)->pluck('user_id');

     $expenses = Expense::whereIn('user_id', $memberIds)
        ->with([
            'user:id,name',
            'category:id,name',
            'budget.category:id,name', 
        ])
        ->get();


    return view('dashboard.family_expenses', compact('expenses'));
}


   public function Members()
{
    $family = Family::where('father_id', auth()->id())->with([
        'members' => function ($query) {
            $query->where('status', 'accepted');
        },
        'members.user'
    ])->first();

    if (!$family) {
        return view('dashboard.members', ['members' => collect()]); // empty collection if no family
    }

    $members = $family->members;

    return view('dashboard.members', compact('members'));
}

  public function Categories()
{
    $defaultCategories = Category::whereNull('user_id')->get();
    $userCategories = Category::where('user_id', auth()->id())->get();

    return view('dashboard.categories', [
        'defaultCategories' => $defaultCategories,
        'userCategories' => $userCategories
    ]);
}



public function MyRequests()
{
    $requests = FundRequest::where('user_id', auth()->id())
        ->with('category')
        ->orderBy('created_at', 'desc')
        ->get();

    return view('dashboard.my_requests', compact('requests'));
}

public function FamilyRequests()
{
    $family = Family::where('father_id', auth()->id())->first();

    if (!$family) {
        return view('dashboard.family_requests', ['requests' => []]); // Ya koi message show karo
    }

    $requests = FundRequest::where('family_id', $family->id)
        ->with(['category:id,name', 'user:id,name,email'])
        ->orderBy('created_at', 'desc')
        ->get();

    return view('dashboard.family_requests', compact('requests'));
}

// 1. Loan Categories View
public function LoanCategories()
{
    $categories = LoanCategory::all();
    return view('dashboard.loan_categories', compact('categories'));
}

// 2. Loans View (with family filtering like API)
public function Loans()
{
    $family = Family::where('father_id', Auth::id())->firstOrFail();

    $loans = Loan::with('category')
        ->where('family_id', $family->id)
        ->get();

    return view('dashboard.loans', compact('loans'));
}

// 3. Loan Detail View
public function Loan($id)
{
    $loan = Loan::with(['repayments', 'contributions.user', 'category'])->findOrFail($id);
    return view('dashboard.loan_detail', compact('loan'));
}

// 4. Loan Repayments View
public function LoanRepayments($loan_id)
{
    $repayments = LoanRepayment::with('loan.category')
        ->where('loan_id', $loan_id)
        ->orderBy('date', 'desc')
        ->get();

    return view('dashboard.repayments', compact('repayments'));
}

// 5. My Contributions View (same family filter as API)
public function MyContributions()
{
    $user = Auth::user();
    $family = Family::where('father_id', $user->id)->first();

    if ($family) {
        $memberIds = $family->members()->pluck('user_id');

        $contributions = LoanContribution::with([
            'loan.category:id,name',
            'user:id,name'
        ])
        ->whereIn('user_id', $memberIds)
        ->orderBy('created_at', 'desc')
        ->get();
    } else {
        $contributions = LoanContribution::with([
            'loan.category:id,name',
            'user:id,name'
        ])
        ->where('user_id', $user->id)
        ->orderBy('created_at', 'desc')
        ->get();
    }

    return view('dashboard.contributions', compact('contributions'));
}


   public function FamilyGoals()
{
    $user = Auth::user();
    $family = Family::where('father_id', $user->id)->first();

    if ($family) {
        // Father → show all family goals
        $goals = Goal::with(['user:id,name', 'contributions'])
            ->where('type', 'family')
            ->where('family_id', $family->id)
            ->get()
            ->map(function ($goal) {
                $goal->collected_amount = $goal->contributions->sum('amount');
                return $goal;
            });
    } else {
        // Mother/Child → show own family goals
        $goals = Goal::with('contributions')
            ->where('type', 'family')
            ->where('user_id', $user->id)
            ->get()
            ->map(function ($goal) {
                $goal->collected_amount = $goal->contributions->sum('amount');
                return $goal;
            });
    }

    return view('dashboard.family_goals', compact('goals'));
}

public function MyGoals()
{
    $user = Auth::user();
    $family = $user->familyMember?->family ?? Family::where('father_id', $user->id)->first();

    if ($family && $family->father_id === $user->id) {
        // Father → all members' personal goals
        $memberIds = FamilyMember::where('family_id', $family->id)->pluck('user_id')->toArray();
        $memberIds[] = $user->id;

        $goals = Goal::with(['user:id,name', 'contributions'])
            ->whereIn('user_id', $memberIds)
            ->where('type', 'personal')
            ->get()
            ->map(function ($goal) {
                $goal->collected_amount = $goal->contributions->sum('amount');
                return $goal;
            });
    } else {
        // Mother/Child → their own goals
        $goals = Goal::with('contributions')
            ->where('user_id', $user->id)
            ->where('type', 'personal')
            ->get()
            ->map(function ($goal) {
                $goal->collected_amount = $goal->contributions->sum('amount');
                return $goal;
            });
    }

    return view('dashboard.personal_goals', compact('goals'));
}

   public function GoalProgress($id)
{
    $goal = Goal::with('contributions')->findOrFail($id);
    $contributed = $goal->contributions->sum('amount');
    $targetAmount = $goal->target_amount ?? 0;

    $progress = $targetAmount == 0 ? 0 : ($contributed / $targetAmount) * 100;

    return view('dashboard.goal_progress', [
        'goal' => $goal,
        'progress' => round($progress, 2),
        'contributed' => $contributed
    ]);
}

  public function Savings()
{
    $saving = Saving::firstOrCreate(['user_id' => Auth::id()], ['amount' => 0]);
    return view('dashboard.savings', compact('saving'));
}

public function SavingsHistory()
{
    $history = SavingsTransaction::where('user_id', Auth::id())
        ->orderBy('created_at', 'desc')
        ->get();

    return view('dashboard.savings_history', compact('history'));
}


public function Posts()
{
    $posts = Post::with(['user', 'comments.reactions', 'reactions'])
        ->withCount([
            'comments as comment_count',
            'reactions as like_count'
        ])
        ->latest()
        ->get()
        ->map(function ($post) {
            // Add reaction count to each comment
            $post->comments->map(function ($comment) {
                $comment->reaction_count = $comment->reactions->count();
                unset($comment->reactions); // remove raw data if not needed
                return $comment;
            });
            return $post;
        });

    return view('dashboard.posts', compact('posts'));
}

public function MyPosts()
{
    $user = Auth::user();
    $posts = $user->posts()
        ->with(['comments', 'reactions'])
        ->latest()
        ->get();

    return view('dashboard.my_posts', compact('posts'));
}

public function Post(Post $post)
{
    $post->load(['user', 'comments.user', 'reactions']);

    return view('dashboard.post_detail', compact('post'));
}


    public function Comments(Post $post)
{
    $comments = $post->comments()->with(['user', 'reactions'])->latest()->get();


    $comments->map(function ($comment) {
        $comment->reaction_count = $comment->reactions->count();
        return $comment;
    });

    return view('dashboard.comments', compact('post', 'comments'));
}

   public function Followers(User $user)
{
    $followers = $user->followers()->with('posts')->get();
    return view('dashboard.followers', compact('user', 'followers'));
}


    public function Followings(User $user)
{
    $followings = $user->followings()->with('posts')->get();
    return view('dashboard.followings', compact('user', 'followings'));
}


  public function profileStats(User $user)
{
    $followerCount = $user->followers()->count();
    $followingCount = $user->followings()->count();
    $uploadCount = $user->posts()->whereNotNull('photo')->count();
    $totalPosts = $user->posts()->count();

    $posts = $user->posts()
        ->with([
            'reactions',
            'comments' => function ($query) {
                $query->withCount('reactions')->with('user');
            }
        ])
        ->withCount(['reactions as likes_count', 'comments as comments_count'])
        ->latest()
        ->get();

    return view('dashboard.profile_stats', compact(
        'user',
        'totalPosts',
        'uploadCount',
        'followerCount',
        'followingCount',
        'posts'
    ));
}

     public function endOfMonthRolloverView()
    {
        $month = Carbon::now()->format('Y-m');

        $budgets = Budget::where('month', $month)->get();
        $totalRemaining = 0;

        foreach ($budgets as $budget) {
            $used = BudgetTransaction::where('budget_id', $budget->id)->sum('amount');
            $remaining = $budget->amount - $used;
            if ($remaining > 0) {
                $totalRemaining += $remaining;
            }
        }

        if ($totalRemaining > 0) {
            $saving = Saving::first();
            if ($saving) {
                $saving->amount += $totalRemaining;
                $saving->save();
            } else {
                $saving = Saving::create(['amount' => $totalRemaining]);
            }

            SavingsTransaction::create([
                'user_id' => auth()->id() ?? 1,
                'action' => 'add',
                'amount' => $totalRemaining,
                'source' => 'rollover',
            ]);
        }

        return view('dashboard.rollover_summary', [
            'total_remaining' => $totalRemaining,
            'month' => $month,
            'budgets' => $budgets,
        ]);
    }
}
