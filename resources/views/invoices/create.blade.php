@extends('layouts.app')

@section('title', __('invoices.create_title'))
@section('page-title', __('invoices.create_title'))

@php
    $productsJson = $products->map(function ($p) {
        return [
            'id' => $p->id,
            'name' => $p->name,
            'price' => (float) $p->price,
            'stock_status' => $p->stock_status,
            'quantity' => (float) $p->quantity,
            'track_stock' => (bool) $p->track_stock,
        ];
    });
@endphp

@section('content')

    <form method="POST" action="{{ route('invoices.store') }}" id="quickSaleForm">
        @csrf

        <div class="row g-4">

            {{-- Customer info --}}
            <div class="col-lg-5">
                <div class="card">
                    <div class="card-header">
                        <i class="bi bi-person-fill text-primary me-1"></i>
                        <span class="fw-bold">بيانات العميل</span>
                    </div>
                    <div class="card-body row g-3">
                        <div class="col-12">
                            <label class="form-label">{{ __('invoices.fields.customer_name') }}</label>
                            <input type="text" name="customer_name"
                                class="form-control @error('customer_name') is-invalid @enderror"
                                value="{{ old('customer_name') }}" placeholder="اسم العميل (اختياري)">
                            @error('customer_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-12">
                            <label class="form-label">{{ __('invoices.fields.customer_phone') }}</label>
                            <input type="text" name="customer_phone" class="form-control"
                                value="{{ old('customer_phone') }}" placeholder="01xxxxxxxxx (اختياري)">
                            <div class="form-text text-muted">إذا كان موجودًا يتم ربط الفاتورة بالعميل تلقائيًا</div>
                        </div>
                    </div>
                </div>

                {{-- Grand Total Card --}}
                <div class="card mt-3 border-success">
                    <div class="card-body text-center">
                        <div class="text-muted small mb-1">{{ __('invoices.fields.grand_total') }}</div>
                        <div class="display-6 fw-bold text-success" id="grand-total-display">0.00</div>
                        <div class="text-muted">{{ __('messages.currency') }}</div>
                    </div>
                </div>

                <div class="d-flex gap-2 mt-3">
                    <button type="submit" class="btn btn-success btn-lg flex-fill">
                        <i class="bi bi-save-fill me-1"></i>{{ __('invoices.actions.save') }}
                    </button>
                    <a href="{{ route('invoices.index') }}" class="btn btn-outline-secondary btn-lg">
                        <i class="bi bi-arrow-right me-1"></i>{{ __('messages.back') }}
                    </a>
                </div>
            </div>

            {{-- Items --}}
            <div class="col-lg-7">
                <div class="card">
                    <div class="card-header d-flex align-items-center justify-content-between">
                        <span class="fw-bold">
                            <i class="bi bi-list-ul text-success me-1"></i>{{ __('invoices.fields.items') }}
                        </span>
                        <button type="button" class="btn btn-outline-success btn-sm" onclick="addRow()">
                            <i class="bi bi-plus-lg me-1"></i>{{ __('invoices.actions.add_item') }}
                        </button>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table mb-0" id="items-table">
                                <thead>
                                    <tr>
                                        <th style="min-width:200px">{{ __('invoices.fields.product') }}</th>
                                        <th style="width:90px">{{ __('invoices.fields.quantity') }}</th>
                                        <th style="width:110px">{{ __('invoices.fields.unit_price') }}</th>
                                        <th style="width:100px">{{ __('invoices.fields.line_total') }}</th>
                                        <th style="width:50px" class="text-center">حذف</th>
                                    </tr>
                                </thead>
                                <tbody id="items-body"></tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </form>

    <style>
        .product-select-wrap { position: relative; }
        .product-select-trigger {
            display: block; width: 100%; padding: 0.25rem 0.5rem; font-size: 0.875rem; font-weight: 400; line-height: 1.5;
            color: #212529; background-color: #fff; border: 1px solid #ced4da; border-radius: 0.25rem;
            text-align: right; cursor: pointer; -webkit-appearance: none; -moz-appearance: none; appearance: none;
        }
        .product-select-trigger:hover { border-color: #86b7fe; }
        .product-select-dropdown {
            display: none; position: absolute; top: 100%; left: 0; right: 0; z-index: 9999;
            background: #fff; border: 1px solid #ced4da; border-top: 0; border-radius: 0 0 0.25rem 0.25rem;
            max-height: 220px; overflow: hidden; box-shadow: 0 0.5rem 1rem rgba(0,0,0,0.15);
        }
        .product-select-wrap.open .product-select-dropdown { display: block; }
        .product-select-search {
            width: 100%; padding: 0.35rem 0.5rem; font-size: 0.875rem; border: 0; border-bottom: 1px solid #dee2e6;
            outline: 0; box-sizing: border-box;
        }
        .product-select-options { max-height: 180px; overflow-y: auto; }
        .product-select-option {
            padding: 0.35rem 0.5rem; font-size: 0.875rem; cursor: pointer; text-align: right;
        }
        .product-select-option:hover:not(.product-option-oos) { background: #e9ecef; }
        .product-option-oos { color: #999; cursor: not-allowed; }
        #quickSaleForm .card-body.p-0,
        #quickSaleForm .table-responsive { overflow: visible; }
        #quickSaleForm #items-table { overflow: visible; }
    </style>

@endsection

@push('scripts')
    <script>
        const products = @json($productsJson);
        const selectProductPlaceholder = @json(__('invoices.messages.select_product'));
        let rowIdx = 0;

        function isOutOfStock(p) {
            return p.stock_status === 'out_of_stock' || (p.track_stock && p.quantity <= 0);
        }

        function buildProductOptionsHtml() {
            return products.map(function(p) {
                var oos = isOutOfStock(p);
                var label = oos ? p.name + ' (نفد المخزون)' : p.name;
                var oosClass = oos ? ' product-option-oos' : '';
                return '<div class="product-select-option' + oosClass + '" data-id="' + p.id + '" data-price="' + p.price + '">' + label + '</div>';
            }).join('');
        }

        function addRow() {
            var idx = rowIdx++;
            var optionsHtml = buildProductOptionsHtml();
            var row = '<tr id="row-' + idx + '">' +
                '<td>' +
                '<div class="product-select-wrap" data-row-idx="' + idx + '">' +
                '<input type="hidden" name="items[' + idx + '][product_id]" value="" required>' +
                '<div class="product-select-trigger">' + selectProductPlaceholder + '</div>' +
                '<div class="product-select-dropdown">' +
                '<input type="text" class="product-select-search" placeholder="بحث..." autocomplete="off">' +
                '<div class="product-select-options">' + optionsHtml + '</div>' +
                '</div>' +
                '</div>' +
                '</td>' +
                '<td><input type="number" name="items[' + idx +
                '][quantity]" class="form-control form-control-sm" value="1" min="0.01" step="0.01" oninput="recalcRow(' +
                idx + ')" required></td>' +
                '<td><input type="number" name="items[' + idx +
                '][unit_price]" class="form-control form-control-sm" id="price-' + idx +
                '" value="0.00" min="0" step="0.01" oninput="recalcRow(' + idx + ')" required></td>' +
                '<td><input type="text" class="form-control form-control-sm bg-light" id="total-' + idx +
                '" value="0.00" readonly></td>' +
                '<td class="text-center"><button type="button" class="btn btn-sm btn-outline-danger" onclick="removeRow(' +
                idx + ')"><i class="bi bi-trash"></i></button></td>' +
                '</tr>';

            document.getElementById('items-body').insertAdjacentHTML('beforeend', row);
            initProductSelectSearch(document.getElementById('row-' + idx));
        }

        function recalcRow(idx) {
            var qty = parseFloat(document.querySelector('[name="items[' + idx + '][quantity]"]').value) || 0;
            var price = parseFloat(document.getElementById('price-' + idx).value) || 0;
            document.getElementById('total-' + idx).value = (qty * price).toFixed(2);
            recalcGrandTotal();
        }

        function removeRow(idx) {
            var el = document.getElementById('row-' + idx);
            if (el) el.remove();
            recalcGrandTotal();
        }

        function recalcGrandTotal() {
            var total = 0;
            document.querySelectorAll('[id^="total-"]').forEach(function(el) {
                total += parseFloat(el.value) || 0;
            });
            document.getElementById('grand-total-display').textContent = total.toFixed(2);
        }

        (function() {
            function openDropdown(wrap) {
                wrap.classList.add('open');
                var search = wrap.querySelector('.product-select-search');
                if (search) {
                    search.value = '';
                    search.style.display = '';
                    setTimeout(function() { search.focus(); }, 0);
                }
                filterProductOptions(wrap, '');
                document.addEventListener('click', closeOnClickOutside);
            }
            function closeDropdown(wrap) {
                wrap.classList.remove('open');
                document.removeEventListener('click', closeOnClickOutside);
            }
            function closeOnClickOutside(e) {
                document.querySelectorAll('.product-select-wrap.open').forEach(function(w) {
                    if (!w.contains(e.target)) closeDropdown(w);
                });
            }
            function filterProductOptions(wrap, q) {
                q = (q || '').trim().toLowerCase();
                wrap.querySelectorAll('.product-select-option').forEach(function(el) {
                    var text = (el.textContent || '').toLowerCase();
                    el.style.display = (!q || text.indexOf(q) !== -1) ? '' : 'none';
                });
            }
            function selectProduct(wrap, productId, price, label, idx) {
                wrap.querySelector('input[name*="[product_id]"]').value = productId || '';
                var trigger = wrap.querySelector('.product-select-trigger');
                if (trigger) trigger.textContent = label || selectProductPlaceholder;
                closeDropdown(wrap);
                var priceEl = document.getElementById('price-' + idx);
                if (priceEl) {
                    priceEl.value = parseFloat(price || 0).toFixed(2);
                    if (typeof recalcRow === 'function') recalcRow(idx);
                }
            }
            function initProductSelectSearch(container) {
                container = container || document;
                (container.querySelectorAll ? container.querySelectorAll('.product-select-wrap') : []).forEach(function(wrap) {
                    if (wrap._productSelectInit) return;
                    wrap._productSelectInit = true;
                    var idx = wrap.getAttribute('data-row-idx');
                    var trigger = wrap.querySelector('.product-select-trigger');
                    var dropdown = wrap.querySelector('.product-select-dropdown');
                    var searchInput = wrap.querySelector('.product-select-search');
                    var optionsContainer = wrap.querySelector('.product-select-options');
                    if (!trigger || !dropdown || !optionsContainer) return;
                    trigger.addEventListener('click', function(e) {
                        e.preventDefault();
                        e.stopPropagation();
                        if (wrap.classList.contains('open')) closeDropdown(wrap);
                        else openDropdown(wrap);
                    });
                    if (searchInput) {
                        searchInput.addEventListener('input', function() { filterProductOptions(wrap, this.value); });
                        searchInput.addEventListener('keydown', function(e) { if (e.key === 'Escape') closeDropdown(wrap); });
                    }
                    optionsContainer.querySelectorAll('.product-select-option').forEach(function(opt) {
                        if (opt.classList.contains('product-option-oos')) return;
                        opt.addEventListener('click', function(e) {
                            e.preventDefault();
                            e.stopPropagation();
                            selectProduct(wrap, this.getAttribute('data-id'), this.getAttribute('data-price'), this.textContent.trim(), idx);
                        });
                    });
                });
            }
            window.initProductSelectSearch = initProductSelectSearch;
        })();

        // Add first row on load
        addRow();
    </script>
@endpush
