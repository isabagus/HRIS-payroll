<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" type="image/svg+xml" href="{{ asset('images/logo_HRISPro.svg') }}">
    <link rel="shortcut icon" href="{{ asset('images/logo_HRISPro.svg') }}">

    <title>Login - HRIS Payroll System</title>
    
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
            background: linear-gradient(135deg, #1d4ed8, #2563eb);
            transition: all 0.2s ease;
        }

        .btn-glass-primary:hover {
            background: linear-gradient(135deg, #1e40af, #1d4ed8);
            transform: translateY(-1px);
            box-shadow: 0 8px 20px rgba(37, 99, 235, 0.4);
        }

        .demo-role-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(0, 0, 0, 0.25);
        }
    </style>
</head>
<body>
    <div class="login-backdrop">
        <div class="w-full max-w-md">
            
            {{-- Welcome Text Above Card --}}
            <div class="text-center mb-6">
                <h1 class="text-3xl sm:text-4xl font-extrabold text-white text-shadow-sm tracking-tight mb-1">
                    Selamat Datang
                </h1>
                <p class="text-white/90 text-sm sm:text-base font-medium text-shadow-sm">
                    HRISPro - Payroll System
                </p>
            </div>

            {{-- Glassmorphism Card --}}
            <div class="glass-card p-7 sm:p-9 text-white">
                
                {{-- Logo --}}
                <div class="flex justify-center mb-5">
                    <img src="{{ asset('images/logo_HRISPro.svg') }}"
                         alt="HRISPro Logo"
                         class="h-12 w-auto object-contain drop-shadow-md"
                         onerror="this.style.display='none'">
                </div>

                {{-- Flash: Sukses Reset Password --}}
                @if(session('success_reset'))
                    <div class="mb-5 p-4 rounded-xl bg-emerald-500/20 border border-emerald-400/40 text-emerald-100 text-sm">
                        <div class="flex items-center gap-2 font-semibold mb-1">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 flex-shrink-0 text-emerald-300" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/>
                            </svg>
                            Permintaan Terkirim
                        </div>
                        <p class="opacity-90">{{ session('success_reset') }}</p>
                    </div>
                @endif

                {{-- Validation Errors --}}
                @if ($errors->any())
                    <div class="mb-5 p-4 rounded-xl bg-red-500/20 border border-red-400/40 text-red-100 text-sm">
                        <div class="flex items-center gap-2 font-semibold mb-1">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 flex-shrink-0 text-red-300" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>
                            </svg>
                            Gagal Masuk
                        </div>
                        <ul class="list-disc list-inside space-y-0.5 opacity-90">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                {{-- ── FITUR LOGIN CEPAT DEMO ────────────────────── --}}
                <div class="mb-6 p-4 rounded-2xl bg-white/10 border border-white/20 backdrop-blur-md shadow-inner">
                    <div class="flex items-center justify-between mb-3">
                        <div class="flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                            <span class="text-xs font-bold uppercase tracking-wider text-white">Login Cepat Demo</span>
                        </div>
                        <span class="text-[11px] text-white/70">1-Klik Masuk</span>
                    </div>

                    <div class="grid grid-cols-3 gap-2">
                        {{-- Super Admin --}}
                        <button type="button" 
                                onclick="quickDemoLogin('root', 'root123', 'Super Admin')"
                                class="demo-role-btn group p-2.5 rounded-xl bg-white/10 hover:bg-blue-600/70 border border-white/20 hover:border-blue-400 transition-all duration-200 text-center flex flex-col items-center gap-1 focus:outline-none focus:ring-2 focus:ring-blue-400">
                            <div class="w-8 h-8 rounded-lg bg-blue-500/30 group-hover:bg-white/20 flex items-center justify-center text-blue-200 group-hover:text-white transition-colors">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
                                    <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                                </svg>
                            </div>
                            <span class="text-xs font-bold text-white block">Super Admin</span>
                            <span class="text-[10px] text-white/60 font-mono">root</span>
                        </button>

                        {{-- Manager --}}
                        <button type="button" 
                                onclick="quickDemoLogin('manager', 'manager123', 'Manager')"
                                class="demo-role-btn group p-2.5 rounded-xl bg-white/10 hover:bg-sky-600/70 border border-white/20 hover:border-sky-400 transition-all duration-200 text-center flex flex-col items-center gap-1 focus:outline-none focus:ring-2 focus:ring-sky-400">
                            <div class="w-8 h-8 rounded-lg bg-sky-500/30 group-hover:bg-white/20 flex items-center justify-center text-sky-200 group-hover:text-white transition-colors">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
                                    <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/>
                                    <circle cx="9" cy="7" r="4"/>
                                    <path d="M22 21v-2a4 4 0 0 0-3-3.87"/>
                                    <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                                </svg>
                            </div>
                            <span class="text-xs font-bold text-white block">Manager</span>
                            <span class="text-[10px] text-white/60 font-mono">manager</span>
                        </button>

                        {{-- Admin --}}
                        <button type="button" 
                                onclick="quickDemoLogin('admin', 'admin123', 'Admin')"
                                class="demo-role-btn group p-2.5 rounded-xl bg-white/10 hover:bg-amber-600/70 border border-white/20 hover:border-amber-400 transition-all duration-200 text-center flex flex-col items-center gap-1 focus:outline-none focus:ring-2 focus:ring-amber-400">
                            <div class="w-8 h-8 rounded-lg bg-amber-500/30 group-hover:bg-white/20 flex items-center justify-center text-amber-200 group-hover:text-white transition-colors">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
                                    <rect x="2" y="7" width="20" height="14" rx="2" ry="2"/>
                                    <path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/>
                                </svg>
                            </div>
                            <span class="text-xs font-bold text-white block">Admin HR</span>
                            <span class="text-[10px] text-white/60 font-mono">admin</span>
                        </button>
                    </div>

                    {{-- Feedback status on click --}}
                    <div id="demo-status" class="hidden mt-2.5 text-center text-xs font-semibold text-blue-200 animate-pulse">
                        Sedang menghubungkan ke akun...
                    </div>
                </div>

                {{-- Separator --}}
                <div class="relative flex items-center justify-center my-5">
                    <div class="border-t border-white/20 w-full"></div>
                    <span class="px-3 text-[11px] uppercase tracking-wider text-white/60 font-semibold">atau masuk manual</span>
                    <div class="border-t border-white/20 w-full"></div>
                </div>

                <form id="login-form" method="POST" action="{{ route('login') }}" class="space-y-4">
                    @csrf

                    {{-- Username / Email Input --}}
                    <div>
                        <label for="login" class="block text-sm font-medium text-white/90 mb-1.5">
                            Username
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
                                   placeholder="Masukkan email/username" 
                                   class="glass-input w-full pl-11 pr-4 py-3 rounded-xl text-sm font-medium">
                        </div>
                    </div>

                    {{-- Password Input --}}
                    <div>
                        <label for="password" class="block text-sm font-medium text-white/90 mb-1.5">
                            Password
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-white/70">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
                                    <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                                </svg>
                            </div>
                            <input type="password" 
                                   name="password" 
                                   id="password" 
                                   required
                                   placeholder="••••••••" 
                                   class="glass-input w-full pl-11 pr-11 py-3 rounded-xl text-sm font-medium">
                            <button type="button" 
                                    id="togglePassword" 
                                    class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-white/70 hover:text-white transition-colors"
                                    title="Tampilkan / Sembunyikan Password">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" id="eyeIcon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                                    <circle cx="12" cy="12" r="3"/>
                                </svg>
                            </button>
                        </div>
                    </div>

                    {{-- Remember Me & Forgot Password --}}
                    <div class="flex items-center justify-between text-xs sm:text-sm pt-1">
                        <label class="flex items-center gap-2 cursor-pointer text-white/90 hover:text-white">
                            <input type="checkbox" 
                                   name="remember" 
                                   class="w-4 h-4 rounded border-white/30 text-blue-600 focus:ring-offset-0 focus:ring-0 cursor-pointer">
                            <span>Ingat Saya</span>
                        </label>
                        <a href="{{ route('password.request.form') }}" class="text-white/80 hover:text-white underline decoration-white/40 transition-colors">
                            Lupa password?
                        </a>
                    </div>

                    {{-- Submit Button --}}
                    <button type="submit" 
                            class="btn-glass-primary w-full py-3.5 px-4 text-white font-semibold rounded-xl text-base shadow-lg flex items-center justify-center gap-2 mt-4">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                            <path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"/>
                            <polyline points="10 17 15 12 10 7"/>
                            <line x1="15" y1="12" x2="3" y2="12"/>
                        </svg>
                        Masuk Sistem
                    </button>
                </form>

                {{-- Card Footer --}}
                <div class="mt-7 pt-4 border-t border-white/10 text-center text-xs text-white/70">
                    &copy; {{ date('Y') }} HRIS Payroll. Powered By Forchais.Id
                </div>

            </div>
        </div>
    </div>

    <script>
        const togglePassword = document.getElementById('togglePassword');
        const passwordInput = document.getElementById('password');
        const eyeIcon = document.getElementById('eyeIcon');

        togglePassword?.addEventListener('click', function() {
            const isPassword = passwordInput.type === 'password';
            passwordInput.type = isPassword ? 'text' : 'password';
            
            eyeIcon.innerHTML = isPassword 
                ? `<path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/>`
                : `<path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>`;
        });

        function quickDemoLogin(username, password, roleName) {
            const loginInput = document.getElementById('login');
            const passInput = document.getElementById('password');
            const statusDiv = document.getElementById('demo-status');
            const form = document.getElementById('login-form');

            loginInput.value = username;
            passInput.value = password;

            if (statusDiv) {
                statusDiv.textContent = 'Menghubungkan sebagai ' + roleName + ' (' + username + ')...';
                statusDiv.classList.remove('hidden');
            }

            setTimeout(function() {
                form.submit();
            }, 180);
        }
    </script>
</body>
</html>
