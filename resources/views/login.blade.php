<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Letter Management System</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <style>
        :root {
            --brand: #24382e;
            --brand-dark: #1a2921;
        }

        body {
            background: #f8f9fa;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', sans-serif;
        }

        .login-wrap {
            width: 100%;
            max-width: 420px;
            padding: 1.5rem;
        }

        .brand-title {
            color: var(--brand);
            font-weight: 700;
        }

        .login-card {
            border: none;
            border-radius: 1rem;
            box-shadow: 0 10px 30px rgba(0,0,0,.08);
            overflow: hidden;
        }

        .login-card .card-header {
            background: #fff;
            border-bottom: 1px solid #eee;
            padding: 1.75rem 1.75rem 1rem;
        }

        .login-card .card-body {
            padding: 1.5rem 1.75rem 1.75rem;
        }

        .form-control:focus {
            border-color: var(--brand);
            box-shadow: 0 0 0 0.2rem rgba(36,56,46,.15);
        }

        .input-icon {
            position: relative;
        }

        .input-icon i.leading {
            position: absolute;
            left: 0.9rem;
            top: 50%;
            transform: translateY(-50%);
            color: #adb5bd;
        }

        .input-icon .form-control {
            padding-left: 2.4rem;
        }

        .input-icon .form-control.has-toggle {
            padding-right: 2.4rem;
        }

        .toggle-password {
            position: absolute;
            right: 0.9rem;
            top: 50%;
            transform: translateY(-50%);
            border: none;
            background: none;
            color: #adb5bd;
            padding: 0;
        }

        .toggle-password:hover {
            color: #6c757d;
        }

        .form-check-input:checked {
            background-color: var(--brand);
            border-color: var(--brand);
        }

        a.link-brand {
            color: var(--brand);
            text-decoration: none;
        }

        a.link-brand:hover {
            text-decoration: underline;
        }

        .btn-brand {
            background: var(--brand);
            border-color: var(--brand);
            color: #fff;
        }

        .btn-brand:hover {
            background: var(--brand-dark);
            border-color: var(--brand-dark);
            color: #fff;
        }

        .status-box {
            background: #eef2ea;
            border: 1px solid #cdd9c8;
            color: var(--brand);
            border-radius: 0.5rem;
        }
    </style>
</head>
<body>

    <div class="login-wrap">

        {{-- Logo / Brand --}}
        <div class="text-center mb-4">
            <h1 class="h3 brand-title mb-0">Admin Panel</h1>
            <p class="text-muted small mt-1 mb-0">Letter System</p>
        </div>

        {{-- Card Login --}}
        <div class="card login-card">

            {{-- Header Card --}}
            <div class="card-header">
                <h2 class="h5 mb-1">Masuk ke Akun Anda</h2>
                <p class="text-muted small mb-0">Silakan masukkan email dan password untuk melanjutkan.</p>
            </div>

            <div class="card-body">

                {{-- Alert Error --}}
                @if ($errors->any())
                    <div class="alert alert-danger py-2 small" role="alert">
                        {{ $errors->first() }}
                    </div>
                @endif

                @if (session('status'))
                    <div class="status-box px-3 py-2 small mb-3">
                        {{ session('status') }}
                    </div>
                @endif

                {{-- Form Login --}}
                <form action="{{ url('/login') }}" method="POST">
                    @csrf

                    {{-- Username --}}
                <div class="mb-3">
                    <label for="username" class="form-label">
                        Username <span class="text-danger">*</span>
                    </label>

                    <div class="input-icon">
                        <i class="fa-regular fa-user leading"></i>

                        <input
                            id="username"
                            type="text"
                            name="username"
                            value="{{ old('username') }}"
                            required
                            autofocus
                            placeholder="Masukkan username"
                            class="form-control @error('username') is-invalid @enderror">
                    </div>

                        @error('username')
                            <div class="text-danger small mt-1">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>

                    {{-- Password --}}
                    <div class="mb-3">
                        <label for="password" class="form-label">
                            Password <span class="text-danger">*</span>
                        </label>
                        <div class="input-icon">
                            <i class="fa-solid fa-lock leading"></i>
                            <input
                                id="password"
                                type="password"
                                name="password"
                                required
                                placeholder="••••••••"
                                class="form-control has-toggle @error('password') is-invalid @enderror">
                            <button type="button" class="toggle-password" onclick="togglePassword()">
                                <i id="toggleIcon" class="fa-regular fa-eye"></i>
                            </button>
                        </div>
                        @error('password')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Remember + Forgot --}}
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <div class="form-check">
                            <input type="checkbox" name="remember" class="form-check-input" id="remember">
                            <label class="form-check-label small text-muted" for="remember">
                                Ingat saya
                            </label>
                        </div>

                        @if (Route::has('password.request'))
                            <a href="{{ route('password.request') }}" class="link-brand small">
                                Lupa password?
                            </a>
                        @endif
                    </div>

                    {{-- Submit --}}
                    <button type="submit" class="btn btn-brand w-100 d-flex align-items-center justify-content-center gap-2">
                        <i class="fa-solid fa-right-to-bracket"></i>
                        Masuk
                    </button>
                </form>
                    <p class="text-center small text-muted mt-3 mb-0">
                        Belum punya akun?
                        <a href="{{ route('register') }}" class="link-brand">Daftar di sini</a>
                    </p>
            </div>
        </div>

        {{-- Footer --}}
        <p class="text-center text-muted small mt-4 mb-0">
            &copy; {{ date('Y') }} Letter Management System. All rights reserved.
        </p>
    </div>

    <script>
        function togglePassword() {
            const input = document.getElementById('password');
            const icon = document.getElementById('toggleIcon');
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        }
    </script>
</body>
</html>
