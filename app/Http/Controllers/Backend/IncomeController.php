<?php

declare(strict_types=1);

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Family;
use App\Models\FamilyMember;
use App\Models\Income;
use App\Traits\HasFamilyScope;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class IncomeController extends Controller
{
    use HasFamilyScope;

    public function myIncomes(Request $request): Renderable
    {
        $query = Income::where('user_id', Auth::id())->with('user')->latest('received_on');

        if ($request->filled('from')) {
            $query->whereDate('received_on', '>=', $request->from);
        }
        if ($request->filled('to')) {
            $query->whereDate('received_on', '<=', $request->to);
        }
        if ($request->filled('source')) {
            $query->where('source', $request->source);
        }

        $incomes = $query->paginate(15)->withQueryString();
        $totalForRange = (clone $query)->sum('amount');

        return view('dashboard.incomes.my', compact('incomes', 'totalForRange'));
    }

    public function familyIncomes(Request $request): Renderable|RedirectResponse
    {
        $user = Auth::user();
        $isHeadOrSuper = $this->isSuperadmin() || Family::where('father_id', $user->id)->exists();

        if (!$isHeadOrSuper) {
            return redirect()->route('admin.dashboard')->with('error', 'Only the family head can view family incomes.');
        }

        $familyIds = $this->familyIdsInScope();
        $memberIds = FamilyMember::whereIn('family_id', $familyIds)->pluck('user_id');

        $query = Income::whereIn('user_id', $memberIds)
            ->with(['user:id,name', 'family:id,name'])
            ->latest('received_on');

        if ($request->filled('family_id')) {
            $query->where('family_id', $request->family_id);
        }
        if ($request->filled('from')) {
            $query->whereDate('received_on', '>=', $request->from);
        }
        if ($request->filled('to')) {
            $query->whereDate('received_on', '<=', $request->to);
        }
        if ($request->filled('source')) {
            $query->where('source', $request->source);
        }
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        $incomes = $query->paginate(15)->withQueryString();
        $totalForRange = (clone $query)->sum('amount');

        $members = FamilyMember::with('user:id,name')->whereIn('family_id', $familyIds)->get();
        $families = $this->familiesInScope();

        return view('dashboard.incomes.family', compact('incomes', 'totalForRange', 'members', 'families'));
    }

    public function create(): Renderable
    {
        return view('dashboard.incomes.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'title'               => ['required', 'string', 'max:255'],
            'amount'              => ['required', 'numeric', 'min:0.01'],
            'source'              => ['required', 'in:salary,business,freelance,rental,investment,gift,other'],
            'received_on'         => ['required', 'date'],
            'note'                => ['nullable', 'string'],
            'recurring'           => ['nullable', 'boolean'],
            'recurrence_interval' => ['nullable', 'in:monthly,weekly,yearly'],
        ]);

        $user = Auth::user();
        $familyMember = $user->familyMember;

        Income::create([
            'user_id'             => $user->id,
            'family_id'           => $familyMember?->family_id,
            'title'               => $data['title'],
            'amount'              => $data['amount'],
            'source'              => $data['source'],
            'received_on'         => $data['received_on'],
            'note'                => $data['note'] ?? null,
            'recurring'           => (bool) ($data['recurring'] ?? false),
            'recurrence_interval' => $data['recurring'] ?? false ? ($data['recurrence_interval'] ?? 'monthly') : null,
        ]);

        return redirect()->route('admin.incomes.my')->with('success', 'Income recorded.');
    }

    public function destroy(int $id): RedirectResponse
    {
        $income = Income::where('user_id', Auth::id())->findOrFail($id);
        $income->delete();

        return back()->with('success', 'Income deleted.');
    }
}
