@extends('layouts.app')

@section('content')
  <h2 class="text-2xl font-bold mb-6">
    {{ $mode === 'create' ? 'Tambah' : 'Edit' }} Aset AC
  </h2>

  <form action="{{ $mode === 'create' 
                    ? route('inventory.ac.store') 
                    : route('inventory.ac.update',$data->id_ac) }}" 
        method="POST" 
        class="space-y-5">
    @csrf
    @if($mode === 'edit') @method('PUT') @endif

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
      @foreach($fields as $name => $label)
        <div>
          <label class="block text-sm font-medium mb-1" for="{{ $name }}">{{ $label }}</label>
          <input id="{{ $name }}" name="{{ $name }}" 
                 value="{{ old($name, $data->{$name}) }}"
                 class="w-full rounded-lg border border-gray-300 px-3 py-2"
                 {{ $name==='id_ac' && $mode==='edit' ? 'readonly' : '' }}/>
          @error($name) 
            <div class="text-sm text-red-600 mt-1">{{ $message }}</div> 
          @enderror
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
                  placeholder="Misal: Ganti remote, servis kompresor, tambah freon, dll.">
          {{ old('catatan_histori') }}
        </textarea>
      </div>
    @endif

    <div class="flex items-center gap-3">
      <button class="rounded-lg bg-blue-600 text-white px-4 py-2 hover:bg-blue-700">
        {{ $mode === 'create' ? 'Simpan' : 'Update' }}
      </button>
      <a href="{{ route('inventory.ac.index') }}" 
         class="rounded-lg border px-4 py-2 hover:bg-gray-50">Batal</a>
    </div>
  </form>
@endsection
