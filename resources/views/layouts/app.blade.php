<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Sistem Manajemen Surat</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

    {{-- <link rel="stylesheet" href="{{ asset('css/assets/style.css') }}"> --}}

   {{-- <link rel="stylesheet" href="{{ asset('asset/tambahsurat.css') }}"> --}}

    @stack('styles')

    <style>
        body {
            margin: 0;
            background: #f6f7fb;
            font-family: 'Inter', 'Segoe UI', sans-serif;
        }

        .app-shell {
            min-height: 100vh;
            width: 100%;
        }

        .content {
            margin-left: 280px;
            padding: 30px;
            padding-top: calc(30px + 60px);
            overflow-x: hidden;
        }

        @media (max-width: 767px) {
            .content {
                margin-left: 0;
                padding-top: 30px;
            }
        }

        /* ---- Tombol hamburger (muncul di layar kecil) ---- */
        .sidebar-toggle-btn {
            display: none;
            border: none;
            background: #f0fdf4;
            color: #064e3b;
            width: 42px;
            height: 42px;
            border-radius: 10px;
            font-size: 1.35rem;
            align-items: center;
            justify-content: center;
            margin-right: 0.7rem;
        }

        @media (max-width: 767px) {
            .sidebar-toggle-btn {
                display: inline-flex;
            }
        }

        .sidebar-backdrop {
            display: none;
            position: fixed;
            top: 60px;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(6, 20, 15, 0.45);
            z-index: 1040;
        }

        .sidebar-backdrop.show {
            display: block;
        }

        .navbar-custom {
            height: 60px;
            width: 100%;
            display: flex;
            align-items: center;
            background: #fff;
            border-bottom: 1px solid #e9ecef;
            position: fixed;
            top: 0;
            left: 0;
            z-index: 1000;
            padding: 0 20px;
        }

        .profile-btn {
            border: none;
            background: none;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .profile-btn i {
            color: #033689;
        }

        .profile-btn .bi-person-circle {
            font-size: 34px;
        }

        .profile-btn .bi-chevron-down {
            font-size: 18px;
        }

        .dropdown-menu {
            border: none;
            border-radius: 12px;
            box-shadow: 0 5px 20px rgba(0,0,0,.1);
        }

        .dropdown-item {
            padding: 10px 15px;
        }

        .dropdown-item:hover {
            background: #f5f7fb;
        }

        .logo {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .logo img {
            height: 65px;
            width: auto;
            object-fit: contain;
        }
    </style>
</head>
<body>

{{-- Navbar atas, fixed --}}
<nav class="navbar navbar-expand-lg navbar-custom">
    <div class="container-fluid">

        <div class="logo d-flex align-items-center gap-3">
            <img src="{{ asset('images/logosgn.png') }}" class="logo-img">
            <img src="{{ asset('images/pglogo.png') }}" class="logo-img">
        </div>

        <div class="d-flex align-items-center ms-auto">
            <button class="sidebar-toggle-btn" id="sidebarToggleBtn" type="button" aria-label="Buka menu">
                <i class="bi bi-list"></i>
            </button>

            <div class="dropdown">
                <button class="profile-btn" data-bs-toggle="dropdown">
                    <i class="bi bi-person-circle"></i>
                    <i class="bi bi-chevron-down"></i>
                </button>

                <ul class="dropdown-menu dropdown-menu-end">
                    <li class="dropdown-header">
                        <strong>Administrator</strong><br>
                        <small>{{ session('user_email') }}</small>
                    </li>
                    <li><hr class="dropdown-divider"></li>
                    <li>
                        <form action="{{ route('logout') }}" method="POST">
                            @csrf
                            <button class="dropdown-item text-danger">
                                <i class="bi bi-box-arrow-right me-2"></i>
                                Logout
                            </button>
                        </form>
                    </li>
                </ul>
            </div>
        </div>

    </div>
</nav>

<div class="sidebar-backdrop" id="sidebarBackdrop"></div>

<div class="app-shell">

    @include('sidebar')

    <main class="content">
        @yield('content')
    </main>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const toggleBtn = document.getElementById('sidebarToggleBtn');
        const sidebar = document.querySelector('.sidebar');
        const backdrop = document.getElementById('sidebarBackdrop');

        if (!toggleBtn || !sidebar || !backdrop) return;

        function openSidebar() {
            sidebar.classList.add('sidebar-open');
            backdrop.classList.add('show');
        }

        function closeSidebar() {
            sidebar.classList.remove('sidebar-open');
            backdrop.classList.remove('show');
        }

        toggleBtn.addEventListener('click', function () {
            sidebar.classList.contains('sidebar-open') ? closeSidebar() : openSidebar();
        });

        backdrop.addEventListener('click', closeSidebar);

        // Tutup otomatis kalau salah satu link menu diklik (biar nggak nutupin konten setelah pindah halaman)
        sidebar.querySelectorAll('a').forEach(function (link) {
            link.addEventListener('click', closeSidebar);
        });

        // Kalau layar dibesarin balik ke ukuran desktop, pastikan sidebar & backdrop reset
        window.addEventListener('resize', function () {
            if (window.innerWidth > 767) closeSidebar();
        });
    });
</script>

@stack('scripts') {{-- <<< BARIS BARU: tempat nampung @push('scripts') dari child view --}}

</body>
</html>