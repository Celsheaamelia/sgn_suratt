<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\RiwayatSuratController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\KeepController;
use App\Http\Controllers\ArsipKasbonController;
use App\Services\GeminiService;
use App\Http\Controllers\RegisterController;
use App\Http\Controllers\KaryawanController;
use App\Http\Controllers\KontrakController;
use App\Http\Controllers\TemplateController;
use App\Http\Controllers\WismaTamuController;

Route::get('/', function () {
    return redirect()->route('login');
});

// Tampilan TV Wisma Tamu — publik, tanpa login, dibuka langsung di browser TV resepsionis.
Route::get('/wisma-tamu/tv', [WismaTamuController::class, 'tv'])->name('wisma-tamu.tv');
Route::get('/wisma-tamu/tv/data', [WismaTamuController::class, 'tvData'])->name('wisma-tamu.tv-data');

// Login
Route::middleware('guest')->group(function () {

    Route::get('/login', [LoginController::class,'index'])->name('login');
    Route::post('/login', [LoginController::class,'login']);

    Route::get('/register', [RegisterController::class, 'index'])->name('register');
    Route::post('/register', [RegisterController::class, 'register'])->name('register.store');

    Route::get('/auth/google', [RegisterController::class, 'redirectToGoogle'])->name('google.redirect');
    Route::get('/auth/google/callback', [RegisterController::class, 'handleGoogleCallback'])->name('google.callback');

});

// Helper: baca data surat dari file JSON
function bacaSurat(): array
{
    if (!Storage::exists('surat.json')) {
        return [];
    }
    return json_decode(Storage::get('surat.json'), true) ?? [];
}

// Helper: simpan data surat ke file JSON
function simpanSurat(array $data): void
{
    Storage::put('surat.json', json_encode($data, JSON_PRETTY_PRINT));
}
    // Tampilkan form tambah surat
    Route::middleware('auth')->group(function () {

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('/dashboard/chart-data', [DashboardController::class, 'chartRange'])
    ->name('dashboard.chart-data');

    Route::get('/dashboard/spp-chart-data', [DashboardController::class, 'chartRangeSpp'])
    ->name('dashboard.spp-chart-data');

    Route::get('/surat/tambah', [RiwayatSuratController::class,'create'])
        ->name('tambahsurat');

    Route::post('/surat/tambah', [RiwayatSuratController::class,'store'])
        ->name('surat.store');

    Route::get('/riwayat-surat', [RiwayatSuratController::class,'index'])
        ->name('riwayatsurat');

    Route::get('/riwayat-surat/export', [RiwayatSuratController::class, 'exportExcel'])
        ->name('surat.export');

    Route::get('/keep-nomor-surat', [KeepController::class, 'keepNomorSurat'])
        ->name('keepnomorsurat');

    Route::post('/keep-nomor-surat', [KeepController::class, 'storeKeepNomor'])
        ->name('keepnomorsurat.store');

    // Route::post('/keep-nomor-surat/{id}/gunakan', [KeepController::class, 'gunakanKeepNomor'])
    //     ->name('keepnomorsurat.gunakan');

    Route::get('/keepnomorsurat/cek-nomor', [KeepController::class, 'cekNomorTerpakai'])->name('keepnomorsurat.cek-nomor');

    Route::delete('/keepnomorsurat/{id}/cancel', [KeepController::class, 'cancelKeepNomor'])->name('keepnomorsurat.cancel');

    Route::get('/riwayat-surat/{riwayatSurat}', [RiwayatSuratController::class,'show'])
        ->name('surat.show');

    Route::get('/riwayat-surat/{riwayatSurat}/upload', [RiwayatSuratController::class,'uploadForm'])
        ->name('surat.upload.form');

    Route::post('/riwayat-surat/{riwayatSurat}/upload', [RiwayatSuratController::class,'upload'])
        ->name('surat.upload');

    Route::get('/surat/upload/{surat}', [RiwayatSuratController::class, 'showUpload'])->name('surat.upload.show');
    Route::post('/surat/upload/{surat}', [RiwayatSuratController::class, 'storeUpload'])->name('surat.upload.store');

    Route::delete('/surat/upload/{surat}', [RiwayatSuratController::class, 'deleteUpload'])
        ->name('surat.upload.delete');

    Route::get('/surat/next-sequence', [RiwayatSuratController::class, 'getNextSequence'])
        ->name('surat.next-sequence');

    Route::get('/surat/cek-status-nomor', [RiwayatSuratController::class, 'cekStatusNomor'])->name('surat.cek-status-nomor');

    Route::get('/arsip-kasbon', [ArsipKasbonController::class, 'index'])
        ->name('arsipkasbon.index');

    Route::get('/arsip-kasbon/tambah', [ArsipKasbonController::class, 'create'])
        ->name('arsipkasbon.create');

    Route::get('/arsip-kasbon/export', [ArsipKasbonController::class, 'export'])
        ->name('arsipkasbon.export');

    Route::post('/arsip-kasbon/scan', [ArsipKasbonController::class, 'scan'])
        ->name('arsipkasbon.scan');

    Route::post('/arsip-kasbon', [ArsipKasbonController::class, 'store'])
        ->name('arsipkasbon.store');

    Route::get('/arsip-kasbon/{arsipKasbon}', [ArsipKasbonController::class, 'show'])
        ->name('arsipkasbon.show');

    Route::delete('/arsip-kasbon/{arsipKasbon}', [ArsipKasbonController::class, 'destroy'])
        ->name('arsipkasbon.destroy');

    Route::get('/arsip-kasbon-api/lookup-akun/{noAkun}', [ArsipKasbonController::class, 'lookupAkun'])
        ->name('arsipkasbon.lookup-akun');

    Route::get('/arsip-kasbon-api/check-document', [ArsipKasbonController::class, 'checkDocumentNo'])
        ->name('arsipkasbon.check-document');

        // Data Karyawan
    Route::get('/karyawan', [KaryawanController::class, 'index'])->name('karyawan.index');
    Route::get('/karyawan/tambah', [KaryawanController::class, 'create'])->name('karyawan.create');
    Route::post('/karyawan', [KaryawanController::class, 'store'])->name('karyawan.store');
    Route::get('/karyawan/{karyawan}/edit', [KaryawanController::class, 'edit'])->name('karyawan.edit');
    Route::put('/karyawan/{karyawan}', [KaryawanController::class, 'update'])->name('karyawan.update');
    Route::delete('/karyawan/{karyawan}', [KaryawanController::class, 'destroy'])->name('karyawan.destroy');
    Route::get('/karyawan-api/search', [KaryawanController::class, 'search'])->name('karyawan.search');

    // Manajemen Kontrak
    Route::get('/kontrak', [KontrakController::class, 'index'])->name('kontrak.index');
    Route::get('/kontrak/tambah', [KontrakController::class, 'create'])->name('kontrak.create');
    Route::post('/kontrak', [KontrakController::class, 'store'])->name('kontrak.store');
    Route::get('/kontrak/next-sequence', [KontrakController::class, 'getNextSequence'])->name('kontrak.next-sequence');
    Route::get('/kontrak/cek-status-nomor', [KontrakController::class, 'cekStatusNomor'])->name('kontrak.cek-status-nomor');
    Route::get('/kontrak/cek-status-bagian', [KontrakController::class, 'cekStatusBagian'])->name('kontrak.cek-status-bagian');
    Route::post('/kontrak/generate-bagian', [KontrakController::class, 'generateByBagian'])->name('kontrak.generate-bagian');
    Route::get('/kontrak/download-bagian', [KontrakController::class, 'downloadByBagian'])->name('kontrak.download-bagian');
    Route::post('/kontrak/download-terpilih', [KontrakController::class, 'downloadSelected'])->name('kontrak.download-selected');
    Route::get('/kontrak/{kontrak}', [KontrakController::class, 'show'])->name('kontrak.show');
    Route::get('/kontrak/{kontrak}/download', [KontrakController::class, 'download'])->name('kontrak.download');
    Route::post('/kontrak/{kontrak}/regenerate', [KontrakController::class, 'regenerate'])->name('kontrak.regenerate');
    Route::get('/kontrak/{kontrak}/preview', [KontrakController::class, 'preview'])->name('kontrak.preview');
    Route::get('/kontrak/{kontrak}/preview/file', [KontrakController::class, 'previewFile'])->name('kontrak.preview.file');
    // Route::post('/kontrak/{kontrak}/publish', [KontrakController::class, 'publish'])->name('kontrak.publish');
    Route::post('/kontrak/{kontrak}/switch-template', [KontrakController::class, 'switchTemplate'])->name('kontrak.switch-template');
    Route::get('/kontrak/{kontrak}/upload', [KontrakController::class, 'uploadSignedForm'])->name('kontrak.upload.form');
    Route::post('/kontrak/{kontrak}/upload', [KontrakController::class, 'uploadSigned'])->name('kontrak.upload.store');
    Route::delete('/kontrak/{kontrak}/upload', [KontrakController::class, 'deleteSigned'])->name('kontrak.upload.delete');
    Route::delete('/kontrak/{kontrak}', [KontrakController::class, 'destroy'])->name('kontrak.destroy');

    // Kelola Template Kontrak
    Route::get('/kontrak-template', [TemplateController::class, 'index'])->name('kontrak-template.index');
    Route::post('/kontrak-template', [TemplateController::class, 'store'])->name('kontrak-template.store');
    Route::post('/kontrak-template/{template}/set-default', [TemplateController::class, 'setDefault'])->name('kontrak-template.set-default');
    Route::post('/kontrak-template/{template}/publish', [TemplateController::class, 'publish'])->name('kontrak-template.publish');
    Route::get('/kontrak-template/{template}/download', [TemplateController::class, 'download'])->name('kontrak-template.download');
    Route::delete('/kontrak-template/{template}', [TemplateController::class, 'destroy'])->name('kontrak-template.destroy');
    Route::get('/kontrak-template/{template}/preview', [TemplateController::class, 'preview'])->name('kontrak-template.preview');
    Route::get('/kontrak-template/{template}/preview/file', [TemplateController::class, 'previewFile'])->name('kontrak-template.preview.file');

    Route::post('/logout', [LoginController::class,'logout'])
        ->name('logout');

    // Wisma Tamu
    Route::get('/wisma-tamu', [WismaTamuController::class, 'index'])->name('wisma-tamu.index');
    Route::get('/wisma-tamu/tambah', [WismaTamuController::class, 'create'])->name('wisma-tamu.create');
    Route::post('/wisma-tamu', [WismaTamuController::class, 'store'])->name('wisma-tamu.store');
    Route::get('/wisma-tamu/{wismaTamu}/edit', [WismaTamuController::class, 'edit'])->name('wisma-tamu.edit');
    Route::put('/wisma-tamu/{wismaTamu}', [WismaTamuController::class, 'update'])->name('wisma-tamu.update');
    Route::delete('/wisma-tamu/{wismaTamu}', [WismaTamuController::class, 'destroy'])->name('wisma-tamu.destroy');

        Route::get('/test-gemini', function (GeminiService $gemini) {
    return $gemini->generateText('Halo, siapa kamu?');
});
});