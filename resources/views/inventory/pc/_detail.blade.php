{{-- resources/views/inventory/pc/_detail.blade.php --}}
@php
    use Illuminate\Support\Facades\Schema;

    // Ambil daftar kolom dari controller jika ada; kalau tidak, baca dari skema DB
    $allCols = isset($cols) && is_array($cols)
        ? $cols
        : array_values(array_diff(Schema::getColumnListing('asset_pc'), ['created_at','updated_at']));

    // Urutkan kolom: standar dulu, sisanya (kolom tambahan) di belakang
    $std = [
        'id_pc','unit_kerja','user','jabatan','ruang','tipe_asset','merk',
        'processor','socket_processor','motherboard','jumlah_slot_ram',
        'total_kapasitas_ram','tipe_ram','ram_1','ram_2',
        'tipe_storage_1','storage_1','tipe_storage_2','storage_2', 'tipe_storage_3', 'storage_3',
        'vga','optical_drive','network_adapter','power_suply',
        'operating_sistem','monitor','keyboard','mouse','tahun_pembelian',
    ];
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
  {{-- Grid responsif: 1 → 2 kolom (md) --}}
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

  {{-- Tombol aksi disembunyikan bila readonly=1 (dipakai saat load via modal) --}}
  @unless(request('readonly') == '1')
    <div class="flex justify-end gap-2">
      <a href="{{ route('inventory.pc.edit', $data->id_pc) }}"
         class="rounded-lg border border-gray-200 px-4 py-2 hover:bg-gray-50">
        Edit
      </a>
      <form action="{{ route('inventory.pc.destroy', $data->id_pc) }}" method="POST"
            onsubmit="return confirm('Hapus data ini?')">
        @csrf @method('DELETE')
        <button class="rounded-lg border border-red-300 text-red-700 px-4 py-2 hover:bg-red-50">
          Hapus
        </button>
      </form>
    </div>
  @endunless
</div>
