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

    .ledger-status-pill.is-reserved {
        background: var(--brass-tint);
        color: var(--brass-dark);
        border: 1px solid rgba(169,129,47,0.25);
    }

    /* ==========================================================================
       Toolbar — same input styling language as Tambah Surat's form fields
       ========================================================================== */

    .ledger-help {
        color: var(--ink-soft);
        font-family: var(--font-mono);
        font-size: 0.72rem;
        letter-spacing: 0.03em;
        text-transform: uppercase;
    }

    .ledger-btn-ghost {
        background: transparent;
        border: 1px solid var(--line);
        color: var(--ink-soft);
        font-weight: 600;
        font-size: 0.9rem;
        border-radius: 0.55rem;
        padding: 0.65rem 0.95rem;
        transition: background 0.15s ease, color 0.15s ease, border-color 0.15s ease;
    }

    .ledger-btn-ghost:hover {
        background: var(--ledger);
        border-color: var(--ledger-line);
        color: var(--ink);
    }

    #searchInput,
    #filterKlasifikasi,
    #sortOrder,
    #filterTanggalDari,
    #filterTanggalSampai {
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
    #sortOrder:focus,
    #filterTanggalDari:focus,
    #filterTanggalSampai:focus {
        outline: none;
        border-color: var(--brass);
        box-shadow: 0 0 0 3px rgba(169,129,47,0.16);
    }

    #searchInput:focus-visible,
    #filterKlasifikasi:focus-visible,
    #sortOrder:focus-visible,
    #filterTanggalDari:focus-visible,
    #filterTanggalSampai:focus-visible {
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

    .ledger-table .ledger-no {
        color: var(--ink-soft);
        font-family: var(--font-mono);
        font-size: 0.82rem;
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

    /* ==========================================================================
       Pagination — override Bootstrap's default blue to match ledger theme
       ========================================================================== */

    #archiveCard .pagination {
        gap: 0.3rem;
        flex-wrap: wrap;
    }

    #archiveCard .page-item .page-link {
        font-family: var(--font-mono);
        font-size: 0.82rem;
        font-weight: 600;
        color: var(--ink-soft);
        background-color: var(--paper);
        border: 1px solid var(--line);
        border-radius: 0.5rem;
        padding: 0.45rem 0.75rem;
        transition: background 0.15s ease, color 0.15s ease, border-color 0.15s ease;
    }

    #archiveCard .page-item .page-link:hover {
        background-color: var(--brass-tint);
        color: var(--brass-dark);
        border-color: rgba(169,129,47,0.35);
    }

    #archiveCard .page-item .page-link:focus {
        outline: none;
        box-shadow: 0 0 0 3px rgba(169,129,47,0.16);
        border-color: var(--brass);
    }

    #archiveCard .page-item.active .page-link {
        background-color: var(--brass-dark);
        border-color: var(--brass-dark);
        color: #fff;
    }

    #archiveCard .page-item.disabled .page-link {
        color: var(--ledger-line);
        background-color: var(--paper);
        border-color: var(--line);
        opacity: 0.7;
    }

    #exportExcelBtn.btn-outline-secondary {
        border-color: var(--ink);
        color: var(--ink-soft);
        font-weight: 600;
        font-size: 0.85rem;
        border-radius: 0.5rem;
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
    }

    #exportExcelBtn.btn-outline-secondary:hover {
        background: var(--ink-soft);
        border-color: rgba(28,43,35,0.04);
        color: var(--brass-tint);
    }

    #exportExcelBtn.is-loading {
        opacity: 0.7;
        pointer-events: none;
    }

    .export-spinner {
        display: inline-block; width: 1.05rem; height: 1.05rem;
        border: 2px solid rgba(169,129,47,0.25); border-top-color: var(--brass-dark);
        border-radius: 50%; animation: export-spin 0.7s linear infinite;
    }
    @keyframes export-spin { to { transform: rotate(360deg); } }
    .export-done-text {
        font-size: 0.82rem; font-weight: 600; color: var(--success, #2f7d4f);
        display: inline-flex; align-items: center; gap: 0.3rem;
        animation: export-fade-in 0.15s ease-in;
    }
    @keyframes export-fade-in { from { opacity: 0; } to { opacity: 1; } }

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
                    <div class="d-flex align-items-center gap-2" id="totalCounter">
                        <a href="#" id="exportExcelBtn" class="btn btn-outline-secondary">
                            <i class="bi bi-file-earmark-excel"></i> Export Excel
                        </a>
                        <span class="export-spinner d-none" id="exportSpinner" role="status" aria-hidden="true"></span>
                        <span class="export-done-text d-none" id="exportDoneText">
                            <i class="bi bi-check-circle-fill"></i> Selesai
                        </span>
                        <span class="ledger-badge">
                            <span id="totalCount">{{ $suratList->total() ?? 0 }}</span> surat tercatat
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

                    <div class="col-md-3">
                        <label for="filterTanggalDari" class="ledger-help mb-1 d-block">Dari tanggal</label>
                        <input type="date" id="filterTanggalDari" class="form-control">
                    </div>
                    <div class="col-md-3">
                        <label for="filterTanggalSampai" class="ledger-help mb-1 d-block">Sampai tanggal</label>
                        <input type="date" id="filterTanggalSampai" class="form-control">
                    </div>
                    <div class="col-md-3 d-flex align-items-end">
                        <button type="button" id="resetTanggalBtn" class="btn ledger-btn-ghost w-100">
                            Reset Tanggal
                        </button>
                    </div>
                </div>
            </div>
        </div>

        {{-- Archive table --}}
        <div class="card ledger-card" id="archiveCard">
            @if ($suratList->count() > 0)
                <div class="ledger-table-scroll">
                    <table class="ledger-table">
                        <thead>
                            <tr>
                                <th style="width:1%;">No</th>
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
                               $statusClass = match($surat->status ?? 'Belum Terupload') {
                                    'Terupload'    => 'is-uploaded',
                                    'Direservasi'  => 'is-reserved',
                                    default        => 'is-pending',
                                };

                                $statusLabel = match($surat->status ?? 'Belum Terupload') {
                                    'Terupload'    => 'Terupload',
                                    'Direservasi'  => 'Dicadangkan',
                                    default        => 'Belum Terupload',
                                };
                            @endphp
                            <tr
                                data-perihal="{{ strtolower($surat->perihal) }}"
                                data-nomor="{{ strtolower($surat->nomor_surat) }}"
                                data-klasifikasi="{{ $surat->klasifikasiSurat->kode ?? '' }}"
                                data-tanggal="{{ $surat->tanggal }}">
                                <td class="ledger-no"></td>
                                <td class="ledger-nomor">{{ $surat->nomor_surat }}</td>
                                <td class="ledger-perihal">{{ $surat->perihal }}</td>
                                <td class="ledger-tujuan">{{ $surat->tujuanSurat->nama_tujuan ?? '-' }}</td>
                                <td class="ledger-signatory">{{ $surat->penandatangan->jabatan ?? '-' }}</td>
                                <td class="ledger-tanggal">{{ $surat->tanggal }}</td>
                                <td>
                                    <span class="ledger-status-pill {{ $statusClass }}">
                                        {{ $statusLabel }}
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

                @if ($suratList->hasPages())
                    <div class="card-body">
                        {{ $suratList->links() }}
                    </div>
                @endif

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
    const filterTanggalDari = document.getElementById('filterTanggalDari');
    const filterTanggalSampai = document.getElementById('filterTanggalSampai');
    const resetTanggalBtn = document.getElementById('resetTanggalBtn');
    const archiveList = document.getElementById('archiveList');
    const emptySearchState = document.getElementById('emptySearchState');
    const totalCount = document.getElementById('totalCount');

    // Nomor urut mengikuti halaman pagination yang lagi aktif, jadi kalau
    // paginate-nya 10/halaman dan lagi di halaman 2, nomornya lanjut dari 11.
    // Ambil dari elemen page-link yang aktif; fallback ke 1 kalau tidak ada pagination.
    function currentPageOffset() {
        const activePageLink = document.querySelector('#archiveCard .page-item.active .page-link');
        if (!activePageLink) return 0;
        const pageNum = parseInt(activePageLink.textContent.trim(), 10);
        if (isNaN(pageNum)) return 0;
        // Asumsi jumlah per halaman = jumlah baris di halaman pertama render awal.
        const perPage = archiveList ? archiveList.querySelectorAll('tr').length : 0;
        return (pageNum - 1) * perPage;
    }

    // Beri nomor urut 1,2,3... hanya untuk baris yang sedang terlihat (tidak
    // ada class d-none), sesuai urutan tampil saat ini di DOM (setelah sort).
    function renumberVisibleRows() {
        if (!archiveList) return;
        const offset = currentPageOffset();
        let n = 1;
        Array.from(archiveList.querySelectorAll('tr')).forEach(row => {
            const noCell = row.querySelector('.ledger-no');
            if (!noCell) return;
            if (row.classList.contains('d-none')) {
                noCell.textContent = '';
            } else {
                noCell.textContent = offset + n;
                n++;
            }
        });
    }

    function applyFilters() {
        if (!archiveList) return;

        const query = searchInput.value.trim().toLowerCase();
        const klasifikasi = filterKlasifikasi.value;
        const tanggalDari = filterTanggalDari.value; // format YYYY-MM-DD, cocok buat dibandingkan string langsung
        const tanggalSampai = filterTanggalSampai.value;
        const rows = Array.from(archiveList.querySelectorAll('tr'));

        let visibleCount = 0;

        rows.forEach(row => {
            const matchQuery = !query ||
                row.dataset.nomor.includes(query) ||
                row.dataset.perihal.includes(query);
            const matchKlasifikasi = !klasifikasi || row.dataset.klasifikasi === klasifikasi;

            const rowTanggal = (row.dataset.tanggal || '').slice(0, 10);
            const matchTanggalDari = !tanggalDari || (rowTanggal && rowTanggal >= tanggalDari);
            const matchTanggalSampai = !tanggalSampai || (rowTanggal && rowTanggal <= tanggalSampai);

            const visible = matchQuery && matchKlasifikasi && matchTanggalDari && matchTanggalSampai;

            row.classList.toggle('d-none', !visible);
            if (visible) visibleCount++;
        });

        totalCount.textContent = visibleCount;

        if (emptySearchState) {
            emptySearchState.classList.toggle('d-none', visibleCount !== 0);
        }

        renumberVisibleRows();
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
        filterTanggalDari.addEventListener('change', applyFilters);
        filterTanggalSampai.addEventListener('change', applyFilters);
        sortOrder.addEventListener('change', () => {
            applySort();
            applyFilters();
        });
    }

    if (resetTanggalBtn) {
        resetTanggalBtn.addEventListener('click', () => {
            filterTanggalDari.value = '';
            filterTanggalSampai.value = '';
            applyFilters();
        });
    }

    // Nomori baris begitu halaman selesai dimuat.
    renumberVisibleRows();

    const exportExcelBtn = document.getElementById('exportExcelBtn');
    const exportSpinner = document.getElementById('exportSpinner');
    const exportDoneText = document.getElementById('exportDoneText');
    let exportDoneTimer = null;

    function extractFilename(response, fallback) {
        const header = response.headers.get('Content-Disposition') || '';
        const match = header.match(/filename\*?=(?:UTF-8'')?"?([^";]+)"?/i);
        return match ? decodeURIComponent(match[1]) : fallback;
    }

    if (exportExcelBtn) {
        exportExcelBtn.addEventListener('click', function (e) {
            e.preventDefault();
            if (exportExcelBtn.classList.contains('is-loading')) return;

            const params = new URLSearchParams();

            if (searchInput && searchInput.value.trim()) {
                params.set('search', searchInput.value.trim());
            }
            if (filterKlasifikasi && filterKlasifikasi.value) {
                params.set('klasifikasi', filterKlasifikasi.value);
            }
            if (sortOrder && sortOrder.value) {
                params.set('sort', sortOrder.value);
            }
            if (filterTanggalDari && filterTanggalDari.value) {
                params.set('tanggal_dari', filterTanggalDari.value);
            }
            if (filterTanggalSampai && filterTanggalSampai.value) {
                params.set('tanggal_sampai', filterTanggalSampai.value);
            }

            const exportUrl = `{{ route('surat.export') }}?${params.toString()}`;

            clearTimeout(exportDoneTimer);
            exportDoneText.classList.add('d-none');
            exportExcelBtn.classList.add('is-loading');
            exportSpinner.classList.remove('d-none');

            fetch(exportUrl, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                .then(response => {
                    if (!response.ok) throw new Error('Export gagal');
                    const filename = extractFilename(response, 'riwayat-surat.xlsx');
                    return response.blob().then(blob => ({ blob, filename }));
                })
                .then(({ blob, filename }) => {
                    const url = URL.createObjectURL(blob);
                    const a = document.createElement('a');
                    a.href = url;
                    a.download = filename;
                    document.body.appendChild(a);
                    a.click();
                    a.remove();
                    URL.revokeObjectURL(url);

                    exportSpinner.classList.add('d-none');
                    exportDoneText.classList.remove('d-none');
                    exportDoneTimer = setTimeout(() => {
                        exportDoneText.classList.add('d-none');
                    }, 2500);
                })
                .catch(() => {
                    exportSpinner.classList.add('d-none');
                    alert('Export ke Excel gagal. Coba lagi.');
                })
                .finally(() => {
                    exportExcelBtn.classList.remove('is-loading');
                });
        });
    }
</script>

@endsection