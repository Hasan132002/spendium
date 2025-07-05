<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\{LoanCategory, Loan, LoanRepayment, LoanContribution, Family};
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class LoanCategoryController extends Controller
{
    public function index()
    {
        return response()->json(LoanCategory::all());
    }

    public function store(Request $request)
    {
        $request->validate(['name' => 'required|string|unique:loan_categories,name']);
        
        $category = LoanCategory::create(['name' => $request->name,'user_id' => Auth::id()]);
        return response()->json(['message' => 'Category created', 'category' => $category]);
    }

    public function update(Request $request, $id)
    {
        $category = LoanCategory::findOrFail($id);
        $request->validate(['name' => 'required|string|unique:loan_categories,name,' . $id]);
        $category->update(['name' => $request->name, 'user_id' => Auth::id()]);
        return response()->json(['message' => 'Category updated']);
    }

    public function destroy($id)
    {
        $category = LoanCategory::findOrFail($id);
        $category->delete();
        return response()->json(['message' => 'Category deleted']);
    }
}
