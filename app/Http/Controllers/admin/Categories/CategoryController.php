<?php

namespace App\Http\Controllers\Admin\Categories;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Services\ResourceDeletionRequestService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class CategoryController extends Controller
{

    public function pageAllcategory(Request $request)
    {
        $query = Category::query();

        if ($request->search) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        $categories = $query->latest()->paginate(5);

        return view('Admin.Categories.category-list', compact('categories'));
    }

    public function pageAddcategory()
    {
        return view('Admin.Categories.category-form');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'  => 'required|unique:categories|max:255',
            'image' => 'nullable|image|max:2048'
        ]);

        $data = $request->only('name', 'description');

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('categories', 'public');
        }

        Category::create($data);

        return redirect()->route('allcategory.index')
            ->with('success', 'Category Created!');
    }

    public function edit($id)
    {
        $category = Category::findOrFail($id);
        return view('Admin.Categories.category-form', compact('category'));
    }

    public function update(Request $request, $id)
    {
        $category = Category::findOrFail($id);

        $request->validate([
            'name'  => 'required|max:255|unique:categories,name,' . $id,
            'image' => 'nullable|image|max:2048',
        ]);

        $data = $request->only('name', 'description');

        if ($request->hasFile('image')) {
            if ($category->image && Storage::disk('public')->exists($category->image)) {
                Storage::disk('public')->delete($category->image);
            }

            $data['image'] = $request->file('image')->store('categories', 'public');
        }

        $category->update($data);

        return redirect()->route('allcategory.index')
            ->with('success', 'Category Updated Successfully!');
    }

    public function destroy($id)
    {
        $category = Category::findOrFail($id);

        $result = app(ResourceDeletionRequestService::class)->submit([
            'requester_id' => auth()->id(),
            'resource_type' => 'category',
            'resource_id' => $category->id,
            'resource_name' => $category->name,
            'payload' => ['context' => 'Category record'],
            'reason' => null,
        ]);

        if (!$result['created']) {
            return redirect()->route('allcategory.index')
                ->withErrors(['error' => 'A pending deletion request already exists for this category.']);
        }

        return redirect()->route('allcategory.index')
            ->with('success', 'Category deletion request submitted for admin approval.');
    }
}
