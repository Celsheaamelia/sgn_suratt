@extends('layouts.app')

@section('content')

<style>
    @import url('https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,400;9..144,600;9..144,700&family=Inter:wght@400;500;600;700&family=IBM+Plex+Mono:wght@500;600&display=swap');

    :root {
        --ink:        #1c2b23;
        --ink-soft:   #3d4f45;
        --paper:      #fbfcf9;
        --brass:      #a9812f;
        --brass-dark: #8a6a24;
        --line:       #dfe6da;
        --danger:     #b3432f;
        --danger-bg:  #fdf1ee;
        --success:    #3f6b4a;
        --success-bg: #eef5ef;
        --font-display: 'Fraunces', Georgia, serif;
        --font-body: 'Inter', -apple-system, sans-serif;
        --font-mono: 'IBM Plex Mono', ui-monospace, monospace;
    }

    .ledger-card {
        background: var(--paper);
        border: 1px solid var(--line);
        border-radius: 0.9rem;
        box-shadow: 0 1px 2px rgba(28,43,35,0.05), 0 1px 10px rgba(28,43,35,0.04);
    }
    .ledger-card-header {
        border-bottom: 1px solid var(--line);
        padding: 1.5rem 1.75rem 1.1rem;
    }
    .ledger-title {
        font-family: var(--font-display);
        font-weight: 600;
        font-size: 1.4rem;
        color: var(--ink);
    }
    .ledger-subtitle { color: var(--ink-soft); font-size: 0.85rem; }
    .ledger-card .card-body { padding: 1.75rem; }

    .ledger-dropzone {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
        border: 1.5px dashed var(--line);
        border-radius: 0.85rem;
        padding: 2.5rem 1rem;
        cursor: pointer;
        text-align: center;
        transition: all 0.15s ease;
        background: #fff;
    }
    .ledger-dropzone.is-dragover { border-color: var(--brass); background: #fbf6ea; }
    .ledger-dropzone-icon { font-size: 1.8rem; color: var(--brass-dark); }
    .ledger-dropzone-text { color: var(--ink); }
    .ledger-dropzone-hint { color: var(--ink-soft); font-size: 0.8rem; }

    .ledger-btn-brass {
        background: linear-gradient(180deg, #b8903f, var(--brass-dark));
        box-shadow: 0 4px 14px rgba(138,106,36,0.35);
        border: none; color: #fff; font-weight: 600;
        padding: 0.65rem 1.4rem; border-radius: 0.55rem;
    }
    .ledger-btn-brass:hover { color: #fff; }
    .ledger-btn-brass:disabled { opacity: 0.5; cursor: not-allowed; box-shadow: none; }
    .ledger-btn-ghost {
        background: transparent; color: var(--ink-soft); font-weight: 500;
        border: none; padding: 0.6rem 1rem; text-decoration: none;
    }
    .ledger-btn-ghost:hover { color: var(--ink); }

    .ocr-banner {
        background: #fef8ea; border: 1px solid #f0dfab; color: #6b5320;
        border-radius: 0.7rem; padding: 0.85rem 1.1rem; font-size: 0.85rem;
        display: flex; gap: 0.6rem; align-items: flex-start;
    }

    .item-row { position: relative; border: 1px solid var(--line); border-radius: 0.7rem; padding: 1rem 1rem 1rem 3.1rem; background: #fff; margin-bottom: 0.8rem; }
    .item-row-remove { color: var(--danger); background: none; border: none; font-size: 1.1rem; }
    .item-row-number {
        position: absolute;
        left: 0.85rem;
        top: 1rem;
        width: 1.7rem;
        height: 1.7rem;
        border-radius: 50%;
        background: var(--brass-tint, #f4ecd8);
        color: var(--brass-dark, #8a6a24);
        font-family: var(--font-mono, monospace);
        font-size: 0.78rem;
        font-weight: 700;
        display: flex;
        align-items: center;
        justify-content: center;
        border: 1px solid rgba(169,129,47,0.25);
    }

    .form-label-sm { font-size: 0.78rem; font-weight: 600; color: var(--ink-soft); margin-bottom: 0.25rem; }

    .akun-hint { font-size: 0.78rem; margin-top: 0.25rem; }
    .akun-hint.found { color: var(--success); }
    .akun-hint.notfound { color: var(--danger); }

    .duplicate-banner {
        background: var(--danger-bg); border: 1px solid #f0c3b8; color: var(--danger);
        border-radius: 0.7rem; padding: 0.9rem 1.1rem; font-size: 0.88rem;
        display: flex; gap: 0.6rem; align-items: flex-start; font-weight: 600;
    }
    #f_document_no.is-duplicate { border-color: var(--danger); box-shadow: 0 0 0 3px rgba(179,67,47,0.12); }

    .ocr-failed-banner {
        background: #fef8ea; border: 1px solid #f0dfab; color: #6b5320;
        border-radius: 0.7rem; padding: 0.9rem 1.1rem; font-size: 0.88rem;
        display: flex; gap: 0.6rem; align-items: flex-start; font-weight: 600;
    }

    /* ==================== OCR Loading Overlay ==================== */
    .ocr-loading-overlay {
        position: fixed;
        inset: 0;
        background: rgba(28, 43, 35, 0.55);
        backdrop-filter: blur(2px);
        z-index: 2000;
        display: flex;
        align-items: center;
        justify-content: center;
        opacity: 0;
        pointer-events: none;
        transition: opacity 0.2s ease;
    }
    .ocr-loading-overlay.is-active {
        opacity: 1;
        pointer-events: all;
    }
    .ocr-loading-box {
        background: var(--paper);
        border: 1px solid var(--line);
        border-radius: 1rem;
        padding: 2.5rem 2.75rem;
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 1.1rem;
        box-shadow: 0 10px 40px rgba(28,43,35,0.25);
        max-width: 90vw;
        text-align: center;
    }
    .ocr-loading-spinner {
        width: 46px;
        height: 46px;
        border-radius: 50%;
        border: 4px solid var(--line);
        border-top-color: var(--brass-dark);
        animation: ocr-spin 0.8s linear infinite;
    }
    @keyframes ocr-spin {
        to { transform: rotate(360deg); }
    }
    .ocr-loading-title {
        font-family: var(--font-display);
        font-weight: 600;
        font-size: 1.1rem;
        color: var(--ink);
    }
    .ocr-loading-hint {
        color: var(--ink-soft);
        font-size: 0.82rem;
        max-width: 260px;
    }
</style>

<div class="container-fluid">

    <nav aria-label="breadcrumb" class="mb-3">

    </nav>

    {{-- STEP 1: Upload / Scan --}}
    <div class="card ledger-card mb-4" id="uploadCard">
        <div class="ledger-card-header">
            <div class="ledger-title">Unggah Surat Permintaan Pembayaran</div>
            <div class="ledger-subtitle">Sistem akan membaca field-field utamanya secara otomatis.</div>
        </div>
        <div class="card-body">
            <label for="fileInput" class="ledger-dropzone" id="dropzone">
                <i class="bi bi-camera-fill ledger-dropzone-icon"></i>
                <p class="ledger-dropzone-text mb-1"><span class="fw-semibold">Klik untuk pilih foto</span> atau tarik & lepas di sini</p>
                <p class="ledger-dropzone-hint mb-0">JPG, PNG, JPEG, PDF maks. 10 MB. Pastikan pencahayaan cukup & tulisan tidak miring.</p>
                <input type="file" id="fileInput" accept=".jpg,.jpeg,.png,.pdf" hidden>
            </label>

            <div id="previewRow" class="d-none mt-3 d-flex align-items-center gap-3">
                <img id="previewImg" src="" style="width:70px;height:70px;object-fit:cover;border-radius:0.5rem;border:1px solid var(--line);">
                <div class="flex-grow-1">
                    <div id="previewName" class="fw-semibold" style="color:var(--ink);"></div>
                    <div id="previewSize" class="ledger-subtitle"></div>
                </div>
                <button type="button" class="btn ledger-btn-ghost" id="clearBtn"><i class="bi bi-x-lg"></i></button>
            </div>

            <div class="d-flex justify-content-end mt-4">
                <button type="button" class="btn ledger-btn-brass" id="scanBtn" disabled>
                    <span id="scanBtnIcon"><i class="bi bi-search me-1"></i></span>
                    <span id="scanBtnText">Upload &amp; Baca Otomatis</span>
                </button>
            </div>
        </div>
    </div>

    {{-- STEP 2: Verifikasi hasil OCR --}}
    <div class="card ledger-card d-none" id="verifyCard">
        <div class="ledger-card-header">
            <div class="ledger-title">Verifikasi Data</div>
            <div class="ledger-subtitle">Hasil baca otomatis mungkin tidak 100% tepat — cek dan koreksi dulu sebelum diarsipkan.</div>
        </div>
        <div class="card-body">

            <div class="ocr-banner mb-4">
                <i class="bi bi-info-circle-fill mt-1"></i>
                <div>Field yang kosong atau terlihat salah, isi/koreksi manual. Setelah disimpan, data ini yang jadi acuan arsip — bukan gambar aslinya.</div>
            </div>

            <div class="duplicate-banner mb-4 d-none" id="duplicateBanner">
                <i class="bi bi-exclamation-triangle-fill mt-1"></i>
                <div id="duplicateBannerText">Surat Permintaan Pembayaran ini sudah diunggah.</div>
            </div>

            <div class="ocr-failed-banner mb-4 d-none" id="ocrFailedBanner">
                <i class="bi bi-exclamation-circle-fill mt-1"></i>
                <div id="ocrFailedBannerText">Dokumen gagal terbaca otomatis. Pastikan gambar jelas dan koneksi internet tersambung, lalu isi field di bawah secara manual atau coba upload ulang.</div>
            </div>

            @if($errors->any())
                <div class="duplicate-banner mb-4">
                    <i class="bi bi-exclamation-triangle-fill mt-1"></i>
                    <div>
                        @foreach($errors->all() as $error)
                            {{ $error }}<br>
                        @endforeach
                    </div>
                </div>
            @endif

            <form id="finalForm" method="POST" action="{{ route('arsipkasbon.store') }}" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="temp_path" id="temp_path">

                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label-sm">Tanggal Transaksi</label>
                        <input type="date" name="tanggal_transaksi" id="f_tanggal_transaksi" class="form-control" value="{{ old('tanggal_transaksi') }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label-sm">Document No</label>
                        <input type="text" name="document_no" id="f_document_no" class="form-control @error('document_no') is-duplicate @enderror" value="{{ old('document_no') }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label-sm">Numerator</label>
                        <input type="text" name="numerator" id="f_numerator" class="form-control" value="{{ old('numerator') }}" placeholder="Nomor cap, mis. 2407009">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label-sm">Park Oleh</label>
                        <input type="text" name="park_oleh" id="f_park_oleh" class="form-control" value="{{ old('park_oleh') }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label-sm">Nama Vendor</label>
                        <input type="text" name="nama_vendor" id="f_nama_vendor" class="form-control" value="{{ old('nama_vendor') }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label-sm">Kode Vendor</label>
                        <input type="text" name="kode_vendor" id="f_kode_vendor" class="form-control" value="{{ old('kode_vendor') }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label-sm">Cek / Giro / Trx</label>
                        <input type="text" name="cek_giro_trx" id="f_cek_giro_trx" class="form-control" value="{{ old('cek_giro_trx') }}">
                    </div>
                    <div class="col-md-8">
                        <label class="form-label-sm">Deskripsi Cost Object</label>
                        <input type="text" name="deskripsi_cost_object" id="f_deskripsi_cost_object" class="form-control" value="{{ old('deskripsi_cost_object') }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label-sm">Jumlah Total</label>
                        <div class="input-group">
                            <span class="input-group-text">Rp</span>
                            <input type="text" inputmode="numeric" name="jumlah_total" id="f_jumlah_total" class="form-control" value="{{ old('jumlah_total') }}" placeholder="0">
                        </div>
                    </div>
                    <div class="col-12">
                        <label class="form-label-sm">Terbilang</label>
                        <input type="text" name="terbilang" id="f_terbilang" class="form-control" value="{{ old('terbilang') }}">
                    </div>
                </div>

                <hr class="my-4">

                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div class="ledger-title" style="font-size:1.1rem;">Rincian Akun</div>
                    <button type="button" class="btn btn-outline-secondary btn-sm" id="addRowBtn">
                        <i class="bi bi-plus-lg me-1"></i> Tambah Baris
                    </button>
                </div>
                <div id="itemsWrapper"></div>

                <hr class="my-4">

                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div class="ledger-title" style="font-size:1.1rem;">Lampiran Tambahan</div>
                </div>
                <p class="ledger-subtitle mb-3">Kertas/dokumen pendukung lain yang perlu ikut diarsipkan bersama surat ini (opsional, boleh lebih dari satu).</p>

                <label for="lampiranPicker" class="ledger-dropzone" id="lampiranDropzone" style="padding: 1.5rem 1rem;">
                    <i class="bi bi-paperclip ledger-dropzone-icon"></i>
                    <p class="ledger-dropzone-text mb-1"><span class="fw-semibold">Klik untuk pilih file</span> atau tarik &amp; lepas di sini</p>
                    <p class="ledger-dropzone-hint mb-0">Bisa pilih beberapa file sekaligus. JPG, PNG, PDF — maks. 15 MB per file.</p>
                    <input type="file" id="lampiranPicker" accept=".jpg,.jpeg,.png,.pdf" multiple hidden>
                </label>

                <div id="lampiranList" class="mt-3"></div>

                <input type="file" name="lampiran[]" id="lampiranSubmitInput" multiple hidden>

                <div class="d-flex justify-content-end gap-3 mt-4">
                    <a href="{{ route('arsipkasbon.index') }}" class="btn ledger-btn-ghost">Batal</a>
                    <button type="submit" class="btn ledger-btn-brass">
                        <i class="bi bi-archive-fill me-1"></i> Simpan ke Arsip
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>

{{-- Overlay loading saat proses OCR berjalan --}}
<div class="ocr-loading-overlay" id="ocrLoadingOverlay">
    <div class="ocr-loading-box">
        <div class="ocr-loading-spinner"></div>
        <div class="ocr-loading-title">Sistem sedang membaca data...</div>
        <div class="ocr-loading-hint">Mohon tunggu sebentar, proses ini biasanya memakan waktu beberapa detik.</div>
    </div>
</div>

<template id="itemRowTemplate">
    <div class="item-row" data-row>
        <div class="item-row-number" data-row-number></div>
        <div class="row g-2 align-items-end">
            <div class="col-md-3">
                <label class="form-label-sm">No Akun</label>
                <input type="text" class="form-control form-control-sm" data-field="no_akun">
                <div class="akun-hint" data-akun-hint></div>
            </div>
            <div class="col-md-2">
                <label class="form-label-sm">PK</label>
                <input type="text" class="form-control form-control-sm" data-field="pk">
            </div>
            <div class="col-md-3">
                <label class="form-label-sm">Cost Object</label>
                <input type="text" class="form-control form-control-sm" data-field="cost_object">
            </div>
            <div class="col-md-3">
                <label class="form-label-sm">Jumlah Rupiah</label>
                <div class="input-group input-group-sm">
                    <span class="input-group-text">Rp</span>
                    <input type="text" inputmode="numeric" class="form-control form-control-sm" data-field="jumlah_rupiah" placeholder="0">
                </div>
            </div>
            <div class="col-md-1 text-end">
                <button type="button" class="item-row-remove" data-remove-row title="Hapus baris">
                    <i class="bi bi-trash"></i>
                </button>
            </div>
            <div class="col-12">
                <label class="form-label-sm">Item Text / Deskripsi</label>
                <input type="text" class="form-control form-control-sm" data-field="item_text" placeholder="Deskripsi akun ter-autofill di sini kalau sudah pernah dipakai">
            </div>
        </div>
    </div>
</template>

@push('scripts')
<script>
    // Data dari server: kalau validasi gagal (misal document_no duplikat),
    // Laravel redirect balik ke sini bawa old input -- termasuk baris items.
    const OLD_ITEMS = @json(old('items', []));
    const HAS_VALIDATION_ERROR = @json($errors->any());
</script>
<script>
(function () {
    const dropzone   = document.getElementById('dropzone');
    const fileInput  = document.getElementById('fileInput');
    const previewRow = document.getElementById('previewRow');
    const previewImg = document.getElementById('previewImg');
    const previewName= document.getElementById('previewName');
    const previewSize= document.getElementById('previewSize');
    const clearBtn   = document.getElementById('clearBtn');
    const scanBtn    = document.getElementById('scanBtn');
    const scanBtnText= document.getElementById('scanBtnText');
    const uploadCard = document.getElementById('uploadCard');
    const verifyCard = document.getElementById('verifyCard');
    const itemsWrapper = document.getElementById('itemsWrapper');
    const itemRowTemplate = document.getElementById('itemRowTemplate');
    const addRowBtn = document.getElementById('addRowBtn');
    const ocrLoadingOverlay = document.getElementById('ocrLoadingOverlay');
    const ocrFailedBanner = document.getElementById('ocrFailedBanner');
    const ocrFailedBannerText = document.getElementById('ocrFailedBannerText');

    function showOcrLoading() {
        ocrLoadingOverlay.classList.add('is-active');
    }
    function hideOcrLoading() {
        ocrLoadingOverlay.classList.remove('is-active');
    }

    function showOcrFailedWarning(message) {
        ocrFailedBannerText.textContent = message || 'Dokumen gagal terbaca otomatis. Pastikan gambar jelas dan koneksi internet tersambung, lalu isi field di bawah secara manual atau coba upload ulang.';
        ocrFailedBanner.classList.remove('d-none');
    }
    function clearOcrFailedWarning() {
        ocrFailedBanner.classList.add('d-none');
    }

    // ==================== Format tampilan Rupiah (Rp + titik ribuan) ====================
    function formatRibuan(value) {
        const digitsOnly = String(value ?? '').replace(/[^\d]/g, '');
        if (digitsOnly === '') return '';
        return digitsOnly.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
    }

    function unformatRibuan(value) {
        return String(value ?? '').replace(/[^\d]/g, '');
    }

    function attachRupiahFormatter(input) {
        input.addEventListener('input', () => {
            const cursorFromEnd = input.value.length - input.selectionStart;
            input.value = formatRibuan(input.value);
            const pos = input.value.length - cursorFromEnd;
            input.setSelectionRange(pos, pos);
        });
    }

    function setRupiahValue(input, rawNumber) {
        input.value = formatRibuan(rawNumber);
    }

    const lampiranDropzone = document.getElementById('lampiranDropzone');
    const lampiranPicker = document.getElementById('lampiranPicker');
    const lampiranSubmitInput = document.getElementById('lampiranSubmitInput');
    const lampiranList = document.getElementById('lampiranList');
    let lampiranFiles = [];

    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content
        || document.querySelector('input[name="_token"]').value;

    attachRupiahFormatter(document.getElementById('f_jumlah_total'));
    document.getElementById('f_jumlah_total').value = formatRibuan(document.getElementById('f_jumlah_total').value);

    function formatBytes(b) {
        if (b < 1024) return b + ' B';
        if (b < 1024*1024) return (b/1024).toFixed(1) + ' KB';
        return (b/(1024*1024)).toFixed(1) + ' MB';
    }

    function showPreview(file) {
        previewName.textContent = file.name;
        previewSize.textContent = formatBytes(file.size);
        previewImg.src = URL.createObjectURL(file);
        previewRow.classList.remove('d-none');
        scanBtn.disabled = false;
    }

    fileInput.addEventListener('change', () => {
        if (fileInput.files.length) showPreview(fileInput.files[0]);
    });

    ['dragover','dragenter'].forEach(evt => dropzone.addEventListener(evt, e => {
        e.preventDefault(); dropzone.classList.add('is-dragover');
    }));
    ['dragleave','drop'].forEach(evt => dropzone.addEventListener(evt, e => {
        e.preventDefault(); dropzone.classList.remove('is-dragover');
    }));
    dropzone.addEventListener('drop', e => {
        const files = e.dataTransfer.files;
        if (files.length) { fileInput.files = files; showPreview(files[0]); }
    });

    clearBtn.addEventListener('click', () => {
        fileInput.value = '';
        previewRow.classList.add('d-none');
        scanBtn.disabled = true;
    });

    // Beri nomor urut 1, 2, 3, ... di badge sebelah kiri tiap baris rincian akun,
    // supaya user gak perlu hitung manual baris mana yang keberapa.
    function renumberItemRows() {
        itemsWrapper.querySelectorAll('[data-row]').forEach((row, idx) => {
            const badge = row.querySelector('[data-row-number]');
            if (badge) badge.textContent = idx + 1;
        });
    }

    function addItemRow(data = {}) {
        const node = itemRowTemplate.content.cloneNode(true);
        const row = node.querySelector('[data-row]');

        row.querySelector('[data-field="no_akun"]').value = data.no_akun || '';
        row.querySelector('[data-field="pk"]').value = data.pk || '';
        row.querySelector('[data-field="cost_object"]').value = data.cost_object || '';
        setRupiahValue(row.querySelector('[data-field="jumlah_rupiah"]'), data.jumlah_rupiah);
        row.querySelector('[data-field="item_text"]').value = data.item_text || data.deskripsi_akun || '';

        attachRupiahFormatter(row.querySelector('[data-field="jumlah_rupiah"]'));

        const hint = row.querySelector('[data-akun-hint]');
        if (data.deskripsi_akun) {
            hint.textContent = '✓ Dikenali: ' + data.deskripsi_akun;
            hint.classList.add('found');
        }

        const noAkunInput = row.querySelector('[data-field="no_akun"]');
        const itemTextInput = row.querySelector('[data-field="item_text"]');

        noAkunInput.addEventListener('blur', () => {
            const val = noAkunInput.value.trim();
            hint.textContent = '';
            hint.classList.remove('found', 'notfound');
            if (!val) return;

            fetch(`/arsip-kasbon-api/lookup-akun/${encodeURIComponent(val)}`)
                .then(r => r.json())
                .then(res => {
                    if (res.found) {
                        hint.textContent = '✓ Dikenali: ' + res.deskripsi;
                        hint.classList.add('found');
                        if (!itemTextInput.value) itemTextInput.value = res.deskripsi;
                    } else {
                        hint.textContent = 'Akun baru — deskripsi akan disimpan sebagai referensi baru.';
                        hint.classList.add('notfound');
                    }
                })
                .catch(() => {});
        });

        row.querySelector('[data-remove-row]').addEventListener('click', () => {
            row.remove();
            renumberItemRows();
        });

        itemsWrapper.appendChild(row);
        renumberItemRows();
    }

    // ==================== Lampiran tambahan (multi-file) ====================
    function formatBytesLampiran(b) {
        if (b < 1024) return b + ' B';
        if (b < 1024*1024) return (b/1024).toFixed(1) + ' KB';
        return (b/(1024*1024)).toFixed(1) + ' MB';
    }

    function syncLampiranInput() {
        const dt = new DataTransfer();
        lampiranFiles.forEach(f => dt.items.add(f));
        lampiranSubmitInput.files = dt.files;
    }

    function renderLampiranList() {
        lampiranList.innerHTML = '';

        lampiranFiles.forEach((file, idx) => {
            const isPdf = file.type === 'application/pdf';
            const row = document.createElement('div');
            row.className = 'mb-2';
            row.className = 'mb-2';
            row.style.cssText = 'display:flex;align-items:center;gap:0.75rem;border:1px solid var(--line);border-radius:0.6rem;padding:0.6rem 0.9rem;background:#fff;';
            row.innerHTML = `
                <i class="bi ${isPdf ? 'bi-file-earmark-pdf-fill' : 'bi-file-earmark-image-fill'}" style="font-size:1.3rem;color:var(--brass-dark);"></i>
                <div class="flex-grow-1">
                    <div class="fw-semibold" style="color:var(--ink); font-size:0.88rem;">${file.name}</div>
                    <div class="ledger-subtitle" style="font-size:0.76rem;">${formatBytesLampiran(file.size)}</div>
                </div>
                <button type="button" class="btn ledger-btn-ghost" data-remove-lampiran="${idx}"><i class="bi bi-x-lg"></i></button>
            `;
            lampiranList.appendChild(row);
        });

        lampiranList.querySelectorAll('[data-remove-lampiran]').forEach(btn => {
            btn.addEventListener('click', () => {
                const idx = parseInt(btn.getAttribute('data-remove-lampiran'), 10);
                lampiranFiles.splice(idx, 1);
                syncLampiranInput();
                renderLampiranList();
            });
        });
    }

    function addLampiranFiles(fileList) {
        Array.from(fileList).forEach(f => lampiranFiles.push(f));
        syncLampiranInput();
        renderLampiranList();
    }

    lampiranPicker.addEventListener('change', () => {
        if (lampiranPicker.files.length) addLampiranFiles(lampiranPicker.files);
        lampiranPicker.value = '';
    });

    ['dragover','dragenter'].forEach(evt => lampiranDropzone.addEventListener(evt, e => {
        e.preventDefault(); lampiranDropzone.classList.add('is-dragover');
    }));
    ['dragleave','drop'].forEach(evt => lampiranDropzone.addEventListener(evt, e => {
        e.preventDefault(); lampiranDropzone.classList.remove('is-dragover');
    }));
    lampiranDropzone.addEventListener('drop', e => {
        e.preventDefault();
        if (e.dataTransfer.files.length) addLampiranFiles(e.dataTransfer.files);
    });

    addRowBtn.addEventListener('click', () => addItemRow());

    scanBtn.addEventListener('click', () => {
        if (!fileInput.files.length) return;

        scanBtn.disabled = true;
        scanBtnText.textContent = 'Membaca surat...';
        clearOcrFailedWarning();
        showOcrLoading();

        const fd = new FormData();
        fd.append('file_scan', fileInput.files[0]);
        fd.append('_token', csrfToken);

        fetch('{{ route("arsipkasbon.scan") }}', {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': csrfToken },
            body: fd,
        })
        .then(r => r.json())
        .then(res => {
            document.getElementById('temp_path').value = res.temp_path || '';

            const h = res.header || {};
            document.getElementById('f_tanggal_transaksi').value = h.tanggal_transaksi || '';
            document.getElementById('f_document_no').value = h.document_no || '';
            document.getElementById('f_numerator').value = h.numerator || '';
            document.getElementById('f_park_oleh').value = h.park_oleh || '';
            document.getElementById('f_nama_vendor').value = h.nama_vendor || '';
            document.getElementById('f_kode_vendor').value = h.kode_vendor || '';
            document.getElementById('f_cek_giro_trx').value = h.cek_giro_trx || '';
            document.getElementById('f_deskripsi_cost_object').value = h.deskripsi_cost_object || '';
            setRupiahValue(document.getElementById('f_jumlah_total'), h.jumlah_total);
            document.getElementById('f_terbilang').value = h.terbilang || '';

            itemsWrapper.innerHTML = '';
            const items = (res.items && res.items.length) ? res.items : [{}];
            items.forEach(it => addItemRow(it));

            uploadCard.classList.add('d-none');
            verifyCard.classList.remove('d-none');
            verifyCard.scrollIntoView({ behavior: 'smooth' });

            // Kalau OCR berhasil baca No Dokumen dan ternyata sudah pernah diunggah
            // di TAHUN yang sama, langsung munculkan notifnya di sini -- gak perlu
            // nunggu submit.
            if (res.duplicate) {
                showDuplicateWarning(res.duplicate.message);
            } else {
                clearDuplicateWarning();
            }

            // res.ocr_success sengaja dikirim dari server: true kalau minimal ada
            // satu field/baris item yang berhasil ketebak, false kalau dokumennya
            // sama sekali gagal dibaca (blur, kepotong, atau OCR error). Field yang
            // sudah ketebak (kalau ada) tetap terisi seperti biasa -- ini cuma nambah
            // notifikasi, gak pernah mengosongkan ulang field yang sudah keisi.
            if (res.ocr_success === false) {
                showOcrFailedWarning();
            } else {
                clearOcrFailedWarning();
            }
        })
        .catch(() => {
            itemsWrapper.innerHTML = '';
            addItemRow();
            uploadCard.classList.add('d-none');
            verifyCard.classList.remove('d-none');
            showOcrFailedWarning('Dokumen gagal terbaca. Pastikan gambar jelas dan koneksi internet tersambung, lalu isi field di bawah secara manual atau coba upload ulang.');
        })
        .finally(() => {
            scanBtn.disabled = false;
            scanBtnText.textContent = 'Upload & Baca Otomatis';
            hideOcrLoading();
        });
    });

    // Sebelum submit, kumpulkan baris item jadi input array `items[]`
    document.getElementById('finalForm').addEventListener('submit', function (e) {
        const form = e.target;

        // Bersihkan format "1.507.889" jadi angka polos "1507889" sebelum dikirim ke server
        document.getElementById('f_jumlah_total').value = unformatRibuan(document.getElementById('f_jumlah_total').value);

        form.querySelectorAll('[data-hidden-item]').forEach(el => el.remove());

        const rows = itemsWrapper.querySelectorAll('[data-row]');
        rows.forEach((row, idx) => {
            const fields = ['no_akun', 'pk', 'cost_object', 'jumlah_rupiah', 'item_text'];
            fields.forEach(f => {
                let val = row.querySelector(`[data-field="${f}"]`).value;
                if (f === 'jumlah_rupiah') val = unformatRibuan(val);
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = `items[${idx}][${f}]`;
                input.value = val;
                input.setAttribute('data-hidden-item', '1');
                form.appendChild(input);
            });
            const hintText = row.querySelector('[data-akun-hint]').textContent;
            const isKnown = hintText.startsWith('✓');
            const deskInput = document.createElement('input');
            deskInput.type = 'hidden';
            deskInput.name = `items[${idx}][deskripsi_akun]`;
            deskInput.value = isKnown
                ? hintText.replace('✓ Dikenali: ', '')
                : row.querySelector('[data-field="item_text"]').value;
            deskInput.setAttribute('data-hidden-item', '1');
            form.appendChild(deskInput);
        });

        if (rows.length === 0) {
            e.preventDefault();
            alert('Tambahkan minimal 1 baris akun.');
        }
    });

    // ==================== Cek duplikat No Dokumen (di-scope ke TAHUN transaksi) ====================
    const documentNoInput = document.getElementById('f_document_no');
    const tanggalTransaksiInput = document.getElementById('f_tanggal_transaksi');
    const duplicateBanner = document.getElementById('duplicateBanner');
    const duplicateBannerText = document.getElementById('duplicateBannerText');
    const submitBtn = document.querySelector('#finalForm button[type="submit"]');
    let isDuplicate = false;

    function showDuplicateWarning(message) {
        isDuplicate = true;
        duplicateBannerText.textContent = message || 'Surat Permintaan Pembayaran ini sudah diunggah.';
        duplicateBanner.classList.remove('d-none');
        documentNoInput.classList.add('is-duplicate');
        submitBtn.disabled = true;
    }

    function clearDuplicateWarning() {
        isDuplicate = false;
        duplicateBanner.classList.add('d-none');
        documentNoInput.classList.remove('is-duplicate');
        submitBtn.disabled = false;
    }

    let checkDocTimer = null;
    function checkDocumentNoLive() {
        clearTimeout(checkDocTimer);

        const value = documentNoInput.value.trim();
        if (!value) { clearDuplicateWarning(); return; }

        checkDocTimer = setTimeout(() => {
            const params = new URLSearchParams({ document_no: value });
            if (tanggalTransaksiInput.value) {
                params.set('tanggal_transaksi', tanggalTransaksiInput.value);
            }

            fetch(`{{ route('arsipkasbon.check-document') }}?${params.toString()}`)
                .then(r => r.json())
                .then(res => {
                    if (res.duplicate) showDuplicateWarning(res.message);
                    else clearDuplicateWarning();
                })
                .catch(() => {});
        }, 350);
    }

    // Cek ulang setiap kali Document No ATAU Tanggal Transaksi berubah,
    // karena tahunnya yang menentukan apakah dianggap duplikat.
    documentNoInput.addEventListener('input', checkDocumentNoLive);
    tanggalTransaksiInput.addEventListener('change', checkDocumentNoLive);

    // Blokir submit kalau masih terdeteksi duplikat
    document.getElementById('finalForm').addEventListener('submit', function (e) {
        if (isDuplicate) {
            e.preventDefault();
            duplicateBanner.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
    });

    // Kalau halaman ini dimuat ulang karena validasi server gagal (misal duplikat
    // No Dokumen di tahun yang sama), langsung buka form verifikasi lagi dengan
    // data yang tadi diisi.
    if (HAS_VALIDATION_ERROR) {
        uploadCard.classList.add('d-none');
        verifyCard.classList.remove('d-none');
        itemsWrapper.innerHTML = '';
        if (OLD_ITEMS.length) {
            OLD_ITEMS.forEach(it => addItemRow(it));
        } else {
            addItemRow();
        }
        if (documentNoInput.classList.contains('is-duplicate')) {
            let msg = null;
            if (documentNoInput.value) {
                const yr = tanggalTransaksiInput.value ? new Date(tanggalTransaksiInput.value).getFullYear() : null;
                msg = `Surat Permintaan Pembayaran dengan No Dokumen "${documentNoInput.value}"${yr ? ' untuk tahun ' + yr : ''} sudah pernah diunggah sebelumnya.`;
            }
            showDuplicateWarning(msg);
        }
    }
})();
</script>
@endpush

@endsection