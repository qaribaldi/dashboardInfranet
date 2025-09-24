@extends('layouts.app')

@section('content')
  <div class="flex items-center justify-between mb-6">
    <h2 class="text-2xl font-bold">Detail Aset PC - {{ $data->id_pc }}</h2>
    <div class="flex gap-2">
      <a href="{{ route('inventory.pc.edit',$data->id_pc) }}"
         class="rounded border border-gray-200 px-4 py-2 hover:bg-gray-50">Edit</a>
      <a href="{{ route('inventory.pc.index') }}"
         class="rounded border border-gray-200 px-4 py-2 hover:bg-gray-50">Kembali</a>
    </div>
  </div>

  {{-- ====== DATA UTAMA ====== --}}
  <div class="bg-white rounded-xl border border-gray-200 p-5">
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
      @php
        // Jika model tidak mendefinisikan $fillable, fallback ke seluruh kolom tabel (kecuali timestamps)
        $fillable = (new \App\Models\AssetPc)->getFillable();
        if (empty($fillable)) {
          $fillable = array_values(array_diff(\Schema::getColumnListing('asset_pc'), ['created_at','updated_at']));
        }
        // Label rapi
        $labels = [];
        foreach ($fillable as $f) { $labels[$f] = ucwords(str_replace('_',' ', $f)); }
        $display = function($val) {
          if (is_null($val) || $val === '') return '—';
          if (is_bool($val)) return $val ? 'Ya' : 'Tidak';
          return $val;
        };
      @endphp

      @foreach($fillable as $f)
        <div class="rounded-xl border border-gray-200 bg-white p-3 shadow-sm">
          <div class="text-[11px] uppercase tracking-wide text-gray-500">
            {{ $labels[$f] ?? $f }}
          </div>
          <div class="mt-1 text-sm break-words">
            {{ $display(data_get($data, $f)) }}
          </div>
        </div>
      @endforeach
    </div>
  </div>

  {{-- ====== RIWAYAT PERUBAHAN (HISTORY) ====== --}}
  @php
    // Controller sebaiknya sudah mengirim $histories; kalau tidak ada, biar tidak error, jadikan koleksi kosong
    $histories = isset($histories) ? collect($histories) : collect();
  @endphp

  <div class="rounded-xl border bg-white mt-6">
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
@endsection
