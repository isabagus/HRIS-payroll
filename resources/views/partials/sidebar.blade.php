@php
    $u = auth()->user();
    $isSuperAdmin = $u && strtolower($u->role?->nama_role ?? '') === 'super admin';
    $path = request()->path();
    // Badge: hitung permintaan reset password pending (hanya jika Super Admin)
    $pendingResetCount = 0;
    if ($isSuperAdmin) {
        try {
            $pendingResetCount = \App\Models\PermintaanResetPassword::where('status', 'pending')->count();
        } catch (\Exception $e) {
            $pendingResetCount = 0;
        }
    }
@endphp

{{-- Sidebar Partial --}}
<aside id="sidebar" class="sidebar">

    {{-- Logo & Brand --}}
    <div class="sidebar-brand">
        <div class="brand-logo" style="background:transparent;padding:0;overflow:visible;">
            <img src="{{ asset('images/logo_HRISPro.svg') }}"
                 alt="HRISPro Logo"
                 style="height:36px;width:auto;object-fit:contain;display:block;"
                 onerror="this.style.display='none'">
        </div>
        <div class="brand-info">
            <span class="brand-name">HRIS<span class="brand-accent">Pro</span></span>
            <!-- <span class="brand-subtitle">PACKSOLUTION.ID</span> -->
             <span class="brand-subtitle">Payroll System</span>
        </div>
        <button id="sidebar-toggle-btn" class="sidebar-close-btn" title="Toggle Sidebar">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M15 18l-6-6 6-6"/>
            </svg>
        </button>
    </div>

    {{-- Navigation --}}
    <nav class="sidebar-nav">

        <div class="nav-section">
            <span class="nav-section-label">MENU UTAMA</span>
            <ul class="nav-list">
                <li class="nav-item">
                    <a href="/dashboard" class="nav-link {{ request()->is('dashboard') || request()->is('/') ? 'active' : '' }}" data-tooltip="Dashboard">
                        <span class="nav-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                <rect x="3" y="3" width="7" height="7" rx="1"/>
                                <rect x="14" y="3" width="7" height="7" rx="1"/>
                                <rect x="14" y="14" width="7" height="7" rx="1"/>
                                <rect x="3" y="14" width="7" height="7" rx="1"/>
                            </svg>
                        </span>
                        <span class="nav-text">Dashboard</span>
                    </a>
                </li>
            </ul>
        </div>

        {{-- MASTER DATA SECTION --}}
        @if($u && ($u->hasHakAkses('instansi', 'read') || $u->hasHakAkses('divisi', 'read') || $u->hasHakAkses('jabatan', 'read') || $u->hasHakAkses('status_karyawan', 'read') || $u->hasHakAkses('gaji_pokok', 'read') || $u->hasHakAkses('shift', 'read') || ($u->role && in_array(strtolower($u->role->nama_role), ['super admin', 'manager']))))
        <div class="nav-section">
            <span class="nav-section-label">MASTER DATA</span>
            <ul class="nav-list">
                @if($u->hasHakAkses('instansi', 'read'))
                <li class="nav-item">
                    <a href="/instansi" class="nav-link {{ request()->is('instansi*') ? 'active' : '' }}" data-tooltip="Instansi">
                        <span class="nav-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                <path d="M19 21V5a2 2 0 0 0-2-2H7a2 2 0 0 0-2 2v16"/>
                                <path d="M12 7v4"/><path d="M12 15h.01"/>
                            </svg>
                        </span>
                        <span class="nav-text">Instansi</span>
                    </a>
                </li>
                @endif

                @if($u->hasHakAkses('divisi', 'read'))
                <li class="nav-item">
                    <a href="/divisi" class="nav-link {{ request()->is('divisi*') ? 'active' : '' }}" data-tooltip="Divisi">
                        <span class="nav-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/>
                                <polyline points="9 22 9 12 15 12 15 22"/>
                            </svg>
                        </span>
                        <span class="nav-text">Divisi</span>
                    </a>
                </li>
                @endif

                @if($u->hasHakAkses('jabatan', 'read'))
                <li class="nav-item">
                    <a href="/jabatan" class="nav-link {{ request()->is('jabatan*') ? 'active' : '' }}" data-tooltip="Jabatan">
                        <span class="nav-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                                <circle cx="9" cy="7" r="4"/>
                                <path d="M23 21v-2a4 4 0 0 0-3-3.87"/>
                                <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                            </svg>
                        </span>
                        <span class="nav-text">Jabatan</span>
                    </a>
                </li>
                @endif

                @if($u->hasHakAkses('status_karyawan', 'read'))
                <li class="nav-item">
                    <a href="/status" class="nav-link {{ request()->is('status*') ? 'active' : '' }}" data-tooltip="Status Karyawan">
                        <span class="nav-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                <circle cx="12" cy="12" r="10"/>
                                <path d="M12 8v4l3 3"/>
                            </svg>
                        </span>
                        <span class="nav-text">Status Karyawan</span>
                    </a>
                </li>
                @endif

                @if($u->hasHakAkses('gaji_pokok', 'read'))
                <li class="nav-item">
                    <a href="/gaji" class="nav-link {{ request()->is('gaji*') && !request()->is('gaji-karyawan*') ? 'active' : '' }}" data-tooltip="Gaji Pokok">
                        <span class="nav-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                <rect x="2" y="5" width="20" height="14" rx="2"/>
                                <line x1="2" y1="10" x2="22" y2="10"/>
                            </svg>
                        </span>
                        <span class="nav-text">Komponen Gaji</span>
                    </a>
                </li>
                @endif

                @if($u->hasHakAkses('shift', 'read') || ($u->role && in_array(strtolower($u->role->nama_role), ['super admin', 'manager'])))
                <li class="nav-item">
                    <a href="/shift" class="nav-link {{ request()->is('shift*') ? 'active' : '' }}" data-tooltip="Shift Kerja">
                        <span class="nav-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                <circle cx="12" cy="12" r="10"/>
                                <polyline points="12 6 12 12 16 14"/>
                            </svg>
                        </span>
                        <span class="nav-text">Shift Kerja</span>
                    </a>
                </li>
                @endif
            </ul>
        </div>
        @endif

        {{-- KEPEGAWAIAN SECTION --}}
        @if($u && ($u->hasHakAkses('karyawan', 'read') || $u->hasHakAkses('gaji_karyawan', 'read') || $u->hasHakAkses('absensi', 'read') || $u->hasHakAkses('lembur', 'read') || $u->hasHakAkses('kasbon', 'read')))
        <div class="nav-section">
            <span class="nav-section-label">KEPEGAWAIAN</span>
            <ul class="nav-list">
                @if($u->hasHakAkses('karyawan', 'read'))
                <li class="nav-item">
                    <a href="/karyawan" class="nav-link {{ request()->is('karyawan*') ? 'active' : '' }}" data-tooltip="Data Karyawan">
                        <span class="nav-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                                <circle cx="12" cy="7" r="4"/>
                            </svg>
                        </span>
                        <span class="nav-text">Data Karyawan</span>
                    </a>
                </li>
                @endif

                @if($u->hasHakAkses('gaji_karyawan', 'read'))
                <li class="nav-item">
                    <a href="/gaji-karyawan" class="nav-link {{ request()->is('gaji-karyawan*') ? 'active' : '' }}" data-tooltip="Gaji Karyawan">
                        <span class="nav-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                <line x1="12" y1="1" x2="12" y2="23"/>
                                <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/>
                            </svg>
                        </span>
                        <span class="nav-text">Gaji Karyawan</span>
                    </a>
                </li>
                @endif

                @if($u->hasHakAkses('absensi', 'read'))
                <li class="nav-item has-submenu {{ request()->is('absensi*') ? 'open' : '' }}">
                    <a href="#" class="nav-link submenu-toggle {{ request()->is('absensi*') ? 'active' : '' }}" data-tooltip="Absensi">
                        <span class="nav-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                <path d="M9 11l3 3L22 4"/>
                                <path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/>
                            </svg>
                        </span>
                        <span class="nav-text">Absensi</span>
                        <span class="submenu-arrow">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <polyline points="6 9 12 15 18 9"/>
                            </svg>
                        </span>
                    </a>
                    <ul class="submenu">
                        <li><a href="/absensi" class="submenu-link {{ request()->is('absensi') ? 'active' : '' }}">Rekap Absensi</a></li>
                        @if($u->hasHakAkses('absensi', 'create'))
                        <li><a href="/absensi/create" class="submenu-link {{ request()->is('absensi/create') ? 'active' : '' }}">Input Absensi</a></li>
                        @endif
                    </ul>
                </li>
                @endif

                @if($u->hasHakAkses('lembur', 'read'))
                <li class="nav-item">
                    <a href="/lembur" class="nav-link {{ request()->is('lembur*') ? 'active' : '' }}" data-tooltip="Lembur">
                        <span class="nav-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                <circle cx="12" cy="12" r="10"/>
                                <polyline points="12 6 12 12 16 14"/>
                            </svg>
                        </span>
                        <span class="nav-text">Lembur</span>
                    </a>
                </li>
                @endif

                @if($u->hasHakAkses('kasbon', 'read'))
                <li class="nav-item">
                    <a href="/kasbon" class="nav-link {{ request()->is('kasbon*') ? 'active' : '' }}" data-tooltip="Kasbon">
                        <span class="nav-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                <line x1="12" y1="1" x2="12" y2="23"/>
                                <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/>
                            </svg>
                        </span>
                        <span class="nav-text">Kasbon</span>
                    </a>
                </li>
                @endif
            </ul>
        </div>
        @endif

        {{-- PENGGAJIAN SECTION --}}
        @if($u && $u->hasHakAkses('payroll', 'read'))
        <div class="nav-section">
            <span class="nav-section-label">PENGGAJIAN</span>
            <ul class="nav-list">
                <li class="nav-item">
                    <a href="/payroll" class="nav-link {{ request()->is('payroll*') ? 'active' : '' }}" data-tooltip="Proses Payroll">
                        <span class="nav-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                                <polyline points="14 2 14 8 20 8"/>
                                <line x1="16" y1="13" x2="8" y2="13"/>
                                <line x1="16" y1="17" x2="8" y2="17"/>
                                <polyline points="10 9 9 9 8 9"/>
                            </svg>
                        </span>
                        <span class="nav-text">Proses Payroll</span>
                        <span class="nav-badge badge-warning">NEW</span>
                    </a>
                </li>
            </ul>
        </div>
        @endif

        {{-- PENGATURAN SECTION --}}
        @if($isSuperAdmin || ($u && ($u->hasHakAkses('user_management', 'read') || $u->hasHakAkses('pengaturan_jam_kerja', 'read') || $u->hasHakAkses('reset_password', 'read'))))
        <div class="nav-section">
            <span class="nav-section-label">PENGATURAN</span>
            <ul class="nav-list">
                @if($isSuperAdmin || ($u && $u->hasHakAkses('pengaturan_jam_kerja', 'read')))
                <li class="nav-item">
                    <a href="/pengaturan-jam-kerja" class="nav-link {{ request()->is('pengaturan-jam-kerja*') ? 'active' : '' }}" data-tooltip="Toleransi Keterlambatan">
                        <span class="nav-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                <circle cx="12" cy="12" r="10"/>
                                <polyline points="12 6 12 12 16 14"/>
                            </svg>
                        </span>
                        <span class="nav-text">Toleransi Keterlambatan</span>
                    </a>
                </li>
                @endif

                @if($isSuperAdmin || ($u && $u->hasHakAkses('reset_password', 'read')))
                <li class="nav-item">
                    <a href="/reset-password-requests" class="nav-link {{ request()->is('reset-password-requests*') ? 'active' : '' }}" data-tooltip="Permintaan Reset Password">
                        <span class="nav-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
                                <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                                <circle cx="12" cy="16" r="1" fill="currentColor"/>
                            </svg>
                        </span>
                        <span class="nav-text">Reset Password</span>
                        @if($pendingResetCount > 0)
                            <span class="nav-badge badge-warning">{{ $pendingResetCount }}</span>
                        @endif
                    </a>
                </li>
                @endif

                @if($isSuperAdmin || ($u && $u->hasHakAkses('user_management', 'read')))
                <li class="nav-item has-submenu {{ request()->is('user*') || request()->is('hak-akses*') ? 'open' : '' }}">
                    <a href="#" class="nav-link submenu-toggle {{ request()->is('user*') || request()->is('hak-akses*') ? 'active' : '' }}" data-tooltip="Manajemen User">
                        <span class="nav-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                <circle cx="12" cy="8" r="4"/>
                                <path d="M20 21a8 8 0 1 0-16 0"/>
                            </svg>
                        </span>
                        <span class="nav-text">Manajemen User</span>
                        <span class="submenu-arrow">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <polyline points="6 9 12 15 18 9"/>
                            </svg>
                        </span>
                    </a>
                    <ul class="submenu">
                        <li><a href="/user" class="submenu-link {{ request()->is('user*') ? 'active' : '' }}">Kelola User</a></li>
                        @if($isSuperAdmin)
                        <li><a href="/hak-akses" class="submenu-link {{ request()->is('hak-akses*') ? 'active' : '' }}">Kelola Hak Akses</a></li>
                        @endif
                    </ul>
                </li>
                @endif
            </ul>
        </div>
        @endif

    </nav>

    {{-- Sidebar Footer --}}
    <div class="sidebar-footer">
        <div class="sidebar-footer-info">
            <span class="footer-app-name">HRIS Payroll</span>
            <span class="footer-version">v1.0.0</span>
        </div>
        <form action="{{ route('logout') }}" method="POST" class="inline">
            @csrf
            <button type="submit" class="sidebar-logout-btn" title="Keluar">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                    <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/>
                    <polyline points="16 17 21 12 16 7"/>
                    <line x1="21" y1="12" x2="9" y2="12"/>
                </svg>
            </button>
        </form>
    </div>

</aside>
