<!DOCTYPE html>
<html lang="fa">
<head>
    <meta charset="UTF-8">
    <title>چاپ فاکتور #{{ $invoice->invoice_number }}</title>
    <style>
        body { font-family: Tahoma, sans-serif; direction: rtl; }
        .invoice-box { max-width: 900px; margin: 0 auto; padding: 30px; border: 1px solid #eee; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 25px; }
        table, th, td { border: 1px solid #dedede; }
        th, td { padding: 8px; text-align: center; }
        .total { font-weight: bold; background: #f5f5f5; }
    </style>
</head>
<body onload="window.print()">
    <div class="invoice-box">
        <h2>فاکتور فروش شماره {{ $invoice->invoice_number }}</h2>
        <p>
            <b>مشتری:</b> {{ $invoice->customer->name ?? '-' }}<br>
            <b>تاریخ:</b> {{ jdate($invoice->date)->format('Y/m/d') }}<br>
            <b>فروشنده:</b> {{ $invoice->seller->name ?? '-' }}<br>
            <b>سررسید:</b> {{ jdate($invoice->due_date)->format('Y/m/d') }}
        </p>
        <table>
            <thead>
                <tr>
                    <th>کالا</th>
                    <th>تعداد</th>
                    <th>قیمت واحد</th>
                    <th>مجموع</th>
                </tr>
            </thead>
            <tbody>
                @foreach($invoice->items as $item)
                    <tr>
                        <td>{{ $item->product->name ?? '-' }}</td>
                        <td>{{ $item->qty }}</td>
                        <td>{{ number_format($item->price) }}</td>
                        <td>{{ number_format($item->total) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        <table>
            <tr>
                <td>جمع کل</td>
                <td>{{ number_format($invoice->total_amount) }} ریال</td>
            </tr>
            <tr>
                <td>تخفیف</td>
                <td>{{ number_format($invoice->discount_amount + ($invoice->total_amount * $invoice->discount_percent / 100)) }} ریال</td>
            </tr>
            <tr>
                <td>مالیات</td>
                <td>{{ number_format(($invoice->total_amount - ($invoice->discount_amount + ($invoice->total_amount * $invoice->discount_percent / 100))) * $invoice->tax_percent / 100 ) }} ریال</td>
            </tr>
            <tr class="total">
                <td>مبلغ نهایی</td>
                <td>{{ number_format($invoice->final_amount) }} ریال</td>
            </tr>
        </table>
        <p style="margin-top:40px; text-align:left; font-size:12px;">
            صادر شده توسط سیستم مدیریت فروش پارس‌تک
        </p>
    </div>
</body>
</html>
