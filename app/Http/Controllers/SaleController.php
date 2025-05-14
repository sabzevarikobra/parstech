<?php

namespace App\Http\Controllers;

use App\Models\Seller;
use App\Models\Product;
use Illuminate\Http\Request;
use Morilog\Jalali\CalendarUtils;
use Morilog\Jalali\Jalalian;

class SaleController extends Controller
{

    public function create()
    {

        $issued_at_miladi = Jalalian::fromFormat('Y/m/d - H:i', $request->input('issued_at'))->toCarbon();
        $due_at_miladi = Jalalian::fromFormat('Y/m/d', $request->input('due_at'))->toCarbon();

        $sellers = Seller::all(); // لیست فروشندگان
        $products = Product::all(); // لیست محصولات

        return view('sales.create', compact('sellers', 'products'));
    }
}
