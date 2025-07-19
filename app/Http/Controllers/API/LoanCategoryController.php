<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\{LoanCategory, Loan, LoanRepayment, LoanContribution, Family};
use Illuminate\Support\Facades\Auth;

class LoanCategoryController extends Controller
{
    public function index()
    {
        $categories = LoanCategory::all();
        return $this->success('Loan categories retrieved successfully', $categories);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|unique:loan_categories,name',
        ]);

        $category = LoanCategory::create([
            'name' => $request->name,
            'user_id' => Auth::id()
        ]);

        return $this->success('Category created successfully', $category);
    }

    public function update(Request $request, $id)
    {
        $category = LoanCategory::findOrFail($id);

        $request->validate([
            'name' => 'required|string|unique:loan_categories,name,' . $id,
        ]);

        $category->update([
            'name' => $request->name,
            'user_id' => Auth::id()
        ]);

        return $this->success('Category updated successfully', $category);
    }

    public function destroy($id)
    {
        $category = LoanCategory::findOrFail($id);
        $category->delete();

        return $this->success('Category deleted successfully');
    }
}
