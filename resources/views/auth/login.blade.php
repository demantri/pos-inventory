<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login — POS Inventory</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');

        * {
            font-family: 'Inter', sans-serif;
        }

        @keyframes fadeUp {
            from {
                opacity: 0;
                transform: translateY(24px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes float {

            0%,
            100% {
                transform: translateY(0px);
            }

            50% {
                transform: translateY(-8px);
            }
        }

        @keyframes pulse-slow {

            0%,
            100% {
                opacity: 0.4;
                transform: scale(1);
            }

            50% {
                opacity: 0.7;
                transform: scale(1.05);
            }
        }

        .fade-up {
            animation: fadeUp 0.6s ease forwards;
        }

        .float {
            animation: float 4s ease-in-out infinite;
        }

        .blob {
            animation: pulse-slow 6s ease-in-out infinite;
        }

        .delay-100 {
            animation-delay: 0.1s;
            opacity: 0;
        }

        .delay-200 {
            animation-delay: 0.2s;
            opacity: 0;
        }

        .delay-300 {
            animation-delay: 0.3s;
            opacity: 0;
        }

        .delay-400 {
            animation-delay: 0.4s;
            opacity: 0;
        }

        input:-webkit-autofill {
            -webkit-box-shadow: 0 0 0 30px white inset !important;
        }
    </style>
</head>

<body
    style="background: linear-gradient(135deg, #0f172a 0%, #1e1b4b 50%, #0f172a 100%);
             min-height: 100vh; display: flex; align-items: center; justify-content: center;
             padding: 24px; overflow: hidden; position: relative;">

    {{-- Dekorasi background --}}
    <div class="blob"
        style="position:absolute;top:-120px;left:-80px;width:500px;height:500px;
                border-radius:50%;background:radial-gradient(circle, rgba(99,102,241,0.15) 0%, transparent 70%);
                pointer-events:none;">
    </div>
    <div class="blob"
        style="position:absolute;bottom:-100px;right:-60px;width:600px;height:600px;
                border-radius:50%;background:radial-gradient(circle, rgba(139,92,246,0.12) 0%, transparent 70%);
                pointer-events:none;animation-delay:3s;">
    </div>
    <div class="blob"
        style="position:absolute;top:40%;left:60%;width:300px;height:300px;
                border-radius:50%;background:radial-gradient(circle, rgba(59,130,246,0.1) 0%, transparent 70%);
                pointer-events:none;animation-delay:1.5s;">
    </div>

    {{-- Grid dots background --}}
    <div
        style="position:absolute;inset:0;pointer-events:none;
                background-image: radial-gradient(rgba(255,255,255,0.04) 1px, transparent 1px);
                background-size: 32px 32px;">
    </div>

    <div style="width:100%;max-width:440px;position:relative;z-index:10;">

        {{-- Logo & tagline --}}
        <div class="fade-up" style="text-align:center;margin-bottom:32px;">
            <div class="float"
                style="display:inline-flex;align-items:center;justify-content:center;
                         width:72px;height:72px;border-radius:20px;margin-bottom:16px;
                         background:linear-gradient(135deg,#6366f1,#8b5cf6);
                         box-shadow:0 0 40px rgba(99,102,241,0.4);">
                <svg style="width:36px;height:36px;color:#fff;" fill="none" stroke="currentColor"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01
                             M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7
                             a2 2 0 00-2 2v14a2 2 0 002 2z" />
                </svg>
            </div>
            <h1 style="font-size:26px;font-weight:700;color:#fff;letter-spacing:-0.5px;margin:0;">
                POS Inventory
            </h1>
            <p style="font-size:13px;color:rgba(255,255,255,0.45);margin-top:6px;">
                Sistem Kasir & Manajemen Stok
            </p>
        </div>

        {{-- Card login --}}
        <div class="fade-up delay-100"
            style="background:rgba(255,255,255,0.04);backdrop-filter:blur(24px);
                    border:1px solid rgba(255,255,255,0.1);border-radius:24px;
                    padding:36px 32px;box-shadow:0 32px 64px rgba(0,0,0,0.4);">

            {{-- Flash success --}}
            @if (session('success'))
                <div
                    style="background:rgba(34,197,94,0.15);border:1px solid rgba(34,197,94,0.3);
                        border-radius:10px;padding:12px 14px;margin-bottom:20px;
                        display:flex;align-items:center;gap:10px;">
                    <svg style="width:16px;height:16px;color:#4ade80;flex-shrink:0;" fill="none"
                        stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <p style="font-size:13px;color:#4ade80;margin:0;">{{ session('success') }}</p>
                </div>
            @endif

            {{-- Error global --}}
            @if ($errors->any() && !$errors->has('email') && !$errors->has('password'))
                <div
                    style="background:rgba(239,68,68,0.15);border:1px solid rgba(239,68,68,0.3);
                        border-radius:10px;padding:12px 14px;margin-bottom:20px;
                        display:flex;align-items:center;gap:10px;">
                    <svg style="width:16px;height:16px;color:#f87171;flex-shrink:0;" fill="none"
                        stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <p style="font-size:13px;color:#f87171;margin:0;">{{ $errors->first() }}</p>
                </div>
            @endif

            <form method="POST" action="{{ route('login') }}" id="login-form">
                @csrf

                {{-- Email --}}
                <div class="fade-up delay-200" style="margin-bottom:18px;">
                    <label for="email"
                        style="display:block;font-size:12px;font-weight:600;
                                  color:rgba(255,255,255,0.6);margin-bottom:8px;
                                  text-transform:uppercase;letter-spacing:0.5px;">
                        Email
                    </label>
                    <div style="position:relative;">
                        <svg style="position:absolute;left:14px;top:50%;transform:translateY(-50%);
                                     width:16px;height:16px;color:rgba(255,255,255,0.3);"
                            fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12
                                     a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207" />
                        </svg>
                        <input type="email" id="email" name="email" value="{{ old('email') }}"
                            autocomplete="email" autofocus placeholder="email@perusahaan.com"
                            style="width:100%;padding:13px 14px 13px 42px;
                                      background:rgba(255,255,255,0.07);
                                      border:1px solid {{ $errors->has('email') ? 'rgba(239,68,68,0.6)' : 'rgba(255,255,255,0.12)' }};
                                      border-radius:12px;font-size:14px;color:#fff;
                                      outline:none;box-sizing:border-box;transition:border-color 0.2s,background 0.2s;"
                            onfocus="this.style.borderColor='rgba(99,102,241,0.8)';
                                        this.style.background='rgba(255,255,255,0.1)'"
                            onblur="this.style.borderColor='{{ $errors->has('email') ? 'rgba(239,68,68,0.6)' : 'rgba(255,255,255,0.12)' }}';
                                       this.style.background='rgba(255,255,255,0.07)'">
                    </div>
                    @error('email')
                        <div style="display:flex;align-items:center;gap:6px;margin-top:6px;">
                            <svg style="width:13px;height:13px;color:#f87171;flex-shrink:0;" fill="none"
                                stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <p style="font-size:12px;color:#f87171;margin:0;">{{ $message }}</p>
                        </div>
                    @enderror
                </div>

                {{-- Password --}}
                <div class="fade-up delay-300" style="margin-bottom:20px;">
                    <label for="password"
                        style="display:block;font-size:12px;font-weight:600;
                                  color:rgba(255,255,255,0.6);margin-bottom:8px;
                                  text-transform:uppercase;letter-spacing:0.5px;">
                        Password
                    </label>
                    <div style="position:relative;">
                        <svg style="position:absolute;left:14px;top:50%;transform:translateY(-50%);
                                     width:16px;height:16px;color:rgba(255,255,255,0.3);"
                            fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6
                                     a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                        </svg>
                        <input type="password" id="password" name="password" autocomplete="current-password"
                            placeholder="••••••••"
                            style="width:100%;padding:13px 46px 13px 42px;
                                      background:rgba(255,255,255,0.07);
                                      border:1px solid {{ $errors->has('password') ? 'rgba(239,68,68,0.6)' : 'rgba(255,255,255,0.12)' }};
                                      border-radius:12px;font-size:14px;color:#fff;
                                      outline:none;box-sizing:border-box;transition:border-color 0.2s,background 0.2s;"
                            onfocus="this.style.borderColor='rgba(99,102,241,0.8)';
                                        this.style.background='rgba(255,255,255,0.1)'"
                            onblur="this.style.borderColor='{{ $errors->has('password') ? 'rgba(239,68,68,0.6)' : 'rgba(255,255,255,0.12)' }}';
                                       this.style.background='rgba(255,255,255,0.07)'">
                        {{-- Toggle show/hide --}}
                        <button type="button" onclick="togglePassword()"
                            style="position:absolute;right:14px;top:50%;transform:translateY(-50%);
                                       background:none;border:none;cursor:pointer;
                                       color:rgba(255,255,255,0.3);padding:0;line-height:1;"
                            id="toggle-pw-btn">
                            <svg id="eye-show" style="width:16px;height:16px;" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943
                                         9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            </svg>
                            <svg id="eye-hide" style="width:16px;height:16px;display:none;" fill="none"
                                stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7
                                         a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878
                                         9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3
                                         3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7
                                         a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                            </svg>
                        </button>
                    </div>
                    @error('password')
                        <div style="display:flex;align-items:center;gap:6px;margin-top:6px;">
                            <svg style="width:13px;height:13px;color:#f87171;flex-shrink:0;" fill="none"
                                stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <p style="font-size:12px;color:#f87171;margin:0;">{{ $message }}</p>
                        </div>
                    @enderror
                </div>

                {{-- Remember me --}}
                <div class="fade-up delay-300"
                    style="display:flex;align-items:center;justify-content:space-between;
                            margin-bottom:24px;">
                    <label style="display:flex;align-items:center;gap:8px;cursor:pointer;">
                        <div style="position:relative;">
                            <input type="checkbox" name="remember" id="remember"
                                style="width:16px;height:16px;accent-color:#6366f1;cursor:pointer;">
                        </div>
                        <span style="font-size:13px;color:rgba(255,255,255,0.5);">Ingat saya</span>
                    </label>
                </div>

                {{-- Submit button --}}
                <div class="fade-up delay-400">
                    <button type="submit" id="btn-submit"
                        style="width:100%;padding:14px;
                                   background:linear-gradient(135deg,#6366f1,#8b5cf6);
                                   color:#fff;font-weight:600;font-size:15px;
                                   border:none;border-radius:12px;cursor:pointer;
                                   box-shadow:0 4px 24px rgba(99,102,241,0.4);
                                   transition:all 0.2s;letter-spacing:0.2px;"
                        onmouseover="this.style.transform='translateY(-1px)';
                                         this.style.boxShadow='0 8px 32px rgba(99,102,241,0.5)'"
                        onmouseout="this.style.transform='translateY(0)';
                                        this.style.boxShadow='0 4px 24px rgba(99,102,241,0.4)'">
                        Masuk ke Sistem
                    </button>
                </div>
            </form>
        </div>

        {{-- Demo accounts --}}
        <div class="fade-up delay-400"
            style="margin-top:20px;background:rgba(255,255,255,0.04);
                    border:1px solid rgba(255,255,255,0.08);border-radius:16px;padding:16px 20px;">
            <p
                style="font-size:11px;font-weight:600;color:rgba(255,255,255,0.3);
                      text-transform:uppercase;letter-spacing:0.8px;margin-bottom:12px;">
                Akun Demo
            </p>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px;">
                @foreach ([['superadmin@pos.test', 'Super Admin', '#6366f1'], ['admin@pos.test', 'Admin', '#8b5cf6'], ['kasir@pos.test', 'Kasir', '#06b6d4'], ['gudang@pos.test', 'Gudang', '#10b981']] as [$email, $role, $color])
                    <button type="button" onclick="fillDemo('{{ $email }}')"
                        style="display:flex;align-items:center;gap:8px;padding:8px 10px;
                               background:rgba(255,255,255,0.05);border:1px solid rgba(255,255,255,0.08);
                               border-radius:8px;cursor:pointer;transition:all 0.15s;text-align:left;"
                        onmouseover="this.style.background='rgba(255,255,255,0.1)';
                                     this.style.borderColor='rgba(255,255,255,0.15)'"
                        onmouseout="this.style.background='rgba(255,255,255,0.05)';
                                    this.style.borderColor='rgba(255,255,255,0.08)'">
                        <span
                            style="width:8px;height:8px;border-radius:50%;
                                 background:{{ $color }};flex-shrink:0;
                                 box-shadow:0 0 6px {{ $color }};"></span>
                        <div>
                            <p style="font-size:11px;font-weight:600;color:rgba(255,255,255,0.7);margin:0;">
                                {{ $role }}
                            </p>
                            <p style="font-size:10px;color:rgba(255,255,255,0.3);margin:0;">
                                {{ $email }}
                            </p>
                        </div>
                    </button>
                @endforeach
            </div>
            <p style="font-size:11px;color:rgba(255,255,255,0.2);margin-top:10px;text-align:center;">
                Password: <span style="color:rgba(255,255,255,0.35);font-family:monospace;">password</span>
                — Klik akun untuk auto-fill
            </p>
        </div>

        {{-- Footer --}}
        <p style="text-align:center;font-size:11px;color:rgba(255,255,255,0.2);margin-top:16px;">
            POS Inventory System &copy; {{ date('Y') }}
        </p>
    </div>

    <script>
        function togglePassword() {
            const input = document.getElementById('password');
            const eyeShow = document.getElementById('eye-show');
            const eyeHide = document.getElementById('eye-hide');
            const isHidden = input.type === 'password';

            input.type = isHidden ? 'text' : 'password';
            eyeShow.style.display = isHidden ? 'none' : 'block';
            eyeHide.style.display = isHidden ? 'block' : 'none';

            const btn = document.getElementById('toggle-pw-btn');
            btn.style.color = isHidden ? 'rgba(255,255,255,0.6)' : 'rgba(255,255,255,0.3)';
        }

        function fillDemo(email) {
            document.getElementById('email').value = email;
            document.getElementById('password').value = 'password';

            // Flash efek pada input
            ['email', 'password'].forEach(id => {
                const el = document.getElementById(id);
                el.style.borderColor = 'rgba(99,102,241,0.8)';
                el.style.background = 'rgba(255,255,255,0.1)';
                setTimeout(() => {
                    el.style.borderColor = 'rgba(255,255,255,0.12)';
                    el.style.background = 'rgba(255,255,255,0.07)';
                }, 800);
            });
        }

        // Loading state saat submit
        document.getElementById('login-form').addEventListener('submit', function() {
            const btn = document.getElementById('btn-submit');
            btn.disabled = true;
            btn.textContent = 'Memproses...';
            btn.style.opacity = '0.8';
        });
    </script>
</body>

</html>
