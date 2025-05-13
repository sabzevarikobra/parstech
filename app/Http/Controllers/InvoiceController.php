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
        // اعتبارسنجی اطلاعات پایه
        $request->validate([
            // فیلدهای دیگر...
            'invoice_items' => 'required|string',
        ]);
    
        $items = json_decode($request->invoice_items, true);

        // اعتبارسنجی آیتم‌ها (تعداد >0 و ...)
        if (empty($items) || !is_array($items)) {
            return back()->withErrors(['invoice_items' => 'حداقل یک محصول باید به فاکتور اضافه شود.']);
        }

        // ایجاد رکورد فاکتور
        $invoice = Invoice::create([
            // فیلدهای دیگر...
        ]);

        // ذخیره اقلام فاکتور
        foreach ($items as $item) {
            $invoice->items()->create([
                'item_id' => $item['id'],
                'type' => $item['type'],
                'name' => $item['name'],
                'code' => $item['code'],
                'count' => $item['count'],
                'unit_price' => $item['sale_price'],
                // فیلدهای دیگر...
            ]);
        }

        return redirect()->route('invoices.show', $invoice->id)->with('success', 'فاکتور با موفقیت ثبت شد.');
    }

    // اگر متد show نداری حتما اضافه کن تا فاکتور نمایش داده شود
    public function show($id)
    {
        $invoice = Invoice::with(['items.product', 'customer'])->findOrFail($id);
        return view('invoices.show', compact('invoice'));
    }

}
