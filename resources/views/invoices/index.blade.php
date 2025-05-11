@extends('layouts.app')

@section('title', 'لیست فاکتورها')

@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3>لیست فاکتورها</h3>
        <a href="{{ route('invoices.create') }}" class="btn btn-primary">صدور فاکتور جدید</a>
    </div>
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    <table class="table table-bordered table-striped text-center">
        <thead>
        <tr>
            <th>شماره</th>
            <th>مشتری</th>
            <th>تاریخ</th>
            <th>جمع کل</th>
            <th>وضعیت</th>
            <th>عملیات</th>
        </tr>
        </thead>
        <tbody>
        @forelse($invoices as $invoice)
            <tr>
                <td>{{ $invoice->invoice_number }}</td>
                <td>{{ $invoice->customer->name ?? '-' }}</td>
                <td>{{ jdate($invoice->date)->format('Y/m/d') }}</td>
                <td>{{ number_format($invoice->final_amount) }} ریال</td>
                <td>
                    @if($invoice->status == 'paid')
                        <span class="badge badge-success">پرداخت‌شده</span>
                    @else
                        <span class="badge badge-warning">پیش‌نویس</span>
                    @endif
                </td>
                <td>
                    <a href="{{ route('invoices.show', $invoice->id) }}" class="btn btn-info btn-sm">مشاهده</a>
                    <a href="{{ route('invoices.print', $invoice->id) }}" target="_blank" class="btn btn-secondary btn-sm">چاپ</a>
                    @if($invoice->status !== 'paid')
                        <form action="{{ route('invoices.pay', $invoice->id) }}" method="POST" style="display:inline-block;">
                            @csrf
                            <button type="submit" class="btn btn-success btn-sm">تایید پرداخت</button>
                        </form>
                    @endif
                </td>
            </tr>
        @empty
            <tr><td colspan="6">هیچ فاکتوری ثبت نشده است.</td></tr>
        @endforelse
        </tbody>
    </table>
    <div class="mt-3">
        {{ $invoices->links() }}
    </div>
</div>
@endsection
