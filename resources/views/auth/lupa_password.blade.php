<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" type="image/svg+xml" href="{{ asset('images/logo_HRISPro.svg') }}">
    <link rel="shortcut icon" href="{{ asset('images/logo_HRISPro.svg') }}">

    <title>Lupa Password - HRIS Payroll System</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>

    <style>
        body {
            font-family: 'Inter', sans-serif;
            background: url("{{ asset('images/background.png') }}") no-repeat center center fixed;
            background-size: cover;
            min-height: 100vh;
            margin: 0;
        }

        .login-backdrop {
            background: rgba(15, 23, 42, 0.45);
            min-height: 100vh;
            width: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1.5rem;
        }

        .glass-card {
            background: rgba(255, 255, 255, 0.15);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border: 1px solid rgba(255, 255, 255, 0.25);
            border-radius: 20px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.4);
        }

        .glass-input {
            background: rgba(255, 255, 255, 0.2);
            border: 1px solid rgba(255, 255, 255, 0.3);
            color: #ffffff;
            transition: all 0.2s ease;
        }

        .glass-input::placeholder {
            color: rgba(255, 255, 255, 0.7);
        }

        .glass-input:focus {
            background: rgba(255, 255, 255, 0.3);
            border-color: #ffffff;
            outline: none;
            box-shadow: 0 0 0 3px rgba(255, 255, 255, 0.25);
        }

        .text-shadow-sm {
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.5);
        }

        .btn-glass-primary {
            background: linear-gradient(135deg, #4f46e5, #6366f1);
            transition: all 0.2s ease;
        }

        .btn-glass-primary:hover {
            background: linear-gradient(135deg, #4338ca, #4f46e5);
            transform: translateY(-1px);
            box-shadow: 0 8px 20px rgba(79, 70, 229, 0.4);
        }

        .btn-glass-secondary {
            background: rgba(255, 255, 255, 0.15);
            border: 1px solid rgba(255, 255, 255, 0.3);
            transition: all 0.2s ease;
        }

        .btn-glass-secondary:hover {
            background: rgba(255, 255, 255, 0.25);
            transform: translateY(-1px);
        }

        .info-box {
            background: rgba(99, 102, 241, 0.2);
            border: 1px solid rgba(99, 102, 241, 0.4);
            border-radius: 12px;
        }
    </style>
</head>
<body>
    <div class="login-backdrop">
        <div class="w-full max-w-md">

            {{-- Header Text --}}
            <div class="text-center mb-6">
                <h1 class="text-3xl sm:text-4xl font-extrabold text-white text-shadow-sm tracking-tight mb-1">
                    Lupa Password?
                </h1>
                <p class="text-white/90 text-sm sm:text-base font-medium text-shadow-sm">
                    HRISPro - Payroll System
                </p>
            </div>

            {{-- Glassmorphism Card --}}
            <div class="glass-card p-8 sm:p-10 text-white">

                {{-- Logo --}}
                <div class="flex justify-center mb-6">
                    <img src="{{ asset('images/logo_HRISPro.svg') }}"
                         alt="HRISPro Logo"
                         class="h-14 w-auto object-contain drop-shadow-md"
                         onerror="this.style.display='none'">
                </div>

                {{-- Info Box --}}
                <div class="info-box p-4 mb-6">
                    <div class="flex items-start gap-2.5">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 flex-shrink-0 mt-0.5 text-indigo-300" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>
                        </svg>
                        <p class="text-sm text-white/90 leading-relaxed">
                            Isi form ini untuk mengajukan permintaan reset password. Permintaan akan diproses secara manual oleh <strong>Administrator / HRD</strong> dan password baru akan disampaikan langsung kepada Anda.
                        </p>
                    </div>
                </div>

                {{-- Warning Alert --}}
                @if(session('warning'))
                    <div class="mb-5 p-4 rounded-xl bg-yellow-500/20 border border-yellow-400/40 text-yellow-100 text-sm">
                        <div class="flex items-center gap-2 font-semibold mb-1">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 flex-shrink-0 text-yellow-300" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/>
                            </svg>
                            Perhatian
                        </div>
                        {{ session('warning') }}
                    </div>
                @endif

                {{-- Validation Errors --}}
                @if($errors->any())
                    <div class="mb-5 p-4 rounded-xl bg-red-500/20 border border-red-400/40 text-red-100 text-sm">
                        <div class="flex items-center gap-2 font-semibold mb-1">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 flex-shrink-0 text-red-300" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>
                            </svg>
                            Ada Kesalahan
                        </div>
                        <ul class="list-disc list-inside space-y-0.5 opacity-90">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                {{-- Form --}}
                <form method="POST" action="{{ route('password.request.store') }}" class="space-y-5">
                    @csrf

                    {{-- Username / Email --}}
                    <div>
                        <label for="login" class="block text-sm font-medium text-white/90 mb-1.5">
                            Username atau Email <span class="text-red-300">*</span>
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-white/70">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                                    <circle cx="12" cy="7" r="4"/>
                                </svg>
                            </div>
                            <input type="text"
                                   name="login"
                                   id="login"
                                   value="{{ old('login') }}"
                                   required
                                   autofocus
                                   placeholder="Masukkan username atau email"
                                   class="glass-input w-full pl-11 pr-4 py-3 rounded-xl text-sm font-medium">
                        </div>
                    </div>

                    {{-- Alasan (opsional) --}}
                    <div>
                        <label for="alasan" class="block text-sm font-medium text-white/90 mb-1.5">
                            Alasan / Catatan
                            <span class="text-white/50 font-normal">(opsional)</span>
                        </label>
                        <div class="relative">
                            <textarea name="alasan"
                                      id="alasan"
                                      rows="3"
                                      placeholder="Contoh: Lupa password lama, tidak bisa login sejak kemarin..."
                                      class="glass-input w-full px-4 py-3 rounded-xl text-sm font-medium resize-none">{{ old('alasan') }}</textarea>
                        </div>
                    </div>

                    {{-- Submit Button --}}
                    <button type="submit"
                            class="btn-glass-primary w-full py-3.5 px-4 text-white font-semibold rounded-xl text-base shadow-lg flex items-center justify-center gap-2 mt-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                            <line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/>
                        </svg>
                        Kirim Permintaan Reset Password
                    </button>

                    {{-- Back to Login --}}
                    <a href="{{ route('login') }}"
                       class="btn-glass-secondary w-full py-3 px-4 text-white/90 font-medium rounded-xl text-sm flex items-center justify-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <polyline points="15 18 9 12 15 6"/>
                        </svg>
                        Kembali ke Login
                    </a>
                </form>

                {{-- Card Footer --}}
                <div class="mt-8 pt-4 border-t border-white/10 text-center text-xs text-white/70">
                    &copy; {{ date('Y') }} HRIS Payroll. Powered By For I.Studio.
                </div>

            </div>
        </div>
    </div>
</body>
</html>
