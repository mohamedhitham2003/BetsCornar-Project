@extends('layouts.app')

@section('title', __('customers.title'))
@section('page-title', __('customers.title'))

@section('content')

    {{-- Search Bar --}}
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('customers.index') }}" class="row g-3 align-items-end">
                <div class="col-md-8">
                    <label class="form-label">{{ __('customers.actions.search') }}</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-search"></i></span>
                        <input type="text" name="q" class="form-control" value="{{ $q }}"
                            placeholder="{{ __('customers.filters.search_placeholder') }}">
                    </div>
                </div>
                <div class="col-md-4 d-flex gap-2">
                    <button type="submit" class="btn btn-primary flex-fill">
                        <i class="bi bi-search me-1"></i>{{ __('customers.actions.search') }}
                    </button>
                    <a href="{{ route('customers.index') }}" class="btn btn-outline-secondary">
                        <i class="bi bi-x-circle"></i>
                    </a>
                </div>
            </form>
        </div>
    </div>

    {{-- Header --}}
    <div class="d-flex align-items-center justify-content-between mb-3">
        <div class="d-flex align-items-center gap-2">
            <i class="bi bi-people-fill text-primary fs-5"></i>
            <span class="fw-bold fs-6">{{ $customers->total() }} عميل</span>
        </div>
        <a href="{{ route('customers.create') }}" class="btn btn-primary">
            <i class="bi bi-person-plus-fill me-1"></i>{{ __('customers.actions.add') }}
        </a>
    </div>

    {{-- Table --}}
    <div class="card">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>{{ __('customers.fields.name') }}</th>
                        <th>{{ __('customers.fields.phone') }}</th>
                        <th>{{ __('customers.fields.animal_type') }}</th>
                        <th>{{ __('customers.fields.last_vaccination') }}</th>
                        <th class="text-center">{{ __('messages.actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @if($customers->isNotEmpty())
                        @foreach($customers as $customer)
                            <tr>
                                <td class="text-muted small">{{ $customer->id }}</td>
                            <td class="fw-semibold">{{ $customer->name }}</td>
                            <td>
                                <a href="https://wa.me/{{ $customer->phone }}" target="_blank"
                                    class="text-success text-decoration-none" title="واتساب">
                                    <i class="bi bi-whatsapp me-1"></i>{{ $customer->phone }}
                                </a>
                            </td>
                            <td>
                                <span class="badge bg-light text-dark border">
                                    {{ $customer->animal_type }}
                                </span>
                            </td>
                            <td>
                                @php $lastVacc = $customer->vaccinations->first(); @endphp
                                @if ($lastVacc)
                                    <span class="badge bg-primary text-white">
                                        {{ $lastVacc->vaccination_date->format('Y-m-d') }}
                                    </span>
                                @else
                                    <span class="text-muted small">—</span>
                                @endif
                            </td>
                            <td class="text-center">
                                <a href="{{ route('customers.create') }}?phone={{ urlencode($customer->phone) }}&name={{ urlencode($customer->name) }}"
                                    class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-clipboard2-plus me-1"></i>زيارة جديدة
                                </a>
                            </td>
                        </tr>
                    @endforeach
                    @else
                        <tr>
                            <td colspan="6">
                                <div class="empty-state">
                                    <i class="bi bi-people text-muted"></i>
                                    <p>{{ __('customers.messages.no_customers') }}</p>
                                </div>
                            </td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>
    </div>

    {{-- Pagination --}}
    <div class="mt-4">
        {{ $customers->links() }}
    </div>

@endsection
