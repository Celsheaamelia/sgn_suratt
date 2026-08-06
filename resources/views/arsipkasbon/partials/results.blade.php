@php
    if (!function_exists('highlightArsipMatch')) {
        function highlightArsipMatch($text, $keyword) {
            if ($text === null || $text === '') {
                return '-';
            }
            $escaped = e($text);
            $keyword = trim((string) $keyword);
            if ($keyword === '') {
                return $escaped;
            }
            $pattern = '/(' . preg_quote($keyword, '/') . ')/iu';
            return preg_replace($pattern, '<mark class="search-hl">$1</mark>', $escaped);
        }
    }
@endphp

{{-- @if(request()->filled('tanggal_dari') || request()->filled('tanggal_sampai')) --}}
    {{-- <div class="stats-strip">
        <div class="stat-chip"> --}}
            {{-- <i class="bi bi-calendar-range"></i> --}}
            {{-- periode {{ request('tanggal_dari') ? \Carbon\Carbon::parse(request('tanggal_dari'))->format('d M Y') : '...' }}
            s/d {{ request('tanggal_sampai') ? \Carbon\Carbon::parse(request('tanggal_sampai'))->format('d M Y') : '...' }} --}}
        {{-- </div>
    </div>
@endif --}}

<div class="table-responsive">
    {{-- Tidak ada satu pun kolom di sini yang dipaksa lebar tertentu (kecuali "No"
         yang memang selalu 1-3 digit). Tabelnya sendiri di CSS diset width:auto,
         jadi kalau ada sisa ruang kosong, dia numpuk di LUAR tabel (kanan card),
         bukan nyempil di antara kolom data. --}}
    <table class="table kasbon-table">
        <thead>
            <tr>
                <th style="width:40px;">No</th>
                <th>Tanggal</th>
                <th>Vendor &amp; Dokumen</th>
                <th>Jumlah</th>
                <th>Rincian Akun</th>
                <th class="text-end pe-3"></th>
            </tr>
        </thead>
        <tbody>
            @forelse($arsipList as $kasbon)
                <tr>
                    <td style="white-space:nowrap; color: var(--ink-faint); font-family: var(--font-mono); font-size: 0.82rem;">
                        {{ $arsipList->firstItem() + $loop->index }}
                    </td>
                    <td style="white-space:nowrap;">{{ optional($kasbon->tanggal_transaksi)->format('d M Y') ?? '-' }}</td>
                    <td>
                        <div class="vendor-cell">
                            <div class="vendor-avatar">{{ strtoupper(substr($kasbon->nama_vendor ?? '?', 0, 1)) }}</div>
                            <div>
                                <div class="vendor-name">{!! highlightArsipMatch($kasbon->nama_vendor ?? 'Vendor tidak diketahui', request('q')) !!}</div>
                                <div class="vendor-doc">{!! highlightArsipMatch($kasbon->document_no ?? '—', request('q')) !!}</div>
                                @if($kasbon->numerator)
                                    <div class="vendor-doc" style="color: var(--ink-faint);">No. {!! highlightArsipMatch($kasbon->numerator, request('q')) !!}</div>
                                @endif
                            </div>
                        </div>
                    </td>
                    <td>
                        <span class="amount-val">{{ $kasbon->jumlah_total ? 'Rp ' . number_format($kasbon->jumlah_total, 0, ',', '.') : '-' }}</span>
                    </td>
                    <td style="white-space:nowrap;">
                        @forelse($kasbon->items->take(2) as $item)
                            <span class="akun-badge">{!! highlightArsipMatch($item->no_akun, request('q')) !!}</span>
                        @empty
                            <span class="ledger-subtitle">-</span>
                        @endforelse
                        @if($kasbon->items->count() > 2)
                            <span class="akun-more">+{{ $kasbon->items->count() - 2 }} lagi</span>
                        @endif
                    </td>
                    <td class="text-end pe-4">
                        <div class="action-cell">
                            <a href="{{ route('arsipkasbon.show', $kasbon) }}" class="action-btn-detail">
                                Lihat Detail
                            </a>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6">
                        <div class="empty-state">
                            @if(request()->filled('q') || request()->filled('tanggal_dari') || request()->filled('tanggal_sampai'))
                                <i class="bi bi-search"></i>
                                Tidak ada surat yang cocok dengan filter saat ini.
                                <div class="mt-1" style="font-size:0.85rem;">Coba kata kunci atau rentang tanggal lain.</div>
                            @else
                                <i class="bi bi-inbox"></i>
                                Belum ada SPP yang diarsipkan.
                                <div class="mt-1" style="font-size:0.85rem;">Mulai dengan <a href="{{ route('arsipkasbon.create') }}">Unggah Surat Baru</a>.</div>
                            @endif
                        </div>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

@if($arsipList->hasPages())
    <div class="card-body">
        {{ $arsipList->onEachSide(1)->links() }}
    </div>
@endif