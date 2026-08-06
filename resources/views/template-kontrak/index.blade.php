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
        @if ($errors->any())
            <div class="alert ledger-alert-danger">{{ $errors->first() }}</div>
        @endif

        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
            <div>
                <h2 class="ledger-title mb-1">Kelola Format Kontrak</h2>
                {{-- <p class="ledger-subtitle mb-0">Template yang ditandai <strong>Default</strong> akan otomatis dipakai untuk kontrak baru yang dibuat setelah ini. Kontrak yang sudah ada tidak berubah.</p>
                <p class="ledger-subtitle mb-0">Ada perubahan pasal? Klik <i class="bi bi-download"></i> <strong>Download</strong> untuk ambil filenya, edit di Word (placeholder <code>@{{...}}</code> jangan disentuh), lalu upload lagi lewat tombol Upload Template di bawah.</p> --}}
            </div>
            <a href="{{ route('kontrak.create') }}" class="btn ledger-btn-ghost">
                <i class="bi bi-arrow-left me-1"></i> Kembali ke Buat Kontrak
            </a>
        </div>

        @foreach ($jenisList as $jenis)
            @php $templates = $templatesByJenis->get($jenis->id, collect()); @endphp
            <div class="card ledger-card mb-4">
                <div class="card-header ledger-card-header d-flex justify-content-between align-items-center">
                    <h3 class="ledger-table-title mb-0">{{ $jenis->nama_jenis }}</h3>
                    <button type="button" class="btn ledger-btn-brass btn-sm" data-bs-toggle="collapse"
                            data-bs-target="#uploadForm{{ $jenis->id }}">
                        <i class="bi bi-upload me-1"></i> Upload Template
                    </button>
                </div>

                <div class="collapse" id="uploadForm{{ $jenis->id }}">
                    <div class="card-body border-bottom">
                        <form method="POST" action="{{ route('kontrak-template.store') }}" enctype="multipart/form-data" class="row g-3 align-items-end">
                            @csrf
                            <input type="hidden" name="jenis_kontrak_id" value="{{ $jenis->id }}">
                            <div class="col-md-4">
                                <label class="form-label">Nama Template</label>
                                <input type="text" name="nama_template" class="form-control" required
                                       placeholder="cth: Template 2027 (Pasal 3 Poin)">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">File (.docx)</label>
                                <input type="file" name="file" class="form-control" accept=".docx" required>
                            </div>
                            <div class="col-md-3">
                                <div class="form-check mt-4">
                                    <input type="checkbox" name="is_default" value="1" class="form-check-input" id="default{{ $jenis->id }}">
                                    <label class="form-check-label" for="default{{ $jenis->id }}">
                                        Jadikan default sekarang
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-1">
                                <button type="submit" class="btn ledger-btn-brass w-100">Simpan</button>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="card-body p-0">
                    <table class="table ledger-table align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Nama Template</th>
                                <th>Diupload</th>
                                <th>Status</th>
                                <th class="text-end">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($templates as $tpl)
                                <tr>
                                    <td class="ledger-perihal">{{ $tpl->nama_template }}</td>
                                    <td class="ledger-tanggal">{{ $tpl->created_at->translatedFormat('d M Y H:i') }}</td>
                                    <td>
                                        <div class="d-flex flex-wrap gap-1">
                                            @if ($tpl->is_default)
                                                <span class="badge bg-success">Default (dipakai kontrak baru)</span>
                                            @endif
                                            @if ($tpl->published_at)
                                                <span class="badge bg-primary">Published</span>
                                            @else
                                                <span class="badge bg-warning text-dark">Draft &middot; Belum Publish</span>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="text-end">
                                        <div class="d-flex gap-2 justify-content-end">
                                            <a href="{{ route('kontrak-template.preview', $tpl) }}" class="btn ledger-btn-detail" title="Preview">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                            <a href="{{ route('kontrak-template.download', $tpl) }}" class="btn ledger-btn-detail" title="Download (buat diedit di Word)">
                                                <i class="bi bi-download"></i>
                                            </a>
                                            @unless ($tpl->is_default)
                                                <form method="POST" action="{{ route('kontrak-template.set-default', $tpl) }}">
                                                    @csrf
                                                    <button type="submit" class="btn ledger-btn-detail" title="Jadikan Default">
                                                        <i class="bi bi-star"></i>
                                                    </button>
                                                </form>
                                            @endunless
                                            @unless ($tpl->published_at)
                                                <form method="POST" action="{{ route('kontrak-template.publish', $tpl) }}"
                                                      onsubmit="return confirm('Publish template &quot;{{ $tpl->nama_template }}&quot;? Kontrak yang memakai template ini akan bisa didownload setelah ini.')">
                                                    @csrf
                                                    <button type="submit" class="btn btn-success btn-sm" title="Publish">
                                                        <i class="bi bi-check2-circle"></i>
                                                    </button>
                                                </form>
                                            @endunless
                                            <form method="POST" action="{{ route('kontrak-template.destroy', $tpl) }}"
                                                  onsubmit="return confirm('Hapus template &quot;{{ $tpl->nama_template }}&quot;? Kontrak yang sudah pernah pakai template ini tidak akan terhapus dokumennya.')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn ledger-btn-detail text-danger" title="Hapus">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center py-3 ledger-subtitle">
                                        Belum ada template untuk jenis kontrak ini.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        @endforeach

    </div>
</div>

@endsection
