@extends('layouts.app')

@section('content')

@include('partials.ledger-styles')

<div class="ledger-page">
    <div class="container-fluid py-1 py-md-2">

        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
            <div>
                <h2 class="ledger-title mb-1">Pratinjau Format</h2>
                <p class="ledger-subtitle mb-0">{{ $template->nama_template }}</p>
            </div>
            <a href="{{ route('kontrak-template.index') }}" class="btn ledger-btn-ghost">
                <i class="bi bi-arrow-left me-1"></i> Kembali
            </a>
        </div>

        {{-- <div class="alert alert-info">
            Ini isi template mentah - bagian <code>@{{PLACEHOLDER}}</code> memang belum keisi data
            (baru keisi otomatis kalau dipakai generate kontrak beneran). Cek pasal, susunan, dan layoutnya di sini.
        </div> --}}

        <div class="card ledger-card">
            <div class="card-body">
                <div id="docxPreviewLoading" class="text-center py-5 ledger-subtitle">
                    <div class="spinner-border spinner-border-sm me-2" role="status"></div>
                    Memuat preview template...
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
            if (!res.ok) throw new Error('Gagal mengambil file template (' + res.status + ')');
            return res.blob();
        })
        .then(blob => docx.renderAsync(blob, container, container, {
            className: 'docx-preview',
            inWrapper: true,
            ignoreWidth: false,
            ignoreHeight: false,
            experimental: true,
            renderHeaders: true,
            renderFooters: true,
            renderFootnotes: true,
            renderEndnotes: true,
            breakPages: true,
            useBase64URL: true,
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
