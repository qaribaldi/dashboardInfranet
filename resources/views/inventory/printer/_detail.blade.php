{{-- resources/views/inventory/printer/_detail.blade.php --}}
@php
    use Illuminate\Support\Facades\Schema;

    // Kolom standar printer (sesuaikan dengan migrasi awalmu)
    $std = [
        'id_printer','unit_kerja','user','jabatan', 'ruang','jenis_printer', 'merk',
        'tipe','scanner', 'tinta', 'status_warna','kondisi',
        'tahun_pembelian', 'keterangan_tambahan',
    ];

    // Ambil semua kolom dari DB
    $allCols = array_values(array_diff(
        Schema::getColumnListing('asset_printer'),
        ['created_at','updated_at']
    ));

    // Bedakan kolom tambahan
    $extra = array_values(array_diff($allCols, $std));
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
@endphp

<div class="space-y-4">
  {{-- Grid responsif --}}
  <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
    @foreach($ordered as $col)
      <div class="rounded-lg border p-3 bg-white shadow-sm">
        <div class="text-[11px] uppercase tracking-wide text-gray-500">{{ $labels[$col] }}</div>
        <div class="mt-1 text-sm break-words">{{ $display(data_get($data, $col)) }}</div>
      </div>
    @endforeach
  </div>

  {{-- Tombol aksi (disembunyikan bila readonly) --}}
  @unless(request('readonly') == '1')
    <div class="flex justify-end gap-2">
      <a href="{{ route('inventory.printer.edit', $data->id_printer) }}"
         class="rounded-lg border px-4 py-2 hover:bg-gray-50">
        Edit
      </a>
      <form action="{{ route('inventory.printer.destroy', $data->id_printer) }}" method="POST"
            onsubmit="return confirm('Hapus data ini?')">
        @csrf @method('DELETE')
        <button class="rounded-lg border border-red-300 text-red-700 px-4 py-2 hover:bg-red-50">
          Hapus
        </button>
      </form>
    </div>
  @endunless
</div>
