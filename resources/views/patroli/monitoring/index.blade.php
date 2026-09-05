@extends('layouts.app')

@section('content')
@include('patroli._styles')

<div class="patroli-page">
    <div class="container-fluid py-1 py-md-2">

        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
            <div>
                <div class="patroli-eyebrow mb-1">Patroli Digital</div>
                <h2 class="patroli-title mb-1" style="font-size: 1.6rem;">Monitoring Patroli</h2>
                <p class="patroli-subtitle mb-0">
                    {{ now()->translatedFormat('l, d F Y') }}
                    &middot; diperbarui <span id="updatedAt">{{ $updatedAt }}</span>
                </p>
            </div>
            <a href="{{ route('patroli.monitoring.riwayat') }}" class="btn patroli-btn-ghost">
                <i class="bi bi-clock-history me-1"></i> Riwayat Semua Shift
            </a>
        </div>

        @if (session('success'))
            <div class="patroli-alert-success mb-3">{{ session('success') }}</div>
        @endif

        {{-- ===== Kartu ringkasan ===== --}}
        <div class="row g-3 mb-4">
            <div class="col-6 col-md-3">
                <div class="patroli-card h-100">
                    <div class="card-body patroli-stat">
                        <div class="patroli-stat-icon blue"><i class="bi bi-person-badge"></i></div>
                        <div>
                            <div class="patroli-stat-value" id="statPetugasAktif">{{ $petugasAktif }}</div>
                            <div class="patroli-stat-label">Petugas Aktif</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="patroli-card h-100">
                    <div class="card-body patroli-stat">
                        <div class="patroli-stat-icon slate"><i class="bi bi-geo-alt"></i></div>
                        <div>
                            <div class="patroli-stat-value">{{ $totalCheckpointAktif }}</div>
                            <div class="patroli-stat-label">Titik Checkpoint</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="patroli-card h-100">
                    <div class="card-body patroli-stat">
                        <div class="patroli-stat-icon amber"><i class="bi bi-exclamation-triangle"></i></div>
                        <div>
                            <div class="patroli-stat-value" id="statTotalTemuan">{{ $totalTemuanHariIni }}</div>
                            <div class="patroli-stat-label">Temuan Hari Ini</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="patroli-card h-100">
                    <div class="card-body patroli-stat">
                        <div class="patroli-stat-icon green"><i class="bi bi-list-check"></i></div>
                        <div>
                            <div class="patroli-stat-value">{{ $sesiHariIni->count() }}</div>
                            <div class="patroli-stat-label">Shift Hari Ini</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4">
            {{-- ===== Peta area pabrik + posisi petugas (SOP B.1) ===== --}}
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="patroli-title mb-0" style="font-size: 1.05rem;">Peta Area &amp; Posisi Petugas</h6>
                    <span class="patroli-subtitle small">
                        <i class="bi bi-info-circle"></i>
                        Posisi diambil dari GPS scan checkpoint terakhir tiap petugas (bukan tracking GPS kontinu)
                    </span>
                </div>
                <div class="patroli-card mb-4">
                    <div class="card-body p-0">
                        <div id="peta-monitoring" style="height: 420px; border-radius: 14px; overflow: hidden;"></div>
                    </div>
                </div>
            </div>

            {{-- ===== Daftar shift hari ini ===== --}}
            <div class="col-lg-7">
                <h6 class="patroli-title mb-3" style="font-size: 1.05rem;">Shift Hari Ini</h6>
                <div id="sesiList" class="vstack gap-2">
                    @forelse ($sesiHariIni as $s)
                        @php $pct = $s->total_checkpoint > 0 ? round(($s->scans->count() / $s->total_checkpoint) * 100) : 0; @endphp
                        <div class="patroli-card patroli-card-link">
                            <div class="card-body py-3">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <div class="fw-semibold">
                                            {{ $s->user->username ?? '-' }}
                                            @if ($s->terlambat)
                                                <span class="patroli-pill terlambat ms-1">Terlambat</span>
                                            @endif
                                        </div>
                                        <div class="patroli-subtitle">
                                            {{ optional($s->mulai_at)->format('H:i') }}
                                            &ndash;
                                            {{ optional($s->selesai_at)->format('H:i') ?? 'berjalan' }}
                                        </div>
                                    </div>
                                    <div class="text-end">
                                        @if ($s->status === 'berjalan')
                                            <span class="patroli-pill berjalan">Berjalan</span>
                                        @else
                                            <span class="patroli-pill selesai">Selesai</span>
                                        @endif
                                        <div class="patroli-subtitle mt-1">{{ $s->scans->count() }}/{{ $s->total_checkpoint }} titik</div>
                                    </div>
                                </div>
                                <div class="patroli-progress mt-3">
                                    <div class="bar" style="width: {{ $pct }}%"></div>
                                </div>
                                <a href="{{ route('patroli.monitoring.show', $s->id) }}" class="stretched-link"></a>
                            </div>
                        </div>
                    @empty
                        <div class="patroli-empty">
                            <i class="bi bi-calendar-x d-block mb-2"></i>
                            Belum ada shift patroli hari ini.
                        </div>
                    @endforelse
                </div>
            </div>

            {{-- ===== Temuan terbaru ===== --}}
            <div class="col-lg-5">
                <h6 class="patroli-title mb-3" style="font-size: 1.05rem;">Temuan Terbaru</h6>
                <div id="temuanList" class="vstack gap-2">
                    @forelse ($temuanTerbaru as $t)
                        <div class="patroli-card patroli-card-link" style="border-left: 4px solid {{ $t->status === 'bahaya' ? '#ef4444' : '#f59e0b' }};">
                            <div class="card-body py-3">
                                <div class="d-flex justify-content-between align-items-start gap-2">
                                    <span class="patroli-pill {{ $t->status === 'bahaya' ? 'bahaya' : 'temuan' }}">
                                        <i class="bi bi-exclamation-triangle"></i> {{ ucfirst($t->status) }}
                                    </span>
                                    @if ($t->ditangani_at)
                                        <span class="patroli-pill ditangani">Ditangani</span>
                                    @endif
                                </div>
                                <div class="fw-semibold mt-2">{{ $t->checkpoint->nama_titik ?? '-' }}</div>
                                <div class="patroli-subtitle">
                                    {{ $t->session->user->username ?? '-' }} &middot; {{ $t->scanned_at?->format('H:i') }}
                                </div>
                                @if ($t->catatan)
                                    <div class="small mt-1">{{ \Illuminate\Support\Str::limit($t->catatan, 90) }}</div>
                                @endif
                                <a href="{{ route('patroli.monitoring.show', $t->patrol_session_id) }}" class="stretched-link"></a>
                            </div>
                        </div>
                    @empty
                        <div class="patroli-empty">
                            <i class="bi bi-emoji-smile d-block mb-2"></i>
                            Belum ada temuan hari ini. 👍
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

    </div>
</div>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/leaflet@1.9.4/dist/leaflet.css" />
<script src="https://cdn.jsdelivr.net/npm/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
    // ===== Peta area pabrik + posisi petugas (real-time via polling) =====
    const checkpointPeta = @json($checkpointPeta);
    let posisiPetugas = @json($posisiPetugas);

    const petaEl = document.getElementById('peta-monitoring');
    let peta = null;
    let markerCheckpoint = [];
    let markerPetugas = {}; // keyed by session_id, supaya bisa "digeser" bukan dibuat ulang tiap polling

    function inisialisasiPeta() {
        if (!petaEl) return;

        // Pusatkan peta di rata-rata koordinat checkpoint (fallback: Djatiroto, Lumajang)
        let pusatLat = -8.169, pusatLng = 113.223;
        if (checkpointPeta.length > 0) {
            pusatLat = checkpointPeta.reduce((sum, c) => sum + parseFloat(c.latitude), 0) / checkpointPeta.length;
            pusatLng = checkpointPeta.reduce((sum, c) => sum + parseFloat(c.longitude), 0) / checkpointPeta.length;
        }

        peta = L.map('peta-monitoring').setView([pusatLat, pusatLng], 17);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 20,
            attribution: '&copy; OpenStreetMap contributors'
        }).addTo(peta);

        // Marker checkpoint (statis, warna abu-abu)
        const iconCheckpoint = L.divIcon({
            className: '',
            html: '<div style="background:#64748b;width:14px;height:14px;border-radius:50%;border:2px solid white;box-shadow:0 1px 4px rgba(0,0,0,.4);"></div>',
            iconSize: [14, 14],
            iconAnchor: [7, 7],
        });

        checkpointPeta.forEach(cp => {
            const m = L.marker([cp.latitude, cp.longitude], { icon: iconCheckpoint })
                .addTo(peta)
                .bindPopup(`<strong>${cp.nama_titik}</strong><br>${cp.kode}${cp.area ? ' &middot; ' + cp.area : ''}`);
            markerCheckpoint.push(m);
        });

        perbaruiMarkerPetugas();
    }

    function iconPetugas(terlambat) {
        const warna = terlambat ? '#ef4444' : '#22c55e';
        return L.divIcon({
            className: '',
            html: `<div style="background:${warna};width:20px;height:20px;border-radius:50%;border:3px solid white;box-shadow:0 2px 6px rgba(0,0,0,.5);display:flex;align-items:center;justify-content:center;">
                     <i class="bi bi-person-fill" style="color:white;font-size:11px;"></i>
                   </div>`,
            iconSize: [20, 20],
            iconAnchor: [10, 10],
        });
    }

    function perbaruiMarkerPetugas() {
        if (!peta) return;

        const idAktif = new Set(posisiPetugas.map(p => p.session_id));

        // Hapus marker petugas yang sesinya sudah tidak aktif lagi (selesai/hilang dari daftar)
        Object.keys(markerPetugas).forEach(id => {
            if (!idAktif.has(parseInt(id))) {
                peta.removeLayer(markerPetugas[id]);
                delete markerPetugas[id];
            }
        });

        posisiPetugas.forEach(p => {
            const popupHtml = `<strong>${p.petugas}</strong><br>Titik terakhir: ${p.titik} (${p.waktu})<br><a href="${p.detail_url}">Lihat detail &rarr;</a>`;

            if (markerPetugas[p.session_id]) {
                // Geser marker yang sudah ada (ikon "bergerak" saat posisi berubah)
                markerPetugas[p.session_id].setLatLng([p.latitude, p.longitude]);
                markerPetugas[p.session_id].setIcon(iconPetugas(p.terlambat));
                markerPetugas[p.session_id].setPopupContent(popupHtml);
            } else {
                markerPetugas[p.session_id] = L.marker([p.latitude, p.longitude], { icon: iconPetugas(p.terlambat) })
                    .addTo(peta)
                    .bindPopup(popupHtml);
            }
        });
    }

    if (petaEl) inisialisasiPeta();
</script>

<script>
    // Polling ringan setiap 15 detik supaya dashboard terasa real-time
    async function refreshMonitoring() {
        try {
            const res = await fetch(@json(route('patroli.monitoring.data')), {
                headers: { 'Accept': 'application/json' }
            });
            if (!res.ok) return;
            const data = await res.json();

            document.getElementById('updatedAt').textContent = data.updatedAt;
            document.getElementById('statPetugasAktif').textContent = data.petugasAktif;
            document.getElementById('statTotalTemuan').textContent = data.totalTemuanHariIni;

            // Update posisi petugas di peta tanpa reload halaman penuh
            posisiPetugas = data.posisiPetugas || [];
            perbaruiMarkerPetugas();
        } catch (e) {
            // diam saja kalau gagal, coba lagi di siklus berikutnya
        }
    }
    // Angka ringkasan & posisi peta di-update tiap 15 detik, daftar shift & temuan disegarkan penuh tiap 60 detik
    setInterval(refreshMonitoring, 15000);
    setInterval(() => window.location.reload(), 60000);
</script>
@endsection
