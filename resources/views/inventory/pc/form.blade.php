@extends('layouts.app')

@section('content')
  <h2 class="text-2xl font-bold mb-6">
    {{ $mode === 'create' ? 'Tambah' : 'Edit' }} Aset PC
  </h2>

  <form
    action="{{ $mode === 'create'
        ? route('inventory.pc.store')
        : route('inventory.pc.update', $data->id_pc) }}"
    method="POST"
    class="space-y-5"
  >
    @csrf
    @if($mode === 'edit') @method('PUT') @endif

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
@php
  // daftar kolom bertipe tanggal & datetime dari controller
  $dateCols     = $dateCols     ?? [];
  $datetimeCols = $datetimeCols ?? [];

  // opsi status baru + mapping nilai lama -> baru
  $statusOps = $statusOptions ?? ['In use','In store','Service'];
  $currentStatus = old('status', $data->status ?? '');
  $mapOldToNew = ['available'=>'In store','in_use'=>'In use','broken'=>'Service'];
  if (isset($mapOldToNew[$currentStatus])) $currentStatus = $mapOldToNew[$currentStatus];

  // helper kecil untuk format nilai input
  $fmtVal = function($name, $value) use ($dateCols, $datetimeCols) {
      $v = old($name, $value);
      if ($v === null || $v === '') return '';
      // jika date => YYYY-MM-DD
      if (in_array($name, $dateCols, true)) {
          // ambil 10 char awal (YYYY-MM-DD)
          return substr((string)$v, 0, 10);
      }
      // jika datetime => YYYY-MM-DDTHH:MM
      if (in_array($name, $datetimeCols, true)) {
          $s = substr((string)$v, 0, 16);      // "YYYY-MM-DD HH:MM"
          return str_replace(' ', 'T', $s);    // -> "YYYY-MM-DDTHH:MM"
      }
      return (string)$v;
  };
@endphp

@foreach($fields as $name => $label)
  @if($name === 'status')
    <div>
      <label class="block text-sm font-medium mb-1" for="status">Status</label>
      <select id="status" name="status" class="w-full rounded-lg border border-gray-300 px-3 py-2">
        @foreach($statusOps as $opt)
          <option value="{{ $opt }}" {{ $currentStatus === $opt ? 'selected' : '' }}>
            {{ $opt }}
          </option>
        @endforeach
      </select>
      @error('status') <div class="text-sm text-red-600 mt-1">{{ $message }}</div> @enderror
    </div>
  @else
    <div>
      <label class="block text-sm font-medium mb-1" for="{{ $name }}">{{ $label }}</label>

      @if(in_array($name, $dateCols, true))
        <input type="date" id="{{ $name }}" name="{{ $name }}"
               value="{{ $fmtVal($name, $data->{$name}) }}"
               class="w-full rounded-lg border border-gray-300 px-3 py-2" />
      @elseif(in_array($name, $datetimeCols, true))
        <input type="datetime-local" id="{{ $name }}" name="{{ $name }}"
               value="{{ $fmtVal($name, $data->{$name}) }}"
               class="w-full rounded-lg border border-gray-300 px-3 py-2" />
      @else
        <input id="{{ $name }}" name="{{ $name }}"
               value="{{ $fmtVal($name, $data->{$name}) }}"
               class="w-full rounded-lg border border-gray-300 px-3 py-2" />
      @endif

      @error($name) <div class="text-sm text-red-600 mt-1">{{ $message }}</div> @enderror
    </div>
  @endif
@endforeach
    </div>

    @if($mode === 'edit')
      <div>
        <label class="block text-sm font-medium mb-1" for="catatan_histori">Catatan Histori</label>
        <textarea id="catatan_histori" name="catatan_histori"
                  class="w-full rounded-lg border border-gray-300 px-3 py-2"
                  rows="3"
                  placeholder="Misal: Upgrade RAM dari 8GB ke 16GB, ganti SSD, perbaikan PSU, dll.">{{ old('catatan_histori') }}</textarea>
      </div>
    @endif

    <hr class="my-6">

    <div class="flex items-center gap-3">
      <button class="rounded-lg bg-blue-600 text-white px-4 py-2 hover:bg-blue-700">
        {{ $mode === 'create' ? 'Simpan' : 'Update' }}
      </button>
      <a href="{{ route('inventory.pc.index') }}" class="rounded-lg border px-4 py-2 hover:bg-gray-50">Batal</a>
    </div>
  </form>
@endsection
