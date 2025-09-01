@php
    use Illuminate\Support\Facades\Schema;

    $std = [
        'id_ac','unit_kerja','ruang','merk','remote','tahun_pembelian',
    ];

    // Ambil semua kolom nyata dari tabel
    $allCols = array_values(array_diff(
        Schema::getColumnListing('asset_ac'),
        ['created_at','updated_at']
    ));

    // Urutkan: standar dulu, lalu kolom tambahan
    $extra   = array_values(array_diff($allCols, $std));
    $ordered = array_values(array_unique(array_merge($std, $allCols))); // pastikan semua kolom nyata ikut

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
      <a href="{{ route('inventory.ac.edit', $data->id_ac) }}"
         class="rounded-lg border px-4 py-2 hover:bg-gray-50">
        Edit
      </a>
      <form action="{{ route('inventory.ac.destroy', $data->id_ac) }}"
            method="POST" onsubmit="return confirm('Hapus data ini?')">
        @csrf @method('DELETE')
        <button class="rounded-lg border border-red-300 text-red-700 px-4 py-2 hover:bg-red-50">
          Hapus
        </button>
      </form>
    </div>
  @endunless
</div>
