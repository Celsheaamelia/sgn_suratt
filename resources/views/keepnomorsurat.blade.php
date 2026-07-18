@extends('layouts.app')

@section('content')

<style>
    /* ==========================================================================
       Keep Nomor Surat — Registry Ledger Theme (Bootstrap version)
       Sama persis dengan tema di halaman Tambah Surat, biar konsisten.
       ========================================================================== */

    @import url('https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,400;9..144,600;9..144,700&family=Inter:wght@400;500;600;700&family=IBM+Plex+Mono:wght@500;600&display=swap');

    :root {
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

    .ledger-page {
        background: transparent;
        font-family: var(--font-body);
        color: var(--ink);
        min-height: 100vh;
        position: relative;
        isolation: isolate;
    }

    .ledger-alert-success {
        background: var(--success-bg);
        border: 1px solid #cfe2d4;
        color: var(--success);
        border-radius: 0.75rem;
        font-size: 0.9rem;
    }
    .ledger-alert-danger {
        background: var(--danger-bg);
        border: 1px solid #f2d3cc;
        color: var(--danger);
        border-radius: 0.75rem;
        font-size: 0.9rem;
    }

    .ledger-card,
    .ledger-stamp,
    .ledger-status {
        border-radius: 0.9rem;
        border: 1px solid var(--line);
        box-shadow: 0 1px 2px rgba(28,43,35,0.05), 0 1px 10px rgba(28,43,35,0.04);
    }

    .ledger-card {
        background: var(--paper);
    }

    .ledger-card-header {
        background: transparent;
        border-bottom: 1px solid var(--line);
        padding: 1.5rem 1.75rem 1.1rem;
    }

    .ledger-title {
        font-family: var(--font-display);
        font-weight: 600;
        font-size: 1.55rem;
        color: var(--ink);
        letter-spacing: -0.01em;
        margin-bottom: 0.25rem;
    }

    .ledger-subtitle {
        color: var(--ink-soft);
        font-size: 0.85rem;
    }

    .ledger-card .card-body {
        padding: 1.75rem;
    }

    .ledger-form label {
        color: var(--ink);
        font-weight: 600;
        font-size: 0.82rem;
        letter-spacing: 0.01em;
    }

    .ledger-required {
        color: var(--brass-dark);
    }

    .ledger-form .form-control,
    .ledger-form .form-select {
        border-radius: 0.55rem;
        border: 1px solid var(--line);
        padding: 0.65rem 0.95rem;
        font-size: 0.92rem;
        font-family: var(--font-body);
        color: var(--ink);
        background-color: var(--paper);
        transition: border-color 0.15s ease, box-shadow 0.15s ease;
    }

    .ledger-form .form-control:focus,
    .ledger-form .form-select:focus {
        outline: none;
        border-color: var(--brass);
        box-shadow: 0 0 0 3px rgba(169,129,47,0.16);
    }

    .ledger-form .form-control:focus-visible,
    .ledger-form .form-select:focus-visible,
    .ledger-btn-brass:focus-visible,
    .ledger-btn-ghost:focus-visible,
    .ledger-btn-use:focus-visible {
        outline: 2px solid var(--brass-dark);
        outline-offset: 2px;
    }

    .ledger-form .form-control::placeholder {
        color: #a3ada2;
    }

    .ledger-help {
        color: #8a9587;
        font-size: 0.75rem;
        margin-top: 0.35rem;
    }

    .ledger-btn-ghost {
        background: transparent;
        color: var(--ink-soft);
        font-weight: 500;
        border: none;
        padding: 0.6rem 1rem;
        transition: color 0.15s ease;
    }
    .ledger-btn-ghost:hover {
        color: var(--ink);
    }

    .ledger-btn-brass {
        background: linear-gradient(180deg, #b8903f, var(--brass-dark));
        box-shadow: 0 4px 14px rgba(138,106,36,0.35);
        border: none;
        color: #fff;
        font-weight: 600;
        letter-spacing: 0.01em;
        padding: 0.65rem 1.4rem;
        border-radius: 0.55rem;
    }
    .ledger-btn-brass:hover {
        background: linear-gradient(180deg, #c39a4c, #7a5c1f);
        box-shadow: 0 6px 18px rgba(138,106,36,0.42);
        color: #fff;
    }

    .ledger-stamp {
        background: linear-gradient(160deg, #24382e 0%, var(--ink) 70%);
        border: 1px solid rgba(255,255,255,0.06);
        position: relative;
        overflow: hidden;
        color: var(--brass-tint);
    }

    .ledger-stamp::before {
        content: "TERDAFTAR";
        position: absolute;
        top: 14px;
        right: -34px;
        font-family: var(--font-mono);
        font-size: 0.62rem;
        letter-spacing: 0.28em;
        color: rgba(244,236,216,0.14);
        transform: rotate(8deg);
        pointer-events: none;
    }

    .ledger-stamp-title {
        font-family: var(--font-display);
        font-weight: 600;
        font-size: 1.05rem;
        color: var(--brass-tint);
    }

    .ledger-stamp .fa-eye {
        color: var(--brass);
    }

    .ledger-stamp-box {
        background: rgba(169,129,47,0.08);
        border: 1.5px dashed rgba(244,236,216,0.35);
        border-radius: 0.85rem;
        position: relative;
        padding: 1.1rem 1rem;
    }

    .ledger-stamp-label {
        color: rgba(244,236,216,0.65);
        font-family: var(--font-mono);
        font-size: 0.65rem;
        letter-spacing: 0.22em;
        text-transform: uppercase;
    }

    .ledger-preview-list {
        max-height: 220px;
        overflow-y: auto;
        display: flex;
        flex-direction: column;
        gap: 0.35rem;
    }
    .ledger-preview-list::-webkit-scrollbar {
        width: 6px;
    }
    .ledger-preview-list::-webkit-scrollbar-thumb {
        background: rgba(244,236,216,0.25);
        border-radius: 3px;
    }

    .ledger-preview-item {
        color: var(--brass-tint);
        font-family: var(--font-mono);
        font-weight: 600;
        font-size: 0.92rem;
        letter-spacing: 0.03em;
        margin: 0;
        word-break: break-all;
    }

    .ledger-preview-more {
        color: rgba(244,236,216,0.55);
        font-family: var(--font-mono);
        font-size: 0.78rem;
        font-style: italic;
        margin: 0.2rem 0 0;
    }

    .ledger-preview-empty {
        color: rgba(244,236,216,0.55);
        font-family: var(--font-body);
        font-size: 0.85rem;
        margin: 0;
    }

    .ledger-stamp-key {
        color: rgba(244,236,216,0.55);
        font-size: 0.83rem;
    }
    .ledger-stamp-value {
        color: var(--brass-tint);
        font-family: var(--font-mono);
        font-weight: 600;
        font-size: 0.86rem;
        letter-spacing: 0.02em;
    }

    .ledger-status {
        background: var(--paper);
    }
    .ledger-status-title {
        font-family: var(--font-display);
        font-weight: 600;
        font-size: 1rem;
        color: var(--ink);
    }
    .ledger-status-dot {
        width: 0.5rem;
        height: 0.5rem;
        border-radius: 50%;
        background: var(--brass);
        box-shadow: 0 0 0 3px rgba(169,129,47,0.18);
        display: inline-block;
        flex-shrink: 0;
    }
    .ledger-status-line {
        color: var(--ink-soft);
        font-family: var(--font-mono);
        font-size: 0.82rem;
    }

    .ledger-table-title {
        font-family: var(--font-display);
        font-weight: 600;
        font-size: 1.1rem;
        color: var(--ink);
    }

    .ledger-table thead {
        background: var(--ledger);
    }
    .ledger-table thead th {
        color: var(--ink-soft);
        font-family: var(--font-mono);
        font-size: 0.7rem;
        letter-spacing: 0.1em;
        text-transform: uppercase;
        font-weight: 600;
        border-bottom: none;
        padding: 0.9rem 1.75rem;
    }

    .ledger-table tbody tr {
        border-top: 1px solid var(--ledger-line);
    }
    .ledger-table tbody tr:hover {
        background: var(--brass-tint);
    }
    .ledger-table tbody tr td {
        padding: 0.8rem 1.75rem;
        vertical-align: middle;
    }
    .ledger-table .ledger-nomor {
        color: var(--brass-dark);
        font-family: var(--font-mono);
        font-weight: 600;
        letter-spacing: 0.02em;
    }
    .ledger-table .ledger-perihal {
        color: var(--ink);
    }
    .ledger-table .ledger-tanggal {
        color: var(--ink-soft);
        font-family: var(--font-mono);
        font-size: 0.82rem;
    }

    .ledger-btn-use {
        background: transparent;
        color: var(--brass-dark);
        font-weight: 600;
        font-size: 0.78rem;
        border: 1px solid var(--brass);
        padding: 0.4rem 0.9rem;
        border-radius: 0.5rem;
        transition: background-color 0.15s ease, color 0.15s ease;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        white-space: nowrap;
        cursor: pointer;
    }
    .ledger-btn-use:hover {
        background: var(--brass);
        color: #fff;
    }

    /* Modal — biar nyatu sama tema ledger, bukan Bootstrap default polos */
    #gunakanModal .modal-content {
        border-radius: 0.9rem;
        border: 1px solid var(--line);
        font-family: var(--font-body);
    }
    #gunakanModal .modal-header {
        border-bottom: 1px solid var(--line);
        padding: 1.25rem 1.5rem 1rem;
    }
    #gunakanModal .modal-title {
        font-family: var(--font-display);
        font-weight: 600;
        color: var(--ink);
        font-size: 1.2rem;
    }
    #gunakanModal .modal-body {
        padding: 1.5rem;
    }
    #gunakanModal .modal-footer {
        border-top: 1px solid var(--line);
        padding: 1rem 1.5rem 1.25rem;
    }
    #gunakanModal label {
        color: var(--ink);
        font-weight: 600;
        font-size: 0.82rem;
    }
    #gunakanModal .form-control,
    #gunakanModal .form-select {
        border-radius: 0.55rem;
        border: 1px solid var(--line);
        padding: 0.6rem 0.9rem;
        font-size: 0.9rem;
        color: var(--ink);
        background-color: var(--paper);
    }
    #gunakanModal .form-control:focus,
    #gunakanModal .form-select:focus {
        outline: none;
        border-color: var(--brass);
        box-shadow: 0 0 0 3px rgba(169,129,47,0.16);
    }

    /* ==========================================================================
       Choices.js — theming biar nyatu sama palet brass/ledger
       (Sama persis dengan halaman Buat Nomor Surat, di-scope ke #gunakanModal
       karena select-nya ada di dalam modal, bukan di dalam .ledger-form)
       ========================================================================== */
    #gunakanModal .choices {
        margin-bottom: 0;
        font-size: 0.9rem;
        font-family: var(--font-body);
        width: 100% !important;
        max-width: 100%;
    }

    #gunakanModal .choices__inner {
        background-color: var(--paper) !important;
        border: 1px solid var(--line) !important;
        border-radius: 0.55rem !important;
        padding: 0.45rem 0.7rem !important;
        min-height: calc(1.5em + 1.2rem + 2px);
        width: 100%;
        box-sizing: border-box;
    }

    #gunakanModal .choices.is-focused .choices__inner,
    #gunakanModal .choices.is-open .choices__inner {
        border-color: var(--brass) !important;
        box-shadow: 0 0 0 3px rgba(169,129,47,0.16);
    }

    #gunakanModal .choices__list--single .choices__item {
        color: var(--ink);
    }

    #gunakanModal .choices__list--dropdown,
    #gunakanModal .choices__list[aria-expanded] {
        border: 1px solid var(--line) !important;
        border-radius: 0.55rem !important;
        box-shadow: 0 8px 24px rgba(28,43,35,0.12);
        overflow: hidden;
        width: 100%;
        z-index: 20;
    }

    #gunakanModal .choices__list--dropdown .choices__input {
        border-bottom: 1px solid var(--line) !important;
        background: var(--paper);
        color: var(--ink);
        font-family: var(--font-body);
    }

    #gunakanModal .choices__list--dropdown .choices__item--selectable.is-highlighted {
        background-color: var(--brass-tint) !important;
        color: var(--brass-dark);
    }

    #gunakanModal .choices__list--dropdown .choices__item {
        font-size: 0.9rem;
        padding: 0.6rem 0.85rem;
    }

    #gunakanModal .choices__placeholder {
        opacity: 1;
        color: #a3ada2;
    }

    @media (max-width: 640px) {
        .ledger-stamp::before {
            display: none;
        }
        .ledger-table thead th,
        .ledger-table tbody tr td {
            padding: 0.75rem 1rem;
        }
    }

    @media (prefers-reduced-motion: reduce) {
        * {
            transition: none !important;
        }
    }
</style>

<div class="ledger-page">
    <div class="container-fluid py-1 py-md-2">

        {{-- Alert sukses --}}
        @if (session('success') && !session('created_nomor'))
            <div class="alert ledger-alert-success d-flex align-items-center gap-2" role="alert">
                <i class="fa-solid fa-circle-check"></i>
                <div>{{ session('success') }}</div>
            </div>
        @endif

        {{-- Alert error --}}
        @if (session('error'))
            <div class="alert ledger-alert-danger d-flex align-items-center gap-2" role="alert">
                <i class="fa-solid fa-circle-exclamation"></i>
                <div>{{ session('error') }}</div>
            </div>
        @endif
        @if ($errors->any())
            <div class="alert ledger-alert-danger" role="alert">
                {{ $errors->first() }}
            </div>
        @endif

        <div class="row g-4">

            {{-- FORM --}}
            <div class="col-lg-8">
                <div class="card ledger-card h-100">

                    <div class="card-header ledger-card-header">
                        <h2 class="ledger-title">Buat Keep Nomor</h2>
                        <p class="ledger-subtitle mb-0">Kunci sejumlah nomor surat lebih dulu untuk dipakai nanti.</p>
                    </div>

                    <div class="card-body">
                        <form method="POST" action="{{ route('keepnomorsurat.store') }}" class="ledger-form" id="keepForm">
                            @csrf

                            <div class="row g-3 mb-3">
                                {{-- Penandatangan --}}
                                <div class="col-md-6">
                                    <label for="signatory" class="form-label">
                                        Penandatangan <span class="ledger-required">*</span>
                                    </label>
                                    <select name="signatory" id="signatory" required class="form-select">
                                        <option value="">Pilih Penandatangan</option>
                                        @foreach ($penandatanganList as $penandatangan)
                                            <option value="{{ $penandatangan->id }}"
                                                    data-kode="{{ $penandatangan->kode }}"
                                                    @selected(old('signatory') == $penandatangan->id)>
                                                {{ $penandatangan->jabatan }} ({{ $penandatangan->kode }})
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                {{-- Tanggal --}}
                                <div class="col-md-6">
                                    <label for="tanggal" class="form-label">
                                        Tanggal <span class="ledger-required">*</span>
                                    </label>
                                    <input
                                        type="date"
                                        name="tanggal"
                                        id="tanggal"
                                        required
                                        value="{{ old('tanggal', date('Y-m-d')) }}"
                                        class="form-control">
                                </div>
                            </div>

                            {{-- Rentang Nomor --}}
                            <div class="mb-4">
                                <label class="form-label">
                                    Rentang Nomor <span class="ledger-required">*</span>
                                </label>
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <input
                                            type="number"
                                            name="nomor_awal"
                                            id="nomor_awal"
                                            min="1"
                                            required
                                            placeholder="Nomor awal"
                                            value="{{ old('nomor_awal') }}"
                                            class="form-control">
                                    </div>
                                    <div class="col-md-6">
                                        <input
                                            type="number"
                                            name="nomor_akhir"
                                            id="nomor_akhir"
                                            min="1"
                                            required
                                            placeholder="Nomor akhir"
                                            value="{{ old('nomor_akhir') }}"
                                            class="form-control">
                                    </div>
                                </div>
                                <div class="ledger-help">Semua nomor dalam rentang ini akan dikunci satu-satu untuk penandatangan & tanggal yang dipilih.</div>
                            </div>

                            <div class="d-flex align-items-center justify-content-end gap-3">
                                <button type="reset" class="btn ledger-btn-ghost">
                                    Reset
                                </button>
                                <button type="submit" class="btn ledger-btn-brass" id="keepSubmitBtn">
                                    <i class="fa-solid fa-lock me-1"></i>
                                    Simpan Keep Nomor
                                </button>
                            </div>

                        </form>
                    </div>
                </div>
            </div>

            {{-- PREVIEW --}}
            <div class="col-lg-4">

                <div class="card ledger-stamp mb-3">
                    <div class="card-body">
                        <div class="d-flex align-items-center gap-2 mb-4">
                            <i class="fa-solid fa-eye"></i>
                            <h3 class="ledger-stamp-title mb-0">Live Preview</h3>
                        </div>

                        <div class="ledger-stamp-box mb-4">
                            <p class="ledger-stamp-label mb-2">Nomor Yang Akan Di-keep</p>
                            <div class="ledger-preview-list" id="previewList">
                                <p class="ledger-preview-empty">Isi rentang nomor yang valid.</p>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between mb-2">
                            <span class="ledger-stamp-key">Penandatangan</span>
                            <span class="ledger-stamp-value" id="previewSign">-</span>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span class="ledger-stamp-key">Tanggal</span>
                            <span class="ledger-stamp-value" id="previewTanggal">-</span>
                        </div>
                    </div>
                </div>

                <div class="card ledger-status">
                    <div class="card-body">
                        <h3 class="ledger-status-title mb-3">Status Sistem</h3>
                        <div class="d-flex align-items-center gap-2 ledger-status-line">
                            <span class="ledger-status-dot"></span>
                            <span id="statusLine">Menunggu rentang nomor...</span>
                        </div>
                    </div>
                </div>

            </div>
        </div>

        {{-- Daftar nomor yang sudah di-keep (satu-satu, bukan rentang) --}}
        <div class="card ledger-card mt-4">
            <div class="card-header ledger-card-header">
                <h3 class="ledger-table-title mb-0">Daftar Keep Nomor</h3>
            </div>
            <div class="card-body p-0">
                <table class="table ledger-table align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Penandatangan</th>
                            <th>Tanggal</th>
                            <th>Nomor</th>
                            <th class="text-end">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($keepList ?? [] as $keep)
                            <tr>
                                <td class="ledger-perihal">{{ $keep->signatory->jabatan ?? '-' }}</td>
                                <td class="ledger-tanggal">{{ $keep->tanggal->format('Y-m-d') }}</td>
                                <td class="ledger-nomor">#{{ str_pad($keep->nomor, 3, '0', STR_PAD_LEFT) }}</td>
                                <td class="text-end">
                                    @if ($keep->status === 'aktif')
                                        <button type="button"
                                                class="ledger-btn-use"
                                                data-bs-toggle="modal"
                                                data-bs-target="#gunakanModal"
                                                data-action="{{ route('keepnomorsurat.gunakan', $keep->id) }}"
                                                data-nomor="{{ str_pad($keep->nomor, 3, '0', STR_PAD_LEFT) }}"
                                                data-signatory="{{ $keep->signatory->jabatan ?? '-' }}"
                                                data-tanggal="{{ $keep->tanggal->format('Y-m-d') }}">
                                            Gunakan
                                        </button>
                                    @else
                                        <span class="ledger-subtitle">Terpakai</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center py-4 ledger-subtitle">
                                    Belum ada nomor yang di-keep.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</div>

{{-- Modal: lengkapi data surat sebelum nomor dipakai --}}
<div class="modal fade" id="gunakanModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <form method="POST" id="gunakanForm">
        @csrf
        <div class="modal-header">
          <h5 class="modal-title mb-0">Lengkapi Data Surat — <span id="modalNomor"></span></h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <p class="ledger-subtitle mb-3">
            Penandatangan: <strong id="modalSign"></strong> ·
            Tanggal: <strong id="modalTanggal"></strong>
          </p>

          <div class="mb-3">
            <label class="form-label">Perihal <span class="ledger-required">*</span></label>
            <input type="text" name="perihal" class="form-control" required placeholder="Keterangan">
          </div>

          <div class="mb-3">
            <label for="modalTujuan" class="form-label">Tujuan Surat <span class="ledger-required">*</span></label>
            <select name="kode_tujuan" id="modalTujuan" class="form-select" required>
              <option value="">Pilih Kode Tujuan</option>
              @foreach ($tujuanList as $tujuan)
                <option value="{{ $tujuan->id }}" data-kode="{{ $tujuan->kode }}">
                  {{ $tujuan->kode }} — {{ $tujuan->nama_tujuan }}
                </option>
              @endforeach
            </select>
          </div>

          <div class="mb-3">
            <label for="modalKlasifikasi" class="form-label">Klasifikasi Surat <span class="ledger-required">*</span></label>
            <select name="klasifikasi" id="modalKlasifikasi" class="form-select" required>
              <option value="">Pilih Klasifikasi Surat</option>
              @foreach ($klasifikasiList as $klasifikasi)
                <option value="{{ $klasifikasi->id }}" data-kode="{{ $klasifikasi->kode }}">
                  {{ $klasifikasi->kode }} — {{ $klasifikasi->jenis_surat }}
                </option>
              @endforeach
            </select>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn ledger-btn-ghost" data-bs-dismiss="modal">Batal</button>
          <button type="submit" class="btn ledger-btn-brass">
            <i class="fa-solid fa-floppy-disk me-1"></i>
            Simpan Surat
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

{{-- ==========================================================================
     Modal sukses — muncul setelah nomor keep berhasil dipakai jadi surat,
     tanpa pindah ke halaman Riwayat Surat
     ========================================================================== --}}
@if (session('created_nomor'))
    <div class="modal fade" id="successModal" tabindex="-1" aria-labelledby="successModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content" style="border: 1px solid var(--line); border-radius: 0.9rem;">
                <div class="modal-header border-0 pb-0">
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-center pt-0 pb-4 px-4">
                    <div class="mb-3">
                        <i class="fa-solid fa-circle-check" style="font-size: 2.5rem; color: var(--success);"></i>
                    </div>
                    <h5 class="ledger-title mb-2" style="font-size: 1.2rem;">Surat Berhasil Disimpan</h5>
                    <p class="ledger-subtitle mb-1">Nomor keep berhasil dipakai untuk membuat surat.</p>
                    <p class="mb-0" style="font-family: var(--font-mono); font-weight: 600; color: var(--brass-dark); word-break: break-all;">
                        {{ session('created_nomor') }}
                    </p>
                </div>
                <div class="modal-footer justify-content-center border-0 pt-0 pb-4">
                    <a href="{{ route('riwayatsurat') }}" class="btn ledger-btn-brass">
                        Lihat Riwayat Surat
                    </a>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const successModalEl = document.getElementById('successModal');
            if (successModalEl) {
                const successModal = new bootstrap.Modal(successModalEl);
                successModal.show();
            }
        });
    </script>
@endif

@endsection

@push('styles')
{{-- Choices.js CSS — dropdown searchable untuk Tujuan & Klasifikasi.
     Sama seperti yang dipakai di halaman Buat Nomor Surat.
     Kalau CDN ini sudah dipasang global di layouts/app.blade.php, baris ini boleh dihapus. --}}
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/choices.js/public/assets/styles/choices.min.css">
@endpush

@push('scripts')
{{-- Choices.js JS — sama seperti di atas, kalau sudah global boleh dihapus dari sini. --}}
<script src="https://cdn.jsdelivr.net/npm/choices.js/public/assets/scripts/choices.min.js"></script>

<script>
document.addEventListener("DOMContentLoaded", function () {
        // ===========================
        // Element — form "Buat Keep Nomor"
        // ===========================
        const signEl = document.getElementById('signatory');
        const tanggalEl = document.getElementById('tanggal');
        const awalEl = document.getElementById('nomor_awal');
        const akhirEl = document.getElementById('nomor_akhir');

        const previewList = document.getElementById('previewList');
        const previewSign = document.getElementById('previewSign');
        const previewTanggal = document.getElementById('previewTanggal');
        const statusLine = document.getElementById('statusLine');
        const submitBtn = document.getElementById('keepSubmitBtn');

        const MAX_SHOW = 30; // batas tampil di preview biar nggak kepanjangan kalau rentangnya gede

        let usedNumbers = []; // nomor yang udah kepake di tanggal terpilih (di-refresh tiap tanggal ganti)

        // ===========================
        // Helper
        // ===========================

        function selectedKode(select){
            if(select.selectedIndex==-1) return "-----";
            return select.options[select.selectedIndex].dataset.kode ?? "-----";
        }

        function formatTanggal(tanggal){
            if(!tanggal) return "--------";
            return tanggal.replaceAll("-","");
        }

        function pad(n){
            return String(n).padStart(3,'0');
        }

        // ===========================
        // Ambil nomor yang udah kepake di tanggal terpilih
        // ===========================

        async function loadUsedNumbers(){
            if(!tanggalEl.value){
                usedNumbers = [];
                return;
            }
            try{
                let response = await fetch(
                    `{{ route('keepnomorsurat.cek-nomor') }}?tanggal=${tanggalEl.value}`
                );
                if(!response.ok) throw new Error('Gagal cek nomor terpakai');
                let data = await response.json();
                usedNumbers = data.used ?? [];
            }catch(err){
                console.log(err);
                usedNumbers = [];
            }
        }

        // ===========================
        // Preview
        // ===========================

        function updatePreview(){

            let sign = selectedKode(signEl);
            let tanggalFormatted = formatTanggal(tanggalEl.value);

            previewSign.textContent = signEl.selectedIndex > -1 && signEl.value ? sign : "-";
            previewTanggal.textContent = tanggalEl.value || "-";

            previewList.innerHTML = "";

            let awal = parseInt(awalEl.value);
            let akhir = parseInt(akhirEl.value);

            if(!awal || !akhir || akhir < awal){
                previewList.innerHTML = '<p class="ledger-preview-empty">Isi rentang nomor yang valid.</p>';
                statusLine.textContent = "Menunggu rentang nomor...";
                if (submitBtn) submitBtn.disabled = false;
                return;
            }

            let total = akhir - awal + 1;
            let shown = Math.min(total, MAX_SHOW);
            let conflictCount = 0;

            for(let i = 0; i < shown; i++){
                let seq = awal + i;
                let isUsed = usedNumbers.includes(seq);

                let row = document.createElement('div');
                row.className = 'd-flex align-items-center justify-content-between';

                let line = document.createElement('p');
                line.className = 'ledger-preview-item mb-0';
                line.textContent = `${sign}-----/${tanggalFormatted}.${pad(seq)}`;
                if (isUsed) line.style.opacity = '0.45';

                let badge = document.createElement('span');
                badge.style.fontFamily = 'var(--font-mono)';
                badge.style.fontSize = '0.68rem';
                badge.style.letterSpacing = '0.05em';
                badge.style.marginLeft = '0.5rem';
                badge.style.whiteSpace = 'nowrap';

                if (isUsed) {
                    badge.textContent = 'SUDAH DIPAKAI';
                    badge.style.color = '#e08a7a';
                    conflictCount++;
                } else {
                    badge.textContent = 'TERSEDIA';
                    badge.style.color = '#8fd4a8';
                }

                row.appendChild(line);
                row.appendChild(badge);
                previewList.appendChild(row);
            }

            if(total > MAX_SHOW){
                let more = document.createElement('p');
                more.className = 'ledger-preview-more';
                more.textContent = `+${total - MAX_SHOW} nomor lagi...`;
                previewList.appendChild(more);
            }

            // Cek konflik di seluruh rentang (bukan cuma yang ditampilkan)
            let fullRange = [];
            for(let n = awal; n <= akhir; n++) fullRange.push(n);
            let fullConflict = fullRange.filter(n => usedNumbers.includes(n));

            if (fullConflict.length > 0) {
                let list = fullConflict.map(pad).join(', #');
                statusLine.textContent = `Bentrok: #${list} sudah dipakai. Ubah rentang.`;
                statusLine.parentElement.querySelector('.ledger-status-dot').style.background = '#b3432f';
                if (submitBtn) submitBtn.disabled = true;
            } else {
                statusLine.textContent = `Siap keep ${total} nomor (#${pad(awal)} – #${pad(akhir)})`;
                statusLine.parentElement.querySelector('.ledger-status-dot').style.background = 'var(--brass)';
                if (submitBtn) submitBtn.disabled = false;
            }
        }

        async function refreshAndPreview(){
            await loadUsedNumbers();
            updatePreview();
        }

        signEl.addEventListener("change", updatePreview);
        tanggalEl.addEventListener("change", refreshAndPreview);
        awalEl.addEventListener("input", updatePreview);
        akhirEl.addEventListener("input", updatePreview);

        refreshAndPreview();

    // ===========================
    // Modal "Gunakan" — isi konteks nomor yang dipilih
    // ===========================
    const gunakanModalEl = document.getElementById('gunakanModal');

    if (gunakanModalEl) {
        gunakanModalEl.addEventListener('show.bs.modal', function (event) {
            const btn = event.relatedTarget;
            const form = document.getElementById('gunakanForm');

            form.action = btn.dataset.action;
            document.getElementById('modalNomor').textContent = '#' + btn.dataset.nomor;
            document.getElementById('modalSign').textContent = btn.dataset.signatory;
            document.getElementById('modalTanggal').textContent = btn.dataset.tanggal;
        });

        // ===========================
        // Choices.js — searchable dropdown Tujuan & Klasifikasi
        // Diinisialisasi tiap modal dibuka (biar elemen sudah ke-render dulu),
        // di-destroy tiap ditutup (biar nggak numpuk instance & form ke-reset bersih).
        // Konfigurasi sama persis dengan halaman Buat Nomor Surat.
        // ===========================
        let choicesTujuan = null;
        let choicesKlasifikasi = null;

        gunakanModalEl.addEventListener('shown.bs.modal', function () {
            if (!choicesTujuan) {
                choicesTujuan = new Choices('#modalTujuan', {
                    searchEnabled: true,
                    itemSelectText: '',
                    shouldSort: false,
                });
            }
            if (!choicesKlasifikasi) {
                choicesKlasifikasi = new Choices('#modalKlasifikasi', {
                    searchEnabled: true,
                    itemSelectText: '',
                    shouldSort: false,
                });
            }
        });

        gunakanModalEl.addEventListener('hidden.bs.modal', function () {
            if (choicesTujuan) { choicesTujuan.destroy(); choicesTujuan = null; }
            if (choicesKlasifikasi) { choicesKlasifikasi.destroy(); choicesKlasifikasi = null; }
            document.getElementById('gunakanForm').reset();
        });
    }

});
</script>
@endpush
