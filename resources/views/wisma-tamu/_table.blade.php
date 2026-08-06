<div class="wisma-card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table wisma-table align-middle mb-0">
                <thead>
                    <tr>
                        <th style="width:1%; white-space:nowrap;">No</th>
                        <th style="white-space:nowrap;">Kamar</th>
                        <th style="white-space:nowrap;">Nama Tamu</th>
                        <th style="white-space:nowrap;">Instansi</th>
                        <th style="white-space:nowrap;">No. HP</th>
                        <th style="white-space:nowrap;">Check-in</th>
                        <th style="white-space:nowrap;">Check-out</th>
                        <th style="white-space:nowrap;">Status</th>
                        <th class="text-end" style="white-space:nowrap;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($tamuList as $tamu)
                        <tr>
                            <td class="wisma-subtitle">{{ $tamuList->firstItem() + $loop->index }}</td>
                            <td style="white-space:nowrap;"><strong>{{ $tamu->nomor_kamar }}</strong></td>
                            <td style="white-space:nowrap;">{{ $tamu->nama_tamu }}</td>
                            <td style="white-space:nowrap;">{{ $tamu->asal_instansi ?? '-' }}</td>
                            <td style="white-space:nowrap;">{{ $tamu->no_hp ?? '-' }}</td>
                            <td style="white-space:nowrap;">{{ $tamu->tanggal_checkin->format('d M Y') }}</td>
                            <td style="white-space:nowrap;">{{ $tamu->tanggal_checkout->format('d M Y') }}</td>
                            <td style="white-space:nowrap;">
                                @if ($tamu->sedang_menginap)
                                    <span class="wisma-pill terisi">Masih Menginap</span>
                                @else
                                    <span class="wisma-pill kosong">Sudah Checkout</span>
                                @endif
                            </td>
                            <td class="text-end" style="white-space:nowrap;">
                                <div class="d-flex gap-2 justify-content-end">
                                    <a href="{{ route('wisma-tamu.edit', $tamu) }}" class="wisma-btn-icon" title="Edit">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <form method="POST" action="{{ route('wisma-tamu.destroy', $tamu) }}"
                                          onsubmit="return confirm('Hapus data tamu {{ $tamu->nama_tamu }} di kamar {{ $tamu->nomor_kamar }}?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="wisma-btn-icon text-danger" title="Hapus / Checkout">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center py-4 wisma-subtitle">
                                {{ request('search') ? 'Tidak ada data tamu yang cocok dengan pencarian.' : 'Belum ada data tamu. Silakan tambahkan.' }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if ($tamuList->hasPages())
        <div class="card-body">
            {{ $tamuList->links() }}
        </div>
    @endif
</div>