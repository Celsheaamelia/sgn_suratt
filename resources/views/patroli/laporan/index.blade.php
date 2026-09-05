@extends('layouts.app')

@section('content')
@include('patroli._styles')

<div class="patroli-page">
    <div class="container-fluid py-1 py-md-2">

        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3 d-print-none">
            <div>
                <div class="patroli-eyebrow mb-1">Patroli Digital</div>
                <h2 class="patroli-title mb-1" style="font-size: 1.6rem;">Laporan Patroli</h2>
                <p class="patroli-subtitle mb-0">
                    {{ $mulai->translatedFormat('d M Y') }} &ndash; {{ $selesai->translatedFormat('d M Y') }}
                </p>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('patroli.monitoring.laporan.export', ['jenis' => $jenis, 'tanggal' => $anchor->toDateString()]) }}"
                   class="btn patroli-btn-ghost">
                    <i class="bi bi-file-earmark-spreadsheet"></i> Export CSV
                </a>
                <button onclick="window.print()" class="btn patroli-btn-ghost">
                    <i class="bi bi-printer"></i> Cetak Laporan
                </button>
            </div>
        </div>

        {{-- ===== Filter periode ===== --}}
        <div class="patroli-card mb-4 d-print-none">
            <div class="card-body">
                <form action="{{ route('patroli.monitoring.laporan') }}" method="GET" class="row g-2 align-items-end">
                    <div class="col-auto">
                        <label class="form-label small mb-1">Jenis Laporan</label>
                        <select name="jenis" class="form-select form-select-sm" onchange="this.form.submit()">
                            <option value="harian" {{ $jenis === 'harian' ? 'selected' : '' }}>Harian</option>
                            <option value="mingguan" {{ $jenis === 'mingguan' ? 'selected' : '' }}>Mingguan</option>
                            <option value="bulanan" {{ $jenis === 'bulanan' ? 'selected' : '' }}>Bulanan</option>
                        </select>
                    </div>
                    <div class="col-auto">
                        <label class="form-label small mb-1">Anchor Tanggal</label>
                        <input type="date" name="tanggal" value="{{ $anchor->toDateString() }}"
                               class="form-control form-control-sm">
                    </div>
                    <div class="col-auto">
                        <button type="submit" class="btn patroli-btn-brass btn-sm">
                            <i class="bi bi-funnel"></i> Terapkan
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- ===== Kartu ringkasan ===== --}}
        <div class="row g-3 mb-4">
            <div class="col-6 col-md-3">
                <div class="patroli-card h-100">
                    <div class="card-body patroli-stat">
                        <div class="patroli-stat-icon green"><i class="bi bi-list-check"></i></div>
                        <div>
                            <div class="patroli-stat-value">{{ $totalShift }}</div>
                            <div class="patroli-stat-label">Total Shift</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="patroli-card h-100">
                    <div class="card-body patroli-stat">
                        <div class="patroli-stat-icon blue"><i class="bi bi-graph-up"></i></div>
                        <div>
                            <div class="patroli-stat-value">{{ $rataKepatuhan !== null ? $rataKepatuhan . '%' : '-' }}</div>
                            <div class="patroli-stat-label">Rata-rata Kepatuhan</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="patroli-card h-100">
                    <div class="card-body patroli-stat">
                        <div class="patroli-stat-icon amber"><i class="bi bi-exclamation-triangle"></i></div>
                        <div>
                            <div class="patroli-stat-value">{{ $totalTemuan }}</div>
                            <div class="patroli-stat-label">Temuan</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="patroli-card h-100">
                    <div class="card-body patroli-stat">
                        <div class="patroli-stat-icon slate"><i class="bi bi-exclamation-octagon"></i></div>
                        <div>
                            <div class="patroli-stat-value">{{ $totalBahaya }}</div>
                            <div class="patroli-stat-label">Potensi Bahaya</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ===== Grafik ===== --}}
        <div class="row g-4 mb-4">
            <div class="col-lg-7">
                <div class="patroli-card h-100">
                    <div class="card-body">
                        <h6 class="patroli-title mb-3" style="font-size: 1.05rem;">Grafik Kepatuhan Patroli</h6>
                        @if (count(array_filter($dataKepatuhan, fn($v) => $v !== null)) === 0)
                            <div class="patroli-empty">
                                <i class="bi bi-calendar-x d-block mb-2"></i>
                                Belum ada jadwal patroli pada periode ini, kepatuhan tidak bisa dihitung.
                            </div>
                        @else
                            <canvas id="chartKepatuhan" height="110"></canvas>
                        @endif
                    </div>
                </div>
            </div>
            <div class="col-lg-5">
                <div class="patroli-card h-100">
                    <div class="card-body">
                        <h6 class="patroli-title mb-3" style="font-size: 1.05rem;">Statistik Kejadian</h6>
                        @if (($totalAman + $totalTemuan + $totalBahaya) === 0)
                            <div class="patroli-empty">
                                <i class="bi bi-inbox d-block mb-2"></i>
                                Belum ada data scan pada periode ini.
                            </div>
                        @else
                            <canvas id="chartKejadian" height="180"></canvas>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        {{-- ===== Rekap shift ===== --}}
        <h6 class="patroli-title mb-3" style="font-size: 1.05rem;">Rekap Shift</h6>
        <div class="patroli-card">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 patroli-table">
                    <thead>
                        <tr>
                            <th>Tanggal</th>
                            <th>Petugas</th>
                            <th>Mulai</th>
                            <th>Selesai</th>
                            <th>Status</th>
                            <th>Checkpoint</th>
                            <th>Temuan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($rekapShift as $r)
                            <tr>
                                <td>{{ $r['tanggal'] }}</td>
                                <td>{{ $r['petugas'] }}</td>
                                <td>{{ $r['mulai'] ?: '-' }}</td>
                                <td>{{ $r['selesai'] }}</td>
                                <td>
                                    @if ($r['status'] === 'berjalan')
                                        <span class="patroli-pill berjalan">Berjalan</span>
                                    @else
                                        <span class="patroli-pill selesai">Selesai</span>
                                    @endif
                                    @if ($r['terlambat'])
                                        <span class="patroli-pill terlambat ms-1">Terlambat</span>
                                    @endif
                                </td>
                                <td>{{ $r['checkpoint'] }}</td>
                                <td>
                                    @if ($r['temuan'] > 0)
                                        <span class="patroli-pill temuan">{{ $r['temuan'] }}</span>
                                    @else
                                        <span class="patroli-subtitle">-</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="p-0 border-0">
                                    <div class="patroli-empty m-3">
                                        <i class="bi bi-inbox d-block mb-2"></i>
                                        Tidak ada shift pada periode ini.
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
    const labelHarian   = @json($labelHarian);
    const dataKepatuhan = @json($dataKepatuhan);

    const elKepatuhan = document.getElementById('chartKepatuhan');
    if (elKepatuhan) {
        new Chart(elKepatuhan, {
            type: 'bar',
            data: {
                labels: labelHarian,
                datasets: [{
                    label: 'Kepatuhan (%)',
                    data: dataKepatuhan,
                    backgroundColor: '#34d399',
                    borderRadius: 6,
                    maxBarThickness: 36,
                }],
            },
            options: {
                responsive: true,
                plugins: { legend: { display: false } },
                scales: {
                    y: { beginAtZero: true, max: 100, ticks: { callback: v => v + '%' } },
                },
            },
        });
    }

    const elKejadian = document.getElementById('chartKejadian');
    if (elKejadian) {
        new Chart(elKejadian, {
            type: 'doughnut',
            data: {
                labels: ['Aman', 'Temuan', 'Bahaya'],
                datasets: [{
                    data: [{{ $totalAman }}, {{ $totalTemuan }}, {{ $totalBahaya }}],
                    backgroundColor: ['#10b981', '#f59e0b', '#ef4444'],
                    borderWidth: 0,
                }],
            },
            options: {
                responsive: true,
                plugins: { legend: { position: 'bottom' } },
            },
        });
    }
</script>

<style>
    @media print {
        .sidebar, .navbar-custom, .sidebar-toggle-btn { display: none !important; }
        .content { margin-left: 0 !important; padding-top: 1rem !important; }
    }
</style>
@endsection
