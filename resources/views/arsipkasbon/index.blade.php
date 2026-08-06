@extends('layouts.app')

@section('content')

<style>
    @import url('https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,400;9..144,600;9..144,700&family=Inter:wght@400;500;600;700&family=IBM+Plex+Mono:wght@500;600&display=swap');

    :root {
        --ink: #1c2b23; --ink-soft: #3d4f45; --ink-faint: #7c8a80; --paper: #fbfcf9;
        --brass: #a9812f; --brass-dark: #8a6a24; --brass-bg: #fbf6ea; --line: #dfe6da;
        --success: #3f6b4a; --success-bg: #eef5ef;
        --font-display: 'Fraunces', Georgia, serif;
        --font-body: 'Inter', -apple-system, sans-serif;
        --font-mono: 'IBM Plex Mono', ui-monospace, monospace;
    }

    /* (Catatan: sebelumnya di sini ada max-width untuk halaman, tapi ternyata
       tidak diperlukan — perbaikan gap kolom sudah cukup lewat table-layout:auto
       di bawah. max-width malah bikin konten menciut padahal areanya masih lebar,
       jadi dihapus supaya konten kembali mengisi penuh lebar yang tersedia.) */

    .page-header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 1.75rem; }
    .page-title { font-family: var(--font-display); font-weight: 700; font-size: 1.75rem; color: var(--ink); }
    .page-subtitle { color: var(--ink-soft); font-size: 0.92rem; margin-top: 0.2rem; }

    .ledger-btn-brass {
        background: linear-gradient(180deg, #b8903f, var(--brass-dark));
        box-shadow: 0 4px 14px rgba(138,106,36,0.35);
        border: none; color: #fff; font-weight: 600;
        padding: 0.7rem 1.4rem; border-radius: 0.65rem; text-decoration: none;
        display: inline-flex; align-items: center; font-size: 0.92rem;
        transition: transform 0.15s ease;
    }
    .ledger-btn-brass:hover { color: #fff; transform: translateY(-1px); }

    .ledger-card { background: var(--paper); border: 1px solid var(--line); border-radius: 1rem; box-shadow: 0 1px 2px rgba(28,43,35,0.05), 0 1px 12px rgba(28,43,35,0.04); }

    /* ---- Search hero ---- */
    .search-hero { padding: 1.5rem 1.75rem; }
    .search-hero-label { font-family: var(--font-mono); font-size: 0.7rem; letter-spacing: 0.08em; text-transform: uppercase; color: var(--ink-faint); margin-bottom: 0.6rem; }
    .search-hero-box { position: relative; }
    .search-hero-box input {
        width: 100%; padding: 0.95rem 1.1rem 0.95rem 3.1rem; border: 1.5px solid var(--line);
        border-radius: 0.8rem; font-size: 1rem; color: var(--ink); background: #fff;
        transition: border-color 0.15s ease, box-shadow 0.15s ease;
    }
    .search-hero-box input:focus {
        outline: none; border-color: var(--brass); box-shadow: 0 0 0 4px rgba(169,129,47,0.12);
    }
    .search-hero-box .search-icon {
        position: absolute; left: 1.1rem; top: 50%; transform: translateY(-50%);
        color: var(--ink-faint); font-size: 1rem; pointer-events: none;
    }
    .search-hero-box .clear-btn {
        position: absolute; right: 0.6rem; top: 50%; transform: translateY(-50%);
        border: none; background: var(--line); color: var(--ink-soft);
        width: 30px; height: 30px; border-radius: 50%; display: grid; place-items: center;
        text-decoration: none; font-size: 0.85rem; cursor: pointer;
    }
    .search-hint { font-size: 0.78rem; color: var(--ink-faint); margin-top: 0.55rem; }
    .search-hint strong { color: var(--ink-soft); }

    /* ---- Filter row: tanggal + reset + export ---- */
    .filter-row {
        display: flex; align-items: flex-end; gap: 1rem; flex-wrap: wrap;
    }
    .filter-date-group { display: flex; flex-direction: column; gap: 0.3rem; }
    .filter-date-label {
        font-family: var(--font-mono); font-size: 0.68rem; letter-spacing: 0.06em;
        text-transform: uppercase; color: var(--ink-faint);
    }
    .filter-date-group input[type="date"] {
        border: 1.5px solid var(--line); border-radius: 0.6rem; padding: 0.55rem 0.8rem;
        font-size: 0.88rem; color: var(--ink); background: #fff;
    }
    .filter-date-group input[type="date"]:focus {
        outline: none; border-color: var(--brass); box-shadow: 0 0 0 4px rgba(169,129,47,0.12);
    }
    .filter-row .ledger-btn-ghost {
        padding: 0.55rem 0.9rem; border: 1.5px solid var(--line); border-radius: 0.6rem;
        font-size: 0.85rem;
    }

    /* ---- Highlighted search match ---- */
    mark.search-hl {
        background: var(--brass-bg);
        color: var(--brass-dark);
        font-weight: 700;
        padding: 0 2px;
        border-radius: 3px;
    }

    /* ---- Results container (swapped via AJAX) ---- */
    #resultsContainer { transition: opacity 0.15s ease; }

    /* ---- Stats strip ---- */
    .stats-strip { display: flex; gap: 1rem; padding: 1.25rem 1.75rem 0; flex-wrap: wrap; }
    .stat-chip {
        background: #fff; border: 1px solid var(--line); border-radius: 0.7rem;
        padding: 0.7rem 1.1rem; display: flex; align-items: center; gap: 0.6rem;
        font-size: 0.85rem; color: var(--ink-soft);
    }
    .stat-chip strong { color: var(--ink); font-family: var(--font-mono); font-size: 0.95rem; }
    .stat-chip i { color: var(--brass-dark); }

    /* ---- Table ----
       width: 100% (header perlu penuh selebar card supaya tidak keliatan
       "terpotong"). Sisa ruang kosong sekarang diserap oleh 1 kolom spacer
       tak-terlihat di paling kanan (lihat .spacer-col), BUKAN oleh salah
       satu kolom data — jadi header tetap full, tapi tidak ada gap aneh
       nyempil di antara data. */
    table.kasbon-table { margin: 0; width: 100%; table-layout: auto; }
    table.kasbon-table .spacer-col { width: 100%; }
    table.kasbon-table thead th {
        font-family: var(--font-mono); font-size: 0.68rem; letter-spacing: 0.06em;
        text-transform: uppercase; color: var(--ink-faint); border-bottom: 1px solid var(--line);
        padding: 0.9rem 1rem; background: #f7f8f5; white-space: nowrap;
    }
    table.kasbon-table tbody td { vertical-align: middle; font-size: 0.9rem; padding: 0.95rem 1rem; border-bottom: 1px solid var(--line); }
    table.kasbon-table tbody tr:last-child td { border-bottom: none; }
    table.kasbon-table tbody tr { transition: background 0.12s ease; }
    table.kasbon-table tbody tr:hover { background: #f9f7f0; }

    .vendor-cell { display: flex; align-items: center; gap: 0.7rem; }
    .vendor-avatar {
        width: 34px; height: 34px; border-radius: 50%; flex-shrink: 0;
        display: grid; place-items: center; font-family: var(--font-display); font-weight: 700;
        background: linear-gradient(135deg, #d9c68f, var(--brass-dark)); color: #fff; font-size: 0.85rem;
    }
    .vendor-name { font-weight: 600; color: var(--ink); }
    .vendor-doc { font-family: var(--font-mono); font-size: 0.76rem; color: var(--ink-faint); }

    .amount-val { font-family: var(--font-mono); font-weight: 700; color: var(--ink); white-space: nowrap; }
    .akun-badge {
        font-family: var(--font-mono); font-size: 0.72rem; font-weight: 600;
        background: var(--brass-bg); color: var(--brass-dark); border: 1px solid #ecdfb8;
        padding: 0.25rem 0.55rem; border-radius: 6px; margin-right: 0.25rem; display: inline-block;
        white-space: nowrap;
    }
    .akun-more { font-size: 0.76rem; color: var(--ink-faint); white-space: nowrap; }

    .action-cell { display: flex; justify-content: flex-end; }
    .action-btn-detail {
        display: inline-flex; align-items: center; white-space: nowrap;
        padding: 0.45rem 0.9rem; border-radius: 0.55rem;
        border: 1px solid var(--line); color: var(--ink-soft); background: #fff; text-decoration: none;
        font-size: 0.84rem; font-weight: 600;
        transition: all 0.15s ease;
    }
    .action-btn-detail:hover { background: var(--brass-dark); border-color: var(--brass-dark); color: #fff; }

    .empty-state { text-align: center; padding: 3.5rem 1.5rem; color: var(--ink-soft); }
    .empty-state i { font-size: 2rem; color: var(--line); margin-bottom: 0.8rem; display: block; }
    .empty-state strong { color: var(--ink); }

    /* ---- Pagination — brass instead of Bootstrap blue ---- */
    #resultsContainer .pagination { gap: 0.3rem; flex-wrap: wrap; }
    #resultsContainer .page-item .page-link {
        font-family: var(--font-mono); font-size: 0.82rem; font-weight: 600;
        color: var(--ink-soft); background-color: #fff; border: 1px solid var(--line);
        border-radius: 0.5rem; padding: 0.45rem 0.75rem;
        transition: background 0.15s ease, color 0.15s ease, border-color 0.15s ease;
    }
    #resultsContainer .page-item .page-link:hover {
        background-color: var(--brass-bg); color: var(--brass-dark); border-color: rgba(169,129,47,0.35);
    }
    #resultsContainer .page-item.active .page-link {
        background-color: var(--brass-dark); border-color: var(--brass-dark); color: #fff;
    }
    #resultsContainer .page-item.disabled .page-link {
        color: var(--line); background-color: #fff; border-color: var(--line); opacity: 0.7;
    }

    /* ==================== Export ke Excel: spinner + status selesai ==================== */
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
    #exportBtn.is-loading { opacity: 0.7; pointer-events: none; }
</style>

<div class="container-fluid">

    <div class="page-header">
        <div>
            <div class="page-title">Riwayat Arsip SPP</div>
            <div class="page-subtitle">Semua Surat Permintaan Pembayaran (SPP) yang sudah diarsipkan.</div>
        </div>
        <a href="{{ route('arsipkasbon.create') }}" class="ledger-btn-brass">
            <i class="bi bi-camera-fill me-2"></i> Upload Surat Baru
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success mb-4">{{ session('success') }}</div>
    @endif

    {{-- Card 1: search hero + filter --}}
    <div class="ledger-card mb-4">
        <div class="search-hero">
            <div class="search-hero-label">Cari &amp; Filter Arsip</div>
            <div class="search-hero-box">
                <i class="bi bi-search search-icon"></i>
                <input type="text" id="searchInput" value="{{ request('q') }}"
                       placeholder="Cari nama vendor, no dokumen, no akun, tanggal, cek/giro, deskripsi, terbilang...">
                <a href="#" class="clear-btn d-none" id="clearSearchBtn" title="Reset pencarian">
                    <i class="bi bi-x-lg"></i>
                </a>
            </div>
            <div class="search-hint">
                {{-- Pencarian mencakup <strong>semua informasi surat</strong> — header maupun rincian akun. Hasil muncul otomatis saat kamu mengetik. --}}
            </div>

            <div class="filter-row mt-3">
                <div class="filter-date-group">
                    <label class="filter-date-label" for="filterTanggalDari">Dari tanggal</label>
                    <input type="date" id="filterTanggalDari" value="{{ request('tanggal_dari') }}">
                </div>
                <div class="filter-date-group">
                    <label class="filter-date-label" for="filterTanggalSampai">Sampai tanggal</label>
                    <input type="date" id="filterTanggalSampai" value="{{ request('tanggal_sampai') }}">
                </div>
                <button type="button" class="btn ledger-btn-ghost" id="resetFilterBtn">
                    <i class="bi bi-arrow-counterclockwise me-1"></i> Reset Filter
                </button>
                <div class="d-flex align-items-center gap-2 ms-md-auto">
                    <a href="{{ route('arsipkasbon.export') }}" class="btn btn-outline-secondary" id="exportBtn">
                        <i class="bi bi-file-earmark-excel"></i> Export ke Excel
                    </a>
                    <span class="export-spinner d-none" id="exportSpinner" role="status" aria-hidden="true"></span>
                    <span class="export-done-text d-none" id="exportDoneText">
                        <i class="bi bi-check-circle-fill"></i> Selesai
                    </span>
                </div>
            </div>
        </div>
    </div>

    {{-- Card 2: hasil / daftar arsip --}}
    <div class="ledger-card mb-4">
        <div id="resultsContainer">
            @include('arsipkasbon.partials.results')
        </div>
    </div>

</div>

@push('scripts')
<script>
(function () {
    const searchInput   = document.getElementById('searchInput');
    const tanggalDari   = document.getElementById('filterTanggalDari');
    const tanggalSampai = document.getElementById('filterTanggalSampai');
    const resetBtn      = document.getElementById('resetFilterBtn');
    const clearBtn      = document.getElementById('clearSearchBtn');
    const exportBtn     = document.getElementById('exportBtn');
    const resultsContainer = document.getElementById('resultsContainer');

    const indexUrl  = '{{ route('arsipkasbon.index') }}';
    const exportUrl = '{{ route('arsipkasbon.export') }}';

    let debounceTimer = null;

    function currentParams() {
        const params = new URLSearchParams();
        if (searchInput.value.trim()) params.set('q', searchInput.value.trim());
        if (tanggalDari.value) params.set('tanggal_dari', tanggalDari.value);
        if (tanggalSampai.value) params.set('tanggal_sampai', tanggalSampai.value);
        return params;
    }

    function updateExportLink(params) {
        const qs = params.toString();
        exportBtn.href = qs ? `${exportUrl}?${qs}` : exportUrl;
    }

    function updateClearBtn() {
        clearBtn.classList.toggle('d-none', !searchInput.value.trim());
    }

    function attachPaginationHandlers() {
        resultsContainer.querySelectorAll('.pagination a.page-link').forEach(link => {
            link.addEventListener('click', function (e) {
                e.preventDefault();
                const url = new URL(this.href);
                const page = url.searchParams.get('page');
                fetchResults(page);
                resultsContainer.scrollIntoView({ behavior: 'smooth', block: 'start' });
            });
        });
    }

    function fetchResults(page) {
        const params = currentParams();
        if (page) params.set('page', page);

        updateExportLink(params);
        resultsContainer.style.opacity = '0.45';

        fetch(`${indexUrl}?${params.toString()}`, {
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
        })
        .then(r => r.text())
        .then(html => {
            resultsContainer.innerHTML = html;
            resultsContainer.style.opacity = '1';
            attachPaginationHandlers();

            const qs = params.toString();
            const newUrl = qs ? `${window.location.pathname}?${qs}` : window.location.pathname;
            history.replaceState(null, '', newUrl);
        })
        .catch(() => {
            resultsContainer.style.opacity = '1';
        });
    }

    searchInput.addEventListener('input', () => {
        updateClearBtn();
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(() => fetchResults(), 350);
    });

    // Tetap jaga-jaga: kalau ada yang pencet Enter, langsung cari tanpa nunggu debounce
    searchInput.addEventListener('keydown', (e) => {
        if (e.key === 'Enter') {
            e.preventDefault();
            clearTimeout(debounceTimer);
            fetchResults();
        }
    });

    [tanggalDari, tanggalSampai].forEach(el => {
        el.addEventListener('change', () => fetchResults());
    });

    clearBtn.addEventListener('click', (e) => {
        e.preventDefault();
        searchInput.value = '';
        updateClearBtn();
        fetchResults();
    });

    resetBtn.addEventListener('click', () => {
        searchInput.value = '';
        tanggalDari.value = '';
        tanggalSampai.value = '';
        updateClearBtn();
        fetchResults();
    });

    updateClearBtn();
    updateExportLink(currentParams());
    attachPaginationHandlers();

    // ==================== Export ke Excel: spinner saat loading + "Selesai" ====================
    const exportSpinner  = document.getElementById('exportSpinner');
    const exportDoneText = document.getElementById('exportDoneText');
    let exportDoneTimer  = null;

    function extractFilename(response, fallback) {
        const header = response.headers.get('Content-Disposition') || '';
        const match = header.match(/filename\*?=(?:UTF-8'')?"?([^";]+)"?/i);
        return match ? decodeURIComponent(match[1]) : fallback;
    }

    exportBtn.addEventListener('click', function (e) {
        e.preventDefault();
        if (exportBtn.classList.contains('is-loading')) return;

        clearTimeout(exportDoneTimer);
        exportDoneText.classList.add('d-none');
        exportBtn.classList.add('is-loading');
        exportSpinner.classList.remove('d-none');

        fetch(exportBtn.href, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
            .then(response => {
                if (!response.ok) throw new Error('Export gagal');
                const filename = extractFilename(response, 'arsip-spp.xlsx');
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
                exportBtn.classList.remove('is-loading');
            });
    });
})();
</script>
@endpush

@endsection