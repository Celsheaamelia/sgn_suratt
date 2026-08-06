@extends('layouts.app')

@section('content')

<style>
    @import url('https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,400;9..144,600;9..144,700&family=Inter:wght@400;500;600;700&family=IBM+Plex+Mono:wght@500;600&display=swap');

    :root {
        --ink: #1c2b23; --ink-soft: #3d4f45; --ink-faint: #7c8a80; --paper: #fbfcf9;
        --brass: #a9812f; --brass-dark: #8a6a24; --brass-bg: #fbf6ea; --line: #dfe6da;
        --font-display: 'Fraunces', Georgia, serif;
        --font-body: 'Inter', -apple-system, sans-serif;
        --font-mono: 'IBM Plex Mono', ui-monospace, monospace;
    }
    .ledger-card { background: var(--paper); border: 1px solid var(--line); border-radius: 0.9rem; box-shadow: 0 1px 2px rgba(28,43,35,0.05), 0 1px 10px rgba(28,43,35,0.04); }
    .ledger-card-header { border-bottom: 1px solid var(--line); padding: 1.1rem 1.5rem; }
    .ledger-title { font-family: var(--font-display); font-weight: 600; font-size: 1.2rem; color: var(--ink); }
    .ledger-subtitle { color: var(--ink-soft); font-size: 0.85rem; }
    .ledger-card .card-body { padding: 1.4rem 1.5rem; }
    .ledger-btn-ghost { background: transparent; color: var(--ink-soft); font-weight: 500; border: none; padding: 0.6rem 1rem; text-decoration: none; }
    .ledger-btn-ghost:hover { color: var(--ink); }

    /* ---- Hero: hasil scan + ringkasan cepat ---- */
    .hero-card { display: flex; gap: 1.5rem; padding: 1.5rem; align-items: stretch; flex-wrap: wrap; }
    .hero-thumb {
        flex: 0 0 200px; width: 200px; height: 200px; border-radius: 0.8rem; overflow: hidden;
        border: 1px solid var(--line); cursor: zoom-in; position: relative; background: #eee;
    }
    .hero-thumb img { width: 100%; height: 100%; object-fit: cover; transition: transform 0.2s ease; }
    .hero-thumb:hover img { transform: scale(1.05); }
    .hero-thumb-pdf {
        width: 100%; height: 100%; display: flex; flex-direction: column; align-items: center;
        justify-content: center; gap: 0.3rem; background: #fdf1ee; color: var(--danger, #b3432f);
    }
    .hero-thumb-pdf i { font-size: 2rem; }
    .hero-thumb-pdf span { font-family: var(--font-mono); font-size: 0.7rem; font-weight: 700; letter-spacing: 0.05em; }
    .hero-thumb-overlay {
        position: absolute; inset: 0; background: rgba(28,43,35,0.0); display: flex; align-items: center;
        justify-content: center; color: #fff; opacity: 0; transition: all 0.15s ease; font-size: 1.3rem;
    }
    .hero-thumb:hover .hero-thumb-overlay { opacity: 1; background: rgba(28,43,35,0.35); }
    .hero-empty-thumb {
        flex: 0 0 200px; width: 200px; height: 200px; border-radius: 0.8rem;
        border: 1.5px dashed var(--line); display: flex; flex-direction: column; align-items: center;
        justify-content: center; color: var(--ink-faint); gap: 0.4rem; font-size: 0.82rem; text-align: center;
    }

    .hero-info { flex: 1; min-width: 240px; display: flex; flex-direction: column; justify-content: center; gap: 0.6rem; }
    .hero-doc-no { font-family: var(--font-mono); font-size: 0.78rem; color: var(--brass-dark); font-weight: 600; letter-spacing: 0.03em; }
    .hero-vendor { font-family: var(--font-display); font-weight: 700; font-size: 1.5rem; color: var(--ink); }
    .hero-stats { display: flex; gap: 1.6rem; flex-wrap: wrap; margin-top: 0.3rem; }
    .hero-stat-label { font-size: 0.72rem; text-transform: uppercase; letter-spacing: 0.05em; color: var(--ink-faint); font-family: var(--font-mono); }
    .hero-stat-val { font-weight: 700; color: var(--ink); font-size: 1rem; }

    .hero-actions { flex: 0 0 auto; display: flex; flex-direction: column; gap: 0.6rem; justify-content: center; }

    /* ---- Header Surat: grid 2 kolom, tiap baris label & value SEBARIS ---- */
    .kv-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 0 1.75rem; }
    .kv-grid .kv-full { grid-column: 1 / -1; }
    .kv-row {
        display: flex; align-items: baseline; justify-content: space-between; gap: 0.75rem;
        padding: 0.7rem 0; border-bottom: 1px solid var(--line);
    }
    .kv-grid .kv-row:nth-last-child(-n+2) { border-bottom: none; }
    .kv-key {
        font-size: 0.82rem; color: var(--ink-soft); font-family: var(--font-body);
        flex: 0 0 auto; white-space: nowrap;
    }
    .kv-val { color: var(--ink); font-weight: 600; font-size: 0.92rem; text-align: right; }
    .kv-grid .kv-full {
        flex-direction: column; align-items: flex-start; gap: 0.15rem;
    }
    .kv-grid .kv-full .kv-key { font-size: 0.72rem; text-transform: uppercase; letter-spacing: 0.04em; color: var(--ink-faint); font-family: var(--font-mono); }
    .kv-grid .kv-full .kv-val { text-align: left; }

    /* ---- Tabel rincian akun: item text 1 baris, ellipsis ---- */
    table.rincian-table thead th {
        font-family: var(--font-mono); font-size: 0.68rem; text-transform: uppercase; color: var(--ink-faint);
        border-bottom: 1px solid var(--line); white-space: nowrap; padding: 0.75rem 1rem;
    }
    table.rincian-table tbody td { vertical-align: middle; font-size: 0.87rem; padding: 0.8rem 1rem; border-bottom: 1px solid var(--line); }
    table.rincian-table tbody tr:last-child td { border-bottom: none; }
    .item-text-cell {
        max-width: 220px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; cursor: help;
    }
    @media (max-width: 991px) {
        .item-text-cell { max-width: 140px; }
    }

    /* ---- Modal preview gambar ---- */
    .scan-modal .modal-content { background: transparent; border: none; }
    .scan-modal .modal-body { display: flex; align-items: center; justify-content: center; padding: 0; }
    .scan-modal img { max-width: 100%; max-height: 85vh; border-radius: 0.5rem; box-shadow: 0 20px 60px rgba(0,0,0,0.5); }
    .scan-modal .btn-close { filter: invert(1); position: absolute; top: -2.5rem; right: 0; }
    .scan-pdf-wrapper { display: flex; flex-direction: column; align-items: center; gap: 0.75rem; width: 100%; }
    .scan-pdf-wrapper .scan-pdf-frame {
        width: 100%; height: 80vh; border: none; border-radius: 0.5rem;
        box-shadow: 0 20px 60px rgba(0,0,0,0.5); background: #fff;
    }
</style>

<div class="container-fluid">

    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb" style="font-family: var(--font-mono); font-size:0.72rem; letter-spacing:0.06em; text-transform:uppercase;">
            {{-- <li class="breadcrumb-item"><a href="{{ route('arsipkasbon.index') }}" style="color:var(--ink-soft); text-decoration:none;">Arsip SPP</a></li> --}}
            {{-- <li class="breadcrumb-item active" style="color:var(--brass-dark); font-weight:600;">{{ $kasbon->document_no ?? 'Detail' }}</li> --}}
        </ol>
    </nav>

    {{-- HERO: hasil scan + ringkasan cepat, paling atas --}}
    <div class="card ledger-card mb-4">
        <div class="hero-card">
            @if($kasbon->file_scan)
                @php $isPdfScan = str_ends_with(strtolower($kasbon->file_scan), '.pdf'); @endphp
                <div class="hero-thumb" data-bs-toggle="modal" data-bs-target="#scanPreviewModal">
                    @if($isPdfScan)
                        <div class="hero-thumb-pdf">
                            <i class="bi bi-file-earmark-pdf-fill"></i>
                            <span>PDF</span>
                        </div>
                    @else
                        <img src="{{ Storage::url($kasbon->file_scan) }}" alt="Hasil scan">
                    @endif
                    <div class="hero-thumb-overlay"><i class="bi bi-zoom-in"></i></div>
                </div>
            @else
                <div class="hero-empty-thumb">
                    <i class="bi bi-image" style="font-size:1.6rem;"></i>
                    Tidak ada file scan
                </div>
            @endif

            <div class="hero-info">
                <div class="hero-doc-no">{{ $kasbon->document_no ?? 'TANPA NOMOR DOKUMEN' }}</div>
                <div class="hero-vendor">{{ $kasbon->nama_vendor ?? 'Vendor tidak diketahui' }}</div>
                <div class="hero-stats">
                    <div>
                        <div class="hero-stat-label">Tanggal</div>
                        <div class="hero-stat-val">{{ optional($kasbon->tanggal_transaksi)->format('d M Y') ?? '-' }}</div>
                        </div>
                    <div>
                        <div class="hero-stat-label">Jumlah Total</div>
                        <div class="hero-stat-val">{{ $kasbon->jumlah_total ? 'Rp '.number_format($kasbon->jumlah_total,0,',','.') : '-' }}</div>
                    </div>
                    <div>
                        <div class="hero-stat-label">Rincian Akun</div>
                        <div class="hero-stat-val">{{ $kasbon->items->count() }} baris</div>
                    </div>
                </div>
            </div>

            <div class="hero-actions">
                @if($kasbon->file_scan)
                    <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-toggle="modal" data-bs-target="#scanPreviewModal">
                        <i class="bi bi-zoom-in me-1"></i> Lihat Scan
                    </button>
                @endif
                <form action="{{ route('arsipkasbon.destroy', $kasbon) }}" method="POST"
                      onsubmit="return confirm('Hapus arsip surat ini?')">
                    @csrf @method('DELETE')
                    <button class="btn btn-outline-danger btn-sm w-100"><i class="bi bi-trash me-1"></i> Hapus Arsip</button>
                </form>
            </div>
        </div>
    </div>

    <div class="row g-4">
        {{-- Header Surat --}}
        <div class="col-lg-5">
            <div class="card ledger-card h-100">
                <div class="ledger-card-header">
                    <div class="ledger-title">Header Surat</div>
                </div>
                <div class="card-body">
                    <div class="kv-grid">
                        <div class="kv-row"><span class="kv-key">Tanggal Transaksi</span><span class="kv-val">{{ optional($kasbon->tanggal_transaksi)->format('d M Y') ?? '-' }}</span></div>
                        <div class="kv-row"><span class="kv-key">Document No</span><span class="kv-val">{{ $kasbon->document_no ?? '-' }}</span></div>
                        <div class="kv-row"><span class="kv-key">Numerator</span><span class="kv-val">{{ $kasbon->numerator ?? '-' }}</span></div>
                        <div class="kv-row"><span class="kv-key">Park Oleh</span><span class="kv-val">{{ $kasbon->park_oleh ?? '-' }}</span></div>
                        <div class="kv-row"><span class="kv-key">Nama Vendor</span><span class="kv-val">{{ $kasbon->nama_vendor ?? '-' }}</span></div>
                        <div class="kv-row"><span class="kv-key">Kode Vendor</span><span class="kv-val">{{ $kasbon->kode_vendor ?? '-' }}</span></div>
                        <div class="kv-row"><span class="kv-key">Cek/Giro/Trx</span><span class="kv-val">{{ $kasbon->cek_giro_trx ?? '-' }}</span></div>
                        <div class="kv-row kv-full"><span class="kv-key">Cost Object</span><span class="kv-val">{{ $kasbon->deskripsi_cost_object ?? '-' }}</span></div>
                        <div class="kv-row kv-full"><span class="kv-key">Jumlah Total</span><span class="kv-val">{{ $kasbon->jumlah_total ? 'Rp '.number_format($kasbon->jumlah_total,0,',','.') : '-' }}</span></div>
                        @if($kasbon->terbilang)
                            <div class="kv-row kv-full"><span class="kv-key">Terbilang</span><span class="kv-val">{{ $kasbon->terbilang }}</span></div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        {{-- Rincian Akun --}}
        <div class="col-lg-7">
            <div class="card ledger-card">
                <div class="ledger-card-header">
                    <div class="ledger-title">Rincian Akun</div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table rincian-table mb-0">
                            <thead>
                                <tr>
                                    <th class="ps-4">No Akun</th>
                                    <th>PK</th>
                                    <th>Cost Object</th>
                                    <th>Item Text</th>
                                    <th class="text-end pe-4">Jumlah</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($kasbon->items as $item)
                                    <tr>
                                        <td class="ps-4 fw-semibold">{{ $item->no_akun }}</td>
                                        <td>{{ $item->pk ?? '-' }}</td>
                                        <td>{{ $item->cost_object ?? '-' }}</td>
                                        <td class="item-text-cell" title="{{ $item->item_text }}">{{ $item->item_text ?? '-' }}</td>
                                        <td class="text-end pe-4">Rp {{ number_format($item->jumlah_rupiah, 0, ',', '.') }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center py-4 ledger-subtitle">Tidak ada rincian akun.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            @if($kasbon->lampiran && $kasbon->lampiran->count())
                <div class="card ledger-card mt-3">
                    <div class="ledger-card-header">
                        <div class="ledger-title" style="font-size:1.05rem;">Lampiran Tambahan ({{ $kasbon->lampiran->count() }})</div>
                    </div>
                    <div class="card-body">
                        @foreach($kasbon->lampiran as $lampiran)
                            <div class="d-flex align-items-center justify-content-between py-2" style="border-bottom:1px solid var(--line);">
                                <div class="d-flex align-items-center gap-2">
                                    <i class="bi {{ str_ends_with(strtolower($lampiran->file_path), '.pdf') ? 'bi-file-earmark-pdf-fill' : 'bi-file-earmark-image-fill' }}" style="color:var(--brass-dark);"></i>
                                    <span>{{ $lampiran->file_name ?? 'Lampiran' }}</span>
                                </div>
                                <button type="button" class="btn btn-link small p-0"
                                        data-bs-toggle="modal" data-bs-target="#lampiranPreviewModal"
                                        data-file-url="{{ Storage::url($lampiran->file_path) }}"
                                        data-file-name="{{ $lampiran->file_name ?? 'Lampiran' }}"
                                        data-is-pdf="{{ str_ends_with(strtolower($lampiran->file_path), '.pdf') ? '1' : '0' }}">
                                    Lihat
                                </button>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            <a href="{{ route('arsipkasbon.index') }}" class="btn ledger-btn-ghost mt-3">
                <i class="bi bi-arrow-left me-1"></i> Kembali ke daftar
            </a>
        </div>
    </div>
</div>

{{-- Modal preview hasil scan (pop-up, bukan tab baru) --}}
@if($kasbon->file_scan)
@php $isPdfScan = str_ends_with(strtolower($kasbon->file_scan), '.pdf'); @endphp
<div class="modal fade scan-modal" id="scanPreviewModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content">
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
            <div class="modal-body">
                @if($isPdfScan)
                    <div class="scan-pdf-wrapper">
                        <iframe src="{{ Storage::url($kasbon->file_scan) }}" class="scan-pdf-frame" title="Hasil scan {{ $kasbon->document_no }}"></iframe>
                        <a href="{{ Storage::url($kasbon->file_scan) }}" target="_blank" rel="noopener" class="btn btn-outline-light btn-sm">
                            <i class="bi bi-box-arrow-up-right me-1"></i> Buka PDF di tab baru
                        </a>
                    </div>
                @else
                    <img src="{{ Storage::url($kasbon->file_scan) }}" alt="Hasil scan {{ $kasbon->document_no }}">
                @endif
            </div>
        </div>
    </div>
</div>
@endif

{{-- Modal preview lampiran tambahan (pop-up, dinamis sesuai file yang diklik) --}}
@if($kasbon->lampiran && $kasbon->lampiran->count())
<div class="modal fade scan-modal" id="lampiranPreviewModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content">
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
            <div class="modal-body" id="lampiranModalBody">
                {{-- Diisi JS pas modal dibuka --}}
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('lampiranPreviewModal').addEventListener('show.bs.modal', function (e) {
    const btn = e.relatedTarget;
    const fileUrl  = btn.getAttribute('data-file-url');
    const fileName = btn.getAttribute('data-file-name');
    const isPdf    = btn.getAttribute('data-is-pdf') === '1';
    const body = document.getElementById('lampiranModalBody');

    if (isPdf) {
        body.innerHTML = `
            <div class="scan-pdf-wrapper">
                <iframe src="${fileUrl}" class="scan-pdf-frame" title="${fileName}"></iframe>
                <a href="${fileUrl}" target="_blank" rel="noopener" class="btn btn-outline-light btn-sm">
                    <i class="bi bi-box-arrow-up-right me-1"></i> Buka PDF di tab baru
                </a>
            </div>`;
    } else {
        body.innerHTML = `<img src="${fileUrl}" alt="${fileName}">`;
    }
});
</script>
@endif

@endsection