<?php

namespace App\Http\Controllers;

use App\Models\Seller;
use App\Models\Product;
use Illuminate\Http\Request;

class SaleController extends Controller
{
    public function create()
    {
        $sellers = Seller::all(); // لیست فروشندگان
        $products = Product::all(); // لیست محصولات

        return view('sales.create', compact('sellers', 'products'));
    }
}
