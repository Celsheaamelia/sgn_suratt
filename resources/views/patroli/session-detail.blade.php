@extends('layouts.app')

@section('content')
@include('patroli._styles')

@php
    $backRoute = $isAdmin ? route('patroli.monitoring.index') : route('patroli.riwayat');
    $scansSorted = $sesi->scans->sortBy('scanned_at');
    $pct = $sesi->total_checkpoint > 0 ? round(($sesi->scans->count() / $sesi->total_checkpoint) * 100) : 0;
@endphp

<div class="patroli-page">
    <div class="container-fluid py-1 py-md-2" style="max-width: 780px;">

        <div class="d-flex align-items-center mb-3">
            <a href="{{ $backRoute }}" class="btn patroli-btn-icon me-2">
                <i class="bi bi-arrow-left"></i>
            </a>
            <div>
                <div class="patroli-eyebrow mb-1">Detail Shift</div>
                <h5 class="patroli-title mb-0">Detail Shift Patroli</h5>
                <small class="patroli-subtitle">{{ \Illuminate\Support\Carbon::parse($sesi->tanggal)->translatedFormat('l, d F Y') }}</small>
            </div>
        </div>

        @if (session('success'))
            <div class="patroli-alert-success mb-3">{{ session('success') }}</div>
        @endif

        <div class="patroli-card mb-4">
            <div class="card-body">
                <div class="row g-3">
                    @if ($isAdmin)
                        <div class="col-6 col-md-3">
                            <div class="patroli-subtitle mb-1">Petugas</div>
                            <div class="fw-semibold">{{ $sesi->user->username ?? '-' }}</div>
                        </div>
                    @endif
                    <div class="col-6 col-md-3">
                        <div class="patroli-subtitle mb-1">Mulai</div>
                        <div class="fw-semibold">{{ $sesi->mulai_at?->format('H:i') ?? '-' }}</div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="patroli-subtitle mb-1">Selesai</div>
                        <div class="fw-semibold">{{ $sesi->selesai_at?->format('H:i') ?? 'Masih berjalan' }}</div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="patroli-subtitle mb-1">Status</div>
                        <div>
                            @if ($sesi->status === 'berjalan')
                                <span class="patroli-pill berjalan">Berjalan</span>
                            @else
                                <span class="patroli-pill selesai">Selesai</span>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="mt-4">
                    <div class="d-flex justify-content-between mb-2" style="font-size: 0.85rem;">
                        <span class="patroli-subtitle">Checkpoint tercatat</span>
                        <strong>{{ $sesi->scans->count() }} / {{ $sesi->total_checkpoint }}</strong>
                    </div>
                    <div class="patroli-progress">
                        <div class="bar" style="width: {{ $pct }}%"></div>
                    </div>
                </div>
            </div>
        </div>

        <h6 class="patroli-title mb-3" style="font-size: 1.05rem;">Timeline Checkpoint</h6>

        @if ($scansSorted->isEmpty())
            <div class="patroli-empty">
                <i class="bi bi-geo-alt d-block mb-2"></i>
                Belum ada titik yang dicatat pada shift ini.
            </div>
        @else
            <div class="patroli-timeline">
                @foreach ($scansSorted as $scan)
                    @php
                        $statusInfo = match ($scan->status) {
                            'aman'   => ['label' => 'Aman', 'class' => 'aman', 'icon' => 'bi-check-circle'],
                            'temuan' => ['label' => 'Temuan', 'class' => 'temuan', 'icon' => 'bi-exclamation-triangle'],
                            'bahaya' => ['label' => 'Bahaya', 'class' => 'bahaya', 'icon' => 'bi-exclamation-octagon'],
                        };
                    @endphp
                    <div class="patroli-timeline-item">
                        <div class="patroli-timeline-dot {{ $statusInfo['class'] }}">
                            <i class="bi {{ $statusInfo['icon'] }}"></i>
                        </div>
                        <div class="patroli-card">
                            <div class="card-body py-3">
                                <div class="d-flex justify-content-between align-items-start flex-wrap gap-2">
                                    <div>
                                        <span class="patroli-pill {{ $statusInfo['class'] }} mb-1">
                                            {{ $statusInfo['label'] }}
                                        </span>
                                        <div class="fw-semibold">{{ $scan->checkpoint->nama_titik ?? '-' }}</div>
                                        <div class="patroli-subtitle">{{ $scan->scanned_at?->format('H:i') }}</div>
                                    </div>
                                    @if ($scan->ditangani_at)
                                        <span class="patroli-pill ditangani"><i class="bi bi-check2-all"></i> Sudah ditangani</span>
                                    @elseif ($scan->eskalasi_level > 0)
                                        <span class="patroli-pill bahaya">
                                            <i class="bi bi-alarm"></i>
                                            Eskalasi level {{ $scan->eskalasi_level }}
                                            @if ($scan->eskalasi_terakhir_at)
                                                &middot; terakhir {{ $scan->eskalasi_terakhir_at->diffForHumans() }}
                                            @endif
                                        </span>
                                    @endif
                                </div>

                                @if ($scan->catatan)
                                    <p class="mt-2 mb-2">{{ $scan->catatan }}</p>
                                @endif

                                @if ($scan->foto)
                                    @php
                                        $extVideo = ['mp4', 'mov', 'webm'];
                                        $ext = strtolower(pathinfo($scan->foto, PATHINFO_EXTENSION));
                                    @endphp
                                    @if (in_array($ext, $extVideo))
                                        <video controls class="mt-1 rounded border" style="max-height: 220px; max-width: 100%;">
                                            <source src="{{ \Illuminate\Support\Facades\Storage::url($scan->foto) }}">
                                        </video>
                                    @else
                                        <a href="{{ \Illuminate\Support\Facades\Storage::url($scan->foto) }}" target="_blank">
                                            <img src="{{ \Illuminate\Support\Facades\Storage::url($scan->foto) }}" alt="Foto bukti"
                                                 class="img-thumbnail mt-1" style="max-height: 180px;">
                                        </a>
                                    @endif
                                @endif

                                @if ($scan->latitude && $scan->longitude)
                                    <div class="mt-2 d-flex align-items-center gap-2 flex-wrap">
                                        <a href="https://maps.google.com/?q={{ $scan->latitude }},{{ $scan->longitude }}" target="_blank" class="small">
                                            <i class="bi bi-geo-alt"></i> Lihat lokasi di peta
                                        </a>
                                        @if (! is_null($scan->jarak_meter))
                                            <span class="patroli-pill {{ $scan->jarak_meter > 200 ? 'temuan' : 'aman' }}" style="font-size:0.7rem;">
                                                &plusmn; {{ $scan->jarak_meter }} m dari titik
                                            </span>
                                        @endif
                                    </div>
                                @endif

                                {{-- Tindak lanjut: hanya untuk admin/supervisor pada temuan & bahaya --}}
                                @if ($isAdmin && in_array($scan->status, ['temuan', 'bahaya']))
                                    <div class="mt-3 pt-3" style="border-top: 1px solid var(--p-line);">
                                        @if ($scan->ditangani_at)
                                            <div class="patroli-subtitle mb-1">
                                                Ditindaklanjuti pada {{ $scan->ditangani_at->format('d M Y H:i') }}
                                            </div>
                                            <div class="p-2 rounded" style="background: var(--p-soft);">{{ $scan->tindak_lanjut }}</div>
                                        @else
                                            <form action="{{ route('patroli.monitoring.tindak-lanjut', $scan->id) }}" method="POST">
                                                @csrf
                                                <label class="form-label small fw-semibold">Catat Tindak Lanjut</label>
                                                <div class="input-group">
                                                    <textarea name="tindak_lanjut" rows="1" class="form-control" required
                                                              placeholder="Contoh: Sudah dicek, area diamankan..."></textarea>
                                                    <button type="submit" class="btn patroli-btn-brass">
                                                        <i class="bi bi-send"></i> Simpan
                                                    </button>
                                                </div>
                                            </form>
                                        @endif
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif

    </div>
</div>
@endsection
