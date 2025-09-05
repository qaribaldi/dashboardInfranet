@php
  use Illuminate\Support\Facades\Schema;

  $isStorage = ($data->jenis_hardware === 'storage');

  // Kolom standar yang selalu ditampilkan dari tabel master
  $std = [
    'id_hardware','jenis_hardware','vendor','tanggal_pembelian',
    'jumlah_stock','status',
  ];

  // Tambahkan kolom khusus storage bila perlu
  if ($isStorage) {
    $std[] = 'storage_type';
    $std[] = 'specs';
  }

  // Ambil semua kolom (kecuali timestamps & kolom lama pivot)
  $allCols = array_values(array_diff(
      Schema::getColumnListing('inventory_hardware'),
      ['created_at','updated_at','id_pc','tanggal_digunakan']
  ));

  // Susun urutan: standar dulu lalu sisanya (dengan filter kalau bukan storage)
  $ordered = collect(array_merge($std, $allCols))
              ->unique()
              ->filter(function($c) use ($isStorage) {
                if (!$isStorage && in_array($c, ['storage_type','specs'])) return false;
                return true;
              })
              ->values()
              ->all();

  $labels = [];
  foreach ($ordered as $c) {
    $labels[$c] = ucwords(str_replace('_',' ', $c));
  }

  $display = function($val) {
      if (is_null($val) || $val === '') return '—';
      if ($val instanceof \Carbon\Carbon) return $val->format('Y-m-d');
      if (is_bool($val)) return $val ? 'Ya' : 'Tidak';
      return $val;
  };
@endphp

<div class="space-y-6">
  {{-- MASTER FIELDS --}}
  <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
    @foreach($ordered as $col)
      <div class="rounded-lg border p-3 bg-white shadow-sm">
        <div class="text-[11px] uppercase tracking-wide text-gray-500">{{ $labels[$col] }}</div>
        <div class="mt-1 text-sm break-words">{{ $display(data_get($data, $col)) }}</div>
      </div>
    @endforeach
  </div>

  {{-- PIVOT: DAFTAR PC & TANGGAL DIGUNAKAN --}}
  <div class="rounded-lg border p-4 bg-white shadow-sm">
    <div class="text-[11px] uppercase tracking-wide text-gray-500 mb-2">Pemasangan (PC)</div>

    @php
      $pcs = $data->relationLoaded('pcs') ? $data->pcs : $data->pcs()->get();
    @endphp

    @if($pcs->isEmpty())
      <div class="text-sm text-gray-500">Belum ada PC terpasang.</div>
    @else
      <div class="flex flex-wrap gap-2">
        @foreach($pcs as $pc)
          <span class="inline-flex items-center gap-2 rounded-full bg-gray-100 px-3 py-1">
            <span class="font-medium">{{ $pc->id_pc }}</span>
            @if($pc->pivot->tanggal_digunakan)
              <span class="text-xs text-gray-600">
                {{ \Illuminate\Support\Carbon::parse($pc->pivot->tanggal_digunakan)->format('Y-m-d') }}
              </span>
            @endif
          </span>
        @endforeach
      </div>
    @endif
  </div>

  @unless(request('readonly') == '1')
    <div class="flex justify-end gap-2">
      <a href="{{ route('inventory.hardware.edit', $data->id_hardware) }}"
         class="rounded-lg border px-4 py-2 hover:bg-gray-50">Edit</a>

      <form action="{{ route('inventory.hardware.destroy', $data->id_hardware) }}"
            method="POST" onsubmit="return confirm('Hapus data ini?')">
        @csrf @method('DELETE')
        <button class="rounded-lg border border-red-300 text-red-700 px-4 py-2 hover:bg-red-50">Hapus</button>
      </form>
    </div>
  @endunless
</div>
