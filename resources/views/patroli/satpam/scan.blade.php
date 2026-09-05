@extends('layouts.app')

@section('content')
@include('patroli._styles')

<div class="patroli-page">
    <div class="container-fluid py-1 py-md-2" style="max-width: 640px;">

        <div class="d-flex align-items-center mb-3">
            <a href="{{ route('patroli.index') }}" class="btn patroli-btn-icon me-2">
                <i class="bi bi-arrow-left"></i>
            </a>
            <div>
                <div class="patroli-eyebrow mb-1">Scan Titik</div>
                <h5 class="patroli-title mb-0">{{ $checkpoint->nama_titik }}</h5>
                <small class="patroli-subtitle">
                    {{ $checkpoint->kode }} @if($checkpoint->area) &middot; {{ $checkpoint->area }} @endif
                </small>
            </div>
        </div>

        {{-- GPS wajib aktif sebelum bisa mengisi laporan (SOP 1. Persiapan) --}}
        <div id="lokasiGate" class="patroli-card mb-3">
            <div class="card-body py-3">
                <div id="lokasiGateContent" class="d-flex align-items-center gap-2">
                    <div class="spinner-border spinner-border-sm text-success" role="status"></div>
                    <span>Mengambil lokasi GPS, mohon tunggu...</span>
                </div>
            </div>
        </div>

        <div class="patroli-card" id="formCard" style="display:none;">
            <div class="card-body patroli-form-section">
                <form action="{{ route('patroli.scan.store', $checkpoint->kode) }}" method="POST" enctype="multipart/form-data" id="scanForm">
                    @csrf

                    <label class="form-label d-block mb-2">Kondisi Titik</label>
                    <div class="row g-2 mb-3 patroli-status-pick">
                        <div class="col-4">
                            <input type="radio" name="status" id="status_aman" value="aman"
                                   {{ old('status', 'aman') === 'aman' ? 'checked' : '' }}>
                            <label for="status_aman">
                                <i class="bi bi-check-circle"></i> Aman
                            </label>
                        </div>
                        <div class="col-4">
                            <input type="radio" name="status" id="status_temuan" value="temuan"
                                   {{ old('status') === 'temuan' ? 'checked' : '' }}>
                            <label for="status_temuan">
                                <i class="bi bi-exclamation-triangle"></i> Temuan
                            </label>
                        </div>
                        <div class="col-4">
                            <input type="radio" name="status" id="status_bahaya" value="bahaya"
                                   {{ old('status') === 'bahaya' ? 'checked' : '' }}>
                            <label for="status_bahaya">
                                <i class="bi bi-exclamation-octagon"></i> Bahaya
                            </label>
                        </div>
                    </div>
                    @error('status')
                        <div class="text-danger small mb-3">{{ $message }}</div>
                    @enderror

                    <div class="mb-3">
                        <label for="catatan" class="form-label">
                            Catatan <span id="catatanOptional" class="text-muted fw-normal">(opsional)</span>
                        </label>
                        <textarea name="catatan" id="catatan" rows="3" maxlength="1000"
                                  class="form-control @error('catatan') is-invalid @enderror"
                                  placeholder="Jelaskan kondisi di titik ini...">{{ old('catatan') }}</textarea>
                        @error('catatan')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="foto" class="form-label">
                            Foto / Video Bukti
                            <span id="fotoRequiredLabel" class="text-danger fw-normal d-none">*wajib untuk temuan/bahaya</span>
                        </label>
                        <input type="file" name="foto" id="foto" accept="image/*,video/*" capture="environment"
                               class="form-control @error('foto') is-invalid @enderror">
                        <div class="form-text">Foto maksimal 20MB, atau video singkat (mp4/mov/webm).</div>
                        @error('foto')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div id="fotoPreviewWrap" class="mt-2 d-none">
                            <img id="fotoPreview" src="#" alt="Preview" class="img-fluid rounded border d-none" style="max-height: 220px;">
                            <video id="videoPreview" controls class="img-fluid rounded border d-none" style="max-height: 220px;"></video>
                        </div>
                    </div>

                    <input type="hidden" name="latitude" id="latitude" value="{{ old('latitude') }}">
                    <input type="hidden" name="longitude" id="longitude" value="{{ old('longitude') }}">
                    <div id="lokasiInfo" class="patroli-subtitle mb-3"></div>
                    <div id="jarakWarning" class="patroli-alert-info mb-3 d-none"></div>

                    <button type="submit" class="btn patroli-btn-brass w-100 py-2">
                        <i class="bi bi-send-check"></i> Kirim Laporan
                    </button>
                </form>
            </div>
        </div>

    </div>
</div>

<script>
    // ===== Koordinat checkpoint terdaftar (untuk cek kewajaran jarak, kalau ada) =====
    const cpLat = {{ $checkpoint->latitude ?? 'null' }};
    const cpLon = {{ $checkpoint->longitude ?? 'null' }};
    const AMBANG_JARAK_METER = 200; // toleransi akurasi GPS di area pabrik

    function jarakMeter(lat1, lon1, lat2, lon2) {
        const R = 6371000;
        const toRad = d => d * Math.PI / 180;
        const dLat = toRad(lat2 - lat1);
        const dLon = toRad(lon2 - lon1);
        const a = Math.sin(dLat / 2) ** 2 + Math.cos(toRad(lat1)) * Math.cos(toRad(lat2)) * Math.sin(dLon / 2) ** 2;
        return 2 * R * Math.asin(Math.sqrt(a));
    }

    // ===== Toggle keterangan wajib foto/catatan sesuai status yang dipilih =====
    const radios = document.querySelectorAll('input[name="status"]');
    const fotoRequiredLabel = document.getElementById('fotoRequiredLabel');
    const catatanOptional = document.getElementById('catatanOptional');

    function updateRequirement() {
        const checked = document.querySelector('input[name="status"]:checked');
        const isAman = checked && checked.value === 'aman';
        fotoRequiredLabel.classList.toggle('d-none', isAman);
        catatanOptional.classList.toggle('d-none', !isAman);
    }
    radios.forEach(r => r.addEventListener('change', updateRequirement));
    updateRequirement();

    // ===== Preview foto/video =====
    document.getElementById('foto').addEventListener('change', function (e) {
        const file = e.target.files[0];
        const wrap = document.getElementById('fotoPreviewWrap');
        const img = document.getElementById('fotoPreview');
        const vid = document.getElementById('videoPreview');
        img.classList.add('d-none');
        vid.classList.add('d-none');
        if (!file) { wrap.classList.add('d-none'); return; }

        const url = URL.createObjectURL(file);
        if (file.type.startsWith('video/')) {
            vid.src = url;
            vid.classList.remove('d-none');
        } else {
            img.src = url;
            img.classList.remove('d-none');
        }
        wrap.classList.remove('d-none');
    });

    // ===== GPS WAJIB: form hasil scan baru muncul setelah lokasi berhasil didapat =====
    const gate = document.getElementById('lokasiGateContent');
    const formCard = document.getElementById('formCard');
    const lokasiInfo = document.getElementById('lokasiInfo');
    const jarakWarning = document.getElementById('jarakWarning');

    function tampilkanForm(lat, lon) {
        document.getElementById('latitude').value = lat;
        document.getElementById('longitude').value = lon;
        document.getElementById('lokasiGate').style.display = 'none';
        formCard.style.display = 'block';
        lokasiInfo.innerHTML = '<i class="bi bi-geo-alt-fill text-success"></i> Lokasi berhasil didapat, siap kirim laporan.';

        if (cpLat !== null && cpLon !== null) {
            const jarak = Math.round(jarakMeter(cpLat, cpLon, lat, lon));
            if (jarak > AMBANG_JARAK_METER) {
                jarakWarning.classList.remove('d-none');
                jarakWarning.innerHTML = `<i class="bi bi-exclamation-triangle"></i> Lokasi Anda terdeteksi sekitar ${jarak} meter dari titik checkpoint ini. Pastikan Anda sudah berada di lokasi yang benar sebelum mengirim laporan.`;
            }
        }
    }

    function tampilkanError(pesan) {
        gate.innerHTML = `
            <div class="text-danger">
                <i class="bi bi-geo-alt"></i> ${pesan}
            </div>
            <button type="button" id="retryLokasi" class="btn patroli-btn-brass btn-sm mt-2">
                <i class="bi bi-arrow-clockwise"></i> Coba Ambil Lokasi Lagi
            </button>
        `;
        document.getElementById('retryLokasi').addEventListener('click', mintaLokasi);
    }

    function mintaLokasi() {
        gate.innerHTML = `
            <div class="spinner-border spinner-border-sm text-success" role="status"></div>
            <span class="ms-2">Mengambil lokasi GPS, mohon tunggu...</span>
        `;
        formCard.style.display = 'none';
        document.getElementById('lokasiGate').style.display = 'block';

        if (!navigator.geolocation) {
            tampilkanError('Perangkat/browser ini tidak mendukung GPS. Gunakan HP dengan GPS aktif untuk melapor.');
            return;
        }

        navigator.geolocation.getCurrentPosition(
            pos => tampilkanForm(pos.coords.latitude, pos.coords.longitude),
            () => tampilkanError('Lokasi GPS wajib aktif untuk mengisi laporan patroli. Aktifkan GPS & izinkan akses lokasi, lalu coba lagi.'),
            { timeout: 10000, enableHighAccuracy: true }
        );
    }

    mintaLokasi();
</script>
@endsection
