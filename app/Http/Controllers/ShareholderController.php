<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Models\Seller;
use App\Models\Product;
use Illuminate\Http\Request;

class SaleController extends Controller
{
    public function index()
    {
        $sales = Sale::with(['seller', 'product'])->paginate(10);
        return view('sales.index', compact('sales'));
    }

    public function create()
{
    $sellers = Seller::all(); // لیست فروشندگان
    $products = Product::all(); // لیست محصولات

    return view('sales.create', compact('sellers', 'products'));
}

    public function store(Request $request)
    {
        $request->validate([
            'seller_id' => 'required|exists:sellers,id',
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
            'total_price' => 'required|numeric|min:0',
        ]);

        Sale::create($request->all());

        return redirect()->route('sales.index')->with('success', 'فروش با موفقیت ثبت شد.');
    }
}
