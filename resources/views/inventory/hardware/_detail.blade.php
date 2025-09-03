@php
  use Illuminate\Support\Facades\Schema;

  $std = [
    'id_hardware','jenis_hardware','vendor','tanggal_pembelian',
    'jumlah_stock','status','tanggal_digunakan','id_pc',
  ];

  $allCols = array_values(array_diff(
      Schema::getColumnListing('inventory_hardware'),
      ['created_at','updated_at']
  ));

  $ordered = array_values(array_unique(array_merge($std, $allCols)));

  $labels = [];
  foreach ($ordered as $c) $labels[$c] = ucwords(str_replace('_',' ', $c));

  $display = function($val) {
      if (is_null($val) || $val === '') return '—';
      if (is_bool($val)) return $val ? 'Ya' : 'Tidak';
      return $val;
  };
@endphp

<div class="space-y-4">
  <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
    @foreach($ordered as $col)
      <div class="rounded-lg border p-3 bg-white shadow-sm">
        <div class="text-[11px] uppercase tracking-wide text-gray-500">{{ $labels[$col] }}</div>
        <div class="mt-1 text-sm break-words">{{ $display(data_get($data, $col)) }}</div>
      </div>
    @endforeach
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
