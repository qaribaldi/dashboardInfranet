<div class="space-y-4">
  <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
    @foreach((new \App\Models\AssetProyektor)->getFillable() as $f)
      <div class="rounded-lg border p-3">
        <div class="text-xs uppercase tracking-wide text-gray-500">{{ str_replace('_',' ', $f) }}</div>
        <div class="text-sm mt-0.5">{{ $data->{$f} }}</div>
      </div>
    @endforeach
  </div>

  @unless(request('readonly') == '1')
    <div class="flex justify-end gap-2">
      <a href="{{ route('proyektor.edit', $data->id_proyektor) }}" class="rounded-lg border px-4 py-2 hover:bg-gray-50">Edit</a>
      <form action="{{ route('proyektor.destroy', $data->id_proyektor) }}" method="POST" onsubmit="return confirm('Hapus data ini?')">
        @csrf @method('DELETE')
        <button class="rounded-lg border border-red-300 text-red-700 px-4 py-2 hover:bg-red-50">Hapus</button>
      </form>
    </div>
  @endunless
</div>
