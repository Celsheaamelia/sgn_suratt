@extends('layouts.app')

@section('content')

@include('partials.ledger-styles')

<div class="ledger-page">
    <div class="container-fluid py-1 py-md-2">

        @if ($errors->any())
            <div class="alert ledger-alert-danger">{{ $errors->first() }}</div>
        @endif

        <div class="row justify-content-center">
            <div class="col-lg-9">
                <div class="card ledger-card">
                    <div class="card-header ledger-card-header">
                        <h2 class="ledger-title">Edit Karyawan</h2>
                        <p class="ledger-subtitle mb-0 ledger-nomor">{{ $karyawan->nik }}</p>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="{{ route('karyawan.update', $karyawan) }}" class="ledger-form">
                            @csrf
                            @method('PUT')
                            @include('karyawan._form')
                        </form>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

@endsection
