<?php

namespace App\Http\Controllers\Layouts;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use App\Models\Reservation;

class UserwebController extends Controller
{
    public function pages()
    {
        $categories = Category::withCount('products')->get();
        $products = Product::with('category')->get();
        $reservations = Reservation::latest()->take(100)->get()->map(fn($r) => [
            'name'  => $r->full_name,
            'table' => $r->table_id,
        ]);

        return view('layouts.userweb', compact('categories', 'products', 'reservations'));
    }

    public function tableOrder($tableNumber)
    {
        $categories = Category::withCount('products')->get();
        $products = Product::with('category')
            ->where('qty', '>', 0)
            ->get();

        return view('layouts.table-order', compact('categories', 'products', 'tableNumber'));
    }
}
