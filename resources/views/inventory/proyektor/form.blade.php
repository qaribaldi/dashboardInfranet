@extends('layouts.app')

@section('content')
  <h2 class="text-2xl font-bold mb-6">{{ $mode === 'create' ? 'Tambah' : 'Edit' }} Proyektor</h2>

  <form action="{{ $mode === 'create' ? route('inventory.proyektor.store') : route('inventory.proyektor.update',$data->id_proyektor) }}" method="POST" class="space-y-5">
    @csrf
    @if($mode === 'edit') @method('PUT') @endif

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
      @php
  $statusOps = ['available' => 'Available', 'in_use' => 'In Use', 'broken' => 'Broken'];
@endphp

@foreach($fields as $name => $label)
  @if($name === 'status')
    <div>
      <label class="block text-sm font-medium mb-1" for="status">Status</label>
      <select id="status" name="status" class="w-full rounded-lg border border-gray-300 px-3 py-2">
        @foreach($statusOps as $val => $text)
          <option value="{{ $val }}" {{ old('status', $data->status ?? 'in_use') === $val ? 'selected' : '' }}>
            {{ $text }}
          </option>
        @endforeach
      </select>
      @error('status') <div class="text-sm text-red-600 mt-1">{{ $message }}</div> @enderror
    </div>
  @else
    <div>
      <label class="block text-sm font-medium mb-1" for="{{ $name }}">{{ $label }}</label>
      <input id="{{ $name }}" name="{{ $name }}" value="{{ old($name, $data->{$name}) }}"
             class="w-full rounded-lg border border-gray-300 px-3 py-2" />
      @error($name) <div class="text-sm text-red-600 mt-1">{{ $message }}</div> @enderror
    </div>
  @endif
@endforeach

    </div>

     <div>
  <label class="block text-sm font-medium mb-1" for="catatan_histori">Catatan Histori</label>
  <textarea id="catatan_histori" name="catatan_histori" class="w-full rounded-lg border border-gray-300 px-3 py-2" rows="3" placeholder="Misal: Upgrade RAM dari 8GB ke 16GB, ganti SSD, perbaikan PSU, dll.">{{ old('catatan_histori') }}</textarea>
</div>

    <div class="flex items-center gap-3">
      <button class="rounded-lg bg-blue-600 text-white px-4 py-2 hover:bg-blue-700">
        {{ $mode === 'create' ? 'Simpan' : 'Update' }}
      </button>
      <a href="{{ route('inventory.proyektor.index') }}" class="rounded-lg border px-4 py-2 hover:bg-gray-50">Batal</a>
    </div>
  </form>
@endsection
