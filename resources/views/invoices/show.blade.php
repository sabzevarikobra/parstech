@extends('layouts.app')
@section('title', 'جزئیات فاکتور')

@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between mb-3">
        <h3>جزئیات فاکتور #{{ $invoice->invoice_number }}</h3>
        <a href="{{ route('invoices.index') }}" class="btn btn-link">بازگشت به لیست</a>
    </div>
    <div class="card mb-4 p-4">
        <div class="row mb-2">
            <div class="col-md-4"><b>مشتری:</b> {{ $invoice->customer->name ?? '-' }}</div>
            <div class="col-md-4"><b>تاریخ:</b> {{ jdate($invoice->date)->format('Y/m/d') }}</div>
            <div class="col-md-4"><b>وضعیت:</b>
                @if($invoice->status == 'paid')
                    <span class="badge badge-success">پرداخت‌شده</span>
                @else
                    <span class="badge badge-warning">پیش‌نویس</span>
                @endif
            </div>
        </div>
        <div class="row mb-2">
            <div class="col-md-4"><b>فروشنده:</b> {{ $invoice->seller->name ?? '-' }}</div>
            <div class="col-md-4"><b>سررسید:</b> {{ jdate($invoice->due_date)->format('Y/m/d') }}</div>
            <div class="col-md-4"><b>واحد پول:</b> {{ $invoice->currency->title ?? '-' }}</div>
        </div>
        <div class="row mb-2">
            <div class="col-md-12"><b>ارجاع:</b> {{ $invoice->reference ?? '-' }}</div>
        </div>
    </div>
    <table class="table table-bordered text-center">
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
    <div class="row mt-4">
        <div class="col-md-6"></div>
        <div class="col-md-6">
            <ul class="list-group">
                <li class="list-group-item d-flex justify-content-between">
                    <span>جمع کل:</span>
                    <span>{{ number_format($invoice->total_amount) }} ریال</span>
                </li>
                <li class="list-group-item d-flex justify-content-between">
                    <span>تخفیف:</span>
                    <span>{{ number_format($invoice->discount_amount + ($invoice->total_amount * $invoice->discount_percent / 100)) }} ریال</span>
                </li>
                <li class="list-group-item d-flex justify-content-between">
                    <span>مالیات:</span>
                    <span>{{ number_format(($invoice->total_amount - ($invoice->discount_amount + ($invoice->total_amount * $invoice->discount_percent / 100))) * $invoice->tax_percent / 100 ) }} ریال</span>
                </li>
                <li class="list-group-item d-flex justify-content-between">
                    <strong>مبلغ نهایی:</strong>
                    <strong>{{ number_format($invoice->final_amount) }} ریال</strong>
                </li>
            </ul>
        </div>
    </div>
    <div class="mt-4">
        <a href="{{ route('invoices.print', $invoice->id) }}" target="_blank" class="btn btn-secondary">چاپ فاکتور</a>
        @if($invoice->status != 'paid')
            <form action="{{ route('invoices.pay', $invoice->id) }}" method="POST" style="display:inline-block;">
                @csrf
                <button type="submit" class="btn btn-success">تایید پرداخت</button>
            </form>
        @endif
    </div>
</div>
@endsection
