<div class="card ledger-card">
    <div class="card-body p-0">
        {{-- Kolomnya banyak (identitas + info kontrak terakhir), jadi tabel
             dibuat bisa digeser horizontal daripada dipaksa muat / kepotong. --}}
        <div style="overflow-x: auto;">
            <table class="table ledger-table align-middle mb-0" style="min-width: 1600px;">
                <thead>
                    <tr>
                        <th style="width:1%; white-space:nowrap;">No</th>
                        <th style="white-space:nowrap;">NIK</th>
                        <th style="white-space:nowrap;">Nama</th>
                        <th style="white-space:nowrap;">No. KTP</th>
                        <th style="white-space:nowrap;">Tempat, Tanggal Lahir</th>
                        <th style="white-space:nowrap;">Jenis Kelamin</th>
                        <th style="white-space:nowrap;">Agama</th>
                        <th style="white-space:nowrap;">Status Perkawinan</th>
                        <th style="white-space:nowrap; min-width: 260px;">Alamat</th>
                        <th style="white-space:nowrap;">Status</th>
                        <th style="white-space:nowrap;">Jabatan (kontrak terakhir)</th>
                        <th style="white-space:nowrap;">Bagian (kontrak terakhir)</th>
                        <th style="white-space:nowrap;">Nomor Kontrak</th>
                        <th style="white-space:nowrap;">Periode Kontrak</th>
                        <th class="text-end" style="white-space:nowrap;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($karyawanList as $k)
                        @php
                            $kontrakTerakhir = $k->latestKontrak;
                            $jenisKontrakTerakhir = $kontrakTerakhir->jenisKontrak ?? null;
                        @endphp
                        <tr>
                            <td class="ledger-tanggal">{{ $karyawanList->firstItem() + $loop->index }}</td>
                            <td class="ledger-tanggal" style="white-space:nowrap;">{{ $k->nik }}</td>
                            <td class="ledger-perihal" style="white-space:nowrap;">{{ $k->nama }}</td>
                            <td class="ledger-tanggal" style="white-space:nowrap;">{{ $k->no_ktp ?? '-' }}</td>
                            <td class="ledger-tujuan" style="white-space:nowrap;">{{ $k->tempat_tanggal_lahir ?? '-' }}</td>
                            <td class="ledger-tujuan" style="white-space:nowrap;">{{ $k->jenis_kelamin ?? '-' }}</td>
                            <td class="ledger-tujuan" style="white-space:nowrap;">{{ $k->agama ?? '-' }}</td>
                            <td class="ledger-tujuan" style="white-space:nowrap;">{{ $k->status_perkawinan ?? '-' }}</td>
                            <td class="ledger-tujuan">{{ $k->alamat ?? '-' }}</td>
                            <td style="white-space:nowrap;">
                                @if ($jenisKontrakTerakhir)
                                    <span class="ledger-status-pill {{ $jenisKontrakTerakhir->masa_giling ? 'is-draft' : 'is-active' }}">
                                        {{ $jenisKontrakTerakhir->nama_singkat ?: $jenisKontrakTerakhir->nama_jenis }}
                                    </span>
                                @else
                                    <span class="ledger-subtitle">-</span>
                                @endif
                            </td>
                            <td class="ledger-tujuan" style="white-space:nowrap;">{{ $kontrakTerakhir->jabatan_kontrak ?? '-' }}</td>
                            <td class="ledger-tujuan" style="white-space:nowrap;">{{ $kontrakTerakhir->bagian_kontrak ?? '-' }}</td>
                            <td class="ledger-tanggal" style="white-space:nowrap;">{{ $kontrakTerakhir->nomor_kontrak ?? '-' }}</td>
                            <td class="ledger-tanggal" style="white-space:nowrap;">
                                @if ($kontrakTerakhir?->tanggal_mulai)
                                    {{ $kontrakTerakhir->tanggal_mulai->translatedFormat('d M Y') }}
                                    &ndash;
                                    {{ $kontrakTerakhir->tanggal_selesai?->translatedFormat('d M Y') ?? '-' }}
                                @else
                                    -
                                @endif
                            </td>
                            <td class="text-end" style="white-space:nowrap;">
                                <div class="d-flex gap-2 justify-content-end">
                                    <a href="{{ route('karyawan.edit', $k) }}" class="btn ledger-btn-detail" title="Edit">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <form method="POST" action="{{ route('karyawan.destroy', $k) }}"
                                          onsubmit="return confirm('Hapus data {{ $k->nama }}? Kontrak yang sudah dibuat untuk karyawan ini juga akan terhapus.')">
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
                            <td colspan="15" class="text-center py-4 ledger-subtitle">
                                {{ (request('search') || request('status_kontrak')) ? 'Tidak ada karyawan yang cocok dengan pencarian/filter.' : 'Belum ada data karyawan.' }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if ($karyawanList->hasPages())
        <div class="card-body">
            {{ $karyawanList->links() }}
        </div>
    @endif
</div>