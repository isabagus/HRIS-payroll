<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <link rel="icon" type="image/svg+xml" href="{{ asset('images/logo_HRISPro.svg') }}">
    <link rel="shortcut icon" href="{{ asset('images/logo_HRISPro.svg') }}">

    <title>HRIS Payroll</title>
    <meta name="description" content="@yield('meta-description', 'Sistem Informasi HRIS dan Penggajian Karyawan')">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        /* Select2 Custom HRIS Styling */
        .select2-container--default .select2-selection--single {
            height: 44px !important;
            border-radius: 12px !important;
            border: 1px solid #cbd5e1 !important;
            padding: 6px 14px !important;
            display: flex !important;
            align-items: center !important;
            background-color: #ffffff !important;
        }
        .select2-container--default .select2-selection--single .select2-selection__rendered {
            color: #1e293b !important;
            font-size: 0.875rem !important;
            font-weight: 500 !important;
            padding-left: 0 !important;
        }
        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 42px !important;
            right: 10px !important;
        }
        .select2-dropdown {
            border-radius: 12px !important;
            border: 1px solid #cbd5e1 !important;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1) !important;
            overflow: hidden !important;
            z-index: 9999 !important;
        }
        .select2-search__field {
            border-radius: 8px !important;
            border: 1px solid #cbd5e1 !important;
            padding: 8px 12px !important;
            outline: none !important;
            font-size: 0.875rem !important;
        }
        .select2-container--default .select2-results__option--highlighted[aria-selected] {
            background-color: #4f46e5 !important;
            color: white !important;
        }

        /* SweetAlert2 HRIS Custom Theme */
        div.swal2-popup {
            border-radius: 20px !important;
            padding: 24px !important;
            font-family: 'Inter', sans-serif !important;
            box-shadow: 0 20px 30px -10px rgba(0, 0, 0, 0.15) !important;
        }
        div.swal2-title {
            font-size: 1.2rem !important;
            font-weight: 700 !important;
            color: #1e293b !important;
        }
        div.swal2-html-container {
            font-size: 0.875rem !important;
            color: #64748b !important;
        }
        button.swal2-confirm {
            border-radius: 12px !important;
            font-weight: 600 !important;
            padding: 10px 22px !important;
            font-size: 0.875rem !important;
            box-shadow: 0 4px 12px rgba(79, 70, 229, 0.25) !important;
        }
        button.swal2-cancel {
            border-radius: 12px !important;
            font-weight: 600 !important;
            padding: 10px 22px !important;
            font-size: 0.875rem !important;
        }
    </style>

    @stack('styles')
</head>
<body>

<div class="app-layout">

    <div class="sidebar-overlay" id="sidebar-overlay"></div>

    @include('partials.sidebar')

    <div class="app-main" id="app-main">

        @include('partials.header')

        <div class="content-wrapper" id="content-wrapper">
            <main class="content-inner">

                @include('partials.navbar')

                {{-- Flash Messages --}}
                @if (session('success'))
                    <div class="session-messages">
                        <div class="alert alert-success">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                            {{ session('success') }}
                        </div>
                    </div>
                @endif
                @if (session('error'))
                    <div class="session-messages">
                        <div class="alert alert-danger">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>
                            {{ session('error') }}
                        </div>
                    </div>
                @endif
                @if (session('warning'))
                    <div class="session-messages">
                        <div class="alert alert-warning">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
                            {{ session('warning') }}
                        </div>
                    </div>
                @endif

                @yield('content')

            </main>

            @include('partials.footer')
        </div>

    </div>
</div>

<button class="scroll-to-top" id="scroll-to-top" title="Kembali ke atas">
    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="18 15 12 9 6 15"/></svg>
</button>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="{{ asset('js/import-dropzone.js') }}"></script>
<script>
    window.showAlert = function(title, text, icon = 'info') {
        return Swal.fire({
            title: title,
            text: text,
            icon: icon,
            confirmButtonColor: '#4f46e5',
            confirmButtonText: 'OK'
        });
    };

    window.confirmAction = function(options) {
        return Swal.fire({
            title: options.title || 'Konfirmasi',
            text: options.text || 'Apakah Anda yakin?',
            icon: options.icon || 'warning',
            showCancelButton: true,
            confirmButtonColor: options.confirmButtonColor || '#4f46e5',
            cancelButtonColor: '#94a3b8',
            confirmButtonText: options.confirmButtonText || 'Ya, Lanjutkan',
            cancelButtonText: options.cancelButtonText || 'Batal',
            reverseButtons: true
        });
    };

    window.confirmDelete = function(url, itemName = 'data ini') {
        confirmAction({
            title: 'Konfirmasi Hapus Data',
            text: `Apakah Anda yakin ingin menghapus "${itemName}"? Data yang dihapus tidak dapat dikembalikan!`,
            icon: 'warning',
            confirmButtonText: 'Ya, Hapus Data',
            confirmButtonColor: '#ef4444',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                let form = document.createElement('form');
                form.method = 'POST';
                form.action = url;
                
                let csrf = document.createElement('input');
                csrf.type = 'hidden';
                csrf.name = '_token';
                csrf.value = document.querySelector('meta[name="csrf-token"]').content;
                form.appendChild(csrf);

                let method = document.createElement('input');
                method.type = 'hidden';
                method.name = '_method';
                method.value = 'DELETE';
                form.appendChild(method);

                document.body.appendChild(form);
                form.submit();
            }
        });
    };
</script>
@stack('scripts')
<script src="{{ asset('js/app.js') }}"></script>
</body>
</html>
