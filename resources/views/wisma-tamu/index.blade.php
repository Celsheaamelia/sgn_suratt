@extends('layouts.app')

@section('content')

@include('wisma-tamu._styles')

<div class="wisma-page">
    <div class="container-fluid py-1 py-md-2">

        @if (session('success'))
            <div class="wisma-alert-success mb-3">{{ session('success') }}</div>
        @endif

        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
            <div>
                <h2 class="wisma-title mb-1">Daftar Kamar &amp; Tamu</h2>
                <p class="wisma-subtitle mb-0">Riwayat tamu Wisma Tamu, 15 kamar tersedia.</p>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('wisma-tamu.tv') }}" target="_blank" class="btn wisma-btn-ghost">
                    <i class="bi bi-tv me-1"></i> Tampilan TV
                </a>
                <a href="{{ route('wisma-tamu.create') }}" class="btn wisma-btn-brass">
                    <i class="bi bi-plus-lg me-1"></i> Input Tamu
                </a>
            </div>
        </div>

        <div class="row g-2 mb-3">
            <div class="col-md-6">
                <div class="position-relative">
                    <input type="text" id="tamuLiveSearch" value="{{ request('search') }}" class="form-control"
                           placeholder="Cari nama tamu / instansi / no. kamar..." autocomplete="off">
                    <div class="spinner-border spinner-border-sm text-secondary position-absolute d-none"
                         id="tamuSearchSpinner" style="right: 0.9rem; top: 0.55rem;" role="status"></div>
                </div>
            </div>
        </div>

        <div id="tamuTableWrap">
            @include('wisma-tamu._table')
        </div>

    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const input = document.getElementById('tamuLiveSearch');
    const wrap = document.getElementById('tamuTableWrap');
    const spinner = document.getElementById('tamuSearchSpinner');
    const baseUrl = "{{ route('wisma-tamu.index') }}";
    let debounceTimer = null;
    let currentRequest = null;

    async function runSearch() {
        spinner.classList.remove('d-none');

        const q = input.value.trim();
        const url = q ? `${baseUrl}?search=${encodeURIComponent(q)}` : baseUrl;
        window.history.replaceState({}, '', url);

        if (currentRequest) currentRequest.abort();
        const controller = new AbortController();
        currentRequest = controller;

        try {
            const res = await fetch(url, {
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
                signal: controller.signal,
            });
            wrap.innerHTML = await res.text();
        } catch (err) {
            if (err.name !== 'AbortError') console.error(err);
        } finally {
            spinner.classList.add('d-none');
        }
    }

    input.addEventListener('input', function () {
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(runSearch, 300);
    });
});
</script>
@endpush

@endsection
