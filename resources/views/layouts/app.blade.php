<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
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
            overflow-x: hidden;   /* <-- kontrol scroll horizontal di sini, bukan di navbar */
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
                padding-top: calc(15px + 60px);
                padding-left: 15px;
                padding-right: 15px;
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
            /* JANGAN tambah overflow:hidden di sini */
        }

        @media (max-width: 480px) {
            .navbar-custom {
                padding: 0 12px;
            }
        }
        .notif-bell-btn {
            border: none;
            background: none;
            position: relative;
            width: 42px;
            height: 42px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 10px;
            color: #033689;
            font-size: 1.2rem;
        }
        .notif-bell-btn:hover { background: #f5f7fb; }
        .notif-badge {
            position: absolute;
            top: 4px;
            right: 4px;
            min-width: 16px;
            height: 16px;
            padding: 0 3px;
            border-radius: 999px;
            background: #ef4444;
            color: #fff;
            font-size: 0.62rem;
            font-weight: 700;
            display: flex;
            align-items: center;
            justify-content: center;
            line-height: 1;
        }
        .notif-dropdown { width: 320px; max-height: 380px; overflow-y: auto; padding-bottom: 0.3rem; }
        .notif-item {
            display: block;
            padding: 0.6rem 1rem;
            font-size: 0.85rem;
            color: #1f2937;
            text-decoration: none;
            border-bottom: 1px solid #f1f5f9;
        }
        .notif-item:hover { background: #f8faf9; }
        .notif-item .notif-time { font-size: 0.72rem; color: #9ca3af; display: block; margin-top: 2px; }

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
            min-width: 0;        /* biar flex child bisa nyusut, cegah overflow */
            overflow: hidden;     /* kalau logo kepanjangan, ini yang motong, bukan navbar */
        }

        .logo img {
            height: 45px;
            width: auto;
            object-fit: contain;
            flex-shrink: 0;
        }

        @media (max-width: 480px) {
            .logo {
                gap: 6px;
            }
            .logo img {
                height: 32px;    /* logo mengecil di layar sempit */
            }
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

        <div class="d-flex align-items-center ms-auto gap-1">
            <button class="sidebar-toggle-btn" id="sidebarToggleBtn" type="button" aria-label="Buka menu">
                <i class="bi bi-three-dots-vertical"></i>
            </button>

            @auth
            <div class="dropdown">
                <button class="notif-bell-btn" data-bs-toggle="dropdown" id="notifBellBtn">
                    <i class="bi bi-bell"></i>
                    <span id="notifBadge" class="notif-badge d-none">0</span>
                </button>

                <ul class="dropdown-menu dropdown-menu-end notif-dropdown" id="notifDropdown">
                    <li class="dropdown-header d-flex justify-content-between align-items-center">
                        <strong>Notifikasi</strong>
                        <form action="{{ route('notifikasi.baca-semua') }}" method="POST" class="m-0">
                            @csrf
                            <button type="submit" class="btn btn-link btn-sm p-0" style="font-size: 0.78rem;">Tandai semua dibaca</button>
                        </form>
                    </li>
                    <li><hr class="dropdown-divider"></li>
                    <li id="notifEmpty" class="px-3 py-2 text-muted small">Belum ada notifikasi.</li>
                    <ul id="notifList" class="list-unstyled m-0"></ul>
                </ul>
            </div>
            @endauth

            <div class="dropdown">
                <button class="profile-btn" data-bs-toggle="dropdown">
                    <i class="bi bi-person-circle"></i>
                    <i class="bi bi-chevron-down"></i>
                </button>

                <ul class="dropdown-menu dropdown-menu-end">
                    <li class="dropdown-header">
                        <strong>{{ auth()->user()->username ?? 'Administrator' }}</strong><br>
                        <small>{{ auth()->user()?->labelRole() ?? session('user_email') }}</small>
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

    @auth
    // ===== Polling notifikasi lonceng navbar =====
    (function () {
        const badge = document.getElementById('notifBadge');
        const list = document.getElementById('notifList');
        const empty = document.getElementById('notifEmpty');
        if (!badge || !list) return;

        async function muatNotifikasi() {
            try {
                const res = await fetch(@json(route('notifikasi.data')), {
                    headers: { 'Accept': 'application/json' }
                });
                if (!res.ok) return;
                const data = await res.json();

                if (data.unread_count > 0) {
                    badge.textContent = data.unread_count > 9 ? '9+' : data.unread_count;
                    badge.classList.remove('d-none');
                } else {
                    badge.classList.add('d-none');
                }

                list.innerHTML = '';
                if (data.items.length === 0) {
                    empty.classList.remove('d-none');
                } else {
                    empty.classList.add('d-none');
                    data.items.forEach(item => {
                        const li = document.createElement('li');
                        const csrf = document.querySelector('meta[name="csrf-token"]')?.content
                            || document.querySelector('input[name="_token"]')?.value || '';
                        li.innerHTML = `
                            <form action="/notifikasi/${item.id}/baca" method="POST" class="m-0">
                                <input type="hidden" name="_token" value="${csrf}">
                                <button type="submit" class="notif-item text-start w-100 border-0 bg-transparent">
                                    ${item.pesan}
                                    <span class="notif-time">${item.waktu}</span>
                                </button>
                            </form>
                        `;
                        list.appendChild(li);
                    });
                }
            } catch (e) { /* diam, coba lagi siklus berikutnya */ }
        }

        muatNotifikasi();
        setInterval(muatNotifikasi, 20000);
    })();
    @endauth
</script>

@stack('scripts') {{-- <<< BARIS BARU: tempat nampung @push('scripts') dari child view --}}

</body>
</html>
