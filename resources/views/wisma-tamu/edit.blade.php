@extends('layouts.app')

@section('content')

@include('wisma-tamu._styles')

<div class="wisma-page">
    <div class="container-fluid py-1 py-md-2">

        @if ($errors->any())
            <div class="wisma-alert-danger mb-3">{{ $errors->first() }}</div>
        @endif

        <div class="row">
            <div class="col-12">
                <div class="wisma-card">
                    <div class="card-header">
                        <h2 class="wisma-title" style="font-size:1.35rem;">Edit Data Tamu</h2>
                        <p class="wisma-subtitle mb-0">Kamar {{ $tamu->nomor_kamar }} &middot; {{ $tamu->nama_tamu }}</p>
                    </div>
                    <div class="card-body p-4 p-md-5">
                        <form method="POST" action="{{ route('wisma-tamu.update', $tamu) }}">
                            @csrf
                            @method('PUT')
                            @include('wisma-tamu._form')
                        </form>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

@endsection