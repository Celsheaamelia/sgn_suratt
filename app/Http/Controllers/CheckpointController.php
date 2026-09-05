<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\PatrolCheckpoint;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CheckpointController extends Controller
{
    public function index(Request $request)
    {
        $checkpoints = PatrolCheckpoint::query()
            ->when($request->search, function ($q) use ($request) {
                $keyword = trim($request->search);
                $q->where(function ($sub) use ($keyword) {
                    $sub->where('nama_titik', 'like', "%{$keyword}%")
                        ->orWhere('kode', 'like', "%{$keyword}%")
                        ->orWhere('area', 'like', "%{$keyword}%");
                });
            })
            ->withCount('scans')
            ->urut()
            ->paginate(10)
            ->withQueryString();

        return view('patroli.checkpoint.index', compact('checkpoints'));
    }

    public function store(Request $request)
    {
        $data = $this->validateData($request);
        $data['kode'] = PatrolCheckpoint::kodeBerikutnya();

        PatrolCheckpoint::create($data);

        return back()->with('success', 'Titik checkpoint berhasil ditambahkan.');
    }

    public function update(Request $request, PatrolCheckpoint $checkpoint)
    {
        $data = $this->validateData($request);

        $checkpoint->update($data);

        return back()->with('success', 'Titik checkpoint berhasil diperbarui.');
    }

    /**
     * Hapus checkpoint HANYA jika belum pernah dipakai untuk scan (jaga histori patroli).
     * Kalau sudah pernah dipakai, arahkan admin untuk nonaktifkan saja.
     */
    public function destroy(PatrolCheckpoint $checkpoint)
    {
        if ($checkpoint->scans()->exists()) {
            return back()->with(
                'error',
                "Titik {$checkpoint->nama_titik} sudah punya histori scan dan tidak bisa dihapus. Nonaktifkan saja titik ini."
            );
        }

        $checkpoint->delete();

        return back()->with('success', 'Titik checkpoint berhasil dihapus.');
    }

    public function toggleAktif(PatrolCheckpoint $checkpoint)
    {
        $checkpoint->update(['aktif' => ! $checkpoint->aktif]);

        return back()->with('success', $checkpoint->aktif
            ? "Titik {$checkpoint->nama_titik} diaktifkan kembali."
            : "Titik {$checkpoint->nama_titik} dinonaktifkan.");
    }

    /**
     * Halaman cetak QR semua titik aktif (untuk dilaminasi & ditempel di lapangan).
     * Bisa diakses admin & supervisor.
     */
    public function print()
    {
        $checkpoints = PatrolCheckpoint::aktif()->urut()->get();

        return view('patroli.checkpoint.print', compact('checkpoints'));
    }

    private function validateData(Request $request): array
    {
        return $request->validate([
            'nama_titik' => 'required|string|max:150',
            'area'       => 'nullable|string|max:150',
            'urutan'     => 'nullable|integer|min:0',
            'deskripsi'  => 'nullable|string|max:500',
            'latitude'   => 'nullable|numeric|between:-90,90',
            'longitude'  => 'nullable|numeric|between:-180,180',
        ]);
    }
}
