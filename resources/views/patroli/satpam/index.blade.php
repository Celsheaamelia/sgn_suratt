@extends('layouts.app')

@section('content')
@include('patroli._styles')

<div class="patroli-page">
    <div class="container-fluid py-1 py-md-2">

        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
            <div>
                <div class="patroli-eyebrow mb-1">Patroli Digital</div>
                <h2 class="patroli-title mb-1" style="font-size: 1.6rem;">Halo, {{ auth()->user()->username }} </h2>
                <p class="patroli-subtitle mb-0">{{ now()->translatedFormat('l, d F Y') }}</p>
            </div>
            <a href="{{ route('patroli.riwayat') }}" class="btn patroli-btn-ghost">
                <i class="bi bi-clock-history me-1"></i> Riwayat Shift Saya
            </a>
        </div>

        @if (session('success'))
            <div class="patroli-alert-success mb-3">{{ session('success') }}</div>
        @endif
        @if (session('info'))
            <div class="patroli-alert-info mb-3">{{ session('info') }}</div>
        @endif
        @if (session('error'))
            <div class="patroli-alert-danger mb-3">{{ session('error') }}</div>
        @endif

        {{-- ===== Info jadwal hari ini (SOP: supervisor pastikan jadwal terinput) ===== --}}
        @if ($jadwalHariIni)
            <div class="patroli-alert-info mb-3">
                <i class="bi bi-calendar-check"></i>
                Anda terjadwal patroli hari ini
                @if ($jadwalHariIni->jam_mulai)
                    pukul {{ \Illuminate\Support\Str::of($jadwalHariIni->jam_mulai)->substr(0,5) }}
                    @if($jadwalHariIni->jam_selesai) &ndash; {{ \Illuminate\Support\Str::of($jadwalHariIni->jam_selesai)->substr(0,5) }} @endif
                @endif
                @if ($jadwalHariIni->catatan)
                    &middot; {{ $jadwalHariIni->catatan }}
                @endif
            </div>
        @else
            <div class="patroli-alert-danger mb-3">
                <i class="bi bi-calendar-x"></i>
                Anda belum terjadwal untuk patroli hari ini. Hubungi supervisor kalau ini bukan hari libur Anda.
            </div>
        @endif

        @php
            $totalCheckpoint = $checkpoints->count();
            $sudahScan = $scanByCheckpoint->count();
            $progres = $totalCheckpoint > 0 ? round(($sudahScan / $totalCheckpoint) * 100) : 0;
        @endphp

        {{-- ===== Hero: status shift ===== --}}
        <div class="patroli-hero mb-4">
            @if ($sesi)
                <div class="d-flex justify-content-between align-items-start flex-wrap gap-3 position-relative">
                    <div>
                        <span class="patroli-pill berjalan mb-2">
                            <i class="bi bi-shield-check"></i> Shift sedang berjalan
                        </span>
                        <div class="patroli-hero-muted">Mulai pukul {{ $sesi->mulai_at->format('H:i') }}</div>
                    </div>
                    <div class="d-flex gap-2">
                        <button type="button" class="btn patroli-btn-light" data-bs-toggle="modal" data-bs-target="#modalScanQr">
                            <i class="bi bi-qr-code-scan"></i> Scan QR Titik
                        </button>
                        <form action="{{ route('patroli.finish') }}" method="POST"
                              onsubmit="return confirm('Yakin ingin menyelesaikan shift patroli sekarang?');">
                            @csrf
                            <button type="submit" class="btn patroli-btn-light">
                                <i class="bi bi-flag"></i> Selesaikan Patroli
                            </button>
                        </form>
                    </div>
                </div>

                <div class="mt-4 position-relative">
                    <div class="d-flex justify-content-between mb-2" style="font-size: 0.85rem;">
                        <span class="patroli-hero-muted">Progres titik checkpoint</span>
                        <strong>{{ $sudahScan }} / {{ $totalCheckpoint }}</strong>
                    </div>
                    <div class="patroli-progress" style="background: rgba(255,255,255,0.18); height: 10px;">
                        <div class="bar" style="width: {{ $progres }}%; background: linear-gradient(90deg, #6ee7b7, #34d399);"></div>
                    </div>
                </div>
            @else
                <div class="text-center py-3 position-relative">
                    <i class="bi bi-shield-lock" style="font-size: 2.6rem; color: rgba(240,253,244,0.55);"></i>
                    <p class="mt-3 mb-3 patroli-hero-muted">Anda belum memulai shift patroli hari ini.</p>
                    <form action="{{ route('patroli.start') }}" method="POST">
                        @csrf
                        <button type="submit" class="btn patroli-btn-brass px-4">
                            <i class="bi bi-play-fill"></i> Mulai Patroli
                        </button>
                    </form>
                </div>
            @endif
        </div>

        {{-- ===== Daftar titik checkpoint (status saja — scan HARUS lewat QR fisik, bukan klik dari sini) ===== --}}
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="patroli-title mb-0" style="font-size: 1.15rem;">Titik Checkpoint</h5>
            @if ($totalCheckpoint > 0)
                <span class="patroli-subtitle">{{ $sudahScan }} dari {{ $totalCheckpoint }} titik tercatat</span>
            @endif
        </div>

        @if ($sesi)
            <div class="patroli-subtitle mb-3">
                <i class="bi bi-info-circle"></i>
                Tekan tombol <strong>"Scan QR Titik"</strong> di atas lalu arahkan kamera ke stiker QR yang tertempel di lokasi. Laporan hanya bisa diisi setelah QR fisik berhasil dipindai.
            </div>
        @endif

        @if ($checkpoints->isEmpty())
            <div class="patroli-empty">
                <i class="bi bi-geo-alt d-block mb-2"></i>
                Belum ada titik checkpoint aktif. Hubungi admin.
            </div>
        @else
            <div class="row g-3">
                @foreach ($checkpoints as $cp)
                    @php
                        $scan = $scanByCheckpoint->get($cp->id);
                        $statusInfo = match ($scan?->status) {
                            'aman'   => ['label' => 'Aman', 'class' => 'aman', 'icon' => 'bi-check-circle'],
                            'temuan' => ['label' => 'Temuan', 'class' => 'temuan', 'icon' => 'bi-exclamation-triangle'],
                            'bahaya' => ['label' => 'Bahaya', 'class' => 'bahaya', 'icon' => 'bi-exclamation-octagon'],
                            default  => ['label' => 'Belum discan', 'class' => 'kosong', 'icon' => 'bi-dash-circle'],
                        };
                        $tileClass = $scan ? 'st-' . $scan->status : '';
                        // Terkunci: sesi berjalan, belum discan, dan urutannya lebih besar dari titik yang wajib discan berikutnya.
                        $terkunci = $sesi && ! $scan && $urutanBerikutnya !== null && $cp->urutan > $urutanBerikutnya;
                    @endphp
                    <div class="col-md-4 col-sm-6">
                        <div class="patroli-cp-tile h-100 {{ $tileClass }}" style="{{ $terkunci ? 'opacity: .55;' : '' }}">
                            <div class="card-body d-flex flex-column h-100">
                                <div class="d-flex justify-content-between align-items-start mb-2 gap-2">
                                    <h6 class="mb-0 fw-semibold">{{ $cp->nama_titik }}</h6>
                                    @if ($terkunci)
                                        <span class="patroli-pill kosong"><i class="bi bi-lock"></i> Terkunci</span>
                                    @else
                                        <span class="patroli-pill {{ $statusInfo['class'] }}">
                                            <i class="bi {{ $statusInfo['icon'] }}"></i> {{ $statusInfo['label'] }}
                                        </span>
                                    @endif
                                </div>
                                @if ($cp->area)
                                    <div class="patroli-subtitle mb-1"><i class="bi bi-geo-alt"></i> {{ $cp->area }}</div>
                                @endif
                                <div class="patroli-subtitle mb-2" style="font-family: 'IBM Plex Mono', monospace; font-size: 0.78rem;">
                                    {{ $cp->kode }}
                                </div>

                                <div class="mt-auto">
                                    @if ($scan)
                                        <div class="patroli-subtitle small">
                                            <i class="bi bi-check2"></i> Dicatat {{ $scan->scanned_at->format('H:i') }}
                                        </div>
                                    @elseif (! $sesi)
                                        <div class="patroli-subtitle small">Mulai shift dulu untuk scan titik ini.</div>
                                    @elseif ($terkunci)
                                        <div class="patroli-subtitle small">Selesaikan titik sebelumnya dulu sesuai urutan rute.</div>
                                    @else
                                        <div class="patroli-subtitle small">Belum discan &mdash; pindai QR fisik di lokasi.</div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif

    </div>
</div>

{{-- ===== Modal Scan QR (kamera) ===== --}}
<div class="modal fade" id="modalScanQr" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius: 16px; border: none;">
            <div class="modal-header">
                <h5 class="modal-title patroli-title" style="font-size: 1.1rem;">Scan QR Titik Checkpoint</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="qr-reader" style="width: 100%;"></div>
                <div id="qrStatus" class="patroli-subtitle text-center mt-2">Arahkan kamera ke stiker QR di lokasi checkpoint.</div>

                <hr class="my-3">
                <details>
                    <summary class="patroli-subtitle small" style="cursor: pointer;">Kamera tidak bisa dipakai?</summary>
                    <div class="mt-2">
                        <label class="form-label small">Masukkan kode titik yang tertulis di kartu QR</label>
                        <div class="input-group">
                            <input type="text" id="kodeManual" class="form-control" placeholder="Contoh: CP-001">
                            <button type="button" id="btnKodeManual" class="btn patroli-btn-brass">Buka</button>
                        </div>
                        <div class="form-text">Kode tetap harus dibaca langsung dari stiker fisik di lokasi.</div>
                    </div>
                </details>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
<script>
    const scanBaseUrl = "{{ url('/patroli/scan') }}";
    let html5QrCode = null;
    const modalScanQr = document.getElementById('modalScanQr');

    function bukaKode(kode) {
        kode = (kode || '').trim().toUpperCase();
        if (!kode) return;
        window.location.href = `${scanBaseUrl}/${encodeURIComponent(kode)}`;
    }

    function ekstrakKode(teks) {
        // QR berisi URL lengkap (mis. https://.../patroli/scan/CP-001) atau bisa juga cuma kode-nya saja.
        const match = teks.match(/([A-Za-z]{1,10}-\d+)\s*$/);
        return match ? match[1] : teks;
    }

    if (modalScanQr) {
        modalScanQr.addEventListener('shown.bs.modal', function () {
            const statusEl = document.getElementById('qrStatus');
            html5QrCode = new Html5Qrcode('qr-reader');

            html5QrCode.start(
                { facingMode: 'environment' },
                { fps: 10, qrbox: { width: 230, height: 230 } },
                (decodedText) => {
                    statusEl.innerHTML = '<i class="bi bi-check-circle text-success"></i> QR terbaca, membuka laporan...';
                    html5QrCode.stop().catch(() => {});
                    bukaKode(ekstrakKode(decodedText));
                },
                () => { /* frame tanpa QR terdeteksi, abaikan & lanjut scan */ }
            ).catch(() => {
                statusEl.innerHTML = '<i class="bi bi-camera-video-off text-danger"></i> Tidak bisa mengakses kamera. Izinkan akses kamera di browser, atau gunakan input manual di bawah.';
            });
        });

        modalScanQr.addEventListener('hidden.bs.modal', function () {
            if (html5QrCode) {
                html5QrCode.stop().catch(() => {});
                html5QrCode.clear();
                html5QrCode = null;
            }
        });
    }

    document.getElementById('btnKodeManual')?.addEventListener('click', function () {
        bukaKode(document.getElementById('kodeManual').value);
    });
</script>
@endsection
