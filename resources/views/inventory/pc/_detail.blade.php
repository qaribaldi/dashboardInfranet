<div class="space-y-4">
  {{-- Grid responsif: 1 → 2 kolom (md) --}}
  <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
    @foreach((new \App\Models\AssetPc)->getFillable() as $f)
      <div class="rounded-xl border border-gray-200 bg-white p-3 shadow-sm">
        <div class="text-[11px] uppercase tracking-wide text-gray-500">
          {{ str_replace('_',' ', $f) }}
        </div>
        <div class="mt-1 text-sm break-words">
          {{ $data->{$f} ?: '—' }}
        </div>
      </div>
    @endforeach
  </div>

  {{-- Tombol aksi disembunyikan bila readonly=1 (modal) --}}
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
