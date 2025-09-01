@extends('layouts.app')

@section('content')
  <h2 class="text-2xl font-bold mb-6">{{ $mode === 'create' ? 'Tambah' : 'Edit' }} Printer</h2>

  <form action="{{ $mode === 'create' ? route('inventory.printer.store') : route('inventory.printer.update',$data->id_printer) }}" method="POST" class="space-y-5">
    @csrf
    @if($mode === 'edit') @method('PUT') @endif

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
      @foreach($fields as $name => $label)
        <div>
          <label class="block text-sm font-medium mb-1" for="{{ $name }}">{{ $label }}</label>
          <input id="{{ $name }}" name="{{ $name }}" value="{{ old($name, $data->{$name}) }}"
                 class="w-full rounded-lg border border-gray-300 px-3 py-2" />
          @error($name) <div class="text-sm text-red-600 mt-1">{{ $message }}</div> @enderror
        </div>
      @endforeach
    </div>

     {{-- Catatan histori hanya muncul saat edit --}}
    @if($mode === 'edit')
      <div>
        <label class="block text-sm font-medium mb-1" for="catatan_histori">Catatan Histori</label>
        <textarea id="catatan_histori" name="catatan_histori" 
                  class="w-full rounded-lg border border-gray-300 px-3 py-2" 
                  rows="3" 
                  placeholder="Misal: Upgrade RAM dari 8GB ke 16GB, ganti SSD, perbaikan PSU, dll.">
          {{ old('catatan_histori') }}
        </textarea>
      </div>
    @endif

    <div class="flex items-center gap-3">
      <button class="rounded-lg bg-blue-600 text-white px-4 py-2 hover:bg-blue-700">
        {{ $mode === 'create' ? 'Simpan' : 'Update' }}
      </button>
      <a href="{{ route('inventory.printer.index') }}" class="rounded-lg border px-4 py-2 hover:bg-gray-50">Batal</a>
    </div>
  </form>
@endsection
