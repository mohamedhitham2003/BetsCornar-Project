@extends('layouts.app')

@section('title', 'فاتورة ' . $invoice->invoice_number)
@section('page-title', 'تفاصيل الفاتورة')

@section('content')

    <div class="row g-4">

        {{-- Invoice Header --}}
        <div class="col-md-5">
            <div class="card">
                <div class="card-header">
                    <i class="bi bi-receipt text-primary me-1"></i>
                    <span class="fw-bold">بيانات الفاتورة</span>
                </div>
                <div class="card-body">
                    <table class="table table-borderless mb-0">
                        <tr>
                            <td class="text-muted" style="width:40%">رقم الفاتورة</td>
                            <td class="fw-bold text-primary font-monospace">{{ $invoice->invoice_number }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">العميل</td>
                            <td class="fw-semibold">{{ $invoice->customer_name }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">المصدر</td>
                            <td>
                                @if ($invoice->source === 'customer')
                                    <span class="badge bg-primary text-white">زيارة عميل</span>
                                @else
                                    <span class="badge bg-success text-white">بيع سريع</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <td class="text-muted">التاريخ</td>
                            <td>{{ $invoice->created_at->format('Y-m-d H:i') }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">الإجمالي</td>
                            <td class="fw-bold fs-5 text-success">
                                {{ number_format($invoice->total, 2) }} {{ __('messages.currency') }}
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
            <div class="mt-3">
                <a href="{{ route('invoices.index') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-right me-1"></i>{{ __('messages.back') }}
                </a>
            </div>
        </div>

        {{-- Invoice Items --}}
        <div class="col-md-7">
            <div class="card">
                <div class="card-header">
                    <i class="bi bi-list-ul text-success me-1"></i>
                    <span class="fw-bold">بنود الفاتورة</span>
                </div>
                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead>
                            <tr>
                                <th>المنتج / الخدمة</th>
                                <th class="text-center">الكمية</th>
                                <th class="text-center">سعر الوحدة</th>
                                <th class="text-center">الإجمالي</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($invoice->items as $item)
                                <tr>
                                    <td>
                                        <div class="fw-semibold">{{ $item->product->name }}</div>
                                        <div class="text-muted small">
                                            {{ ['product' => 'منتج', 'service' => 'خدمة', 'vaccination' => 'تطعيم'][$item->product->type] ?? $item->product->type }}
                                        </div>
                                    </td>
                                    <td class="text-center">{{ number_format($item->quantity, 2) }}</td>
                                    <td class="text-center">{{ number_format($item->unit_price, 2) }}</td>
                                    <td class="text-center fw-bold text-success">
                                        {{ number_format($item->line_total, 2) }} {{ __('messages.currency') }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr class="table-light fw-bold">
                                <td colspan="3" class="text-start">الإجمالي الكلي</td>
                                <td class="text-center text-success fs-6">
                                    {{ number_format($invoice->total, 2) }} {{ __('messages.currency') }}
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

            {{-- Vaccinations if any --}}
            @if ($invoice->vaccinations->isNotEmpty())
                <div class="card mt-3">
                    <div class="card-header">
                        <i class="bi bi-capsule-pill text-primary me-1"></i>
                        <span class="fw-bold">سجل التطعيمات المرتبطة</span>
                    </div>
                    <div class="table-responsive">
                        <table class="table align-middle mb-0">
                            <thead>
                                <tr>
                                    <th>اللقاح</th>
                                    <th>تاريخ التطعيم</th>
                                    <th>موعد الجرعة القادمة</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($invoice->vaccinations as $vacc)
                                    <tr>
                                        <td>{{ $vacc->product->name ?? '—' }}</td>
                                        <td>{{ $vacc->vaccination_date }}</td>
                                        <td>{{ $vacc->next_dose_date ?? '—' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif
        </div>

    </div>

@endsection
