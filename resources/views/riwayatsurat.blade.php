@extends('layouts.app')

@section('content')

<style>
    /* ==========================================================================
       Riwayat Surat — Registry Ledger Theme (matches Tambah Surat)
       Same token set / typography / card language as tambahsurat.blade.php.
       ========================================================================== */

    @import url('https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,400;9..144,600;9..144,700&family=Inter:wght@400;500;600;700&family=IBM+Plex+Mono:wght@500;600&display=swap');

    :root {
        /* Color tokens — identical to Tambah Surat */
        --ink:        #1c2b23;
        --ink-soft:   #3d4f45;
        --ledger:     #eef3ea;
        --ledger-line:#cdd9c8;
        --paper:      #fbfcf9;
        --brass:      #a9812f;
        --brass-dark: #8a6a24;
        --brass-tint: #f4ecd8;
        --line:       #dfe6da;
        --danger:     #b3432f;
        --danger-bg:  #fdf1ee;
        --success:    #3f6b4a;
        --success-bg: #eef5ef;

        --font-display: 'Fraunces', Georgia, serif;
        --font-body: 'Inter', -apple-system, sans-serif;
        --font-mono: 'IBM Plex Mono', ui-monospace, monospace;
    }

    /* ==========================================================================
       Page base — same ledger paper canvas as Tambah Surat
       ========================================================================== */

    .ledger-page {
        background: transparent;
        font-family: var(--font-body);
        color: var(--ink);
        min-height: 100vh;
    }

    /* Breadcrumb */
    .ledger-breadcrumb {
        background: transparent;
        padding: 0;
        color: var(--ink-soft);
        font-family: var(--font-mono);
        font-size: 0.72rem;
        letter-spacing: 0.06em;
        text-transform: uppercase;
    }
    .ledger-breadcrumb .breadcrumb-item a {
        color: var(--ink-soft);
        text-decoration: none;
    }
    .ledger-breadcrumb .breadcrumb-item.active {
        color: var(--brass-dark);
        font-weight: 600;
    }

    /* Alert */
    .ledger-alert-success {
        background: var(--success-bg);
        border: 1px solid #cfe2d4;
        color: var(--success);
        border-radius: 0.75rem;
        font-size: 0.9rem;
    }

    /* ==========================================================================
       Cards — same surface, radius, shadow as Tambah Surat
       ========================================================================== */

    .ledger-card {
        background: var(--paper);
        border: 1px solid var(--line);
        border-radius: 0.9rem;
        box-shadow: 0 1px 2px rgba(28,43,35,0.05), 0 1px 10px rgba(28,43,35,0.04);
    }

    .ledger-title {
        font-family: var(--font-display);
        font-weight: 600;
        font-size: 1.55rem;
        color: var(--ink);
        letter-spacing: -0.01em;
    }

    .ledger-subtitle {
        color: var(--ink-soft);
        font-size: 0.85rem;
    }

    /* Counter badge — brass stamp pill, echoes the registrar's stamp on Tambah Surat */
    .ledger-badge {
        background: var(--brass-tint);
        color: var(--brass-dark);
        font-family: var(--font-mono);
        font-size: 0.78rem;
        font-weight: 600;
        letter-spacing: 0.03em;
        padding: 0.4rem 0.85rem;
        border-radius: 999px;
        white-space: nowrap;
        display: inline-block;
        border: 1px solid rgba(169,129,47,0.25);
    }

    /* ==========================================================================
       Toolbar — same input styling language as Tambah Surat's form fields
       ========================================================================== */

    #searchInput,
    #filterKlasifikasi,
    #sortOrder {
        font-family: var(--font-body);
        color: var(--ink);
        background-color: var(--paper);
        border: 1px solid var(--line);
        border-radius: 0.55rem;
        padding: 0.65rem 0.95rem;
        font-size: 0.92rem;
    }

    .ledger-input-icon {
        background-color: var(--ledger);
        border: 1px solid var(--line);
        border-right: none;
        color: var(--ink-soft);
        border-radius: 0.55rem 0 0 0.55rem;
    }

    .input-group #searchInput {
        border-radius: 0 0.55rem 0.55rem 0;
    }

    #searchInput:focus,
    #filterKlasifikasi:focus,
    #sortOrder:focus {
        outline: none;
        border-color: var(--brass);
        box-shadow: 0 0 0 3px rgba(169,129,47,0.16);
    }

    #searchInput:focus-visible,
    #filterKlasifikasi:focus-visible,
    #sortOrder:focus-visible {
        outline: 2px solid var(--brass-dark);
        outline-offset: 2px;
    }

    #filterKlasifikasi,
    #sortOrder {
        cursor: pointer;
    }

    /* ==========================================================================
       Archive table — same ledger surface/ink language, now as a real <table>
       ========================================================================== */

    #archiveCard {
        overflow: hidden;
    }

    .ledger-table {
        width: 100%;
        margin-bottom: 0;
        border-collapse: collapse;
    }

    .ledger-table thead th {
        background: var(--ledger);
        color: var(--ink-soft);
        font-family: var(--font-mono);
        font-size: 0.7rem;
        letter-spacing: 0.1em;
        text-transform: uppercase;
        font-weight: 600;
        border-bottom: none;
        padding: 0.9rem 1.5rem;
        white-space: nowrap;
    }

    .ledger-table tbody tr {
        border-top: 1px solid var(--ledger-line);
        transition: background 0.15s ease;
    }

    .ledger-table tbody tr:hover {
        background: var(--brass-tint);
    }

    .ledger-table tbody td {
        padding: 1rem 1.5rem;
        vertical-align: middle;
        font-size: 0.9rem;
    }

    .ledger-table .ledger-nomor {
        color: var(--brass-dark);
        font-family: var(--font-mono);
        font-weight: 600;
        font-size: 0.84rem;
        letter-spacing: 0.02em;
        word-break: break-all;
    }

    .ledger-table .ledger-perihal {
        color: var(--ink);
    }

    .ledger-table .ledger-tujuan,
    .ledger-table .ledger-signatory {
        color: var(--ink-soft);
    }

    .ledger-table .ledger-tanggal {
        color: var(--ink-soft);
        font-family: var(--font-mono);
        font-size: 0.82rem;
        white-space: nowrap;
    }

    /* Status pill */
    .ledger-status-pill {
        font-family: var(--font-mono);
        font-size: 0.68rem;
        font-weight: 600;
        letter-spacing: 0.05em;
        text-transform: uppercase;
        padding: 0.32rem 0.7rem;
        border-radius: 999px;
        white-space: nowrap;
        display: inline-block;
    }

    .ledger-status-pill.is-uploaded {
        background: var(--success-bg);
        color: var(--success);
        border: 1px solid #cfe2d4;
    }

    .ledger-status-pill.is-pending {
        background: var(--brass-tint);
        color: var(--brass-dark);
        border: 1px solid rgba(169,129,47,0.25);
    }

    .ledger-btn-detail {
        background: transparent;
        border: 1px solid var(--ink);
        color: var(--ink-soft);
        font-family: var(--font-body);
        font-weight: 600;
        font-size: 0.8rem;
        padding: 0.4rem 0.9rem;
        border-radius: 0.5rem;
        white-space: nowrap;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        transition: background 0.15s ease, color 0.15s ease;
    }

    .ledger-btn-detail:hover {
        background: var(--ink);
        color: #fff;
    }

    /* ==========================================================================
       Empty states — same brass/dashed accent as Tambah Surat's stamp box
       ========================================================================== */

    .ledger-empty {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        text-align: center;
        padding: 3.5rem 2rem;
        color: var(--ink-soft);
    }

    .ledger-empty i {
        font-size: 2rem;
        color: var(--ledger-line);
        margin-bottom: 1rem;
    }

    .ledger-empty p {
        margin: 0 0 1rem 0;
        font-size: 0.92rem;
    }

    .ledger-cta {
        font-family: var(--font-mono);
        font-size: 0.82rem;
        font-weight: 600;
        color: var(--brass-dark);
        text-decoration: none;
        border-bottom: 1px dashed var(--brass-dark);
        padding-bottom: 2px;
    }

    .ledger-cta:hover {
        color: var(--brass);
        border-color: var(--brass);
    }

    /* ==========================================================================
       Responsive — let the table scroll horizontally on small screens
       instead of squeezing/breaking columns
       ========================================================================== */

    .ledger-table-scroll {
        overflow-x: auto;
    }

    @media (prefers-reduced-motion: reduce) {
        * {
            transition: none !important;
        }
    }
</style>

<div class="ledger-page" id="riwayatPage">
    <div class="container-fluid py-1 py-md-2">

        {{-- Alert sukses (jika ada aksi hapus dll) --}}
        @if (session('success'))
            <div class="alert ledger-alert-success d-flex align-items-center gap-2" role="alert">
                <i class="fa-solid fa-circle-check"></i>
                <div>{{ session('success') }}</div>
            </div>
        @endif

        {{-- Header + Toolbar --}}
        <div class="card ledger-card mb-4">
            <div class="card-body">
                <div class="d-flex align-items-start justify-content-between flex-wrap gap-3">
                    <div>
                        <h2 class="ledger-title mb-1">Riwayat Surat</h2>
                    </div>
                    <div id="totalCounter">
                        <span class="ledger-badge">
                            <span id="totalCount">{{ count($suratList ?? []) }}</span> surat tercatat
                        </span>
                    </div>
                </div>

                {{-- Toolbar: search + filter klasifikasi (sesuai tabel klasifikasi_surat) + sort --}}
                <div class="row g-3 mt-3">
                    <div class="col-md-6">
                        <div class="input-group">
                            <span class="input-group-text ledger-input-icon">
                                <i class="fa-solid fa-magnifying-glass"></i>
                            </span>
                            <input
                                type="text"
                                id="searchInput"
                                placeholder="Cari nomor surat atau perihal..."
                                class="form-control">
                        </div>
                    </div>

                    <div class="col-md-3">
                        <select id="filterKlasifikasi" class="form-select">
                            <option value="">Semua Klasifikasi</option>
                            @foreach ($klasifikasiList ?? [] as $klasifikasi)
                                <option value="{{ $klasifikasi->kode }}">{{ $klasifikasi->kode }} — {{ $klasifikasi->jenis_surat }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-3">
                        <select id="sortOrder" class="form-select">
                            <option value="desc">Terbaru dulu</option>
                            <option value="asc">Terlama dulu</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        {{-- Archive table --}}
        <div class="card ledger-card" id="archiveCard">
            @if (count($suratList ?? []) > 0)
                <div class="ledger-table-scroll">
                    <table class="ledger-table">
                        <thead>
                            <tr>
                                <th>Nomor Surat</th>
                                <th>Perihal</th>
                                <th>Tujuan</th>
                                <th>Penandatangan</th>
                                <th>Tanggal Dibuat</th>
                                <th>Status</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody id="archiveList">
                            @foreach ($suratList as $surat)
                                @php
                                    $isUploaded = ($surat->status ?? 'Belum Terupload') === 'Terupload';
                                @endphp
                                <tr
                                    data-perihal="{{ strtolower($surat->perihal) }}"
                                    data-nomor="{{ strtolower($surat->nomor_surat) }}"
                                    data-klasifikasi="{{ $surat->klasifikasiSurat->kode ?? '' }}"
                                    data-tanggal="{{ $surat->tanggal }}">
                                    <td class="ledger-nomor">{{ $surat->nomor_surat }}</td>
                                    <td class="ledger-perihal">{{ $surat->perihal }}</td>
                                    <td class="ledger-tujuan">{{ $surat->tujuanSurat->nama_tujuan ?? '-' }}</td>
                                    <td class="ledger-signatory">{{ $surat->penandatangan->jabatan ?? '-' }}</td>
                                    <td class="ledger-tanggal">{{ $surat->tanggal }}</td>
                                    <td>
                                        <span class="ledger-status-pill {{ $isUploaded ? 'is-uploaded' : 'is-pending' }}">
                                            {{ $surat->status ?? 'Belum Terupload' }}
                                        </span>
                                    </td>
                                    <td>
                                        <a href="{{ route('surat.upload.show', $surat->id) }}" class="ledger-btn-detail">
                                            <i class="fa-regular fa-eye"></i>
                                            Detail
                                        </a>

                                    </td>

                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div id="emptySearchState" class="d-none">
                    <div class="ledger-empty">
                        <i class="fa-solid fa-folder-open"></i>
                        <p>Tidak ada surat yang cocok dengan pencarian.</p>
                    </div>
                </div>
            @else
                <div class="ledger-empty">
                    <i class="fa-solid fa-box-archive"></i>
                    <p>Belum ada surat yang tercatat.</p>
                    <a href="{{ route('tambahsurat') }}" class="ledger-cta">Buat surat pertama</a>
                </div>
            @endif
        </div>
    </div>
</div>

<script>
    const searchInput = document.getElementById('searchInput');
    const filterKlasifikasi = document.getElementById('filterKlasifikasi');
    const sortOrder = document.getElementById('sortOrder');
    const archiveList = document.getElementById('archiveList');
    const emptySearchState = document.getElementById('emptySearchState');
    const totalCount = document.getElementById('totalCount');

    function applyFilters() {
        if (!archiveList) return;

        const query = searchInput.value.trim().toLowerCase();
        const klasifikasi = filterKlasifikasi.value;
        const rows = Array.from(archiveList.querySelectorAll('tr'));

        let visibleCount = 0;

        rows.forEach(row => {
            const matchQuery = !query ||
                row.dataset.nomor.includes(query) ||
                row.dataset.perihal.includes(query);
            const matchKlasifikasi = !klasifikasi || row.dataset.klasifikasi === klasifikasi;
            const visible = matchQuery && matchKlasifikasi;

            row.classList.toggle('d-none', !visible);
            if (visible) visibleCount++;
        });

        totalCount.textContent = visibleCount;

        if (emptySearchState) {
            emptySearchState.classList.toggle('d-none', visibleCount !== 0);
        }
    }

    function applySort() {
        if (!archiveList) return;

        const rows = Array.from(archiveList.querySelectorAll('tr'));
        const direction = sortOrder.value;

        rows.sort((a, b) => {
            const dateA = new Date(a.dataset.tanggal);
            const dateB = new Date(b.dataset.tanggal);
            return direction === 'asc' ? dateA - dateB : dateB - dateA;
        });

        rows.forEach(row => archiveList.appendChild(row));
    }

    if (searchInput) {
        searchInput.addEventListener('input', applyFilters);
        filterKlasifikasi.addEventListener('change', applyFilters);
        sortOrder.addEventListener('change', () => {
            applySort();
            applyFilters();
        });
    }
</script>

@endsection
