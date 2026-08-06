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
                        <h2 class="ledger-title">Tambah Karyawan</h2>
                        <p class="ledger-subtitle mb-0">Data ini akan tersedia untuk autofill saat membuat kontrak.</p>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="{{ route('karyawan.store') }}" class="ledger-form">
                            @csrf
                            @include('karyawan._form')
                        </form>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

@endsection
