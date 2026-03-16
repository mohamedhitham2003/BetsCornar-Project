@extends('layouts.app')

@section('title', __('customers.create_title'))
@section('page-title', __('customers.create_title'))

@section('content')

    <!-- Choices CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/choices.js/public/assets/styles/choices.min.css" />

    <form method="POST" action="{{ route('customers.store') }}" id="visitForm">
        @csrf

        <div class="row g-4">

            {{-- === Customer Info === --}}
            <div class="col-lg-6">
                <div class="card h-100">
                    <div class="card-header">
                        <i class="bi bi-person-fill text-primary me-1"></i>
                        <span class="fw-bold">بيانات العميل والحيوان</span>
                    </div>
                    <div class="card-body row g-3">
                        <div class="col-md-6">
                            <label class="form-label">{{ __('customers.fields.name') }} <span
                                    class="text-danger">*</span></label>
                            <input type="text" name="name" id="name"
                                class="form-control @error('name') is-invalid @enderror"
                                value="{{ old('name', request('name')) }}" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">{{ __('customers.fields.phone') }} <span
                                    class="text-danger">*</span></label>
                            <input type="text" name="phone" id="phone"
                                class="form-control @error('phone') is-invalid @enderror"
                                value="{{ old('phone', request('phone')) }}" placeholder="01xxxxxxxxx" required>
                            @error('phone')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">{{ __('customers.fields.animal_type') }} <span
                                    class="text-danger">*</span></label>
                            <input type="text" name="animal_type" id="animal_type"
                                class="form-control @error('animal_type') is-invalid @enderror" list="animal-types-list"
                                value="{{ old('animal_type') }}" placeholder="قط، كلب، طائر..." required>
                            <datalist id="animal-types-list">
                                @foreach (['قط', 'كلب', 'طائر', 'أرنب', 'زواحف', 'أخرى'] as $t)
                                    <option value="{{ $t }}">
                                @endforeach
                            </datalist>
                            @error('animal_type')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">{{ __('customers.fields.address') }}</label>
                            <input type="text" name="address" class="form-control" value="{{ old('address') }}">
                        </div>
                        <div class="col-12">
                            <label class="form-label">{{ __('customers.fields.notes') }}</label>
                            <textarea name="notes" class="form-control" rows="2">{{ old('notes') }}</textarea>
                        </div>
                    </div>
                </div>
            </div>

            {{-- === Visit Info === --}}
            <div class="col-lg-6">
                <div class="card h-100">
                    <div class="card-header">
                        <i class="bi bi-clipboard2-pulse-fill text-success me-1"></i>
                        <span class="fw-bold">{{ __('customers.visit.title') }}</span>
                    </div>
                    <div class="card-body row g-3">

                        {{-- Consultation --}}
                        <div class="col-12">
                            <label class="form-label">{{ __('customers.visit.consultation_price') }} <span
                                    class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="number" name="consultation_price" id="consultation_price"
                                    class="form-control @error('consultation_price') is-invalid @enderror"
                                    value="{{ old('consultation_price', $consultationProduct?->price ?? 0) }}"
                                    step="0.01" min="0" required>
                                <span class="input-group-text">{{ __('messages.currency') }}</span>
                            </div>
                            @error('consultation_price')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Vaccination toggle --}}
                        <div class="col-12">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" role="switch" id="has_vaccination"
                                    name="has_vaccination" value="1" {{ old('has_vaccination') ? 'checked' : '' }}>
                                <label class="form-check-label fw-semibold" for="has_vaccination">
                                    {{ __('customers.visit.has_vaccination') }}
                                </label>
                            </div>
                        </div>

                        {{-- Vaccination fields (hidden by default) --}}
                        <div id="vaccination-section" class="{{ old('has_vaccination') ? '' : 'd-none' }} col-12">
                            <div class="border rounded-3 p-3 bg-light row g-3">
                                <div class="col-12">
                                    <span class="section-heading" style="font-size:.95rem;">
                                        <i
                                            class="bi bi-capsule-pill me-1 text-primary"></i>{{ __('customers.visit.vaccination_section') }}
                                    </span>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">{{ __('customers.visit.vaccine_product') }}</label>
                                    <select name="vaccine_product_id" id="vaccine_product_id"
                                        class="form-select @error('vaccine_product_id') is-invalid @enderror">
                                        <option value="">{{ __('customers.visit.select_vaccine') }}</option>
                                        @foreach ($vaccines as $v)
                                            @php
                                                $usableQty = $vaccineUsableQty[$v->id] ?? 0;
                                                $isVaccineOos = $v->stock_status === 'out_of_stock' ||
                                                    ($v->track_stock && $v->quantity <= 0) ||
                                                    $usableQty <= 0;
                                            @endphp
                                            <option value="{{ $v->id }}" 
                                                data-price="{{ $v->price }}"
                                                {{ old('vaccine_product_id') == $v->id ? 'selected' : '' }}
                                                {{ $isVaccineOos ? 'disabled' : '' }}
                                                style="{{ $isVaccineOos ? 'color:#999;' : '' }}">
                                                {{ $v->name }}{{ $isVaccineOos ? ' (نفد المخزون)' : '' }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('vaccine_product_id')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">{{ __('customers.visit.vaccine_quantity') }}</label>
                                    <input type="number" name="vaccine_quantity" id="vaccine_quantity"
                                        class="form-control" value="{{ old('vaccine_quantity', 1) }}" step="0.01"
                                        min="0.01">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">سعر الوحدة</label>
                                    <div class="input-group">
                                        <input type="number" name="vaccine_unit_price" id="vaccine_unit_price"
                                            class="form-control" value="{{ old('vaccine_unit_price', 0) }}"
                                            step="0.01" min="0">
                                        <span class="input-group-text">{{ __('messages.currency') }}</span>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">{{ __('customers.visit.vaccination_date') }}</label>
                                    <input type="date" name="vaccination_date"
                                        class="form-control @error('vaccination_date') is-invalid @enderror"
                                        value="{{ old('vaccination_date', date('Y-m-d')) }}">
                                    @error('vaccination_date')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">{{ __('customers.visit.next_dose_date') }}</label>
                                    <input type="date" name="next_dose_date"
                                        class="form-control @error('next_dose_date') is-invalid @enderror"
                                        value="{{ old('next_dose_date') }}">
                                    @error('next_dose_date')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>

            {{-- === Additional Items === --}}
            <div class="col-12">
                <div class="card">
                    <div class="card-header d-flex align-items-center justify-content-between">
                        <span class="fw-bold">
                            <i
                                class="bi bi-plus-circle-fill text-secondary me-1"></i>{{ __('customers.visit.additional_items') }}
                        </span>
                        <button type="button" class="btn btn-outline-primary btn-sm" onclick="addItem()">
                            <i class="bi bi-plus-lg me-1"></i>{{ __('customers.visit.add_item') }}
                        </button>
                    </div>
                    <div class="card-body p-0">
                        <table class="table mb-0" id="items-table">
                            <thead>
                                <tr>
                                    <th style="width:40%">{{ __('customers.visit.product_service') }}</th>
                                    <th style="width:15%">{{ __('customers.visit.quantity') }}</th>
                                    <th style="width:18%">{{ __('customers.visit.unit_price') }}</th>
                                    <th style="width:18%">{{ __('customers.visit.line_total') }}</th>
                                    <th style="width:9%" class="text-center">حذف</th>
                                </tr>
                            </thead>
                            <tbody id="items-body">
                                {{-- JS-rendered rows --}}
                            </tbody>
                            <tfoot>
                                <tr class="table-light fw-bold">
                                    <td colspan="3" class="text-start">{{ __('customers.visit.grand_total') }}</td>
                                    <td id="grand-total-cell" class="text-primary fw-bold">0.00
                                        {{ __('messages.currency') }}</td>
                                    <td></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>

            {{-- === Submit === --}}
            <div class="col-12 d-flex gap-3">
                <button type="submit" class="btn btn-primary btn-lg px-5">
                    <i class="bi bi-save-fill me-1"></i>{{ __('customers.actions.save') }}
                </button>
                <a href="{{ route('customers.index') }}" class="btn btn-outline-secondary btn-lg">
                    <i class="bi bi-arrow-right me-1"></i>{{ __('messages.back') }}
                </a>
            </div>

        </div>
    </form>



    @push('scripts')
        <!-- Choices JS -->
        <script src="https://cdn.jsdelivr.net/npm/choices.js/public/assets/scripts/choices.min.js"></script>
        <script>
            @php
                $vaccinesJson = $vaccines->map(function ($v) use ($vaccineUsableQty) {
                    return [
                        'id'           => $v->id,
                        'name'         => $v->name,
                        'price'        => (float) $v->price,
                        'stock_status' => $v->stock_status,
                        'quantity'     => (float) $v->quantity,
                        'track_stock'  => (bool) $v->track_stock,
                        'usable_qty'   => (float) ($vaccineUsableQty[$v->id] ?? 0),
                    ];
                });
            @endphp

            const vaccinesData = @json($vaccinesJson);
            const selectProductPlaceholder = @json(__('customers.visit.select_product_service'));
            const selectVaccinePlaceholder = @json(__('customers.visit.select_vaccine'));
            let itemIndex = 0;

            /* ─── OOS helpers ─────────────────────────────────────────── */
            function isProductOos(p) {
                return p.stock_status === 'out_of_stock' || (p.track_stock && p.quantity <= 0);
            }

            function isVaccineOos(v) {
                return v.stock_status === 'out_of_stock' || (v.track_stock && v.quantity <= 0) || v.usable_qty <= 0;
            }

            /* ─── Vaccine Choices.js ──────────────────────────────────── */
            var vaccineChoices = null;

            document.getElementById('has_vaccination').addEventListener('change', function () {
                document.getElementById('vaccination-section').classList.toggle('d-none', !this.checked);
                recalcTotal();

                if (this.checked && !vaccineChoices) {
                    vaccineChoices = new Choices('#vaccine_product_id', {
                        searchEnabled:          true,
                        searchPlaceholderValue: 'ابحث عن لقاح...',
                        noResultsText:          'لا توجد نتائج',
                        itemSelectText:         '',
                        shouldSort:             false,
                        allowHTML:              false,
                    });

                    document.getElementById('vaccine_product_id').addEventListener('change', function () {
                        var selected = this.options[this.selectedIndex];
                        if (selected) {
                            var price = selected.getAttribute('data-price') || 0;
                            document.getElementById('vaccine_unit_price').value = parseFloat(price).toFixed(2);
                            recalcTotal();
                        }
                    });
                }
            });

            /* ─── Product Choices.js (AJAX, per-row) ─────────────────── */
            function initProductSelect(selectEl) {
                if (!selectEl) return;
                const idx = selectEl.getAttribute('data-idx');

                const instance = new Choices(selectEl, {
                    searchEnabled:          true,
                    searchPlaceholderValue: 'ابحث عن منتج...',
                    noResultsText:          'لا توجد نتائج',
                    noChoicesText:          'اكتب للبحث...',
                    itemSelectText:         '',
                    shouldSort:             false,
                    allowHTML:              false,
                });

                // Store the instance for later destroy
                selectEl._choicesInstance = instance;

                // Debounced AJAX search
                let debounceTimer;
                selectEl.addEventListener('search', function (e) {
                    clearTimeout(debounceTimer);
                    debounceTimer = setTimeout(function () {
                        const q = e.detail.value || '';
                        fetch('/products/search?q=' + encodeURIComponent(q))
                            .then(function (r) { return r.json(); })
                            .then(function (products) {
                                const choices = products.map(function (p) {
                                    const oos = isProductOos(p);
                                    return {
                                        value:             String(p.id),
                                        label:             p.name + (oos ? ' (نفد المخزون)' : ''),
                                        customProperties:  { price: p.price },
                                        disabled:          oos,
                                    };
                                });
                                instance.clearChoices();
                                instance.setChoices(choices, 'value', 'label', true);
                            })
                            .catch(function () { /* silently ignore network errors */ });
                    }, 300);
                });

                // Auto-fill unit price on selection
                selectEl.addEventListener('change', function () {
                    const val = this.value;
                    if (!val) return;

                    // Get price directly from the selected choice in the instance
                    const selectedChoice = instance.getValue();
                    const price = selectedChoice && selectedChoice.customProperties
                        ? selectedChoice.customProperties.price
                        : 0;

                    const priceInput = document.querySelector(
                        '[name="additional_items[' + idx + '][unit_price]"]'
                    );
                    if (priceInput) {
                        priceInput.value = parseFloat(price || 0).toFixed(2);
                        recalcRow(idx);
                    }
                });
            }

            /* ─── Row management ─────────────────────────────────────── */
            function addItem() {
                const idx = itemIndex++;
                const row = `
        <tr id="row-${idx}">
            <td>
                <select name="additional_items[${idx}][product_id]"
                        id="product-select-${idx}"
                        class="form-select form-select-sm product-choices-select"
                        data-idx="${idx}" required>
                    <option value=""></option>
                </select>
            </td>
            <td>
                <input type="number" name="additional_items[${idx}][quantity]"
                       class="form-control form-control-sm qty-input"
                       data-idx="${idx}" value="1" min="0.01" step="0.01"
                       oninput="recalcRow(${idx})" required>
            </td>
            <td>
                <div class="input-group input-group-sm">
                    <input type="number" name="additional_items[${idx}][unit_price]"
                           class="form-control form-control-sm price-input"
                           data-idx="${idx}" value="0.00" min="0" step="0.01"
                           oninput="recalcRow(${idx})" required>
                    <span class="input-group-text">ج.م</span>
                </div>
            </td>
            <td>
                <input type="text" class="form-control form-control-sm bg-light line-total"
                       id="line-total-${idx}" value="0.00" readonly>
            </td>
            <td class="text-center">
                <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeItem(${idx})">
                    <i class="bi bi-trash"></i>
                </button>
            </td>
        </tr>`;
                document.getElementById('items-body').insertAdjacentHTML('beforeend', row);
                // Initialise Choices.js AFTER the row is in the DOM
                initProductSelect(document.getElementById('product-select-' + idx));
            }

            function recalcRow(idx) {
                const qty   = parseFloat(document.querySelector(`[name="additional_items[${idx}][quantity]"]`).value) || 0;
                const price = parseFloat(document.querySelector(`[name="additional_items[${idx}][unit_price]"]`).value) || 0;
                document.getElementById(`line-total-${idx}`).value = (qty * price).toFixed(2);
                recalcTotal();
            }

            function removeItem(idx) {
                const row = document.getElementById(`row-${idx}`);
                if (row) {
                    // Destroy the Choices.js instance to prevent memory leaks
                    const sel = row.querySelector('.product-choices-select');
                    if (sel && sel._choicesInstance) {
                        sel._choicesInstance.destroy();
                    }
                    row.remove();
                }
                recalcTotal();
            }

            function recalcTotal() {
                let total = parseFloat(document.getElementById('consultation_price').value) || 0;

                if (document.getElementById('has_vaccination').checked) {
                    const qty   = parseFloat(document.getElementById('vaccine_quantity').value)   || 0;
                    const price = parseFloat(document.getElementById('vaccine_unit_price').value) || 0;
                    total += qty * price;
                }

                document.querySelectorAll('.line-total').forEach(function (el) {
                    total += parseFloat(el.value) || 0;
                });

                document.getElementById('grand-total-cell').textContent = total.toFixed(2) + ' ج.م';
            }

            /* ─── Global input listeners ─────────────────────────────── */
            document.getElementById('consultation_price').addEventListener('input', recalcTotal);
            document.getElementById('vaccine_quantity').addEventListener('input',   recalcTotal);
            document.getElementById('vaccine_unit_price').addEventListener('input', recalcTotal);
        </script>
    @endpush

@endsection
