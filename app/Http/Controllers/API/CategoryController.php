<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Category;
use App\Models\Family;

class CategoryController extends Controller
{
    public function store(Request $request)
    {
        $request->validate(['name' => 'required|string|unique:categories,name']);

        $category = Category::create([
            'name' => $request->name,
            'user_id' => auth()->id()
        ]);

        return response()->json(['message' => 'Category created', 'category' => $category]);
    }

    public function index()
    {
        $defaultCategories = Category::whereNull('user_id')->get();
        $userCategories = Category::where('user_id', auth()->id())->get();

        return response()->json([
            'default' => $defaultCategories,
            'custom' => $userCategories
        ]);
    }
    public function update(Request $request, $id)
{
    $category = Category::findOrFail($id);
    // dd($request->toArray());
    $category->update($request->only('name'));
    return response()->json(['message' => 'Category updated', 'category' => $category]);
}

public function destroy($id)
{
    $category = Category::findOrFail($id);
    $category->delete();
    return response()->json(['message' => 'Category deleted']);
}

}