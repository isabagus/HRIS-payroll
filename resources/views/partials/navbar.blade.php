{{-- Navbar / Page Header Partial - HRIS Payroll Gaji --}}
{{-- Used inside content area to display page title + action buttons --}}

<div class="page-header">
    <div class="page-header-left">
        <h1 class="page-title">@yield('page-title', 'Dashboard')</h1>
        @hasSection('page-subtitle')
            <p class="page-subtitle">@yield('page-subtitle')</p>
        @endif
    </div>
    <div class="page-header-right">
        @hasSection('page-actions')
            <div class="page-actions">
                @yield('page-actions')
            </div>
        @endif
    </div>
</div>
