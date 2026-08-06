@extends('layouts.app')

@section('content')

@include('partials.ledger-styles')

<div class="ledger-page">
    <div class="container-fluid py-1 py-md-2">

        @if (session('error'))
            <div class="alert ledger-alert-danger" role="alert">{{ session('error') }}</div>
        @endif

        <div class="card ledger-card">
            <div class="card-header ledger-card-header d-flex align-items-center justify-content-between flex-wrap gap-2">
                <div>
                    <h2 class="ledger-title mb-0">Petakan Field — {{ $template->nama_template }}</h2>
                    <p class="ledger-subtitle mb-0">
                        Sistem menemukan {{ count($detected) }} bagian titik-titik di file ini.
                        Cocokkan tiap bagian ke field data yang sesuai, sisanya biarkan "Lewati".
                    </p>
                </div>
                <a href="{{ route('kontrak-template.index') }}" class="btn ledger-btn-ghost">
                    <i class="bi bi-arrow-left me-1"></i> Kembali
                </a>
            </div>

            <div class="card-body">

                @if (count($detected) === 0)
                    <div class="alert alert-info mb-0">
                        Tidak ada pola titik-titik yang terdeteksi di file ini. Kalau file ini memang draft
                        kosong yang perlu dipetakan, cek manual — mungkin polanya beda dari yang dikenali sistem
                        (misalnya pakai garis bawah panjang tanpa titik).
                    </div>
                @else
                    <form method="POST" action="{{ route('kontrak-template.mapping.apply', $template) }}">
                        @csrf

                        <div class="table-responsive">
                            <table class="table align-middle">
                                <thead>
                                    <tr>
                                        <th style="width: 45%">Konteks di dokumen</th>
                                        <th>Field ini diisi apa?</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($detected as $i => $item)
                                        <tr>
                                            <td>
                                                <div class="ledger-help mb-1">
                                                    @if ($item['label'])
                                                        Label terdeteksi: <strong>{{ $item['label'] }}</strong>
                                                    @else
                                                        <em>Tidak ada label jelas di depannya</em>
                                                    @endif
                                                </div>
                                                <code style="font-size: 0.85rem;">
                                                    …{{ str_replace($item['matched_text'], '▓▓▓▓', $item['context']) }}…
                                                </code>
                                            </td>
                                            <td style="min-width: 260px;">
                                                <input type="hidden"
                                                       name="fields[{{ $item['paragraph_index'] }}][{{ $i }}][offset]"
                                                       value="{{ $item['offset'] }}">
                                                <input type="hidden"
                                                       name="fields[{{ $item['paragraph_index'] }}][{{ $i }}][matched_text]"
                                                       value="{{ $item['matched_text'] }}">
                                                <select name="fields[{{ $item['paragraph_index'] }}][{{ $i }}][field]"
                                                        class="form-select form-select-sm">
                                                    <option value="">— Lewati (bukan data yang berubah-ubah) —</option>
                                                    @foreach ($fieldOptions as $key => $label)
                                                        <option value="{{ $key }}"
                                                                @selected($item['suggested_field'] === $key)>
                                                            {{ $label }} ({{ $key }})
                                                        </option>
                                                    @endforeach
                                                </select>
                                                @if ($item['suggested_field'])
                                                    <div class="ledger-help mt-1">
                                                        <i class="bi bi-pen me-1"></i>Saran otomatis sudah dipilih — cek dulu sebelum simpan.
                                                    </div>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="d-flex justify-content-end gap-3 mt-3">
                            <button type="submit" class="btn ledger-btn-brass">
                                <i class="bi bi-check2-circle me-1"></i>
                                Simpan Pemetaan
                            </button>
                        </div>
                    </form>
                @endif

            </div>
        </div>
    </div>
</div>

@endsection
