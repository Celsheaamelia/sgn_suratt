@extends('layouts.app')

@section('content')
@include('patroli._styles')

<div class="patroli-page">
    <div class="container-fluid py-1 py-md-2">

        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
            <div>
                <div class="patroli-eyebrow mb-1">Patroli Digital</div>
                <h2 class="patroli-title mb-1" style="font-size: 1.6rem;">Jadwal Patroli</h2>
                <p class="patroli-subtitle mb-0">Tugaskan satpam untuk patroli di tiap tanggal.</p>
            </div>
            <div class="d-flex gap-2 align-items-center">
                <a href="{{ route('patroli.jadwal.index', ['minggu' => $mingguIni->copy()->subWeek()->toDateString()]) }}"
                   class="btn patroli-btn-ghost btn-sm"><i class="bi bi-chevron-left"></i></a>
                <span class="patroli-subtitle" style="min-width: 190px; text-align:center;">
                    {{ $mingguIni->translatedFormat('d M') }} &ndash; {{ $mingguIni->copy()->addDays(6)->translatedFormat('d M Y') }}
                </span>
                <a href="{{ route('patroli.jadwal.index', ['minggu' => $mingguIni->copy()->addWeek()->toDateString()]) }}"
                   class="btn patroli-btn-ghost btn-sm"><i class="bi bi-chevron-right"></i></a>
                <button type="button" class="btn patroli-btn-brass btn-sm ms-2" data-bs-toggle="modal" data-bs-target="#modalTambahJadwal">
                    <i class="bi bi-plus-lg"></i> Tugaskan
                </button>
            </div>
        </div>

        @if (session('success'))
            <div class="patroli-alert-success mb-3">{{ session('success') }}</div>
        @endif

        @if ($petugasList->isEmpty())
            <div class="patroli-empty">
                <i class="bi bi-people d-block mb-2"></i>
                Belum ada akun dengan role satpam. Tambahkan dulu akun satpam sebelum membuat jadwal.
            </div>
        @else
            <div class="row g-3">
                @foreach ($hariRange as $hari)
                    @php
                        $daftarHari = $jadwal->get($hari->toDateString(), collect());
                        $isHariIni = $hari->isToday();
                    @endphp
                    <div class="col-md-6 col-lg-4 col-xl-3">
                        <div class="patroli-card h-100 {{ $isHariIni ? 'border-2' : '' }}" style="{{ $isHariIni ? 'border-color:#10b981;' : '' }}">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <div class="fw-semibold">{{ $hari->translatedFormat('l') }}</div>
                                    @if ($isHariIni)
                                        <span class="patroli-pill berjalan">Hari ini</span>
                                    @endif
                                </div>
                                <div class="patroli-subtitle mb-3">{{ $hari->translatedFormat('d F Y') }}</div>

                                @forelse ($daftarHari as $j)
                                    <div class="d-flex justify-content-between align-items-start gap-2 mb-2 p-2 rounded" style="background: var(--p-soft);">
                                        <div>
                                            <div class="fw-semibold small">{{ $j->user->username ?? '-' }}</div>
                                            @if ($j->jam_mulai)
                                                <div class="patroli-subtitle" style="font-size: 0.76rem;">
                                                    {{ \Illuminate\Support\Str::of($j->jam_mulai)->substr(0,5) }}
                                                    @if($j->jam_selesai) &ndash; {{ \Illuminate\Support\Str::of($j->jam_selesai)->substr(0,5) }} @endif
                                                </div>
                                            @endif
                                        </div>
                                        <form action="{{ route('patroli.jadwal.destroy', $j) }}" method="POST"
                                              onsubmit="return confirm('Hapus jadwal {{ $j->user->username ?? '' }} pada {{ $hari->translatedFormat('d M') }}?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm p-0 text-danger border-0" style="background:none;">
                                                <i class="bi bi-x-lg"></i>
                                            </button>
                                        </form>
                                    </div>
                                @empty
                                    <div class="patroli-subtitle small fst-italic">Belum ada petugas ditugaskan.</div>
                                @endforelse
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif

        {{-- ===== Modal tambah jadwal ===== --}}
        <div class="modal fade" id="modalTambahJadwal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content" style="border-radius: 16px; border: none;">
                    <form action="{{ route('patroli.jadwal.store') }}" method="POST" class="patroli-form-section">
                        @csrf
                        <div class="modal-header">
                            <h5 class="modal-title patroli-title" style="font-size: 1.1rem;">Tugaskan Petugas Patroli</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <div class="mb-3">
                                <label class="form-label">Petugas (Satpam)</label>
                                <select name="user_id" class="form-select" required>
                                    <option value="">-- Pilih petugas --</option>
                                    @foreach ($petugasList as $p)
                                        <option value="{{ $p->id }}">{{ $p->username }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Tanggal</label>
                                <input type="date" name="tanggal" class="form-control" required
                                       value="{{ old('tanggal', now()->toDateString()) }}">
                            </div>
                            <div class="row g-2 mb-3">
                                <div class="col-6">
                                    <label class="form-label">Jam Mulai</label>
                                    <input type="time" name="jam_mulai" class="form-control">
                                </div>
                                <div class="col-6">
                                    <label class="form-label">Jam Selesai</label>
                                    <input type="time" name="jam_selesai" class="form-control">
                                </div>
                            </div>
                            <div class="mb-1">
                                <label class="form-label">Catatan</label>
                                <input type="text" name="catatan" class="form-control" maxlength="255"
                                       placeholder="Contoh: fokus area gudang malam ini">
                            </div>
                            <div class="form-text">Kalau petugas & tanggal sudah pernah dijadwalkan, data lama akan diganti dengan yang baru.</div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn patroli-btn-ghost" data-bs-dismiss="modal">Batal</button>
                            <button type="submit" class="btn patroli-btn-brass">Simpan Jadwal</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

    </div>
</div>
@endsection
