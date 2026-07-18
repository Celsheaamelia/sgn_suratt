@extends('layouts.app')

@section('content')

<style>
    @import url('https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,400;9..144,600;9..144,700&family=Inter:wght@400;500;600;700&family=IBM+Plex+Mono:wght@500;600&display=swap');

    :root {
        --ink:        #1c2b23;
        --ink-soft:   #3d4f45;
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

    /* .ledger-page {
        background: var(--ledger);
        font-family: var(--font-body);
        color: var(--ink);
        min-height: 100vh;
    } */

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
    .ledger-stamp {
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
        font-size: 1.4rem;
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

    .ledger-btn-ghost {
        background: transparent;
        color: var(--ink-soft);
        font-weight: 500;
        border: none;
        padding: 0.6rem 1rem;
        transition: color 0.15s ease;
        text-decoration: none;
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
    .ledger-btn-brass:disabled {
        opacity: 0.5;
        cursor: not-allowed;
        box-shadow: none;
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
    .ledger-stamp-number {
        display: inline-block;
        color: var(--brass-tint);
        font-family: var(--font-mono);
        font-weight: 600;
        font-size: 1.05rem;
        letter-spacing: 0.04em;
        word-break: break-all;
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
        text-align: right;
    }

    /* Status badge di header card info */
    .ledger-status-pill {
        font-family: var(--font-mono);
        font-size: 0.68rem;
        font-weight: 600;
        letter-spacing: 0.05em;
        text-transform: uppercase;
        padding: 0.35rem 0.8rem;
        border-radius: 999px;
        white-space: nowrap;
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
    }
    .ledger-status-pill.is-uploaded {
        background: var(--success-bg);
        color: var(--success);
        border: 1px solid #cfe2d4;
    }
    .ledger-status-pill.is-pending {
        background: var(--danger-bg);
        color: var(--danger);
        border: 1px solid #f2d3cc;
    }

    /* Dropzone */
    .ledger-dropzone {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        text-align: center;
        border: 1.5px dashed var(--line);
        border-radius: 0.85rem;
        padding: 2.5rem 1.5rem;
        cursor: pointer;
        background: var(--ledger);
        transition: border-color .15s ease, background .15s ease;
    }
    .ledger-dropzone:hover,
    .ledger-dropzone.is-dragover {
        border-color: var(--brass);
        background: rgba(169,129,47,0.06);
    }
    .ledger-dropzone-icon {
        font-size: 1.8rem;
        color: var(--brass);
        margin-bottom: 0.75rem;
    }
    .ledger-dropzone-text {
        color: var(--ink);
        font-size: 0.92rem;
    }
    .ledger-dropzone-hint {
        color: var(--ink-soft);
        font-size: 0.78rem;
    }

    /* Baris file (sudah ada / baru dipilih) */
    .file-row {
        display: flex;
        align-items: center;
        gap: 0.9rem;
        padding: 0.9rem 1rem;
        border: 1px solid var(--line);
        border-radius: 0.7rem;
        background: var(--paper);
    }
    .file-row-icon {
        width: 40px;
        height: 40px;
        border-radius: 0.6rem;
        background: var(--brass-tint);
        color: var(--brass-dark);
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        font-size: 1.05rem;
    }
    .file-row-name {
        font-weight: 600;
        color: var(--ink);
        font-size: 0.9rem;
        word-break: break-all;
    }
    .file-row-meta {
        color: var(--ink-soft);
        font-size: 0.78rem;
        font-family: var(--font-mono);
    }
    .file-row-clear {
        border: none;
        background: transparent;
        color: var(--ink-soft);
        font-size: 1rem;
        flex-shrink: 0;
    }
    .file-row-clear:hover {
        color: var(--danger);
    }

    /* Modal preview file — tetap dalam tema ledger */
    #filePreviewModal .modal-content {
        border: 1px solid var(--line);
        border-radius: 0.9rem;
        overflow: hidden;
    }
    #filePreviewModal .modal-header {
        background: var(--paper);
        border-bottom: 1px solid var(--line);
        padding: 1.1rem 1.5rem;
    }
    #filePreviewModal .modal-title {
        font-family: var(--font-display);
        font-weight: 600;
        font-size: 1.05rem;
        color: var(--ink);
    }
    #filePreviewModal .modal-body {
        background: #f0f0f0;
    }
    #filePreviewModal .modal-footer {
        background: var(--paper);
        border-top: 1px solid var(--line);
        padding: 0.9rem 1.5rem;
    }

    @media (prefers-reduced-motion: reduce) {
        * { transition: none !important; }
    }
</style>

<div class="ledger-page">
    <div class="container-fluid py-1 py-md-2">

        @if($surat->detailSurat)
        <div class="alert alert-success">
            <h5>File sudah diupload</h5>
            <p>
                <strong>Nama File:</strong>
                {{ $surat->detailSurat->file_name }}
            </p>
            <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#filePreviewModal">
                Lihat File
            </button>
        </div>
        @endif

        {{-- Alert --}}
        @if (session('success'))
            <div class="alert ledger-alert-success d-flex align-items-center gap-2" role="alert">
                <i class="fa-solid fa-circle-check"></i>
                <div>{{ session('success') }}</div>
            </div>
        @endif

        @if ($errors->any())
            <div class="alert ledger-alert-danger" role="alert">
                {{ $errors->first() }}
            </div>
        @endif

        @php
            $isUploaded = ($surat->status ?? 'Belum Terupload') === 'Terupload';
        @endphp

        <div class="row g-4">

            {{-- KIRI: Info surat + Upload --}}
            <div class="col-lg-8">

                {{-- Info surat --}}
                <div class="card ledger-card mb-4">
                    <div class="card-header ledger-card-header d-flex align-items-center justify-content-between flex-wrap gap-2">
                        <div>
                            <h2 class="ledger-title mb-1">Detail Surat</h2>
                            <p class="ledger-subtitle mb-0">{{ $surat->nomor_surat }}</p>
                        </div>
                        <span class="ledger-status-pill {{ $isUploaded ? 'is-uploaded' : 'is-pending' }}">
                            <i class="fa-solid {{ $isUploaded ? 'fa-circle-check' : 'fa-circle-exclamation' }}"></i>
                            {{ $surat->status ?? 'Belum Terupload' }}
                        </span>
                    </div>

                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-12">
                                <div class="ledger-subtitle mb-1">Perihal</div>
                                <div>{{ $surat->perihal }}</div>
                            </div>
                            <div class="col-sm-4">
                                <div class="ledger-subtitle mb-1">Klasifikasi</div>
                                <div>{{ $surat->klasifikasiSurat->kode ?? '-' }}</div>
                            </div>
                            <div class="col-sm-4">
                                <div class="ledger-subtitle mb-1">Penandatangan</div>
                                <div>{{ $surat->penandatangan->jabatan ?? '-' }}</div>
                            </div>
                            <div class="col-sm-4">
                                <div class="ledger-subtitle mb-1">Tujuan</div>
                                <div>{{ $surat->tujuanSurat->nama_tujuan ?? '-' }}</div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Upload surat --}}
                <div class="card ledger-card">
                    <div class="card-header ledger-card-header">
                        <h2 class="ledger-title h6 mb-1">Upload Surat Hasil Scan</h2>
                        <p class="ledger-subtitle mb-0">Format PDF atau gambar (JPG/PNG), maksimal 10 MB.</p>
                    </div>

                    <div class="card-body">

                        {{-- File yang sudah pernah diupload --}}
                        @if($surat->detailSurat)
                            <div class="file-row mb-4">

                                <div class="file-row-icon">
                                    <i class="fa-solid fa-file"></i>
                                </div>

                                <div class="flex-grow-1">
                                    <div class="file-row-name">
                                        {{ $surat->detailSurat->file_name }}
                                    </div>

                                    <div class="file-row-meta">
                                        Diupload
                                        {{ $surat->detailSurat->uploaded_at->format('d M Y H:i') }}
                                    </div>
                                </div>

                                <div class="d-flex gap-2">

                                    <button type="button" class="btn btn-outline-primary btn-sm" data-bs-toggle="modal" data-bs-target="#filePreviewModal">
                                        <i class="fa-solid fa-eye"></i>
                                        Lihat
                                    </button>

                                    <form action="{{ route('surat.upload.delete', $surat->id) }}"
                                        method="POST"
                                        onsubmit="return confirm('Hapus file surat ini?')">

                                        @csrf
                                        @method('DELETE')

                                        <button class="btn btn-outline-danger btn-sm">
                                            <i class="fa-solid fa-trash"></i>
                                            Hapus
                                        </button>

                                    </form>

                                </div>

                            </div>
                            @endif

                        <form method="POST"
                              action="{{ route('surat.upload.store', $surat->id) }}"
                              enctype="multipart/form-data"
                              id="uploadForm">
                            @csrf

                            <label for="fileInput" class="ledger-dropzone" id="dropzone">
                                <i class="fa-solid fa-cloud-arrow-up ledger-dropzone-icon"></i>
                                <p class="ledger-dropzone-text mb-1">
                                    <span class="fw-semibold">Klik untuk pilih file</span> atau tarik & lepas di sini
                                </p>
                                <p class="ledger-dropzone-hint mb-0">PDF, JPG, PNG — maks. 10 MB</p>
                                <input type="file" name="file_surat" id="fileInput" accept=".pdf,.jpg,.jpeg,.png" hidden>
                            </label>

                            <div id="fileSelectedRow" class="file-row mt-3 d-none">
                                <div class="file-row-icon">
                                    <i class="fa-solid fa-file" id="fileSelectedIcon"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <div class="file-row-name" id="fileSelectedName">-</div>
                                    <div class="file-row-meta" id="fileSelectedSize">-</div>
                                </div>
                                <button type="button" class="file-row-clear" id="clearFileBtn" aria-label="Hapus file">
                                    <i class="fa-solid fa-xmark"></i>
                                </button>
                            </div>

                            <div class="d-flex justify-content-end gap-3 mt-4">
                                <a href="{{ route('riwayatsurat') }}" class="btn ledger-btn-ghost">
                                    Kembali
                                </a>
                                <button type="submit" class="btn ledger-btn-brass" id="uploadSubmitBtn" disabled>
                                    <i class="fa-solid fa-upload me-1"></i>
                                    Upload Surat
                                </button>
                            </div>
                        </form>

                    </div>
                </div>

            </div>

            {{-- KANAN: Stempel nomor surat --}}
            <div class="col-lg-4">
                <div class="card ledger-stamp">
                    <div class="card-body">
                        <div class="d-flex align-items-center gap-2 mb-4">
                            <i class="fa-solid fa-stamp"></i>
                            <h3 class="ledger-stamp-title mb-0">Nomor Terdaftar</h3>
                        </div>

                        <div class="ledger-stamp-box mb-4">
                            <p class="ledger-stamp-label mb-1">Nomor Surat</p>
                            <p class="mb-0 ledger-stamp-number">{{ $surat->nomor_surat }}</p>
                        </div>

                        <div class="d-flex justify-content-between mb-2">
                            <span class="ledger-stamp-key">Klasifikasi</span>
                            <span class="ledger-stamp-value">{{ $surat->klasifikasiSurat->kode ?? '-' }}</span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="ledger-stamp-key">Penandatangan</span>
                            <span class="ledger-stamp-value">{{ $surat->penandatangan->kode ?? '-' }}</span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="ledger-stamp-key">Tujuan</span>
                            <span class="ledger-stamp-value">{{ $surat->tujuanSurat->kode ?? '-' }}</span>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span class="ledger-stamp-key">Tanggal</span>
                            <span class="ledger-stamp-value">{{ $surat->tanggal }}</span>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

@if($surat->detailSurat)
    @php
        $fileUrl = Storage::url($surat->detailSurat->file_path);
        $isPdfFile = str_ends_with(strtolower($surat->detailSurat->file_path), '.pdf');
    @endphp
    <div class="modal fade" id="filePreviewModal" tabindex="-1" aria-labelledby="filePreviewModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="filePreviewModalLabel">
                        {{ $surat->detailSurat->file_name }}
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-0" style="height: 75vh;">
                    @if($isPdfFile)
                        <iframe src="{{ $fileUrl }}" style="width:100%; height:100%; border:0;"></iframe>
                    @else
                        <div class="d-flex align-items-center justify-content-center h-100 p-3">
                            <img src="{{ $fileUrl }}" alt="{{ $surat->detailSurat->file_name }}" style="max-width:100%; max-height:100%; object-fit:contain;">
                        </div>
                    @endif
                </div>
                <div class="modal-footer">
                    <a href="{{ $fileUrl }}" target="_blank" class="ledger-cta">
                        Buka di tab baru
                    </a>
                    <button type="button" class="btn ledger-btn-ghost" data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>
@endif

<script>
    const dropzone = document.getElementById('dropzone');
    const fileInput = document.getElementById('fileInput');
    const fileSelectedRow = document.getElementById('fileSelectedRow');
    const fileSelectedName = document.getElementById('fileSelectedName');
    const fileSelectedSize = document.getElementById('fileSelectedSize');
    const fileSelectedIcon = document.getElementById('fileSelectedIcon');
    const clearFileBtn = document.getElementById('clearFileBtn');
    const uploadSubmitBtn = document.getElementById('uploadSubmitBtn');

    function formatBytes(bytes) {
        if (bytes < 1024) return bytes + ' B';
        if (bytes < 1024 * 1024) return (bytes / 1024).toFixed(1) + ' KB';
        return (bytes / (1024 * 1024)).toFixed(1) + ' MB';
    }

    function showSelectedFile(file) {
        if (!file) return;

        fileSelectedName.textContent = file.name;
        fileSelectedSize.textContent = formatBytes(file.size);

        const isPdf = file.type === 'application/pdf' || file.name.toLowerCase().endsWith('.pdf');
        fileSelectedIcon.className = isPdf ? 'fa-solid fa-file-pdf' : 'fa-solid fa-file-image';

        fileSelectedRow.classList.remove('d-none');
        uploadSubmitBtn.disabled = false;
    }

    fileInput.addEventListener('change', () => {
        if (fileInput.files.length) {
            showSelectedFile(fileInput.files[0]);
        }
    });

    ['dragover', 'dragenter'].forEach(evt => {
        dropzone.addEventListener(evt, (e) => {
            e.preventDefault();
            dropzone.classList.add('is-dragover');
        });
    });

    ['dragleave', 'drop'].forEach(evt => {
        dropzone.addEventListener(evt, (e) => {
            e.preventDefault();
            dropzone.classList.remove('is-dragover');
        });
    });

    dropzone.addEventListener('drop', (e) => {
        const files = e.dataTransfer.files;
        if (files.length) {
            fileInput.files = files;
            showSelectedFile(files[0]);
        }
    });

    clearFileBtn.addEventListener('click', () => {
        fileInput.value = '';
        fileSelectedRow.classList.add('d-none');
        uploadSubmitBtn.disabled = true;
    });
</script>

@endsection
