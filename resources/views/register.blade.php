<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Letter Management System</title>

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

        .btn-google {
            background: #fff;
            border: 1px solid #dcdcdc;
            color: #3c4043;
        }

        .btn-google:hover {
            background: #f7f7f7;
            color: #3c4043;
        }

        .divider {
            display: flex;
            align-items: center;
            text-align: center;
            color: #adb5bd;
            font-size: .8rem;
            margin: 1.25rem 0;
        }

        .divider::before,
        .divider::after {
            content: "";
            flex: 1;
            border-bottom: 1px solid #eee;
        }

        .divider:not(:empty)::before {
            margin-right: .75rem;
        }

        .divider:not(:empty)::after {
            margin-left: .75rem;
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

        {{-- Card Register --}}
        <div class="card login-card">

            <div class="card-header">
                <h2 class="h5 mb-1">Buat Akun Baru</h2>
                <p class="text-muted small mb-0">Lengkapi data di bawah untuk mendaftar.</p>
            </div>

            <div class="card-body">

                @if ($errors->any())
                    <div class="alert alert-danger py-2 small" role="alert">
                        {{ $errors->first() }}
                    </div>
                @endif

                <form action="{{ route('register.store') }}" method="POST">
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
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Email --}}
                    <div class="mb-3">
                        <label for="email" class="form-label">
                            Email <span class="text-danger">*</span>
                        </label>
                        <div class="input-icon">
                            <i class="fa-regular fa-envelope leading"></i>
                            <input
                                id="email"
                                type="email"
                                name="email"
                                value="{{ old('email') }}"
                                required
                                placeholder="nama@email.com"
                                class="form-control @error('email') is-invalid @enderror">
                        </div>
                        @error('email')
                            <div class="text-danger small mt-1">{{ $message }}</div>
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
                                placeholder="Minimal 8 karakter"
                                class="form-control has-toggle @error('password') is-invalid @enderror">
                            <button type="button" class="toggle-password" onclick="togglePassword('password','toggleIcon1')">
                                <i id="toggleIcon1" class="fa-regular fa-eye"></i>
                            </button>
                        </div>
                        @error('password')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Confirm Password --}}
                    <div class="mb-3">
                        <label for="password_confirmation" class="form-label">
                            Konfirmasi Password <span class="text-danger">*</span>
                        </label>
                        <div class="input-icon">
                            <i class="fa-solid fa-lock leading"></i>
                            <input
                                id="password_confirmation"
                                type="password"
                                name="password_confirmation"
                                required
                                placeholder="Ulangi password"
                                class="form-control has-toggle">
                            <button type="button" class="toggle-password" onclick="togglePassword('password_confirmation','toggleIcon2')">
                                <i id="toggleIcon2" class="fa-regular fa-eye"></i>
                            </button>
                        </div>
                    </div>

                    {{-- Submit --}}
                    <button type="submit" class="btn btn-brand w-100 d-flex align-items-center justify-content-center gap-2">
                        <i class="fa-solid fa-user-plus"></i>
                        Daftar
                    </button>
                </form>

                <div class="divider">atau</div>

                {{-- Register via Google --}}
                <a href="{{ route('google.redirect') }}" class="btn btn-google w-100 d-flex align-items-center justify-content-center gap-2">
                    <img src="{{ asset('images/google.png') }}" alt="Google" width="25" height="20">
                    Daftar dengan Google
                </a>

                <p class="text-center small text-muted mt-3 mb-0">
                    Sudah punya akun?
                    <a href="{{ route('login') }}" class="link-brand">Masuk di sini</a>
                </p>
            </div>
        </div>

        <p class="text-center text-muted small mt-4 mb-0">
            &copy; {{ date('Y') }} Letter Management System. All rights reserved.
        </p>
    </div>

    <script>
        function togglePassword(inputId, iconId) {
            const input = document.getElementById(inputId);
            const icon = document.getElementById(iconId);
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
