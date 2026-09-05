@extends('layouts.app')

@section('content')
@include('patroli._styles')

<div class="patroli-page">
    <div class="container-fluid py-1 py-md-2">

        <div class="d-flex align-items-center mb-3">
            <a href="{{ route('patroli.monitoring.index') }}" class="btn patroli-btn-icon me-2">
                <i class="bi bi-arrow-left"></i>
            </a>
            <div>
                <div class="patroli-eyebrow mb-1">Patroli Digital</div>
                <h5 class="patroli-title mb-0">Riwayat Semua Shift Patroli</h5>
            </div>
        </div>

        <div class="patroli-card mb-3">
            <div class="card-body">
                <form action="{{ route('patroli.monitoring.riwayat') }}" method="GET" class="row g-2 align-items-end">
                    <div class="col-auto">
                        <label class="form-label small mb-1">Tanggal</label>
                        <input type="date" name="tanggal" value="{{ request('tanggal') }}" class="form-control form-control-sm">
                    </div>
                    <div class="col-auto">
                        <label class="form-label small mb-1">Petugas</label>
                        <select name="user_id" class="form-select form-select-sm">
                            <option value="">Semua Petugas</option>
                            @foreach ($petugasList as $petugas)
                                <option value="{{ $petugas->id }}" {{ (string) request('user_id') === (string) $petugas->id ? 'selected' : '' }}>
                                    {{ $petugas->username }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-auto">
                        <button type="submit" class="btn patroli-btn-brass btn-sm">
                            <i class="bi bi-funnel"></i> Filter
                        </button>
                        @if (request('tanggal') || request('user_id'))
                            <a href="{{ route('patroli.monitoring.riwayat') }}" class="btn patroli-btn-ghost btn-sm">Reset</a>
                        @endif
                    </div>
                </form>
            </div>
        </div>

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
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($sesiList as $sesi)
                            <tr>
                                <td>{{ \Illuminate\Support\Carbon::parse($sesi->tanggal)->translatedFormat('d M Y') }}</td>
                                <td>{{ $sesi->user->username ?? '-' }}</td>
                                <td>{{ $sesi->mulai_at?->format('H:i') ?? '-' }}</td>
                                <td>{{ $sesi->selesai_at?->format('H:i') ?? '-' }}</td>
                                <td>
                                    @if ($sesi->status === 'berjalan')
                                        <span class="patroli-pill berjalan">Berjalan</span>
                                    @else
                                        <span class="patroli-pill selesai">Selesai</span>
                                    @endif
                                </td>
                                <td>{{ $sesi->scans->count() }} / {{ $sesi->total_checkpoint }}</td>
                                <td>
                                    @php $jmlTemuan = $sesi->scans->whereIn('status', ['temuan', 'bahaya'])->count(); @endphp
                                    @if ($jmlTemuan > 0)
                                        <span class="patroli-pill temuan">{{ $jmlTemuan }}</span>
                                    @else
                                        <span class="patroli-subtitle">-</span>
                                    @endif
                                </td>
                                <td class="text-end">
                                    <a href="{{ route('patroli.monitoring.show', $sesi->id) }}" class="btn patroli-btn-ghost btn-sm">
                                        Detail
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="p-0 border-0">
                                    <div class="patroli-empty m-3">
                                        <i class="bi bi-inbox d-block mb-2"></i>
                                        Tidak ada data untuk filter ini.
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if ($sesiList->hasPages())
                <div class="card-footer bg-white border-top">
                    {{ $sesiList->links() }}
                </div>
            @endif
        </div>

    </div>
</div>
@endsection
