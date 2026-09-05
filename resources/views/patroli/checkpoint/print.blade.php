@extends('layouts.app')

@section('content')
@include('patroli._styles')

<div class="patroli-page">
    <div class="container-fluid py-1 py-md-2">

        <div class="d-flex justify-content-between align-items-center mb-4 d-print-none flex-wrap gap-2">
            <div>
                <div class="patroli-eyebrow mb-1">Patroli Digital</div>
                <h2 class="patroli-title mb-1" style="font-size: 1.5rem;">Cetak QR Checkpoint</h2>
                <p class="patroli-subtitle mb-0">Tempel &amp; laminasi di masing-masing titik patroli</p>
            </div>
            <button onclick="window.print()" class="btn patroli-btn-brass">
                <i class="bi bi-printer"></i> Cetak Semua QR
            </button>
        </div>

        <div class="row g-3">
            @foreach ($checkpoints as $cp)
                <div class="col-md-3 col-6">
                    <div class="patroli-qr-card">
                        <div id="qr-{{ $cp->id }}" class="mx-auto mb-2" style="width: 160px;"></div>
                        <h6 class="mt-2 mb-0">{{ $cp->nama_titik }}</h6>
                        <small class="patroli-subtitle" style="font-family: 'IBM Plex Mono', monospace;">{{ $cp->kode }}</small>
                    </div>
                </div>
            @endforeach
        </div>

    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
<script>
    @foreach ($checkpoints as $cp)
        new QRCode(document.getElementById("qr-{{ $cp->id }}"), {
            text: "{{ url('/patroli/scan/' . $cp->kode) }}",
            width: 160,
            height: 160,
        });
    @endforeach
</script>
@endsection
