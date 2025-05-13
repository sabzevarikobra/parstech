@php
    $tabType = $type ?? 'product'; // 'product' or 'service'
@endphp
<div class="row mb-3">
    <div class="col-12 col-md-4">
        <select class="form-select sales-form-select mb-2" id="{{ $tabType }}-category-select">
            <option value="">دسته‌بندی: همه</option>
            <!-- Ajax: دسته‌بندی‌ها لود می‌شوند -->
        </select>
    </div>
    <div class="col-12 col-md-8">
        <input type="text" class="form-control sales-form-input mb-2"
               id="{{ $tabType }}-search-input"
               placeholder="جستجو در {{ $tabType == 'product' ? 'محصولات' : 'خدمات' }} ...">
    </div>
</div>
<div class="table-responsive">
    <table class="table table-hover align-middle sales-product-table">
        <thead>
        <tr>
            <th>افزودن</th>
            <th>کد</th>
            <th>تصویر</th>
            <th>نام</th>
            <th>موجودی</th>
            <th>دسته‌بندی</th>
            <th>قیمت فروش</th>
        </tr>
        </thead>
        <tbody id="{{ $tabType }}-table-body">
        <!-- Ajax: لیست محصولات یا خدمات -->
        </tbody>
    </table>
    <div class="text-center py-2">
        <button class="btn btn-outline-primary d-none" id="{{ $tabType }}-load-more-btn">
            <i class="fa-solid fa-arrow-down ms-1"></i>
            بارگذاری بیشتر
        </button>
    </div>
</div>
