<?php


declare(strict_types=1);

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Traits\HasFamilyScope;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Models\{Family, Budget, Expense, Category, FundRequest, FamilyMember,Loan, LoanCategory, LoanRepayment, LoanContribution, Goal, Saving, SavingsTransaction, Post, User, BudgetTransaction};
use Carbon\Carbon;

class AllAppController extends Controller
{
    use HasFamilyScope;


    public function familyBudget(Request $request)
    {
        $familyIds = $this->familyIdsInScope();

        if (empty($familyIds)) {
            return redirect()->back()->with('error', 'No family found for this user.');
        }

        $query = Budget::with(['user', 'category', 'family'])
            ->whereIn('family_id', $familyIds);

        if ($request->filled('family_id')) {
            $query->where('family_id', $request->family_id);
        }

        if ($request->filled('family')) {
            $query->whereHas('user', function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->family . '%');
            });
        }

        $budgets = $query->get();
        $families = $this->familiesInScope();

        // For backwards compatibility with existing view
        $family = $families->first();

        return view('dashboard.family_budgets', compact('budgets', 'family', 'families'));
    }



public function assignedBudgets(Request $request)
{
    $user = auth()->user();

    if (!$user) {
        return redirect()->route('login')->with('error', 'Please login to access this page.');
    }

    if ($this->isSuperadmin() || Family::where('father_id', $user->id)->exists()) {
        $query = Budget::with(['user:id,name,email', 'category:id,name', 'family:id,name'])
            ->whereIn('family_id', $this->familyIdsInScope())
            ->whereNotNull('user_id');

        if ($request->filled('family_id')) {
            $query->where('family_id', $request->family_id);
        }
        $budgets = $query->get();
    } else {
        $budgets = Budget::with('category:id,name')
            ->where('user_id', $user->id)
            ->get();
    }

    $families = $this->familiesInScope();

    return view('dashboard.assigned_budgets', compact('budgets', 'families'));
}

public function MyExpenses(Request $request)
{
    $user = auth()->user();

    if (!$user) {
        return redirect()->route('login')->with('error', 'Please login to continue.');
    }

    $query = Expense::where('user_id', $user->id)
        ->with(['category:id,name', 'budget'])
        ->orderBy('date', 'desc');

    if ($request->filled('from')) {
        $query->whereDate('date', '>=', $request->from);
    }
    if ($request->filled('to')) {
        $query->whereDate('date', '<=', $request->to);
    }
    if ($request->filled('q')) {
        $query->where('title', 'like', '%' . $request->q . '%');
    }
    if ($request->filled('category_id')) {
        $query->where('category_id', $request->category_id);
    }

    $expenses = $query->paginate(20)->withQueryString();
    $categories = Category::whereNull('user_id')->orWhere('user_id', $user->id)->get();

    return view('dashboard.my_expenses', compact('expenses', 'categories'));
}


   public function FamilyExpenses(Request $request)
{
    $user = auth()->user();

    if (!$user) {
        return redirect()->route('login')->with('error', 'Please login to continue.');
    }

    $familyIds = $this->familyIdsInScope();

    if (empty($familyIds)) {
        return redirect()->back()->with('error', 'No family found for this user.');
    }

    $memberIds = FamilyMember::whereIn('family_id', $familyIds)->pluck('user_id');

    $query = Expense::whereIn('user_id', $memberIds)
        ->with([
            'user:id,name',
            'category:id,name',
            'budget.category:id,name',
        ])
        ->orderBy('date', 'desc');

    if ($request->filled('from')) {
        $query->whereDate('date', '>=', $request->from);
    }
    if ($request->filled('to')) {
        $query->whereDate('date', '<=', $request->to);
    }
    if ($request->filled('q')) {
        $query->where('title', 'like', '%' . $request->q . '%');
    }
    if ($request->filled('user_id')) {
        $query->where('user_id', $request->user_id);
    }
    if ($request->filled('approved')) {
        $query->where('approved', (bool) $request->approved);
    }

    $expenses = $query->paginate(20)->withQueryString();
    $members = FamilyMember::with('user:id,name')->whereIn('family_id', $familyIds)->get();

    return view('dashboard.family_expenses', compact('expenses', 'members'));
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
    $default = Category::whereNull('user_id')->get();
    $custom = Category::where('user_id', auth()->id())->get();

    return view('dashboard.categories', compact('default', 'custom'));
}



public function MyRequests(Request $request)
{
    $query = FundRequest::where('user_id', auth()->id())
        ->with('category')
        ->orderBy('created_at', 'desc');

    if ($request->filled('status')) {
        $query->where('status', $request->status);
    }
    if ($request->filled('from')) {
        $query->whereDate('created_at', '>=', $request->from);
    }
    if ($request->filled('to')) {
        $query->whereDate('created_at', '<=', $request->to);
    }

    $requests = $query->paginate(15)->withQueryString();

    return view('dashboard.my_requests', compact('requests'));
}

public function FamilyRequests(Request $request)
{
    $familyIds = $this->familyIdsInScope();

    if (empty($familyIds)) {
        return view('dashboard.family_requests', ['requests' => collect(), 'members' => collect()]);
    }

    $query = FundRequest::whereIn('family_id', $familyIds)
        ->with(['category:id,name', 'user:id,name,email', 'family:id,name'])
        ->orderBy('created_at', 'desc');

    if ($request->filled('family_id')) {
        $query->where('family_id', $request->family_id);
    }

    if ($request->filled('status')) {
        $query->where('status', $request->status);
    }
    if ($request->filled('user_id')) {
        $query->where('user_id', $request->user_id);
    }
    if ($request->filled('from')) {
        $query->whereDate('created_at', '>=', $request->from);
    }
    if ($request->filled('to')) {
        $query->whereDate('created_at', '<=', $request->to);
    }

    $requests = $query->paginate(15)->withQueryString();
    $members = FamilyMember::with('user:id,name')->whereIn('family_id', $familyIds)->get();

    return view('dashboard.family_requests', compact('requests', 'members'));
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
    $familyIds = $this->familyIdsInScope();

    if (empty($familyIds)) {
        return redirect()->route('admin.dashboard')->with('error', 'No family found.');
    }

    $loans = Loan::with(['category', 'family:id,name'])
        ->whereIn('family_id', $familyIds)
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
    $isHeadOrSuper = $this->isSuperadmin() || Family::where('father_id', $user->id)->exists();

    if ($isHeadOrSuper) {
        $memberIds = FamilyMember::whereIn('family_id', $this->familyIdsInScope())->pluck('user_id');

        $contributions = LoanContribution::with([
            'loan.category:id,name',
            'loan.family:id,name',
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
    $isHeadOrSuper = $this->isSuperadmin() || Family::where('father_id', $user->id)->exists();

    if ($isHeadOrSuper) {
        $goals = Goal::with(['user:id,name', 'family:id,name', 'contributions'])
            ->where('type', 'family')
            ->whereIn('family_id', $this->familyIdsInScope())
            ->get()
            ->map(function ($goal) {
                $goal->collected_amount = $goal->contributions->sum('amount');
                return $goal;
            });
    } else {
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
    $isHeadOrSuper = $this->isSuperadmin() || Family::where('father_id', $user->id)->exists();

    if ($isHeadOrSuper) {
        $memberIds = FamilyMember::whereIn('family_id', $this->familyIdsInScope())->pluck('user_id')->toArray();
        if (!in_array($user->id, $memberIds, true)) {
            $memberIds[] = $user->id;
        }

        $goals = Goal::with(['user:id,name', 'family:id,name', 'contributions'])
            ->whereIn('user_id', $memberIds)
            ->where('type', 'personal')
            ->get()
            ->map(function ($goal) {
                $goal->collected_amount = $goal->contributions->sum('amount');
                return $goal;
            });
    } else {
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
    $saving = Saving::with('transactions')->firstOrCreate(
        ['user_id' => Auth::id()],
        ['total' => 0]
    );

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

   public function endOfMonthRolloverView(Request $request)
{
    $user = Auth::user();
    $month = $request->input('month', Carbon::now()->format('Y-m'));

    $budgets = Budget::with('category', 'transactions')
        ->where('user_id', $user->id)
        ->where('month', $month)
        ->get();

    $totalRemaining = 0;

    foreach ($budgets as $budget) {
        $used = $budget->transactions->sum('amount');
        $remaining = $budget->amount - $used;
        if ($remaining > 0) {
            $totalRemaining += $remaining;
        }
    }

return view('dashboard.rollover_summary', [
    'total_remaining' => $totalRemaining,
    'month' => $month,
    'budgets' => $budgets,
]);
}
}
