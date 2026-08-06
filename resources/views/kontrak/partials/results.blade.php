<div class="card ledger-card">
    <div class="card-body p-0">
        <table class="table ledger-table align-middle mb-0">
            <thead>
                <tr>
                    <th style="width: 48px;">No.</th>
                    <th class="ledger-checkbox-col d-none" style="width: 36px;"></th>
                    <th>Nomor Kontrak</th>
                    <th>Karyawan</th>
                    <th>Bagian</th>
                    <th>Status</th>
                    <th>Periode</th>
                    <th class="text-end"></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($kontrakList as $row)
                    @php $kontrak = $row['kontrak']; @endphp
                    <tr>
                        <td class="ledger-help">{{ $kontrakList->firstItem() + $loop->index }}</td>
                        <td class="ledger-checkbox-col d-none">
                            <input type="checkbox" class="form-check-input kontrak-checkbox" value="{{ $kontrak->id }}">
                        </td>
                        <td class="ledger-nomor">
                            {{ $kontrak->nomor_kontrak }}
                            @if ($row['total_riwayat'] > 1)
                                <span class="badge ledger-badge-riwayat" title="Punya {{ $row['total_riwayat'] }} riwayat kontrak">
                                    <i class="bi bi-clock-history"></i> {{ $row['total_riwayat'] }}
                                </span>
                            @endif
                        </td>
                        <td>
                            <div class="ledger-perihal">{{ $kontrak->karyawan->nama ?? '-' }}</div>
                            <div class="ledger-help mb-0">{{ $kontrak->karyawan->nik ?? '-' }}</div>
                        </td>
                        <td>{{ $kontrak->bagian_kontrak ?: '-' }}</td>
                        <td class="ledger-tujuan">{{ $kontrak->jenisKontrak->nama_jenis ?? '-' }}</td>
                        <td class="ledger-tanggal">
                            {{ optional($kontrak->tanggal_mulai)->format('d/m/Y') }}
                            &ndash;
                            @if ($kontrak->tanggal_selesai)
                                {{ $kontrak->tanggal_selesai->format('d/m/Y') }}
                            @elseif (optional($kontrak->jenisKontrak)->masa_giling)
                                 Berakhirnya Masa Giling
                            @else
                                Tetap
                            @endif
                        </td>
                        <td class="text-end">
                            <div class="d-flex gap-2 justify-content-end">
                                @if ($kontrak->generated_file_path)
                                    <a href="{{ route('kontrak.download', $kontrak) }}" class="btn ledger-btn-detail" title="Download dokumen Word">
                                        <i class="bi bi-file-earmark-word"></i>
                                    </a>
                                @endif
                                <a href="{{ route('kontrak.show', $kontrak) }}" class="btn ledger-btn-detail" title="Detail (termasuk riwayat kontrak lama)">
                                    <i class="bi bi-eye"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center py-4 ledger-subtitle">Belum ada kontrak yang dibuat.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if ($kontrakList->hasPages())
        <div class="card-body">
            {{ $kontrakList->appends(request()->query())->links() }}
        </div>
    @endif
</div>