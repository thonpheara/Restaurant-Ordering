<?php

namespace App\Http\Controllers\Admin\Product;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Category;
use App\Services\ResourceDeletionRequestService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::with('category');
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%$search%")
                  ->orWhereHas('category', function ($q2) use ($search) {
                      $q2->where('name', 'like', "%$search%");
                  });
            });
        }
        $products = $query->latest()->paginate(5)->withQueryString();
        return view('Admin.Products.product-list', compact('products'));
    }

    public function create()
    {
        $categories = Category::all();
        return view('Admin.Products.product-form', compact('categories'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'        => 'required|max:255',
            'qty'         => 'required|integer|min:0',
            'price'       => 'required|numeric|min:0',
            'cost'        => 'required|numeric|min:0',
            'count'       => 'required|integer|min:0',
            'description' => 'nullable|string',
            'category_id' => 'required|exists:categories,id',
            'images'      => 'nullable|image|max:2048'
        ]);

        $data = $request->only(['name','qty','price','cost','count','description','category_id']);

        if ($request->hasFile('images')) {
            $path = $request->file('images')->store('products', 'public');
            $data['images'] = [$path];
        }

        Product::create($data);
        return redirect()->route('allproduct.index')->with('success', 'Product Created Successfully!');
    }

    public function edit($id)
    {
        $product = Product::findOrFail($id);
        $categories = Category::all();
        return view('Admin.Products.product-form', compact('product', 'categories'));
    }

    public function update(Request $request, $id)
    {
        $product = Product::findOrFail($id);
        $request->validate([
            'name'        => 'required|max:255',
            'qty'         => 'required|integer|min:0',
            'price'       => 'required|numeric|min:0',
            'cost'        => 'required|numeric|min:0',
            'count'       => 'required|integer|min:0',
            'description' => 'nullable|string',
            'category_id' => 'required|exists:categories,id',
            'images'      => 'nullable|image|max:2048'
        ]);

        $data = $request->only(['name','qty','price','cost','count','description','category_id']);

        if ($request->hasFile('images')) {
            if ($product->images && is_array($product->images)) {
                foreach ($product->images as $oldImg) {
                    Storage::disk('public')->delete($oldImg);
                }
            }
            $path = $request->file('images')->store('products', 'public');
            $data['images'] = [$path];
        }

        $product->update($data);
        return redirect()->route('allproduct.index')->with('success', 'Product Updated Successfully!');
    }

    public function destroy($id)
    {
        $product = Product::with('category')->findOrFail($id);

        $result = app(ResourceDeletionRequestService::class)->submit([
            'requester_id' => auth()->id(),
            'resource_type' => 'product',
            'resource_id' => $product->id,
            'resource_name' => $product->name,
            'payload' => ['context' => $product->category?->name ?: 'Product record'],
            'reason' => null,
        ]);

        if (!$result['created']) {
            return redirect()->route('allproduct.index')
                ->withErrors(['error' => 'A pending deletion request already exists for this product.']);
        }

        return redirect()->route('allproduct.index')
            ->with('success', 'Product deletion request submitted for admin approval.');
    }

    public function show($id)
    {
        $product = Product::findOrFail($id);
        return view('Admin.Products.product-show', compact('product'));
    }
}
