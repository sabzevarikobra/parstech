<?php

namespace App\Http\Controllers;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InvoiceController extends Controller
{
    public function create()
    {
        $currencies = \App\Models\Currency::orderBy('title')->get();
        return view('invoices.create', compact('currencies'));
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
        $request->validate([
            'invoiceNumber' => 'required|string|unique:invoices,number',
            'date' => 'required|string',
            'dueDate' => 'required|string',
            'customer_id' => 'required|integer|exists:persons,id',
            'seller'      => 'nullable|integer|exists:sellers,id',
            'currency_id' => 'required|integer|exists:currencies,id',
            'discount_percent' => 'nullable|numeric|min:0|max:100',
            'discount_amount'  => 'nullable|numeric|min:0',
            'tax_percent'      => 'nullable|numeric|min:0|max:100',
            'products'   => 'required|array|min:1',
            'products.*.qty' => 'required|numeric|min:1',
            'products.*.price' => 'required|numeric|min:0',
        ], [
            'products.required' => 'حداقل یک محصول باید انتخاب شود.',
            'customer_id.required' => 'مشتری را انتخاب کنید.'
        ]);


        // اعتبارسنجی داده‌ها
    $validated = $request->validate([
        'invoice_number' => 'required|numeric|unique:invoices,invoice_number',
        'date'           => 'required|date',
        'due_date'       => 'required|date',
        'customer_id'    => 'required|exists:people,id',
        'currency_id'    => 'required|exists:currencies,id',
        'seller_id'      => 'required|exists:users,id',
        // سایر فیلدهای مورد نیاز...
    ]);

    // ساخت فاکتور
    $invoice = Invoice::create($validated);

    // ذخیره آیتم‌های فاکتور (در صورت ارسال)
    if ($request->has('items')) {
        foreach ($request->items as $item) {
            $invoice->items()->create([
                'product_id' => $item['product_id'],
                'qty'        => $item['qty'],
                'price'      => $item['price'],
                'total'      => $item['total'],
            ]);
        }
    }

    return redirect()->route('invoices.index')->with('success', 'فاکتور با موفقیت ثبت شد.');



        DB::beginTransaction();
        try {
            // محاسبه مبلغ‌ها
            $items = [];
            $total = 0;
            foreach ($request->products as $id => $row) {
                $qty = floatval($row['qty']);
                $price = floatval($row['price']);
                $total += $price * $qty;
                $items[] = [
                    'product_id' => $id,
                    'price' => $price,
                    'qty'   => $qty,
                ];
            }

            $discount_amount = $request->discount_amount ? floatval($request->discount_amount) : 0;
            $discount_percent = $request->discount_percent ? floatval($request->discount_percent) : 0;
            if ($discount_amount <= 0 && $discount_percent > 0) {
                $discount_amount = ($total * $discount_percent) / 100;
            }
            $after_discount = $total - $discount_amount;
            $tax_percent = $request->tax_percent ? floatval($request->tax_percent) : 0;
            $tax_amount = ($after_discount * $tax_percent) / 100;
            $final_amount = $after_discount + $tax_amount;

            $invoice = Invoice::create([
                'number' => $request->invoiceNumber,
                'date' => $request->date,
                'due_date' => $request->dueDate,
                'customer_id' => $request->customer_id,
                'seller_id'   => $request->seller,
                'currency_id' => $request->currency_id,
                'discount_percent' => $discount_percent,
                'discount_amount'  => $discount_amount,
                'tax_percent'      => $tax_percent,
                'tax_amount'       => $tax_amount,
                'total' => $total,
                'final_amount' => $final_amount,
            ]);

            // ذخیره ردیف‌های فاکتور و کم کردن موجودی انبار
            foreach ($items as $item) {
                InvoiceItem::create([
                    'invoice_id' => $invoice->id,
                    'product_id' => $item['product_id'],
                    'price'      => $item['price'],
                    'qty'        => $item['qty'],
                ]);
                // کم کردن موجودی انبار
                $product = Product::find($item['product_id']);
                if ($product) {
                    $product->stock = max(0, $product->stock - $item['qty']);
                    $product->save();
                }
            }

            DB::commit();
            // ریدایرکت به صفحه نمایش فاکتور
            return redirect()->route('invoices.show', $invoice->id)->with('success', 'فاکتور با موفقیت ثبت شد.');
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'خطا در ثبت فاکتور، لطفا دوباره تلاش کنید. ' . $e->getMessage()]);
        }
    }

    // اگر متد show نداری حتما اضافه کن تا فاکتور نمایش داده شود
    public function show($id)
    {
        $invoice = Invoice::with(['items.product', 'customer'])->findOrFail($id);
        return view('invoices.show', compact('invoice'));
    }
    public function index()
{
    $invoices = \App\Models\Invoice::with(['customer'])->orderBy('id', 'desc')->paginate(15);
    return view('invoices.index', compact('invoices'));
}
}
