@extends('layouts.app')

@section('content')
<link rel="stylesheet" href="{{ asset('css/invoice-create.css') }}">
<form id="sales-invoice-form" class="row g-4" autocomplete="off">
    @if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif
@if($errors->any())
    <div class="alert alert-danger">
        @foreach($errors->all() as $error) <div>{{ $error }}</div> @endforeach
    </div>
@endif
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
                       value="{{ old('invoice_number', $nextNumber ?? '') }}"
                       readonly
                >
                <button class="btn btn-light border" type="button" id="edit-invoice-number-btn"
                    data-locked="true" aria-label="ویرایش شماره فاکتور">
                    <i class="fa-solid fa-lock text-secondary" id="invoice-lock-icon"></i>
                </button>
            </div>
            <div class="form-text text-muted mt-1">
                مثال: 10001
            </div>
        </div>
    </div>
    <!-- نام مشتری -->
    <div class="col-12 col-md-4">
        <label for="customer_search" class="form-label">مشتری</label>
        <input type="text" class="form-control" id="customer_search" placeholder="جستجوی مشتری...">
        <input type="hidden" name="customer_id" id="customer_id">
        <div class="dropdown-menu" id="customer-search-results" style="width:100%"></div>
        <div class="form-text text-muted mt-1">نام مشتری را تایپ کنید و انتخاب نمایید.</div>
    </div>
    <!-- فروشنده -->
    <div class="mb-3">
        <label for="seller_id" class="form-label">فروشنده</label>
        <select id="seller_id" name="seller_id" class="form-select" required>
            <option value="">انتخاب فروشنده</option>
            @foreach($sellers as $seller)
            <option value="{{ $seller->id }}">{{ $seller->first_name }} {{ $seller->last_name }}</option>
            @endforeach
        </select>
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
    <input type="hidden" name="invoice_items" id="invoice_items_input">

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

<script>
    document.addEventListener("DOMContentLoaded", function () {
        const customerSearchInput = document.getElementById("customer_search");
        const customerSearchResults = document.getElementById("customer-search-results");
        const customerIdInput = document.getElementById("customer_id");

        customerSearchInput.addEventListener("input", function () {
            const query = customerSearchInput.value.trim();
            if (query.length === 0) {
                customerSearchResults.classList.remove("show");
                customerSearchResults.innerHTML = "";
                return;
            }

            fetch(`/customers/search?q=${encodeURIComponent(query)}`)
                .then(response => response.json())
                .then(data => {
                    customerSearchResults.innerHTML = "";
                    if (data.length > 0) {
                        data.forEach(customer => {
                            const item = document.createElement("div");
                            item.className = "dropdown-item";
                            item.textContent = customer.name;
                            item.dataset.id = customer.id;
                            item.addEventListener("click", function () {
                                customerSearchInput.value = customer.name;
                                customerIdInput.value = customer.id;
                                customerSearchResults.classList.remove("show");
                            });
                            customerSearchResults.appendChild(item);
                        });
                        customerSearchResults.classList.add("show");
                    } else {
                        customerSearchResults.innerHTML = "<div class='dropdown-item text-muted'>موردی یافت نشد.</div>";
                        customerSearchResults.classList.add("show");
                    }
                })
                .catch(error => {
                    console.error("خطا در جستجو:", error);
                });
        });

        document.addEventListener("click", function (event) {
            if (!customerSearchResults.contains(event.target) && event.target !== customerSearchInput) {
                customerSearchResults.classList.remove("show");
            }
        });
    });
    </script>
@endsection
