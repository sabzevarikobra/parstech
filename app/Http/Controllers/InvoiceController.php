<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Product;
use App\Models\Currency;
use App\Models\Person;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Morilog\Jalali\Jalalian;

class InvoiceController extends Controller
{
    // نمایش لیست فاکتورها
    public function index()
    {
        $invoices = Invoice::with(['customer'])->orderBy('id', 'desc')->paginate(15);
        return view('invoices.index', compact('invoices'));
    }

    // نمایش فرم صدور فاکتور
    public function create()
    {
        $currencies = Currency::orderBy('title')->get();
        $customers = Person::orderBy('first_name')->orderBy('last_name')->get();
        $products  = Product::orderBy('name')->get();

        // شماره بعدی فاکتور (اگر نیاز داری)
        $last = Invoice::where('invoice_number', 'LIKE', 'invoices-%')
            ->orderByRaw("CAST(SUBSTRING(invoice_number, 10) AS UNSIGNED) DESC")
            ->first();
        if ($last) {
            $lastNum = intval(substr($last->invoice_number, 9));
            $nextInvoiceNumber = "invoices-" . ($lastNum + 1);
        } else {
            $nextInvoiceNumber = "invoices-10001";
        }

        return view('invoices.create', compact('currencies', 'customers', 'products', 'nextInvoiceNumber'));
    }

    // گرفتن شماره بعدی فاکتور (برای ajax)
    public function getNextNumber()
    {
        $last = Invoice::where('invoice_number', 'LIKE', 'invoices-%')
            ->orderByRaw("CAST(SUBSTRING(invoice_number, 10) AS UNSIGNED) DESC")
            ->first();

        if ($last) {
            $lastNum = intval(substr($last->invoice_number, 9));
            $next = $lastNum + 1;
        } else {
            $next = 10001;
        }
        return response()->json(['number' => "invoices-$next"]);
    }

    // ذخیره فاکتور جدید
    public function store(Request $request)
    {
        $request->validate([
            'invoiceNumber'    => 'required|string|unique:invoices,invoice_number',
            'date'             => 'required|string',
            'dueDate'          => 'required|string',
            'customer_id'      => 'required|integer|exists:persons,id',
            'seller'           => 'nullable|integer',
            'currency_id'      => 'required|integer|exists:currencies,id',
            'discount_percent' => 'nullable|numeric|min:0|max:100',
            'discount_amount'  => 'nullable|numeric|min:0',
            'tax_percent'      => 'nullable|numeric|min:0|max:100',
            'products'         => 'required|array|min:1',
            'products.*.qty'   => 'required|numeric|min:1',
            'products.*.price' => 'required|numeric|min:0',
        ], [
            'products.required' => 'حداقل یک محصول باید انتخاب شود.',
            'customer_id.required' => 'مشتری را انتخاب کنید.'
        ]);

        try {
            $invoiceDate = Jalalian::fromFormat('Y/m/d', $request->date)->toCarbon();
            $dueDate    = Jalalian::fromFormat('Y/m/d', $request->dueDate)->toCarbon();
        } catch (\Exception $e) {
            return back()->withErrors(['date' => 'فرمت تاریخ معتبر نیست یا مقدار تاریخ خالی است.']);
        }

        DB::beginTransaction();
        try {
            $total = 0;
            $items = [];
            foreach ($request->products as $id => $row) {
                $qty   = floatval($row['qty']);
                $price = floatval($row['price']);
                $total += $qty * $price;
                $items[] = [
                    'product_id' => $id,
                    'qty'        => $qty,
                    'price'      => $price,
                    'total'      => $qty * $price,
                ];
            }

            $discount_amount  = $request->discount_amount  ? floatval($request->discount_amount)  : 0;
            $discount_percent = $request->discount_percent ? floatval($request->discount_percent) : 0;
            if ($discount_amount <= 0 && $discount_percent > 0) {
                $discount_amount = ($total * $discount_percent) / 100;
            }
            $after_discount = $total - $discount_amount;
            $tax_percent    = $request->tax_percent ? floatval($request->tax_percent) : 0;
            $tax_amount     = ($after_discount * $tax_percent) / 100;
            $final_amount   = $after_discount + $tax_amount;

            $invoice = Invoice::create([
                'invoice_number'    => $request->invoiceNumber,
                'date'              => $invoiceDate,
                'due_date'          => $dueDate,
                'customer_id'       => $request->customer_id,
                'seller_id'         => $request->seller,
                'currency_id'       => $request->currency_id,
                'reference'         => $request->reference,
                'discount_percent'  => $discount_percent,
                'discount_amount'   => $discount_amount,
                'tax_percent'       => $tax_percent,
                'tax_amount'        => $tax_amount,
                'total_amount'      => $total,
                'final_amount'      => $final_amount,
            ]);

            foreach ($items as $item) {
                InvoiceItem::create([
                    'invoice_id' => $invoice->id,
                    'product_id' => $item['product_id'],
                    'qty'        => $item['qty'],
                    'price'      => $item['price'],
                    'total'      => $item['total'],
                ]);
                // اگر می‌خواهی موجودی کم شود:
                $product = Product::find($item['product_id']);
                if ($product) {
                    $product->stock = max(0, $product->stock - $item['qty']);
                    $product->save();
                }
            }

            DB::commit();
            return redirect()->route('invoices.index')->with('success', 'فاکتور با موفقیت ثبت شد.');
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'خطا در ثبت فاکتور، لطفا دوباره تلاش کنید. ' . $e->getMessage()]);
        }
    }

    // نمایش فاکتور
    public function show($id)
    {
        $invoice = Invoice::with(['items.product', 'customer'])->findOrFail($id);
        return view('invoices.show', compact('invoice'));
    }

    // چاپ فاکتور
    public function print($id)
    {
        $invoice = Invoice::with(['items.product', 'customer'])->findOrFail($id);
        return view('invoices.print', compact('invoice'));
    }

    // تایید پرداخت فاکتور
    public function pay($id)
    {
        $invoice = Invoice::findOrFail($id);
        $invoice->status = 'paid';
        $invoice->save();
        return redirect()->route('invoices.index')->with('success', 'پرداخت فاکتور تایید شد.');
    }
}
