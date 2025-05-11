<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Product;
use App\Models\Person;
use App\Models\Currency;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InvoiceController extends Controller
{
    // نمایش لیست فاکتورها
    public function index()
    {
        $invoices = Invoice::with('customer', 'seller', 'currency')->orderByDesc('created_at')->paginate(20);
        return view('invoices.index', compact('invoices'));
    }

    // فرم صدور فاکتور جدید
    public function create()
    {
        $currencies = Currency::all();
        $nextInvoiceNumber = Invoice::max('invoice_number') + 1 ?? 1001;
        return view('invoices.create', compact('currencies', 'nextInvoiceNumber'));
    }

    // ذخیره‌سازی فاکتور جدید و کم کردن موجودی انبار
    public function store(Request $request)
    {
        $request->validate([
            'invoiceNumber'   => 'required|unique:invoices,invoice_number',
            'date'            => 'required|date',
            'dueDate'         => 'required|date',
            'customer_id'     => 'required|exists:persons,id',
            'currency_id'     => 'required|exists:currencies,id',
            'seller'          => 'required|exists:users,id',
            'products'        => 'required|array|min:1',
            'products.*.id'   => 'required|exists:products,id',
            'products.*.qty'  => 'required|integer|min:1',
            'products.*.price'=> 'required|numeric|min:0',
            'discount_amount' => 'nullable|numeric|min:0',
            'discount_percent'=> 'nullable|numeric|min:0|max:100',
            'tax_percent'     => 'nullable|numeric|min:0|max:100'
        ], [
            'products.required' => 'حداقل یک محصول باید انتخاب شود.'
        ]);

        DB::beginTransaction();
        try {
            // بررسی موجودی محصولات
            foreach ($request->products as $item) {
                $product = Product::find($item['id']);
                if ($product->inventory < $item['qty']) {
                    return back()->withInput()->withErrors([
                        'products' => "موجودی محصول «{$product->name}» کافی نیست. موجودی فعلی: {$product->inventory}"
                    ]);
                }
            }

            // ایجاد فاکتور
            $invoice = Invoice::create([
                'invoice_number'  => $request->invoiceNumber,
                'date'            => $request->date,
                'due_date'        => $request->dueDate,
                'customer_id'     => $request->customer_id,
                'currency_id'     => $request->currency_id,
                'seller_id'       => $request->seller,
                'reference'       => $request->reference,
                'discount_amount' => $request->discount_amount ?: 0,
                'discount_percent'=> $request->discount_percent ?: 0,
                'tax_percent'     => $request->tax_percent ?: 0,
                'status'          => 'draft', // وضعیت اولیه: پیش‌نویس
                'total_amount'    => 0, // بعداً محاسبه می‌شود
                'final_amount'    => 0, // بعداً محاسبه می‌شود
            ]);

            $total = 0;
            foreach ($request->products as $item) {
                $product = Product::find($item['id']);
                // کم کردن موجودی محصول
                $product->inventory -= $item['qty'];
                $product->save();

                // درج آیتم فاکتور
                InvoiceItem::create([
                    'invoice_id' => $invoice->id,
                    'product_id' => $product->id,
                    'qty'        => $item['qty'],
                    'price'      => $item['price'],
                    'total'      => $item['qty'] * $item['price'],
                ]);
                $total += $item['qty'] * $item['price'];
            }

            // محاسبه مالیات و تخفیف و مبلغ نهایی
            $discount = $request->discount_amount ?: 0;
            if ($request->discount_percent) {
                $discount += ($total * $request->discount_percent) / 100;
            }
            $tax = $request->tax_percent ? ($total - $discount) * $request->tax_percent / 100 : 0;
            $final = $total - $discount + $tax;

            // به روزرسانی مبلغ فاکتور
            $invoice->update([
                'total_amount' => $total,
                'final_amount' => $final
            ]);

            DB::commit();

            return redirect()->route('invoices.index')->with('success', 'فاکتور با موفقیت ثبت شد و موجودی انبار به‌روزرسانی شد.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->withErrors(['general' => 'خطا در ثبت فاکتور: ' . $e->getMessage()]);
        }
    }

    // نمایش جزئیات فاکتور
    public function show($id)
    {
        $invoice = Invoice::with('items.product', 'customer', 'seller', 'currency')->findOrFail($id);
        return view('invoices.show', compact('invoice'));
    }

    // چاپ فاکتور (PDF یا صفحه چاپ)
    public function print($id)
    {
        $invoice = Invoice::with('items.product', 'customer', 'seller', 'currency')->findOrFail($id);
        // برای نمایش صفحه چاپ یا تولید PDF
        return view('invoices.print', compact('invoice'));
    }

    // تغییر وضعیت فاکتور به پرداخت‌شده
    public function pay($id)
    {
        $invoice = Invoice::findOrFail($id);
        $invoice->update(['status' => 'paid']);
        return back()->with('success', 'وضعیت فاکتور به پرداخت‌شده تغییر یافت.');
    }
}
