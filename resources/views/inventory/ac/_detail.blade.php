{{-- resources/views/inventory/ac/_detail.blade.php --}}
@php
    use Illuminate\Support\Facades\Schema;

    // Kolom standar yang ingin ditampilkan duluan
    $std = [
        'id_ac','unit_kerja','user','jabatan','ruang','tipe_asset','merk',
        'spes','remote','tahun_pembelian',
    ];

    // Ambil semua kolom nyata dari tabel (kecuali timestamps)
    $allCols = array_values(array_diff(
        Schema::getColumnListing('asset_ac'),
        ['created_at','updated_at']
    ));

    // Bedakan kolom tambahan & susun: standar dulu, lalu sisanya
    $extra   = array_values(array_diff($allCols, $std));
    $ordered = array_values(array_unique(array_merge($std, $extra)));

    // Label rapi
    $labels = [];
    foreach ($ordered as $c) {
        $labels[$c] = ucwords(str_replace('_',' ', $c));
    }

    // Helper tampilan nilai
    $display = function($val) {
        if (is_null($val) || $val === '') return '—';
        if (is_bool($val)) return $val ? 'Ya' : 'Tidak';
        return $val;
    };

    // Histori mungkin sudah dikirim dari controller (show()); fallback koleksi kosong
    $histories = isset($histories) ? collect($histories) : collect();
@endphp

<div class="space-y-6">
  {{-- ======= DETAIL FIELDS ======= --}}
  <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
    @foreach($ordered as $col)
      <div class="rounded-xl border border-gray-200 bg-white p-3 shadow-sm">
        <div class="text-[11px] uppercase tracking-wide text-gray-500">
          {{ $labels[$col] }}
        </div>
        <div class="mt-1 text-sm break-words">
          {{ $display(data_get($data, $col)) }}
        </div>
      </div>
    @endforeach
  </div>

  {{-- ======= AKSI (sembunyikan jika readonly=1) ======= --}}
  @unless(request('readonly') == '1')
    <div class="flex justify-end gap-2">
      <a href="{{ route('inventory.ac.edit', $data->id_ac) }}"
         class="rounded-lg border border-gray-200 px-4 py-2 hover:bg-gray-50">
        Edit
      </a>
      <form action="{{ route('inventory.ac.destroy', $data->id_ac) }}" method="POST"
            onsubmit="return confirm('Hapus data ini?')">
        @csrf @method('DELETE')
        <button class="rounded-lg border border-red-300 text-red-700 px-4 py-2 hover:bg-red-50">
          Hapus
        </button>
      </form>
    </div>
  @endunless

  {{-- ======= RIWAYAT PERUBAHAN (HISTORY) ======= --}}
  <div class="rounded-xl border bg-white">
    <div class="flex items-center justify-between border-b px-4 py-3">
      <h3 class="font-semibold">Riwayat Perubahan</h3>
      @if($histories->isNotEmpty())
        <div class="text-xs text-gray-500">Menampilkan {{ $histories->count() }} entri terbaru</div>
      @endif
    </div>

    <div class="p-4 overflow-x-auto">
      @if($histories->isEmpty())
        <div class="text-sm text-gray-500">Belum ada histori untuk aset ini.</div>
      @else
        <table class="min-w-full text-sm">
          <thead class="bg-gray-50">
            <tr>
              <th class="text-left px-3 py-2">Waktu</th>
              <th class="text-left px-3 py-2">Aksi</th>
              <th class="text-left px-3 py-2">Perubahan</th>
              <th class="text-left px-3 py-2">Catatan</th>
              <th class="text-left px-3 py-2">Edited By</th>
            </tr>
          </thead>
          <tbody>
            @foreach($histories as $h)
              @php
                $changes = is_array($h->changes_json ?? null) ? $h->changes_json : [];
                $lines   = [];
                foreach ($changes as $k => $pair) {
                    $from = $pair['from'] ?? null;
                    $to   = $pair['to'] ?? null;
                    $lines[] = e($k).': '.e($from ?? '—').' → '.e($to ?? '—');
                }
              @endphp
              <tr class="border-t align-top">
                <td class="px-3 py-2 whitespace-nowrap">
                  {{ optional($h->created_at)->timezone('Asia/Jakarta')->format('d M Y, H:i') }}
                </td>
                <td class="px-3 py-2">
                  <span class="inline-flex items-center px-2 py-0.5 rounded text-xs
                    @if($h->action==='upgrade') bg-blue-100 text-blue-700
                    @elseif($h->action==='repair') bg-emerald-100 text-emerald-700
                    @elseif($h->action==='delete') bg-rose-100 text-rose-700
                    @else bg-gray-100 text-gray-700 @endif">
                    {{ strtoupper($h->action) }}
                  </span>
                </td>
                <td class="px-3 py-2">
                  @if(!empty($lines))
                    @foreach($lines as $line)
                      <div class="mb-0.5">{{ $line }}</div>
                    @endforeach
                  @else
                    <span class="text-gray-500">—</span>
                  @endif
                </td>
                <td class="px-3 py-2">{{ $h->note ? e($h->note) : '—' }}</td>
                <td class="px-3 py-2">{{ $h->edited_by ? e($h->edited_by) : '—' }}</td>
              </tr>
            @endforeach
          </tbody>
        </table>
      @endif
    </div>
  </div>
</div>
