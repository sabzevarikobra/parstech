@extends('layouts.sales')

@section('content')
<form id="sales-invoice-form" class="row g-4" autocomplete="off">
    <!-- شماره فاکتور و قابلیت قفل/ویرایش -->
    <div class="col-12 col-md-4">
        <div class="sales-form-section">
            <label class="sales-form-label mb-2" for="invoice_number">
                شماره فاکتور
                <span class="ms-1" data-bs-toggle="tooltip" title="کد فاکتور به صورت خودکار تولید می‌شود، اما می‌توانید آن را ویرایش کنید.">
                    <i class="fa-regular fa-circle-question text-primary"></i>
                </span>
            </label>
            <div class="input-group">
                <input type="text"
                       class="form-control sales-form-input"
                       id="invoice_number"
                       name="invoice_number"
                       value="{{ old('invoice_number', $invoiceNumber ?? '') }}"
                       readonly
                >
                <button class="btn btn-light border" type="button" id="edit-invoice-number-btn"
                    data-locked="true" aria-label="ویرایش شماره فاکتور">
                    <i class="fa-solid fa-lock text-secondary" id="invoice-lock-icon"></i>
                </button>
            </div>
            <div class="form-text text-muted mt-1">
                مثال: invoices-10001
            </div>
        </div>
    </div>
    <!-- نام مشتری -->
    <div class="col-12 col-md-4">
        <div class="sales-form-section">
            <label class="sales-form-label mb-2" for="customer_search">نام مشتری</label>
            <div class="position-relative">
                <input type="text"
                       class="form-control sales-form-input"
                       id="customer_search"
                       name="customer_search"
                       placeholder="جستجوی مشتری..."
                       autocomplete="off"
                >
                <div class="dropdown-menu w-100 shadow-sm" id="customer-search-results"></div>
            </div>
            <input type="hidden" name="customer_id" id="customer_id">
        </div>
    </div>
    <!-- فروشنده -->
    <div class="col-12 col-md-4">
        <div class="sales-form-section">
            <label class="sales-form-label mb-2" for="seller_id">فروشنده</label>
            <select class="form-select sales-form-select" name="seller_id" id="seller_id" required>
                <option value="">انتخاب کنید</option>
                @foreach($sellers as $seller)
                    <option value="{{ $seller->id }}" {{ old('seller_id') == $seller->id ? 'selected' : '' }}>
                        {{ $seller->name }}
                    </option>
                @endforeach
            </select>
        </div>
    </div>
    <!-- واحد پول و تاریخ صدور و سررسید -->
    <div class="col-12 col-lg-6">
        <div class="sales-form-section">
            <div class="row g-3 align-items-end">
                <div class="col-12 col-md-4">
                    <label class="sales-form-label mb-2" for="currency_id">واحد پول</label>
                    <select class="form-select sales-form-select" name="currency_id" id="currency_id" required>
                        <option value="">انتخاب کنید</option>
                        @foreach($currencies as $currency)
                            <option value="{{ $currency->id }}" {{ old('currency_id') == $currency->id ? 'selected' : '' }}>
                                {{ $currency->name }} ({{ $currency->code }})
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12 col-md-4">
                    <label class="sales-form-label mb-2" for="issued_at">تاریخ صدور</label>
                    <input type="datetime-local" class="form-control sales-form-input" id="issued_at" name="issued_at"
                        value="{{ old('issued_at', now()->format('Y-m-d\TH:i')) }}">
                </div>
                <div class="col-12 col-md-4">
                    <label class="sales-form-label mb-2" for="due_at">
                        تاریخ سررسید
                        <span class="ms-1" data-bs-toggle="tooltip" title="در این تاریخ هشدار پرداخت صادر می‌شود.">
                            <i class="fa-regular fa-bell text-primary"></i>
                        </span>
                    </label>
                    <input type="date" class="form-control sales-form-input" id="due_at" name="due_at"
                        value="{{ old('due_at', now()->addDays(7)->format('Y-m-d')) }}">
                </div>
            </div>
        </div>
    </div>
    <!-- دکمه ثبت اولیه -->
    <div class="col-12 col-lg-6 d-flex align-items-center justify-content-end">
        <button type="submit" class="btn btn-primary btn-lg px-4 shadow-sm">
            <i class="fa-solid fa-floppy-disk ms-2"></i>
            ثبت فاکتور و افزودن محصولات
        </button>
    </div>
</form>
    {{-- فرم اطلاعات اولیه --}}
    @include('sales.partials.product_list')
    {{-- جدول اقلام فاکتور --}}
    @include('sales.partials.invoice_items_table')
@endsection

@section('scripts')
<script src="{{ asset('js/sales-invoice-init.js') }}"></script>
<script src="{{ asset('js/sales-products.js') }}"></script>
@endsection
