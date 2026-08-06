@extends('layouts.app')

@section('content')

@include('partials.ledger-styles')

<div class="ledger-page">
    <div class="container-fluid py-1 py-md-2">

        @if (session('success'))
            <div class="alert ledger-alert-success">{{ session('success') }}</div>
        @endif
        @if (session('error'))
            <div class="alert ledger-alert-danger">{{ session('error') }}</div>
        @endif

        <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
                <h2 class="ledger-title mb-1">Detail Kontrak</h2>
                <p class="ledger-subtitle mb-0 ledger-nomor">{{ $kontrak->nomor_kontrak }}</p>
            </div>
            <a href="{{ route('kontrak.index') }}" class="btn ledger-btn-ghost">
                <i class="bi bi-arrow-left me-1"></i> Kembali
            </a>
        </div>

        <div class="row g-4">
            <div class="col-lg-8">
                <div class="card ledger-card">
                    <div class="card-header ledger-card-header">
                        <h3 class="ledger-table-title mb-0">Informasi Kontrak</h3>
                    </div>
                    <div class="card-body">
                        <dl class="row mb-0">
                            <dt class="col-sm-4 ledger-subtitle">Karyawan</dt>
                            <dd class="col-sm-8">{{ $kontrak->karyawan->nama }} ({{ $kontrak->karyawan->nik }})</dd>

                            <dt class="col-sm-4 ledger-subtitle">Jabatan</dt>
                            <dd class="col-sm-8">{{ $kontrak->karyawan->jabatan ?? '-' }}</dd>

                            <dt class="col-sm-4 ledger-subtitle">Jenis Kontrak</dt>
                            <dd class="col-sm-8">{{ $kontrak->jenisKontrak->nama_jenis }}</dd>

                            <dt class="col-sm-4 ledger-subtitle">Penandatangan</dt>
                            <dd class="col-sm-8">{{ $kontrak->penandatangan->jabatan }} ({{ $kontrak->penandatangan->kode }})</dd>

                            <dt class="col-sm-4 ledger-subtitle">Periode</dt>
                            <dd class="col-sm-8">
                                {{ $kontrak->tanggal_mulai->format('d F Y') }}
                                &ndash;
                                {{ $kontrak->tanggal_selesai ? $kontrak->tanggal_selesai->format('d F Y') : 'Tidak ditentukan (tetap)' }}
                            </dd>

                            <dt class="col-sm-4 ledger-subtitle">Gaji Pokok</dt>
                            <dd class="col-sm-8">{{ $kontrak->gaji_pokok ? 'Rp ' . number_format($kontrak->gaji_pokok, 0, ',', '.') : '-' }}</dd>

                            <dt class="col-sm-4 ledger-subtitle">Catatan</dt>
                            <dd class="col-sm-8">{{ $kontrak->catatan ?: '-' }}</dd>

                            <dt class="col-sm-4 ledger-subtitle">Dibuat oleh</dt>
                            <dd class="col-sm-8">{{ $kontrak->user->name ?? '-' }} &middot; {{ $kontrak->created_at->format('d/m/Y H:i') }}</dd>
                        </dl>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card ledger-status">
                    <div class="card-body d-flex flex-column gap-3">
                        <h3 class="ledger-status-title mb-0">Dokumen</h3>

                        @if ($kontrak->generated_file_path)
                            <a href="{{ route('kontrak.download', $kontrak) }}" class="btn ledger-btn-brass w-100">
                                <i class="bi bi-file-earmark-word me-1"></i> Unduh Dokumen Word
                            </a>
                        @else
                            <p class="ledger-help mb-0">Dokumen belum berhasil dibuat.</p>
                        @endif

                        <form method="POST" action="{{ route('kontrak.regenerate', $kontrak) }}">
                            @csrf
                            <button type="submit" class="btn ledger-btn-ghost w-100">
                                <i class="bi bi-arrow-repeat me-1"></i> Buat Ulang Dokumen
                            </button>
                        </form>

                        <a href="{{ route('kontrak.upload.form', $kontrak) }}" class="btn ledger-btn-ghost w-100">
                            <i class="bi bi-upload me-1"></i> Kelola File Bertanda Tangan
                        </a>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

@endsection