<header id="topbar">
    <button class="toggle-btn" onclick="toggleSidebar()"><i class="bi bi-list"></i></button>
    <h1 class="page-title">@yield('page-title', __('messages.app_name'))</h1>
    <div class="d-flex align-items-center gap-2">
        <a href="{{ route('customers.create') }}" class="btn btn-primary btn-sm">
            <i class="bi bi-plus-lg me-1"></i>{{ __('messages.new_visit') }}
        </a>
        <a href="{{ route('invoices.create') }}" class="btn btn-outline-success btn-sm">
            <i class="bi bi-lightning-fill me-1"></i>{{ __('messages.quick_sale') }}
        </a>
        
        {{-- تم الإضافة: زر تسجيل الخروج لجميع المستخدمين --}}
        <form method="POST" action="{{ route('logout') }}" class="d-inline">
            @csrf
            <button type="submit" class="btn btn-outline-danger btn-sm" title="تسجيل خروج">
                <i class="bi bi-box-arrow-left"></i>
            </button>
        </form>
    </div>
</header>
