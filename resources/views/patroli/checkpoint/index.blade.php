@extends('layouts.app')

@section('content')
@include('patroli._styles')

@php
    $isAdmin = (auth()->user()->role ?? null) === 'supervisor';
@endphp

<div class="patroli-page">
    <div class="container-fluid py-1 py-md-2">

        <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
            <div>
                <div class="patroli-eyebrow mb-1">Patroli Digital</div>
                <h2 class="patroli-title mb-1" style="font-size: 1.6rem;">Titik Checkpoint</h2>
                <p class="patroli-subtitle mb-0">Kelola titik-titik yang wajib dicek saat patroli</p>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('patroli.checkpoint.print') }}" target="_blank" class="btn patroli-btn-ghost">
                    <i class="bi bi-printer"></i> Cetak Semua QR
                </a>
                @if ($isAdmin)
                    <button type="button" class="btn patroli-btn-brass" data-bs-toggle="modal" data-bs-target="#modalTambah">
                        <i class="bi bi-plus-lg"></i> Tambah Titik
                    </button>
                @endif
            </div>
        </div>

        @if (session('success'))
            <div class="patroli-alert-success mb-3">{{ session('success') }}</div>
        @endif
        @if (session('error'))
            <div class="patroli-alert-danger mb-3">{{ session('error') }}</div>
        @endif

        <div class="patroli-card mb-3">
            <div class="card-body">
                <form action="{{ route('patroli.checkpoint.index') }}" method="GET" class="row g-2 align-items-end">
                    <div class="col-md-4">
                        <label class="form-label small mb-1">Cari</label>
                        <input type="text" name="search" value="{{ request('search') }}" class="form-control form-control-sm"
                               placeholder="Nama titik, kode, atau area...">
                    </div>
                    <div class="col-auto">
                        <button type="submit" class="btn patroli-btn-brass btn-sm">
                            <i class="bi bi-search"></i> Cari
                        </button>
                        @if (request('search'))
                            <a href="{{ route('patroli.checkpoint.index') }}" class="btn patroli-btn-ghost btn-sm">Reset</a>
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
                            <th style="width: 40px;">#</th>
                            <th>Kode</th>
                            <th>Nama Titik</th>
                            <th>Area</th>
                            <th class="text-center">Urutan</th>
                            <th class="text-center">Total Scan</th>
                            <th class="text-center">Status</th>
                            <th class="text-end">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($checkpoints as $cp)
                            <tr>
                                <td>{{ $loop->iteration + ($checkpoints->currentPage() - 1) * $checkpoints->perPage() }}</td>
                                <td><code>{{ $cp->kode }}</code></td>
                                <td>
                                    <div class="fw-semibold">{{ $cp->nama_titik }}</div>
                                    @if ($cp->deskripsi)
                                        <div class="patroli-subtitle">{{ \Illuminate\Support\Str::limit($cp->deskripsi, 60) }}</div>
                                    @endif
                                </td>
                                <td>{{ $cp->area ?? '-' }}</td>
                                <td class="text-center">{{ $cp->urutan }}</td>
                                <td class="text-center">{{ $cp->scans_count }}</td>
                                <td class="text-center">
                                    @if ($cp->aktif)
                                        <span class="patroli-pill aman">Aktif</span>
                                    @else
                                        <span class="patroli-pill kosong">Nonaktif</span>
                                    @endif
                                </td>
                                <td class="text-end">
                                    <div class="d-inline-flex gap-1">
                                        <form action="{{ route('patroli.checkpoint.toggle', $cp) }}" method="POST"
                                              onsubmit="return confirm('{{ $cp->aktif ? 'Nonaktifkan' : 'Aktifkan' }} titik {{ $cp->nama_titik }}?');">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="btn patroli-btn-icon"
                                                    title="{{ $cp->aktif ? 'Nonaktifkan' : 'Aktifkan' }}">
                                                <i class="bi {{ $cp->aktif ? 'bi-toggle-on text-success' : 'bi-toggle-off text-muted' }}"></i>
                                            </button>
                                        </form>

                                        @if ($isAdmin)
                                            <button type="button" class="btn patroli-btn-icon"
                                                    data-bs-toggle="modal" data-bs-target="#modalEdit{{ $cp->id }}" title="Edit">
                                                <i class="bi bi-pencil"></i>
                                            </button>

                                            <form action="{{ route('patroli.checkpoint.destroy', $cp) }}" method="POST"
                                                  onsubmit="return confirm('Hapus titik {{ $cp->nama_titik }}? Tindakan ini tidak bisa dibatalkan.');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn patroli-btn-icon text-danger" title="Hapus">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>

                            {{-- ===== Modal edit per baris (admin saja) ===== --}}
                            @if ($isAdmin)
                                <div class="modal fade" id="modalEdit{{ $cp->id }}" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog">
                                        <div class="modal-content" style="border-radius: 16px; border: none;">
                                            <form action="{{ route('patroli.checkpoint.update', $cp) }}" method="POST" class="patroli-form-section">
                                                @csrf
                                                @method('PUT')
                                                <div class="modal-header">
                                                    <h5 class="modal-title patroli-title" style="font-size: 1.1rem;">Edit Titik: {{ $cp->nama_titik }}</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <div class="mb-3">
                                                        <label class="form-label">Nama Titik</label>
                                                        <input type="text" name="nama_titik" value="{{ $cp->nama_titik }}"
                                                               class="form-control" required maxlength="150">
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label">Area</label>
                                                        <input type="text" name="area" value="{{ $cp->area }}"
                                                               class="form-control" maxlength="150">
                                                    </div>
                                                    <div class="row g-2 mb-3">
                                                        <div class="col-6">
                                                            <label class="form-label">Urutan</label>
                                                            <input type="number" name="urutan" value="{{ $cp->urutan }}"
                                                                   class="form-control" min="0">
                                                        </div>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label">Deskripsi</label>
                                                        <textarea name="deskripsi" rows="2" class="form-control" maxlength="500">{{ $cp->deskripsi }}</textarea>
                                                    </div>
                                                    <div class="row g-2">
                                                        <div class="col-6">
                                                            <label class="form-label">Latitude</label>
                                                            <input type="number" step="any" name="latitude" value="{{ $cp->latitude }}"
                                                                   class="form-control" placeholder="-7.123456">
                                                        </div>
                                                        <div class="col-6">
                                                            <label class="form-label">Longitude</label>
                                                            <input type="number" step="any" name="longitude" value="{{ $cp->longitude }}"
                                                                   class="form-control" placeholder="112.123456">
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn patroli-btn-ghost" data-bs-dismiss="modal">Batal</button>
                                                    <button type="submit" class="btn patroli-btn-brass">Simpan Perubahan</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        @empty
                            <tr>
                                <td colspan="8" class="p-0 border-0">
                                    <div class="patroli-empty m-3">
                                        <i class="bi bi-geo-alt d-block mb-2"></i>
                                        @if (request('search'))
                                            Tidak ada titik yang cocok dengan pencarian "{{ request('search') }}".
                                        @else
                                            Belum ada titik checkpoint. @if($isAdmin) Klik "Tambah Titik" untuk membuat yang pertama. @endif
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if ($checkpoints->hasPages())
                <div class="card-footer bg-white border-top">
                    {{ $checkpoints->links() }}
                </div>
            @endif
        </div>

        {{-- ===== Modal tambah titik (admin saja) ===== --}}
        @if ($isAdmin)
            <div class="modal fade" id="modalTambah" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content" style="border-radius: 16px; border: none;">
                        <form action="{{ route('patroli.checkpoint.store') }}" method="POST" class="patroli-form-section">
                            @csrf
                            <div class="modal-header">
                                <h5 class="modal-title patroli-title" style="font-size: 1.1rem;">Tambah Titik Checkpoint</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                                <div class="mb-3">
                                    <label class="form-label">Nama Titik</label>
                                    <input type="text" name="nama_titik" value="{{ old('nama_titik') }}"
                                           class="form-control @error('nama_titik') is-invalid @enderror"
                                           placeholder="Contoh: Gudang Gula - Pintu Utara" required maxlength="150">
                                    @error('nama_titik')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Area</label>
                                    <input type="text" name="area" value="{{ old('area') }}"
                                           class="form-control @error('area') is-invalid @enderror"
                                           placeholder="Contoh: Area Produksi" maxlength="150">
                                    @error('area')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Urutan</label>
                                    <input type="number" name="urutan" value="{{ old('urutan', 0) }}"
                                           class="form-control @error('urutan') is-invalid @enderror" min="0">
                                    @error('urutan')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Deskripsi</label>
                                    <textarea name="deskripsi" rows="2" class="form-control @error('deskripsi') is-invalid @enderror"
                                              maxlength="500">{{ old('deskripsi') }}</textarea>
                                    @error('deskripsi')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="row g-2">
                                    <div class="col-6">
                                        <label class="form-label">Latitude</label>
                                        <input type="number" step="any" name="latitude" value="{{ old('latitude') }}"
                                               class="form-control @error('latitude') is-invalid @enderror" placeholder="-7.123456">
                                        @error('latitude')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-6">
                                        <label class="form-label">Longitude</label>
                                        <input type="number" step="any" name="longitude" value="{{ old('longitude') }}"
                                               class="form-control @error('longitude') is-invalid @enderror" placeholder="112.123456">
                                        @error('longitude')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="form-text mt-2">Kode QR (mis. CP-001) akan dibuat otomatis oleh sistem.</div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn patroli-btn-ghost" data-bs-dismiss="modal">Batal</button>
                                <button type="submit" class="btn patroli-btn-brass">Simpan Titik</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            @if ($errors->any() && old('nama_titik') !== null)
                <script>
                    document.addEventListener('DOMContentLoaded', function () {
                        new bootstrap.Modal(document.getElementById('modalTambah')).show();
                    });
                </script>
            @endif
        @endif

    </div>
</div>
@endsection
