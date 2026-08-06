@extends('layouts.app')

@section('content')

@include('partials.ledger-styles')

<div class="ledger-page">
    <div class="container-fluid py-1 py-md-2">

        @if (session('success'))
            <div class="alert ledger-alert-success d-flex align-items-center gap-2" role="alert">
                <i class="bi bi-check-circle-fill"></i>
                <div>{{ session('success') }}</div>
            </div>
        @endif
        @if (session('error'))
            <div class="alert ledger-alert-danger" role="alert">{{ session('error') }}</div>
        @endif

        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
            <div>
                <h2 class="ledger-title mb-1">Daftar Kontrak</h2>
                {{-- <p class="ledger-subtitle mb-0">Semua kontrak karyawan yang pernah dibuat lewat sistem.</p> --}}
            </div>

            <div class="d-flex gap-2">
                <a href="{{ route('kontrak-template.index') }}" class="btn ledger-btn-ghost">
                    <i class="bi bi-file-earmark-text me-1"></i> Kelola Format
                </a>
                <a href="{{ route('kontrak.create') }}" class="btn ledger-btn-brass">
                    <i class="bi bi-file-earmark-plus me-1"></i> Buat Kontrak
                </a>
            </div>
        </div>
         <hr>

        <div class="ledger-toolbar d-flex flex-wrap align-items-center gap-2 mb-3">
            <input type="text" id="searchInput" value="{{ request('search') }}" class="form-control"
                   style="flex: 1 1 220px; min-width: 180px;"
                   placeholder="Cari nomor kontrak / nama / NIK karyawan...">

            <select id="jenisFilter" class="form-select" style="flex: 1 1 170px; min-width: 150px; width: auto;">
                <option value="">Semua Jenis Kontrak</option>
                @foreach ($jenisList as $jenis)
                    <option value="{{ $jenis->id }}" @selected(request('jenis') == $jenis->id)>{{ $jenis->nama_jenis }}</option>
                @endforeach
            </select>

            <select id="bagianFilter" class="form-select" style="flex: 1 1 160px; min-width: 140px; width: auto;">
                <option value="">Pilih bagian </option>
                @foreach ($bagianList as $bagianOpt)
                    <option value="{{ $bagianOpt }}" @selected(request('bagian') == $bagianOpt)>{{ $bagianOpt }}</option>
                @endforeach
            </select>

            <button type="button" id="btnGenerateBagian" class="btn ledger-btn-ghost text-nowrap d-none">
                <i class="bi bi-arrow-repeat me-1"></i> Buat Semua Dokumen <span id="btnGenerateBagianBadge"></span>
            </button>

            <button type="button" id="btnDownloadBagian" class="btn ledger-btn-ghost text-nowrap" disabled>
                <i class="bi bi-file-earmark-zip me-1"></i> Unduh per Bagian
            </button>

            <span id="bagianDownloadStatus" class="ledger-inline-status align-self-center"></span>

            <div class="d-flex gap-2 ms-md-auto">
                <button type="button" id="btnToggleCheckbox" class="btn ledger-btn-ghost text-nowrap">
                    <i class="bi bi-check2-square me-1"></i> Unduh Riwayat
                </button>
                <button type="button" id="btnDownloadSelected" class="btn ledger-btn-brass d-none text-nowrap" disabled>
                    <i class="bi bi-download me-1"></i> Unduh Terpilih (<span id="selectedCount">0</span>)
                </button>
                <button type="button" id="btnCancelCheckbox" class="btn ledger-btn-ghost d-none text-nowrap">
                    Batal
                </button>
            </div>
        </div>

        <form id="downloadSelectedForm" method="POST" action="{{ route('kontrak.download-selected') }}" class="d-none">
            @csrf
        </form>

        <div id="resultsContainer" style="transition: opacity 0.15s ease;">
            @include('kontrak.partials.results')
        </div>

    </div>
</div>

@push('scripts')
<script>
(function () {
    const searchInput = document.getElementById('searchInput');
    const jenisFilter = document.getElementById('jenisFilter');
    const bagianFilter = document.getElementById('bagianFilter');
    const resultsContainer = document.getElementById('resultsContainer');
    const indexUrl = '{{ route('kontrak.index') }}';
    const downloadBagianUrl = '{{ route('kontrak.download-bagian') }}';
    const cekStatusBagianUrl = '{{ route('kontrak.cek-status-bagian') }}';
    const generateBagianUrl = '{{ route('kontrak.generate-bagian') }}';
    const csrfToken = '{{ csrf_token() }}';

    const btnDownloadBagian = document.getElementById('btnDownloadBagian');
    const bagianDownloadStatus = document.getElementById('bagianDownloadStatus');
    const btnGenerateBagian = document.getElementById('btnGenerateBagian');
    const btnGenerateBagianBadge = document.getElementById('btnGenerateBagianBadge');

    const btnToggleCheckbox = document.getElementById('btnToggleCheckbox');
    const btnCancelCheckbox = document.getElementById('btnCancelCheckbox');
    const btnDownloadSelected = document.getElementById('btnDownloadSelected');
    const selectedCountEl = document.getElementById('selectedCount');
    const downloadSelectedForm = document.getElementById('downloadSelectedForm');

    let debounceTimer = null;
    let checkboxMode = false;

    function currentParams() {
        const params = new URLSearchParams();
        if (searchInput.value.trim()) params.set('search', searchInput.value.trim());
        if (jenisFilter.value) params.set('jenis', jenisFilter.value);
        if (bagianFilter.value) params.set('bagian', bagianFilter.value);
        return params;
    }

    function attachPaginationHandlers() {
        resultsContainer.querySelectorAll('.pagination a.page-link').forEach(link => {
            link.addEventListener('click', function (e) {
                e.preventDefault();
                const url = new URL(this.href);
                fetchResults(url.searchParams.get('page'));
                resultsContainer.scrollIntoView({ behavior: 'smooth', block: 'start' });
            });
        });
    }

    function attachCheckboxHandlers() {
        resultsContainer.querySelectorAll('.kontrak-checkbox').forEach(cb => {
            cb.addEventListener('change', updateSelectedCount);
        });
        applyCheckboxModeVisibility();
    }

    function updateSelectedCount() {
        const count = resultsContainer.querySelectorAll('.kontrak-checkbox:checked').length;
        selectedCountEl.textContent = count;
        btnDownloadSelected.disabled = count === 0;
    }

    function applyCheckboxModeVisibility() {
        resultsContainer.querySelectorAll('.ledger-checkbox-col').forEach(el => {
            el.classList.toggle('d-none', !checkboxMode);
        });
    }

    function fetchResults(page) {
        const params = currentParams();
        if (page) params.set('page', page);

        resultsContainer.style.opacity = '0.45';

        fetch(`${indexUrl}?${params.toString()}`, {
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
        })
        .then(r => r.text())
        .then(html => {
            resultsContainer.innerHTML = html;
            resultsContainer.style.opacity = '1';
            attachPaginationHandlers();
            attachCheckboxHandlers();
            updateSelectedCount();

            const qs = params.toString();
            const newUrl = qs ? `${window.location.pathname}?${qs}` : window.location.pathname;
            history.replaceState(null, '', newUrl);
        })
        .catch(() => {
            resultsContainer.style.opacity = '1';
        });
    }

    searchInput.addEventListener('input', () => {
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(() => fetchResults(), 300);
    });

    jenisFilter.addEventListener('change', () => fetchResults());
    bagianFilter.addEventListener('change', () => {
        fetchResults();
        cekStatusBagian();
    });

    // Helper: unduh via fetch supaya bisa kasih notif kecil "Sedang
    // mengunduh..." -> "Selesai", dan kalau gagal, pesan aslinya dari
    // server (bukan reload halaman - biar nggak kena masalah flash
    // message yang keburu kebaca/ilang duluan pas fetch ngikutin redirect).
    function tampilkanAlertGagal(pesan) {
        const existing = document.getElementById('ajaxErrorAlert');
        if (existing) existing.remove();

        const alertEl = document.createElement('div');
        alertEl.id = 'ajaxErrorAlert';
        alertEl.className = 'alert ledger-alert-danger';
        alertEl.setAttribute('role', 'alert');
        alertEl.textContent = pesan;

        const container = document.querySelector('.ledger-page .container-fluid');
        container.insertBefore(alertEl, container.firstChild);
        alertEl.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }

    function downloadWithStatus(url, statusEl, fetchOptions) {
        statusEl.innerHTML = '<span class="ledger-spinner"></span> Sedang mengunduh...';

        const options = Object.assign({
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
        }, fetchOptions || {});

        return fetch(url, options)
            .then(response => {
                const contentType = response.headers.get('content-type') || '';
                const isJson = contentType.includes('application/json');

                if (isJson) {
                    return response.json().then(data => {
                        statusEl.innerHTML = '<i class="bi bi-x-circle-fill text-danger"></i> Gagal';
                        setTimeout(() => { statusEl.innerHTML = ''; }, 3000);
                        tampilkanAlertGagal(data.message || 'Gagal mengunduh dokumen.');
                    });
                }

                if (!response.ok) {
                    statusEl.innerHTML = '<i class="bi bi-x-circle-fill text-danger"></i> Gagal';
                    setTimeout(() => { statusEl.innerHTML = ''; }, 3000);
                    tampilkanAlertGagal('Gagal mengunduh dokumen. Coba lagi beberapa saat.');
                    return;
                }

                let filename = 'download.zip';
                const disposition = response.headers.get('content-disposition') || '';
                const match = disposition.match(/filename\*?=(?:UTF-8'')?"?([^";]+)"?/i);
                if (match && match[1]) filename = decodeURIComponent(match[1]);

                return response.blob().then(blob => {
                    const blobUrl = window.URL.createObjectURL(blob);
                    const a = document.createElement('a');
                    a.href = blobUrl;
                    a.download = filename;
                    document.body.appendChild(a);
                    a.click();
                    a.remove();
                    window.URL.revokeObjectURL(blobUrl);

                    statusEl.innerHTML = '<i class="bi bi-check-circle-fill text-success"></i> Selesai';
                    setTimeout(() => { statusEl.innerHTML = ''; }, 2000);
                });
            })
            .catch(() => {
                statusEl.innerHTML = '<i class="bi bi-x-circle-fill text-danger"></i> Gagal';
                tampilkanAlertGagal('Gagal mengunduh dokumen. Periksa koneksi lalu coba lagi.');
            });
    }

    // Cek berapa kontrak di bagian terpilih yang belum punya dokumen -
    // dipakai buat nampilin/nyembunyiin tombol "Buat Semua Dokumen" dan
    // buat nentuin tombol "Unduh per Bagian" bisa dipencet atau tidak.
    function cekStatusBagian() {
        const bagian = bagianFilter.value;

        if (!bagian) {
            btnGenerateBagian.classList.add('d-none');
            btnDownloadBagian.disabled = true;
            return;
        }

        const url = new URL(cekStatusBagianUrl);
        url.searchParams.set('bagian', bagian);

        fetch(url.toString(), { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
            .then(r => r.json())
            .then(data => {
                if (data.total === 0) {
                    btnGenerateBagian.classList.add('d-none');
                    btnDownloadBagian.disabled = true;
                    return;
                }

                btnDownloadBagian.disabled = data.sudah_generate === 0;

                if (data.belum_generate > 0) {
                    btnGenerateBagianBadge.textContent = `(${data.belum_generate} belum dibuat)`;
                    btnGenerateBagian.classList.remove('d-none');
                } else {
                    btnGenerateBagian.classList.add('d-none');
                }
            })
            .catch(() => {
                btnGenerateBagian.classList.add('d-none');
            });
    }

    // Buat sekaligus semua dokumen kontrak yang belum ada di bagian
    // terpilih, biar abis ini "Unduh per Bagian" langsung lengkap tanpa
    // harus buat dokumennya satu-satu.
    btnGenerateBagian.addEventListener('click', () => {
        const bagian = bagianFilter.value;
        if (!bagian) return;

        btnGenerateBagian.disabled = true;
        btnDownloadBagian.disabled = true;
        bagianDownloadStatus.innerHTML = '<span class="ledger-spinner"></span> Sedang membuat dokumen...';

        fetch(generateBagianUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': csrfToken,
            },
            body: JSON.stringify({ bagian }),
        })
        .then(r => r.json().then(data => ({ ok: r.ok, data })))
        .then(({ ok, data }) => {
            if (!ok || !data.success) {
                bagianDownloadStatus.innerHTML = '<i class="bi bi-x-circle-fill text-danger"></i> Gagal';
                setTimeout(() => { bagianDownloadStatus.innerHTML = ''; }, 3000);
                tampilkanAlertGagal(data.message || 'Gagal membuat dokumen kontrak.');
                return;
            }

            bagianDownloadStatus.innerHTML = '<i class="bi bi-check-circle-fill text-success"></i> Dokumen sudah dibuat';
            setTimeout(() => { bagianDownloadStatus.innerHTML = ''; }, 3000);

            if (data.gagal > 0) {
                tampilkanAlertGagal(data.message);
            }
        })
        .catch(() => {
            bagianDownloadStatus.innerHTML = '<i class="bi bi-x-circle-fill text-danger"></i> Gagal';
            tampilkanAlertGagal('Gagal membuat dokumen. Periksa koneksi lalu coba lagi.');
        })
        .finally(() => {
            btnGenerateBagian.disabled = false;
            cekStatusBagian();
        });
    });

    // Unduh semua kontrak dalam 1 bagian sekaligus (ZIP)
    btnDownloadBagian.addEventListener('click', () => {
        const bagian = bagianFilter.value;
        if (!bagian) return;
        const url = new URL(downloadBagianUrl);
        url.searchParams.set('bagian', bagian);
        btnDownloadBagian.disabled = true;
        downloadWithStatus(url.toString(), bagianDownloadStatus).finally(() => {
            btnDownloadBagian.disabled = false;
        });
    });

    cekStatusBagian();

    // Mode checkbox: pilih beberapa riwayat kontrak lalu unduh sekaligus (ZIP)
    btnToggleCheckbox.addEventListener('click', () => {
        checkboxMode = true;
        btnToggleCheckbox.classList.add('d-none');
        btnDownloadSelected.classList.remove('d-none');
        btnCancelCheckbox.classList.remove('d-none');
        applyCheckboxModeVisibility();
        updateSelectedCount();
    });

    btnCancelCheckbox.addEventListener('click', () => {
        checkboxMode = false;
        btnToggleCheckbox.classList.remove('d-none');
        btnDownloadSelected.classList.add('d-none');
        btnCancelCheckbox.classList.add('d-none');
        resultsContainer.querySelectorAll('.kontrak-checkbox').forEach(cb => cb.checked = false);
        applyCheckboxModeVisibility();
        updateSelectedCount();
    });

    btnDownloadSelected.addEventListener('click', () => {
        const ids = Array.from(resultsContainer.querySelectorAll('.kontrak-checkbox:checked')).map(cb => cb.value);
        if (ids.length === 0) return;

        downloadSelectedForm.querySelectorAll('input[name="ids[]"]').forEach(el => el.remove());
        ids.forEach(id => {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'ids[]';
            input.value = id;
            downloadSelectedForm.appendChild(input);
        });
        downloadSelectedForm.submit();
    });

    attachPaginationHandlers();
    attachCheckboxHandlers();
})();
</script>
@endpush

@endsection
