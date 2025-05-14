@extends('layouts.app')

@section('title', 'ثبت فروش جدید')

@section('content')
<div class="container">
    <h1>ثبت فروش جدید</h1>
    <form action="{{ route('sales.store') }}" method="POST">
        @csrf
        <div class="mb-3">
            <label for="seller_id" class="form-label">فروشنده</label>
            <select id="seller_id" name="seller_id" class="form-select" required>
                <option value="">انتخاب فروشنده</option>
                @foreach($sellers as $seller)
                <option value="{{ $seller->id }}">{{ $seller->first_name }} {{ $seller->last_name }}</option>
                @endforeach
            </select>
        </div>
        <div class="mb-3">
            <label for="product_id" class="form-label">محصول</label>
            <select id="product_id" name="product_id" class="form-select" required>
                <option value="">انتخاب محصول</option>
                @foreach($products as $product)
                <option value="{{ $product->id }}">{{ $product->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="mb-3">
            <label for="quantity" class="form-label">تعداد</label>
            <input type="number" id="quantity" name="quantity" class="form-control" required>
        </div>
        <div class="mb-3">
            <label for="total_price" class="form-label">قیمت کل</label>
            <input type="number" id="total_price" name="total_price" class="form-control" required>
        </div>
        <button type="submit" class="btn btn-success">ثبت</button>
    </form>
</div>
@endsection
