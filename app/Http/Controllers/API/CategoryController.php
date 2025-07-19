<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Category;

class CategoryController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|unique:categories,name',
        ]);

        $category = Category::create([
            'name' => $request->name,
            'user_id' => auth()->id()
        ]);

        return $this->success('Category created', $category);
    }

    public function index()
    {
        $defaultCategories = Category::whereNull('user_id')->get();
        $userCategories = Category::where('user_id', auth()->id())->get();

        return $this->success('Categories fetched', [
            'default' => $defaultCategories,
            'custom' => $userCategories
        ]);
    }

    public function update(Request $request, $id)
    {
        $category = Category::findOrFail($id);

        $category->update($request->only('name'));

        return $this->success('Category updated', $category);
    }

    public function destroy($id)
    {
        $category = Category::findOrFail($id);
        $category->delete();

        return $this->success('Category deleted');
    }
}
