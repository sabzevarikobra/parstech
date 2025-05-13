@extends('layouts.app')

@section('title', 'صدور فاکتور فروش جدید')
@push('styles')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/persian-datepicker@1.2.0/dist/css/persian-datepicker.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css">
    <link rel="stylesheet" href="{{ asset('css/invoice-create.css') }}">
@endpush

@section('content')
@if ($errors->any())
    <div class="alert alert-danger">
        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif
<div class="page-wrapper">
    <div class="sidebar">
        <h3>منو</h3>
        <nav>
            <ul>
                <li><a href="{{ route('dashboard') }}">داشبورد</a></li>
                <li><a href="{{ route('invoices.index') }}">لیست فاکتورها</a></li>
                <li><a href="{{ route('products.index') }}">محصولات</a></li>
                <li><a href="{{ route('persons.customers') }}">مشتریان</a></li>
            </ul>
        </nav>
    </div>
    <div class="main-content">
        <div class="container py-4">
            <div class="invoice-header">
                <h2 class="mb-4">صدور فاکتور فروش جدید</h2>
                <form action="{{ route('invoices.store') }}" method="POST">
                    @csrf

                    <div class="row">
                        <!-- شماره فاکتور -->
                        <div class="col-md-4 mb-3">
                            <div class="form-group">
                                <label for="invoiceNumber">شماره فاکتور</label>
                                <input type="text" id="invoiceNumber" name="invoiceNumber"
                                    class="form-control" value="{{ old('invoiceNumber', $nextInvoiceNumber ?? '') }}" readonly required>
                                <div class="input-group-append">
                                    <div class="input-group-text">
                                        <div class="custom-control custom-switch">
                                            <input type="checkbox" class="custom-control-input" id="invoiceNumberSwitch">
                                            <label class="custom-control-label" for="invoiceNumberSwitch">
                                                شماره‌گذاری دستی
                                            </label>
                                        </div>
                                    </div>
                                </div>
                                @error('invoiceNumber')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>
                        </div>

                        <!-- ارجاع -->
                        <div class="col-md-4 mb-3">
                            <div class="form-group">
                                <label for="reference">ارجاع</label>
                                <input type="text" id="reference" name="reference"
                                    class="form-control" placeholder="شماره ارجاع را وارد کنید" value="{{ old('reference') }}">
                                @error('reference')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>
                        </div>

                        <!-- تاریخ صدور -->
                        <div class="col-md-4 mb-3">
                            <div class="form-group">
                                <label class="form-label required-field">تاریخ صدور فاکتور</label>
                                <input type="text" name="date" id="date" class="form-control datepicker"
                                    value="{{ old('date') }}" required autocomplete="off">
                                <small class="form-text text-muted">تاریخ ثبت فاکتور (شمسی)</small>
                            </div>
                        </div>

                        <!-- تاریخ سررسید -->
                        <div class="col-md-4 mb-3">
                            <div class="form-group">
                                <label class="form-label required-field">تاریخ سررسید</label>
                                <input type="text" name="dueDate" id="dueDate" class="form-control datepicker"
                                    value="{{ old('dueDate') }}" required autocomplete="off">
                                <small class="form-text text-muted">موعد پرداخت فاکتور (شمسی)</small>
                            </div>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <!-- مشتری -->
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-label required-field" for="customer_select">مشتری</label>
                                <select id="customer_select" name="customer_id" class="form-control select2" required>
                                    <option value="">انتخاب مشتری...</option>
                                    @foreach($customers as $customer)
                                        <option value="{{ $customer->id }}" {{ old('customer_id') == $customer->id ? 'selected' : '' }}>
                                            {{ $customer->company_name ? $customer->company_name : ($customer->first_name . ' ' . $customer->last_name) }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <!-- واحد پول -->
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="currency_id" class="form-label required-field">واحد پول</label>
                                <select name="currency_id" id="currency_id" class="form-control select2" required>
                                    <option value="">انتخاب واحد پول ...</option>
                                    @foreach($currencies as $cur)
                                        <option value="{{ $cur->id }}" {{ old('currency_id') == $cur->id ? 'selected' : '' }}>
                                            {{ $cur->title }} {{ $cur->symbol ? '(' . $cur->symbol . ')' : '' }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <!-- فروشنده -->
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="seller" class="form-label required-field">فروشنده</label>
                                <select id="seller" name="seller" class="form-control select2" required>
                                    <option value="">انتخاب فروشنده...</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- جستجوی محصولات -->
                    <div class="product-search-container">
                        <label for="productSearch" class="form-label">جستجوی محصولات</label>
                        <input type="text" id="productSearch" class="form-control"
                               placeholder="نام یا کد محصول را وارد کنید...">
                        <div id="productList" class="product-list mt-2" style="display: none;"></div>
                    </div>

                    <!-- جدول محصولات انتخاب شده -->
                    <div class="table-responsive">
                        <table class="selected-products-table">
                            <thead>
                                <tr>
                                    <th>تصویر</th>
                                    <th>کد کالا</th>
                                    <th>نام محصول</th>
                                    <th>دسته‌بندی</th>
                                    <th>موجودی</th>
                                    <th>قیمت (ریال)</th>
                                    <th>تعداد</th>
                                    <th>مجموع</th>
                                    <th>عملیات</th>
                                </tr>
                            </thead>
                            <tbody id="selectedProducts"></tbody>
                        </table>
                    </div>

                    <!-- تخفیف و مالیات -->
                    <div class="discount-tax-section">
                        <div class="form-group">
                            <label for="tax_percent">درصد مالیات</label>
                            <input type="number" id="tax_percent" name="tax_percent"
                                   class="form-control" min="0" max="100" step="0.01" value="{{ old('tax_percent', 0) }}">
                        </div>
                        <div class="form-group">
                            <label for="discount_amount">مبلغ تخفیف</label>
                            <input type="number" id="discount_amount" name="discount_amount"
                                   class="form-control" min="0" step="1" value="{{ old('discount_amount', 0) }}">
                        </div>
                        <div class="form-group">
                            <label for="discount_percent">درصد تخفیف</label>
                            <input type="number" id="discount_percent" name="discount_percent"
                                   class="form-control" min="0" max="100" step="0.01" value="{{ old('discount_percent', 0) }}">
                        </div>
                    </div>

                    <!-- نمایش مجموع -->
                    <div class="amount-section">
                        <div class="amount-row">
                            <span>مالیات:</span>
                            <span><span id="taxAmount">۰</span> ریال</span>
                        </div>
                        <div class="amount-row">
                            <span>تخفیف:</span>
                            <span><span id="discountAmount">۰</span> ریال</span>
                        </div>
                        <div class="amount-row">
                            <span>جمع کل:</span>
                            <span><span id="totalAmount">۰</span> ریال</span>
                        </div>
                        <div class="amount-row">
                            <span>مبلغ نهایی:</span>
                            <span><span id="finalAmount">۰</span> ریال</span>
                        </div>
                    </div>

                    <div class="text-left mt-4">
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-save ml-2"></i>
                            ثبت فاکتور
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.6.0/dist/jquery.min.js"></script>
    <script src="https://unpkg.com/persian-date@1.1.0/dist/persian-date.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/persian-datepicker@1.2.0/dist/js/persian-datepicker.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="{{ asset('js/invoice-create.js') }}"></script>
@endpush
