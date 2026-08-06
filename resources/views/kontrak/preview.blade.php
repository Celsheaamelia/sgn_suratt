@extends('layouts.app')

@section('content')

@include('partials.ledger-styles')

<div class="ledger-page">
    <div class="container-fluid py-1 py-md-2">

        @if (session('success'))
            <div class="alert ledger-alert-success">{{ session('success') }}</div>
        @endif
        @if (session('error'))
            <div class="alert ledger-alert-danger">{{ session('error') }}</div>
        @endif

        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
            <div>
                <h2 class="ledger-title mb-1">Preview Dokumen</h2>
                <p class="ledger-subtitle mb-0 ledger-nomor">{{ $kontrak->nomor_kontrak }}</p>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('kontrak.show', $kontrak) }}" class="btn ledger-btn-ghost">
                    <i class="bi bi-arrow-left me-1"></i> Kembali
                </a>
                <a href="{{ route('kontrak.download', $kontrak) }}" class="btn ledger-btn-brass">
                    <i class="bi bi-file-earmark-word me-1"></i> Download
                </a>
            </div>
        </div>

        @if (!$kontrak->published_at)
            <div class="alert alert-warning">
                Dokumen ini masih <strong>Draft</strong>. Cek dulu isinya di bawah ini — kalau sudah benar
                (termasuk template, pasal, nama & jabatan penandatangan, nomor SK).
            </div>
        @endif

        <div class="card ledger-card">
            <div class="card-body">
                <div id="docxPreviewLoading" class="text-center py-5 ledger-subtitle">
                    <div class="spinner-border spinner-border-sm me-2" role="status"></div>
                    Memuat preview dokumen...
                </div>
                <div id="docxPreviewError" class="alert alert-danger d-none"></div>
                <div id="docxPreviewContainer" style="max-width: 850px; margin: 0 auto;"></div>
            </div>
        </div>

    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/jszip@3.10.1/dist/jszip.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/docx-preview/dist/docx-preview.min.js"></script>
<script>
(function () {
    const url = @json($docxUrl);
    const loadingEl = document.getElementById('docxPreviewLoading');
    const errorEl = document.getElementById('docxPreviewError');
    const container = document.getElementById('docxPreviewContainer');

    fetch(url)
        .then(res => {
            if (!res.ok) throw new Error('Gagal mengambil file dokumen (' + res.status + ')');
            return res.blob();
        })
        .then(blob => docx.renderAsync(blob, container, container, {
            className: 'docx-preview',
            inWrapper: true,
            ignoreWidth: false,
            ignoreHeight: false,
        }))
        .then(() => {
            loadingEl.classList.add('d-none');
        })
        .catch(err => {
            loadingEl.classList.add('d-none');
            errorEl.classList.remove('d-none');
            errorEl.textContent = 'Gagal menampilkan preview: ' + err.message;
            console.error(err);
        });
})();
</script>
@endpush

@endsection
