<aside class="sidebar">
    <div class="sidebar-top">
        <div class="logo">
            <div class="logo-badge">
                <i class="bi bi-envelope-paper"></i>
            </div>
            <div>
                <h4>Sistem Surat</h4>
            </div>
        </div>

        <ul class="nav-menu">
            <li>
                <a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                    <i class="bi bi-grid-1x2"></i>
                    <span>Halaman Utama</span>
                </a>
            </li>

            @php
                $manajemenSuratActive = request()->routeIs('tambahsurat') || request()->routeIs('keepnomorsurat') || request()->routeIs('riwayatsurat');
            @endphp
            <li class="nav-group">
                <div class="nav-link nav-link-parent nav-link-static {{ $manajemenSuratActive ? 'active' : '' }}">
                    <i class="bi bi-file-earmark-text"></i>
                    <span>Manajemen Surat</span>
                </div>

                <div id="manajemenSuratMenu">
                    <ul class="nav-submenu">
                        <li>
                            <a href="{{ route('tambahsurat') }}"
                               class="nav-sublink {{ request()->routeIs('tambahsurat') ? 'active' : '' }}">
                                <i class="bi bi-file-earmark-plus"></i>
                                <span>Buat Nomor Surat</span>
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('keepnomorsurat') }}"
                               class="nav-sublink {{ request()->routeIs('keepnomorsurat') ? 'active' : '' }}">
                                <i class="bi bi-file-earmark"></i>
                                <span>Cadangan Nomor Surat</span>
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('riwayatsurat') }}"
                                class="nav-sublink {{ request()->routeIs('riwayatsurat') ? 'active' : '' }}">
                                <i class="bi bi-folder2-open"></i>
                                <span>Riwayat Surat</span>
                            </a>
                        </li>
                    </ul>
                </div>
            </li>

            @php
                $manajemenKontrakActive = request()->routeIs('kontrak.*') || request()->routeIs('karyawan.*');
            @endphp
            <li class="nav-group">
                <div class="nav-link nav-link-parent nav-link-static {{ $manajemenKontrakActive ? 'active' : '' }}">
                    <i class="bi bi-file-earmark-ruled"></i>
                    <span>Manajemen Kontrak</span>
                </div>

                <div id="manajemenKontrakMenu">
                    <ul class="nav-submenu">
                        <li>
                            <a href="{{ route('kontrak.create') }}"
                               class="nav-sublink {{ request()->routeIs('kontrak.create') || request()->routeIs('kontrak.store') ? 'active' : '' }}">
                                <i class="bi bi-file-earmark-plus"></i>
                                <span>Buat Kontrak</span>
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('kontrak.index') }}"
                               class="nav-sublink {{ request()->routeIs('kontrak.index') || request()->routeIs('kontrak.show') || request()->routeIs('kontrak.upload.form') ? 'active' : '' }}">
                                <i class="bi bi-folder2-open"></i>
                                <span>Daftar Kontrak</span>
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('karyawan.index') }}"
                               class="nav-sublink {{ request()->routeIs('karyawan.*') ? 'active' : '' }}">
                                <i class="bi bi-people"></i>
                                <span>Data Karyawan</span>
                            </a>
                        </li>
                    </ul>
                </div>
            </li>

            <li class="nav-group">
                <div class="nav-link nav-link-parent nav-link-static {{ request()->routeIs('arsipkasbon.*') ? 'active' : '' }}">
                    <i class="bi bi-receipt"></i>
                    <span class="flex-grow-1">Surat Permintaan Pembayaran</span>
                </div>
                <div id="arsipSppSubmenu">
                    <ul class="nav-submenu">
                        <li>
                            <a href="{{ route('arsipkasbon.create') }}"
                               class="nav-sublink {{ request()->routeIs('arsipkasbon.create') ? 'active' : '' }}">
                                <i class="bi bi-camera"></i>
                                <span>Unggah Surat Baru</span>
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('arsipkasbon.index') }}"
                               class="nav-sublink {{ request()->routeIs('arsipkasbon.index') || request()->routeIs('arsipkasbon.show') ? 'active' : '' }}">
                                <i class="bi bi-clock-history"></i>
                                <span>Riwayat Arsip SPP</span>
                            </a>
                        </li>
                    </ul>
                </div>
            </li>

            <li class="nav-group">
                <div class="nav-link nav-link-parent nav-link-static {{ request()->routeIs('wisma-tamu.*') ? 'active' : '' }}">
                    <i class="bi bi-building"></i>
                    <span>Wisma Tamu</span>
                </div>
                <div id="wismaTamuMenu">
                    <ul class="nav-submenu">
                        <li>
                            <a href="{{ route('wisma-tamu.create') }}"
                               class="nav-sublink {{ request()->routeIs('wisma-tamu.create') || request()->routeIs('wisma-tamu.store') ? 'active' : '' }}">
                                <i class="bi bi-person-plus"></i>
                                <span>Input Tamu</span>
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('wisma-tamu.index') }}"
                               class="nav-sublink {{ request()->routeIs('wisma-tamu.index') || request()->routeIs('wisma-tamu.edit') ? 'active' : '' }}">
                                <i class="bi bi-door-open"></i>
                                <span>Daftar Kamar &amp; Tamu</span>
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('wisma-tamu.tv') }}" target="_blank" class="nav-sublink">
                                <i class="bi bi-tv"></i>
                                <span>Buka Tampilan TV</span>
                            </a>
                        </li>
                    </ul>
                </div>
            </li>

        </ul>
    </div>

    <div class="user-profile">
        <div class="avatar">A</div>
        <div class="user-meta">
            <strong>Admin</strong>
            {{-- <small>Online</small> --}}
        </div>
    </div>
</aside>

<style>
    @import url('https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,400;9..144,600;9..144,700&family=Inter:wght@400;500;600;700&family=IBM+Plex+Mono:wght@500;600&display=swap');

    .sidebar {
        position: fixed;
        top: 60px;
        left: 0;
        bottom: 0;
        width: 280px;
        overflow-y: auto;
        z-index: 900;

        display: flex;
        flex-direction: column;
        justify-content: space-between;
        padding: 1.5rem 1.1rem;
        background: linear-gradient(145deg, #064e3b 0%, #083d2e 100%);
        color: #f8fafc;
        box-shadow: 18px 0 40px rgba(6, 78, 59, 0.18);
        font-family: 'Inter', -apple-system, sans-serif;
    }

    .sidebar-top {
        display: flex;
        flex-direction: column;
        gap: 1.4rem;
    }

    .logo {
        display: flex;
        align-items: center;
        gap: 0.9rem;
        padding: 0.3rem 0.2rem;
    }

    .logo-badge {
        width: 46px;
        height: 46px;
        border-radius: 14px;
        display: grid;
        place-items: center;
        background: linear-gradient(135deg, #34d399, #059669);
        color: white;
        font-size: 1.15rem;
        box-shadow: 0 10px 24px rgba(5, 150, 105, 0.22);
    }

    .logo h4 {
        margin: 0;
        font-family: 'Fraunces', Georgia, serif;
        font-size: 1.05rem;
        font-weight: 600;
        letter-spacing: -0.01em;
    }

    .logo small {
        font-family: 'IBM Plex Mono', ui-monospace, monospace;
        font-size: 0.72rem;
        letter-spacing: 0.03em;
        color: rgba(248, 250, 252, 0.7);
    }

    .nav-menu {
        list-style: none;
        padding: 0;
        margin: 0;
        display: flex;
        flex-direction: column;
        gap: 0.45rem;
    }

    .nav-link {
        display: flex;
        align-items: center;
        gap: 0.8rem;
        text-decoration: none;
        color: rgba(248, 250, 252, 0.86);
        padding: 0.8rem 0.95rem;
        border-radius: 12px;
        transition: all 0.2s ease;
        font-family: 'Inter', -apple-system, sans-serif;
        font-weight: 600;
    }

    .nav-link:hover {
        background: rgba(255, 255, 255, 0.11);
        color: white;
        transform: translateX(2px);
    }

    .nav-link-static:hover {
        transform: none;
    }

    .nav-link.active {
        background: linear-gradient(135deg, #10b981, #047857);
        color: white;
        box-shadow: 0 10px 24px rgba(4, 120, 87, 0.18);
    }

    .nav-link i {
        font-size: 1rem;
        width: 18px;
        text-align: center;
    }

    .nav-group {
        display: flex;
        flex-direction: column;
    }

    .nav-link-parent {
        cursor: default;
    }

    .nav-link-static {
        margin-bottom: 0.1rem;
    }

    .nav-submenu {
        list-style: none;
        margin: 0.35rem 0 0.15rem;
        padding: 0 0 0 1.6rem;
        display: flex;
        flex-direction: column;
        gap: 0.3rem;
        border-left: 1px solid rgba(255, 255, 255, 0.14);
        margin-left: 1.15rem;
    }

    .nav-sublink {
    display: flex;
    align-items: center;
    gap: 0.7rem;
    text-decoration: none;
    color: rgba(248, 250, 252, 0.75);
    padding: 0.6rem 0.85rem;
    border-radius: 10px;
    font-family: 'Inter', -apple-system, sans-serif;
    font-weight: 500;
    font-size: 0.9rem;
    transition: all 0.2s ease;
}

    .nav-sublink i {
        font-size: 0.85rem;
        width: 16px;
        text-align: center;
    }

    .nav-sublink:hover {
        background: rgba(255, 255, 255, 0.1);
        color: white;
    }

    .nav-sublink.active {
        background: linear-gradient(135deg, #10b981, #047857);
        color: white;
        box-shadow: 0 8px 18px rgba(4, 120, 87, 0.18);
    }

    .user-profile {
        display: flex;
        align-items: center;
        gap: 0.8rem;
        padding: 0.95rem 1rem;
        background: rgba(255, 255, 255, 0.08);
        border: 1px solid rgba(255, 255, 255, 0.12);
        border-radius: 16px;
    }

    .avatar {
        width: 42px;
        height: 42px;
        border-radius: 50%;
        display: grid;
        place-items: center;
        background: linear-gradient(135deg, #f59e0b, #fb923c);
        font-family: 'Fraunces', Georgia, serif;
        font-weight: 700;
        color: white;
    }

    .user-meta {
        display: flex;
        flex-direction: column;
        line-height: 1.2;
        font-family: 'Inter', -apple-system, sans-serif;
    }

    .user-meta small {
        font-family: 'IBM Plex Mono', ui-monospace, monospace;
        font-size: 0.72rem;
        letter-spacing: 0.03em;
        color: rgba(248, 250, 252, 0.7);
    }

    @media (max-width: 767px) {
        .sidebar {
            position: fixed;
            top: 60px;
            left: 0;
            bottom: 0;
            width: 280px;
            height: auto;
            box-shadow: 18px 0 40px rgba(6, 78, 59, 0.25);
            transform: translateX(-100%);
            transition: transform 0.3s ease;
            z-index: 1050;
        }

        .sidebar.sidebar-open {
            transform: translateX(0);
        }
    }
</style>