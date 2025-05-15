@extends('layouts.app')

@section('title', 'ایجاد دسته‌بندی جدید')

@section('content')
<link rel="stylesheet" href="{{ asset('css/category-create.css') }}">

<div class="container mt-4 category-create-container">
    <div class="card category-create-card" id="category-create-card">
        <div class="card-header text-white category-create-header" id="category-create-header">
            <h5 class="mb-0" id="category-create-title">ایجاد دسته‌بندی جدید</h5>
        </div>
        <div class="card-body category-create-body">

            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            @if($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="mb-4 d-flex justify-content-center category-create-tabs">
                <button type="button" class="btn category-create-tab-btn" id="btn-person" onclick="showTab('person')">دسته‌بندی اشخاص</button>
                <button type="button" class="btn category-create-tab-btn" id="btn-product" onclick="showTab('product')">دسته‌بندی کالا</button>
                <button type="button" class="btn category-create-tab-btn" id="btn-service" onclick="showTab('service')">دسته‌بندی خدمات</button>
            </div>

            {{-- فرم دسته‌بندی اشخاص --}}
            <form method="POST" action="{{ route('categories.store') }}" enctype="multipart/form-data" id="form-person" style="display: none;">
                @csrf
                <input type="hidden" name="category_type" value="person">
                <div class="mb-3 text-center">
                    <div class="img-upload-wrapper">
                        <img id="img-person" src="{{ asset('img/category-person.png') }}" alt="پیش‌فرض اشخاص" class="img-thumbnail category-create-img" onclick="triggerFileInput('person_image')">
                        <div class="img-overlay" onclick="triggerFileInput('person_image')"><span>تغییر</span></div>
                        <input type="file" name="image" id="person_image" class="form-control category-create-input img-hidden-input" accept="image/*" onchange="previewImage(this, 'img-person')" style="display:none;">
                    </div>
                </div>
                <div class="mb-3">
                    <label for="person_name" class="form-label category-create-label">نام دسته‌بندی اشخاص</label>
                    <input type="text" name="name" id="person_name" class="form-control category-create-input" required>
                </div>
                <div class="mb-3">
                    <label for="person_code" class="form-label category-create-label">کد دسته‌بندی</label>
                    <input type="text" name="code" id="person_code" class="form-control category-create-input" value="{{ $nextPersonCode ?? 'per1001' }}" readonly>
                </div>
                <div class="mb-3">
                    <label for="person_parent_id" class="form-label category-create-label">زیر دسته</label>
                    <select name="parent_id" id="person_parent_id" class="form-control category-create-input">
                        <option value="">بدون زیر دسته</option>
                        @foreach($personCategories as $cat)
                            <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-3">
                    <label for="person_description" class="form-label category-create-label">توضیحات</label>
                    <textarea name="description" id="person_description" class="form-control category-create-input" rows="2"></textarea>
                </div>
                <div class="mb-3 text-end">
                    <button type="submit" class="btn category-create-submit person">ثبت دسته‌بندی اشخاص</button>
                </div>
            </form>

            {{-- فرم دسته‌بندی کالا --}}
            <form method="POST" action="{{ route('categories.store') }}" enctype="multipart/form-data" id="form-product" style="display: none;">
                @csrf
                <input type="hidden" name="category_type" value="product">
                <div class="mb-3 text-center">
                    <div class="img-upload-wrapper">
                        <img id="img-product" src="{{ asset('img/category-product.png') }}" alt="پیش‌فرض کالا" class="img-thumbnail category-create-img" onclick="triggerFileInput('product_image')">
                        <div class="img-overlay" onclick="triggerFileInput('product_image')"><span>تغییر</span></div>
                        <input type="file" name="image" id="product_image" class="form-control category-create-input img-hidden-input" accept="image/*" onchange="previewImage(this, 'img-product')" style="display:none;">
                    </div>
                </div>
                <div class="mb-3">
                    <label for="product_name" class="form-label category-create-label">نام دسته‌بندی کالا</label>
                    <input type="text" name="name" id="product_name" class="form-control category-create-input" required>
                </div>
                <div class="mb-3">
                    <label for="product_code" class="form-label category-create-label">کد دسته‌بندی</label>
                    <input type="text" name="code" id="product_code" class="form-control category-create-input" value="{{ $nextProductCode ?? 'pro1001' }}" readonly>
                </div>
                <div class="mb-3">
                    <label for="product_parent_id" class="form-label category-create-label">زیر دسته</label>
                    <select name="parent_id" id="product_parent_id" class="form-control category-create-input">
                        <option value="">بدون زیر دسته</option>
                        @foreach($productCategories as $cat)
                            <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="mb-3">
                    <label for="product_category_list" class="form-label category-create-label">دسته‌بندی کالا</label>
                    <select id="product_category_list" class="form-control category-create-input">
                        <option value="">انتخاب دسته‌بندی کالا</option>
                        @foreach($productCategories as $cat)
                            <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-3">
                    <label for="product_filter" class="form-label category-create-label">فیلتر محصولات</label>
                    <input type="text" id="product_filter" class="form-control category-create-input" placeholder="نام یا کد محصول...">
                </div>
                <div id="product_list_box" class="mb-3"></div>
                <div class="mb-3">
                    <label for="product_description" class="form-label category-create-label">توضیحات</label>
                    <textarea name="description" id="product_description" class="form-control category-create-input" rows="2"></textarea>
                </div>
                <div class="mb-3 text-end">
                    <button type="submit" class="btn category-create-submit product">ثبت دسته‌بندی کالا</button>
                </div>
            </form>

            {{-- فرم دسته‌بندی خدمات --}}
            <form method="POST" action="{{ route('categories.store') }}" enctype="multipart/form-data" id="form-service" style="display: none;">
                @csrf
                <input type="hidden" name="category_type" value="service">
                <div class="mb-3 text-center">
                    <div class="img-upload-wrapper">
                        <img id="img-service" src="{{ asset('img/category-service.png') }}" alt="پیش‌فرض خدمات" class="img-thumbnail category-create-img" onclick="triggerFileInput('service_image')">
                        <div class="img-overlay" onclick="triggerFileInput('service_image')"><span>تغییر</span></div>
                        <input type="file" name="image" id="service_image" class="form-control category-create-input img-hidden-input" accept="image/*" onchange="previewImage(this, 'img-service')" style="display:none;">
                    </div>
                </div>
                <div class="mb-3">
                    <label for="service_name" class="form-label category-create-label">نام دسته‌بندی خدمات</label>
                    <input type="text" name="name" id="service_name" class="form-control category-create-input" required>
                </div>
                <div class="mb-3">
                    <label for="service_code" class="form-label category-create-label">کد دسته‌بندی</label>
                    <input type="text" name="code" id="service_code" class="form-control category-create-input" value="{{ $nextServiceCode ?? 'ser1001' }}" readonly>
                </div>
                <div class="mb-3">
                    <label for="service_parent_id" class="form-label category-create-label">زیر دسته</label>
                    <select name="parent_id" id="service_parent_id" class="form-control category-create-input">
                        <option value="">بدون زیر دسته</option>
                        @foreach($serviceCategories as $cat)
                            <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="mb-3">
                    <label for="service_category_list" class="form-label category-create-label">دسته‌بندی خدمات</label>
                    <select id="service_category_list" class="form-control category-create-input">
                        <option value="">انتخاب دسته‌بندی خدمات</option>
                        @foreach($serviceCategories as $cat)
                            <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-3">
                    <label for="service_filter" class="form-label category-create-label">فیلتر خدمات</label>
                    <input type="text" id="service_filter" class="form-control category-create-input" placeholder="نام یا کد خدمت...">
                </div>
                <div id="service_list_box" class="mb-3"></div>
                <div class="mb-3">
                    <label for="service_description" class="form-label category-create-label">توضیحات</label>
                    <textarea name="description" id="service_description" class="form-control category-create-input" rows="2"></textarea>
                </div>
                <div class="mb-3 text-end">
                    <button type="submit" class="btn category-create-submit service">ثبت دسته‌بندی خدمات</button>
                </div>
            </form>

            {{-- لیست فروش --}}
            <div id="sale_items_box" class="mt-5"></div>

        </div>
    </div>
</div>

<script>
const tabColors = {
    person: {
        bg: '#1a73e8',
        btn: '#1a73e8',
        btnClass: 'tab-person',
        card: '#e3f0ff'
    },
    product: {
        bg: '#388e3c',
        btn: '#388e3c',
        btnClass: 'tab-product',
        card: '#e8f5e9'
    },
    service: {
        bg: '#fbc02d',
        btn: '#fbc02d',
        btnClass: 'tab-service',
        card: '#fffde7'
    }
};

function showTab(type) {
    document.getElementById('form-person').style.display = (type === 'person') ? 'block' : 'none';
    document.getElementById('form-product').style.display = (type === 'product') ? 'block' : 'none';
    document.getElementById('form-service').style.display = (type === 'service') ? 'block' : 'none';

    document.getElementById('btn-person').classList.remove('active', 'tab-person');
    document.getElementById('btn-product').classList.remove('active', 'tab-product');
    document.getElementById('btn-service').classList.remove('active', 'tab-service');

    document.getElementById('btn-person').style.background = 'transparent';
    document.getElementById('btn-product').style.background = 'transparent';
    document.getElementById('btn-service').style.background = 'transparent';
    document.getElementById('btn-person').style.color = '#1a73e8';
    document.getElementById('btn-product').style.color = '#388e3c';
    document.getElementById('btn-service').style.color = '#fbc02d';

    document.getElementById('btn-' + type).classList.add('active', tabColors[type].btnClass);
    document.getElementById('btn-' + type).style.background = tabColors[type].btn;
    document.getElementById('btn-' + type).style.color = '#fff';

    document.getElementById('category-create-header').style.background = tabColors[type].bg;
    document.getElementById('category-create-card').style.background = tabColors[type].card;

    let label = '';
    if (type === 'person') label = 'ایجاد دسته‌بندی اشخاص';
    if (type === 'product') label = 'ایجاد دسته‌بندی کالا';
    if (type === 'service') label = 'ایجاد دسته‌بندی خدمات';
    document.getElementById('category-create-title').textContent = label;
}

document.addEventListener("DOMContentLoaded", function() {
    showTab('product');
    // لود اولیه لیست محصولات/خدمات
    loadProducts();
    loadServices();
});

function previewImage(input, imgId) {
    if (input.files && input.files[0]) {
        var reader = new FileReader();
        reader.onload = function (e) {
            document.getElementById(imgId).src = e.target.result;
        };
        reader.readAsDataURL(input.files[0]);
    }
}
function triggerFileInput(inputId) {
    document.getElementById(inputId).click();
}

// محصولات تب کالا
document.getElementById('product_category_list').addEventListener('change', loadProducts);
document.getElementById('product_filter').addEventListener('input', loadProducts);

function loadProducts() {
    let cat_id = document.getElementById('product_category_list').value;
    let q = document.getElementById('product_filter').value.trim();
    fetch(`/products/ajax-list?category_id=${cat_id}&q=${encodeURIComponent(q)}`)
        .then(r=>r.json())
        .then(data => {
            let html = '';
            if(data.length === 0) html = '<div class="text-muted">محصولی یافت نشد.</div>';
            else {
                html = '<ul class="list-group">';
                data.forEach(function(p){
                    html += `<li class="list-group-item d-flex align-items-center product-item" data-id="${p.id}" data-name="${p.name}">
                        <img src="${p.image}" alt="" class="rounded me-2" style="width:40px;height:40px;object-fit:cover;">
                        <div>
                            <strong>${p.name}</strong> <span class="text-muted small">(${p.code})</span>
                            <div class="text-muted small">${p.category}</div>
                        </div>
                    </li>`;
                });
                html += '</ul>';
            }
            document.getElementById('product_list_box').innerHTML = html;
            // رویداد کلیک برای اضافه شدن به لیست فروش
            document.querySelectorAll('.product-item').forEach(function(item){
                item.addEventListener('click', function(){
                    addToSaleList({
                        id: this.dataset.id,
                        name: this.dataset.name,
                        type: 'product'
                    });
                });
            });
        });
}

// خدمات تب خدمات
document.getElementById('service_category_list').addEventListener('change', loadServices);
document.getElementById('service_filter').addEventListener('input', loadServices);

function loadServices() {
    let cat_id = document.getElementById('service_category_list').value;
    let q = document.getElementById('service_filter').value.trim();
    fetch(`/services/ajax-list?category_id=${cat_id}&q=${encodeURIComponent(q)}`)
        .then(r=>r.json())
        .then(data => {
            let html = '';
            if(data.length === 0) html = '<div class="text-muted">خدمتی یافت نشد.</div>';
            else {
                html = '<ul class="list-group">';
                data.forEach(function(s){
                    html += `<li class="list-group-item d-flex align-items-center service-item" data-id="${s.id}" data-name="${s.name}">
                        <div>
                            <strong>${s.name}</strong> <span class="text-muted small">(${s.code})</span>
                            <div class="text-muted small">${s.category}</div>
                        </div>
                    </li>`;
                });
                html += '</ul>';
            }
            document.getElementById('service_list_box').innerHTML = html;
            // رویداد کلیک برای اضافه شدن به لیست فروش
            document.querySelectorAll('.service-item').forEach(function(item){
                item.addEventListener('click', function(){
                    addToSaleList({
                        id: this.dataset.id,
                        name: this.dataset.name,
                        type: 'service'
                    });
                });
            });
        });
}

// لیست فروش (نمونه - باید با سیستم فروش واقعی هماهنگ شود)
let saleList = [];
function addToSaleList(item) {
    saleList.push(item);
    renderSaleList();
}
function renderSaleList() {
    let html = '';
    if(saleList.length === 0) html = '<div class="text-muted">محصول/خدمتی انتخاب نشده.</div>';
    else {
        html = '<ul class="list-group">';
        saleList.forEach(function(it, idx){
            html += `<li class="list-group-item d-flex justify-content-between align-items-center">
                ${it.type === 'product' ? '🛒' : '💼'} ${it.name}
                <button class="btn btn-sm btn-danger ms-2" onclick="removeFromSaleList(${idx})">حذف</button>
            </li>`;
        });
        html += '</ul>';
    }
    document.getElementById('sale_items_box').innerHTML = html;
}
function removeFromSaleList(idx) {
    saleList.splice(idx, 1);
    renderSaleList();
}
</script>
@endsection
