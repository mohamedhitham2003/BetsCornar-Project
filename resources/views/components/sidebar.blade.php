<aside id="sidebar">
    <div class="sidebar-brand d-flex align-items-center gap-3">
        <div class="brand-icon">🐾</div>
        <div>
            <div class="brand-text">{{ __('messages.app_name') }}</div>
            <div class="brand-sub">نظام متكامل للعيادة</div>
        </div>
    </div>

    <nav class="sidebar-nav">
        <div class="nav-label">الرئيسية</div>
        <a href="{{ route('dashboard') }}"
           class="sidebar-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
            <i class="bi bi-speedometer2"></i> {{ __('messages.nav_dashboard') }}
        </a>

        <div class="nav-label mt-2">الوحدات</div>
        <a href="{{ route('customers.index') }}"
           class="sidebar-link {{ request()->routeIs('customers.*') ? 'active' : '' }}">
            <i class="bi bi-people-fill"></i> {{ __('messages.nav_customers') }}
        </a>
        <a href="{{ route('invoices.index') }}"
           class="sidebar-link {{ request()->routeIs('invoices.*') ? 'active' : '' }}">
            <i class="bi bi-receipt"></i> {{ __('messages.nav_invoices') }}
        </a>
        <a href="{{ route('products.index') }}"
           class="sidebar-link {{ request()->routeIs('products.*') ? 'active' : '' }}">
            <i class="bi bi-box-seam-fill"></i> {{ __('messages.nav_products') }}
        </a>
        <a href="{{ route('vaccine-batches.index') }}"
           class="sidebar-link {{ request()->routeIs('vaccine-batches.*') ? 'active' : '' }}">
            <i class="bi bi-capsule-pill"></i> {{ __('messages.nav_vaccine_batches') }}
        </a>
        <a href="{{ route('vaccinations.index') }}"
           class="sidebar-link {{ request()->routeIs('vaccinations.*') ? 'active' : '' }}">
            <i class="bi bi-shield-plus"></i> {{ __('messages.nav_vaccinations') }}
        </a>
    </nav>

    <div class="sidebar-footer">
        نظام العيادة البيطرية &copy; {{ date('Y') }}
    </div>
</aside>

<div id="sidebar-overlay" onclick="closeSidebar()"></div>
