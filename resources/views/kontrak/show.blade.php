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
                            <dt class="col-sm-4 ledger-subtitle">Nama</dt>
                            <dd class="col-sm-8">{{ $kontrak->karyawan->nama }}</dd>

                            <dt class="col-sm-4 ledger-subtitle">NIK Karyawan</dt>
                            <dd class="col-sm-8">{{ $kontrak->karyawan->nik }}</dd>

                            <dt class="col-sm-4 ledger-subtitle">No. KTP</dt>
                            <dd class="col-sm-8">{{ $kontrak->karyawan->no_ktp ?: '-' }}</dd>

                            <dt class="col-sm-4 ledger-subtitle">Tempat/Tanggal Lahir</dt>
                            <dd class="col-sm-8">{{ $kontrak->karyawan->tempat_tanggal_lahir ?: '-' }}</dd>

                            <dt class="col-sm-4 ledger-subtitle">Jenis Kelamin</dt>
                            <dd class="col-sm-8">{{ $kontrak->karyawan->jenis_kelamin ?: '-' }}</dd>

                            <dt class="col-sm-4 ledger-subtitle">Agama</dt>
                            <dd class="col-sm-8">{{ $kontrak->karyawan->agama ?: '-' }}</dd>

                            <dt class="col-sm-4 ledger-subtitle">Status Perkawinan</dt>
                            <dd class="col-sm-8">{{ $kontrak->karyawan->status_perkawinan ?: '-' }}</dd>

                            <dt class="col-sm-4 ledger-subtitle">Alamat</dt>
                            <dd class="col-sm-8">{{ $kontrak->karyawan->alamat ?: '-' }}</dd>

                            <dt class="col-sm-4 ledger-subtitle">Jabatan</dt>
                            <dd class="col-sm-8">{{ $kontrak->jabatan_kontrak ?: '-' }}</dd>

                            <dt class="col-sm-4 ledger-subtitle">Bagian / Departemen</dt>
                            <dd class="col-sm-8">{{ $kontrak->bagian_kontrak ?: '-' }}</dd>

                            <dt class="col-sm-4 ledger-subtitle">Rincian Pekerjaan</dt>
                            <dd class="col-sm-8">
                                @if ($kontrak->rincian_pekerjaan_1 || $kontrak->rincian_pekerjaan_2 || $kontrak->rincian_pekerjaan_3)
                                    <ol class="mb-0 ps-3">
                                        @if ($kontrak->rincian_pekerjaan_1)
                                            <li>{{ $kontrak->rincian_pekerjaan_1 }}</li>
                                        @endif
                                        @if ($kontrak->rincian_pekerjaan_2)
                                            <li>{{ $kontrak->rincian_pekerjaan_2 }}</li>
                                        @endif
                                        @if ($kontrak->rincian_pekerjaan_3)
                                            <li>{{ $kontrak->rincian_pekerjaan_3 }}</li>
                                        @endif
                                    </ol>
                                @else
                                    -
                                @endif
                            </dd>

                            <dt class="col-sm-4 ledger-subtitle">Jenis Kontrak</dt>
                            <dd class="col-sm-8">{{ $kontrak->jenisKontrak->nama_jenis }}</dd>

                            <dt class="col-sm-4 ledger-subtitle">Penandatangan</dt>
                            <dd class="col-sm-8">
                                @if ($kontrak->penandatangan)
                                    {{ $kontrak->penandatangan->jabatan }} ({{ $kontrak->penandatangan->kode }})
                                @else
                                    <span class="ledger-subtitle">Belum ditentukan</span>
                                @endif
                            </dd>

                            <dt class="col-sm-4 ledger-subtitle">Periode</dt>
                            <dd class="col-sm-8">
                                {{ $kontrak->tanggal_mulai->format('d F Y') }}
                                &ndash;
                                @if ($kontrak->tanggal_selesai)
                                    {{ $kontrak->tanggal_selesai->format('d F Y') }}
                                @elseif ($kontrak->jenisKontrak->masa_giling)
                                    Sampai dengan berakhirnya Masa Giling
                                @else
                                    Tidak ditentukan (tetap)
                                @endif
                            </dd>

                            <dt class="col-sm-4 ledger-subtitle">Tanggal Dibuat</dt>
                            <dd class="col-sm-8">{{ $kontrak->created_at->format('d/m/Y H:i') }}</dd>
                        </dl>
                    </div>
                </div>

                @if ($riwayatLain->isNotEmpty())
                    <div class="card ledger-card mt-4">
                        <div class="card-header ledger-card-header">
                            <h3 class="ledger-table-title mb-0">
                                <i class="bi bi-clock-history me-1"></i>
                                Riwayat Kontrak Lain ({{ $kontrak->karyawan->nama }})
                            </h3>
                        </div>
                        <div class="card-body p-0">
                            <table class="table ledger-table align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th>Nomor Kontrak</th>
                                        <th>Jenis</th>
                                        <th>Bagian</th>
                                        <th>Periode</th>
                                        <th class="text-end"></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($riwayatLain as $lama)
                                        <tr>
                                            <td class="ledger-nomor">{{ $lama->nomor_kontrak }}</td>
                                            <td>{{ $lama->jenisKontrak->nama_jenis ?? '-' }}</td>
                                            <td>{{ $lama->bagian_kontrak ?: '-' }}</td>
                                            <td class="ledger-tanggal">
                                                {{ optional($lama->tanggal_mulai)->format('d/m/Y') }}
                                                &ndash;
                                                @if ($lama->tanggal_selesai)
                                                    {{ $lama->tanggal_selesai->format('d/m/Y') }}
                                                @elseif (optional($lama->jenisKontrak)->masa_giling)
                                                    Berakhirnya Masa Giling
                                                @else
                                                    Tetap
                                                @endif
                                            </td>
                                            <td class="text-end">
                                                <div class="d-flex gap-2 justify-content-end">
                                                    @if ($lama->generated_file_path)
                                                        <a href="{{ route('kontrak.download', $lama) }}" class="btn ledger-btn-detail" title="Download dokumen Word">
                                                            <i class="bi bi-file-earmark-word"></i>
                                                        </a>
                                                    @endif
                                                    <a href="{{ route('kontrak.show', $lama) }}" class="btn ledger-btn-detail" title="Detail">
                                                        <i class="bi bi-eye"></i>
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endif
            </div>

            <div class="col-lg-4">
                <div class="card ledger-status">
                    <div class="card-body d-flex flex-column gap-3">
                        <div class="d-flex align-items-center justify-content-between">
                            <h3 class="ledger-status-title mb-0">Dokumen</h3>
                            @if ($kontrak->generated_file_path)
                                <span class="badge bg-success">Tersedia</span>
                            @else
                                <span class="badge bg-secondary">Belum Digenerate</span>
                            @endif
                        </div>

                        @if ($kontrak->generated_file_path)
                            <a href="{{ route('kontrak.preview', $kontrak) }}" class="btn ledger-btn-ghost w-100">
                                <i class="bi bi-eye me-1"></i> Preview Dokumen
                            </a>
                            <a href="{{ route('kontrak.download', $kontrak) }}" class="btn ledger-btn-brass w-100">
                                <i class="bi bi-file-earmark-word me-1"></i> Unduh Dokumen Word
                            </a>
                        @else
                            <button type="button" class="btn ledger-btn-brass w-100" disabled
                                    title="Dokumen belum digenerate">
                                <i class="bi bi-file-earmark-word me-1"></i> Download Dokumen Word
                            </button>
                            <p class="ledger-help mb-0">Dokumen belum berhasil digenerate.</p>
                        @endif

                        <hr class="my-1">

                        {{-- Ganti template: dipakai kalau format kontrak berubah (mis. jumlah
                             poin pasal, atau susunan lain), tanpa perlu ubah kode. Ganti
                             template otomatis generate ulang & reset status jadi belum-publish. --}}
                        @php
                            $templateOptions = \App\Models\Template::where('jenis_kontrak_id', $kontrak->jenis_kontrak_id)
                                ->orderByDesc('is_default')
                                ->orderBy('nama_template')
                                ->get();
                        @endphp
                        @if ($templateOptions->count() > 1)
                            <form method="POST" action="{{ route('kontrak.switch-template', $kontrak) }}" class="d-flex flex-column gap-2">
                                @csrf
                                <label class="form-label ledger-subtitle mb-0">Ganti Template</label>
                                <select name="template_id" class="form-select form-select-sm">
                                    @foreach ($templateOptions as $tpl)
                                        <option value="{{ $tpl->id }}" @selected($kontrak->template_id == $tpl->id)>
                                            {{ $tpl->nama_template }}{{ $tpl->is_default ? ' (Default)' : '' }}
                                        </option>
                                    @endforeach
                                </select>
                                <button type="submit" class="btn ledger-btn-ghost btn-sm w-100">
                                    <i class="bi bi-arrow-left-right me-1"></i> Terapkan Template
                                </button>
                            </form>
                        @endif

                        <form method="POST" action="{{ route('kontrak.regenerate', $kontrak) }}">
                            @csrf
                            <button type="submit" class="btn ledger-btn-ghost w-100 d-flex align-items-center justify-content-center gap-2" id="regenerateBtn">
                                <i class="bi bi-arrow-repeat me-1"></i>
                                <span>Buat Ulang Dokumen</span>
                                <span id="regenerateStatus" class="ledger-inline-status"></span>
                            </button>
                        </form>

                        {{-- <a href="{{ route('kontrak.upload.form', $kontrak) }}" class="btn ledger-btn-ghost w-100">
                            <i class="bi bi-upload me-1"></i> Kelola File Bertanda Tangan
                        </a> --}}
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

@push('scripts')
<script>
(function () {
    const form = document.getElementById('regenerateForm');
    const btn = document.getElementById('regenerateBtn');
    const status = document.getElementById('regenerateStatus');

    if (!form) return;

    form.addEventListener('submit', function (e) {
        e.preventDefault();

        btn.disabled = true;
        status.innerHTML = '<span class="ledger-spinner"></span> Membuat dokumen...';

        fetch(form.action, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value,
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
            },
            body: new FormData(form),
        })
        .then(response => response.json().then(data => ({ ok: response.ok, data })))
        .then(({ ok, data }) => {
            if (ok && data.success) {
                status.innerHTML = '<i class="bi bi-check-circle-fill text-success"></i> Selesai';
                setTimeout(() => window.location.reload(), 900);
            } else {
                status.innerHTML = '<i class="bi bi-x-circle-fill text-danger"></i> Gagal';
                btn.disabled = false;
                setTimeout(() => { status.innerHTML = ''; }, 3000);
            }
        })
        .catch(() => {
            status.innerHTML = '<i class="bi bi-x-circle-fill text-danger"></i> Gagal';
            btn.disabled = false;
        });
    });
})();
</script>
@endpush

@endsection
