// sales-invoice-init.js
document.addEventListener('DOMContentLoaded', function () {
    // ---------------- شماره فاکتور ----------------
    const invoiceInput = document.getElementById('invoice_number');
    const lockBtn = document.getElementById('edit-invoice-number-btn');
    const lockIcon = document.getElementById('invoice-lock-icon');
    lockBtn?.addEventListener('click', function () {
        const locked = lockBtn.getAttribute('data-locked') === 'true';
        if (locked) {
            invoiceInput.removeAttribute('readonly');
            invoiceInput.focus();
            lockIcon.classList.remove('fa-lock', 'text-secondary');
            lockIcon.classList.add('fa-lock-open', 'text-primary');
            lockBtn.setAttribute('data-locked', 'false');
        } else {
            invoiceInput.setAttribute('readonly', 'readonly');
            lockIcon.classList.remove('fa-lock-open', 'text-primary');
            lockIcon.classList.add('fa-lock', 'text-secondary');
            lockBtn.setAttribute('data-locked', 'true');
        }
    });


    // ---------------- جستجوی مشتری با Ajax ----------------
    const customerSearch = document.getElementById('customer_search');
    const customerResults = document.getElementById('customer-search-results');
    const customerIdInput = document.getElementById('customer_id');
    let customerTimeout = null;

    customerSearch?.addEventListener('input', function () {
        const query = this.value.trim();
        if (customerTimeout) clearTimeout(customerTimeout);
        if (query.length < 2) {
            customerResults.classList.remove('show');
            customerResults.innerHTML = '';
            return;
        }
        customerTimeout = setTimeout(() => {
            fetch(`/api/customers/search?q=${encodeURIComponent(query)}`)
                .then(res => res.json())
                .then(data => {
                    if (Array.isArray(data) && data.length > 0) {
                        customerResults.innerHTML = data.map(customer =>
                            `<button type="button" class="dropdown-item text-end" data-id="${customer.id}" data-name="${customer.name}">
                                <i class="fa-regular fa-user ms-2"></i> ${customer.name}
                            </button>`
                        ).join('');
                        customerResults.classList.add('show');
                    } else {
                        customerResults.innerHTML = `<div class="dropdown-item disabled text-muted text-center">نتیجه‌ای یافت نشد</div>`;
                        customerResults.classList.add('show');
                    }
                });
        }, 300);
    });

    // انتخاب مشتری
    customerResults?.addEventListener('click', function (e) {
        if (e.target.closest('button[data-id]')) {
            const btn = e.target.closest('button[data-id]');
            customerSearch.value = btn.getAttribute('data-name');
            customerIdInput.value = btn.getAttribute('data-id');
            customerResults.classList.remove('show');
            customerResults.innerHTML = '';
        }
    });

    // بستن لیست نتایج با کلیک بیرون
    document.addEventListener('click', function (e) {
        if (!customerSearch.contains(e.target) && !customerResults.contains(e.target)) {
            customerResults.classList.remove('show');
        }
    });

    
});
