<?php

declare(strict_types=1);

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Traits\HasFamilyScope;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminCategoryController extends Controller
{
    use HasFamilyScope;

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name'      => ['required', 'string', 'max:255'],
            'is_family' => ['nullable', 'boolean'],
        ]);

        $familyMember = Auth::user()->familyMember;

        Category::create([
            'name'      => $data['name'],
            'user_id'   => Auth::id(),
            'family_id' => ($data['is_family'] ?? false) ? ($familyMember?->family_id) : null,
        ]);

        return back()->with('success', 'Category created.');
    }

    public function update(Request $request, int $id): RedirectResponse
    {
        $category = Category::findOrFail($id);

        if (!$this->isSuperadmin() && $category->user_id !== Auth::id()) {
            return back()->with('error', 'You can only edit your own categories.');
        }

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
        ]);

        $category->update(['name' => $data['name']]);

        return back()->with('success', 'Category updated.');
    }

    public function destroy(int $id): RedirectResponse
    {
        $category = Category::findOrFail($id);

        if (!$this->isSuperadmin() && $category->user_id !== Auth::id()) {
            return back()->with('error', 'You can only delete your own categories.');
        }

        if ($category->user_id === null) {
            return back()->with('error', 'Default categories cannot be deleted.');
        }

        $category->delete();

        return back()->with('success', 'Category deleted.');
    }
}
