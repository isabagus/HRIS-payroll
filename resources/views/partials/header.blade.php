@php
    $u = auth()->user();
    $name = $u?->name ?: ($u?->username ?: 'Administrator');
    $roleName = $u?->role?->nama_role ?: 'Super Admin';
    $initials = strtoupper(substr($name, 0, 2));
@endphp

{{-- Header / Topbar Partial --}}
<header class="topbar" id="topbar">
    <div class="topbar-left">
        <button id="sidebar-toggle" class="topbar-toggle" title="Toggle Sidebar">
            <span></span>
            <span></span>
            <span></span>
        </button>

        <nav class="breadcrumb-nav" aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item">
                    <a href="/dashboard">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/>
                            <polyline points="9 22 9 12 15 12 15 22"/>
                        </svg>
                    </a>
                </li>
                @hasSection('breadcrumb')
                    @yield('breadcrumb')
                @else
                    <li class="breadcrumb-item active" aria-current="page">
                        @yield('page-title', 'Dashboard')
                    </li>
                @endif
            </ol>
        </nav>
    </div>


    <div class="topbar-right">
        {{-- Search --}}
        <div class="topbar-search">
            <span class="search-icon">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="11" cy="11" r="8"/>
                    <line x1="21" y1="21" x2="16.65" y2="16.65"/>
                </svg>
            </span>
            <input type="text" class="search-input" id="globalSearch" placeholder="Cari karyawan, payroll...">
            <span class="search-shortcut">Ctrl+K</span>
        </div>

        <div class="topbar-divider"></div>

        {{-- Tanggal --}}
        <div class="topbar-datetime">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
                <line x1="16" y1="2" x2="16" y2="6"/>
                <line x1="8" y1="2" x2="8" y2="6"/>
                <line x1="3" y1="10" x2="21" y2="10"/>
            </svg>
            <span>{{ now()->locale('id')->translatedFormat('d F Y') }}</span>
        </div>

        <div class="topbar-divider"></div>

        {{-- User Profile --}}
        <div class="topbar-dropdown" id="user-dropdown">
            <button class="topbar-user-btn" id="user-btn" title="Profil">
                <div class="topbar-avatar"><span>{{ $initials }}</span></div>
                <div class="topbar-user-info">
                    <span class="topbar-username">{{ $name }}</span>
                    <span class="topbar-userrole">{{ $roleName }}</span>
                </div>
                <svg class="user-caret" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <polyline points="6 9 12 15 18 9"/>
                </svg>
            </button>
            <div class="dropdown-panel user-panel" id="user-panel">
                <div class="user-panel-header">
                    <div class="user-panel-avatar"><span>{{ $initials }}</span></div>
                    <div class="user-panel-info">
                        <p class="user-panel-name">{{ $name }}</p>
                        <p class="user-panel-email">{{ $u?->email ?: $name }}</p>
                    </div>
                </div>
                <div class="user-panel-menu">
                    <form action="{{ route('logout') }}" method="POST">
                        @csrf
                        <button type="submit" class="user-panel-item user-panel-logout w-full text-left bg-transparent border-0 cursor-pointer">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
                            Keluar
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</header>
