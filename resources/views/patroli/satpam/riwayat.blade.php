@extends('layouts.app')

@section('content')
@include('patroli._styles')

<div class="patroli-page">
    <div class="container-fluid py-1 py-md-2">

        <div class="d-flex align-items-center mb-3">
            <a href="{{ route('patroli.index') }}" class="btn patroli-btn-icon me-2">
                <i class="bi bi-arrow-left"></i>
            </a>
            <div>
                <div class="patroli-eyebrow mb-1">Patroli Digital</div>
                <h5 class="patroli-title mb-0">Riwayat Shift Saya</h5>
            </div>
        </div>

        <div class="patroli-card mb-3">
            <div class="card-body">
                <form action="{{ route('patroli.riwayat') }}" method="GET" class="row g-2 align-items-end">
                    <div class="col-auto">
                        <label class="form-label small mb-1">Tanggal</label>
                        <input type="date" name="tanggal" value="{{ request('tanggal') }}" class="form-control form-control-sm">
                    </div>
                    <div class="col-auto">
                        <button type="submit" class="btn patroli-btn-brass btn-sm">
                            <i class="bi bi-funnel"></i> Filter
                        </button>
                        @if (request('tanggal'))
                            <a href="{{ route('patroli.riwayat') }}" class="btn patroli-btn-ghost btn-sm">Reset</a>
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
                            <th>Mulai</th>
                            <th>Selesai</th>
                            <th>Status</th>
                            <th>Checkpoint</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($sesiList as $sesi)
                            <tr>
                                <td>{{ \Illuminate\Support\Carbon::parse($sesi->tanggal)->translatedFormat('d M Y') }}</td>
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
                                <td class="text-end">
                                    <a href="{{ route('patroli.riwayat.show', $sesi->id) }}" class="btn patroli-btn-ghost btn-sm">
                                        Detail
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="p-0 border-0">
                                    <div class="patroli-empty m-3">
                                        <i class="bi bi-clock-history d-block mb-2"></i>
                                        Belum ada riwayat patroli.
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
