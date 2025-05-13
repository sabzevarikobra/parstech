<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Product;
use App\Models\Currency; // ← این خط مهم است
use App\Models\Person;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Morilog\Jalali\Jalalian;

class InvoiceController extends Controller
{
    public function newForm()
{
    $sellers = Seller::all();
    $currencies = Currency::all();
    // داده‌های دیگر...
    return view('sales.create', compact('sellers', 'currencies'));
}


    public function getNextNumber()
    {
        $last = Invoice::where('number', 'LIKE', 'invoices-%')
            ->whereRaw("number REGEXP '^invoices-[0-9]+$'")
            ->orderByRaw("CAST(SUBSTRING(number, 10) AS UNSIGNED) DESC")
            ->first();

        if ($last) {
            $lastNum = intval(substr($last->number, 9));
            $next = $lastNum + 1;
        } else {
            $next = 10001;
        }
        return response()->json(['number' => "invoices-$next"]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_id' => 'required|exists:persons,id',
            'seller_id' => 'required|exists:sellers,id',
            'currency_id' => 'required|exists:currencies,id',
            'invoice_number' => 'required',
            'date' => 'required',
            'due_date' => 'required',
            'products' => 'required|array',
            'products.*.product_id' => 'required|exists:products,id',
            'products.*.quantity' => 'required|numeric|min:1',
            'products.*.unit_price' => 'required|numeric|min:0',
            // بقیه فیلدها
        ]);
        $invoice = Invoice::create([
            // داده‌ها از request
            'invoice_number' => $validated['invoice_number'],
            'date' => $validated['date'],
            'due_date' => $validated['due_date'],
            'customer_id' => $validated['customer_id'],
            'seller_id' => $validated['seller_id'],
            'currency_id' => $validated['currency_id'],
            'reference' => $request->input('reference'),
            'discount_amount' => $request->input('discount_amount', 0),
            'discount_percent' => $request->input('discount_percent', 0),
            'tax_percent' => $request->input('tax_percent', 0),
            'total_amount' => $request->input('total_amount', 0),
            'final_amount' => $request->input('final_amount', 0),
        ]);
        // ذخیره آیتم‌ها
        foreach ($validated['products'] as $item) {
            $invoice->items()->create([
                'product_id' => $item['product_id'],
                'quantity' => $item['quantity'],
                'unit_price' => $item['unit_price'],
                'total' => $item['quantity'] * $item['unit_price'],
            ]);
        }
        return redirect()->route('invoices.create')->with('success', 'فاکتور ثبت شد.');
    }

    // اگر متد show نداری حتما اضافه کن تا فاکتور نمایش داده شود
    public function show($id)
    {
        $invoice = Invoice::with(['items.product', 'customer'])->findOrFail($id);
        return view('invoices.show', compact('invoice'));
    }

}
