@extends('layouts.app')

@section('content')

@include('partials.ledger-styles')

<div class="ledger-page">
    <div class="container-fluid py-1 py-md-2">

        @if (session('success'))
            <div class="alert ledger-alert-success">{{ session('success') }}</div>
        @endif

        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
            <div>
                <h2 class="ledger-title mb-1">Data Karyawan</h2>
                {{-- <p class="ledger-subtitle mb-0">Database identitas ini dipakai untuk isi otomatis dokumen kontrak.</p> --}}
            </div>
            <a href="{{ route('karyawan.create') }}" class="btn ledger-btn-brass">
                <i class="bi bi-person-plus me-1"></i> Tambah Karyawan
            </a>
        </div>
        <hr>

        <div class="row g-2 mb-3">
            <div class="col-md-6">
                <div class="ledger-toolbar position-relative mb-0">
                    <input type="text" id="karyawanLiveSearch" value="{{ request('search') }}" class="form-control"
                           placeholder="Ketik nama / NIK / No. KTP..." autocomplete="off">
                    <div class="spinner-border spinner-border-sm text-secondary position-absolute d-none"
                         id="karyawanSearchSpinner" style="right: 0.9rem; top: 0.65rem;" role="status"></div>
                </div>
            </div>
            <div class="col-md-4">
                <select id="karyawanStatusFilter" class="form-select">
                    <option value="">Semua Status</option>
                    <option value="dmg" @selected(request('status_kontrak') === 'dmg')>PKWT DMG (Musim Giling)</option>
                    <option value="lmg" @selected(request('status_kontrak') === 'lmg')>PKWT DMG-LMG (12 Bulan)</option>
                </select>
            </div>
            <div class="col-md-2">
                <select id="karyawanBagianFilter" class="form-select">
                    <option value="">Semua Bagian</option>
                    @foreach ($bagianList as $bagian)
                        <option value="{{ $bagian }}" @selected(request('bagian') === $bagian)>{{ $bagian }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div id="karyawanTableWrap">
            @include('karyawan._table')
        </div>

    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const input = document.getElementById('karyawanLiveSearch');
    const statusFilter = document.getElementById('karyawanStatusFilter');
    const bagianFilter = document.getElementById('karyawanBagianFilter');
    const wrap = document.getElementById('karyawanTableWrap');
    const spinner = document.getElementById('karyawanSearchSpinner');
    const baseUrl = "{{ route('karyawan.index') }}";
    let debounceTimer = null;
    let currentRequest = null;

    async function runSearch(pushUrl = true) {
        spinner.classList.remove('d-none');

        const params = new URLSearchParams();
        const q = input.value.trim();
        if (q) params.set('search', q);
        if (statusFilter.value) params.set('status_kontrak', statusFilter.value);
        if (bagianFilter.value) params.set('bagian', bagianFilter.value);

        const url = params.toString() ? `${baseUrl}?${params.toString()}` : baseUrl;

        if (pushUrl) {
            window.history.replaceState({}, '', url);
        }

        if (currentRequest) {
            currentRequest.abort();
        }
        const controller = new AbortController();
        currentRequest = controller;

        try {
            const res = await fetch(url, {
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
                signal: controller.signal,
            });
            const html = await res.text();
            wrap.innerHTML = html;
        } catch (err) {
            if (err.name !== 'AbortError') {
                console.error(err);
            }
        } finally {
            spinner.classList.add('d-none');
        }
    }

    input.addEventListener('input', function () {
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(() => runSearch(), 300);
    });

    statusFilter.addEventListener('change', function () {
        runSearch();
    });

    bagianFilter.addEventListener('change', function () {   // <-- tambah blok ini
        runSearch();
    });
});
</script>
@endpush

@endsection
