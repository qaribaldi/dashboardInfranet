@extends('layouts.app')

@section('content')
  <h2 class="text-2xl font-bold mb-6">{{ $mode === 'create' ? 'Tambah' : 'Edit' }} Proyektor</h2>

  <form action="{{ $mode === 'create' ? route('inventory.proyektor.store') : route('inventory.proyektor.update',$data->id_proyektor) }}" method="POST" class="space-y-5">
    @csrf
    @if($mode === 'edit') @method('PUT') @endif

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
@php
  $dateCols     = $dateCols     ?? [];
  $datetimeCols = $datetimeCols ?? [];

  $statusOps = $statusOptions ?? ['In use','In store','Service'];
  $currentStatus = old('status', $data->status ?? '');
  $mapOldToNew = ['available'=>'In store','in_use'=>'In use','broken'=>'Service'];
  if (isset($mapOldToNew[$currentStatus])) $currentStatus = $mapOldToNew[$currentStatus];

  $fmtVal = function($name, $value) use ($dateCols, $datetimeCols) {
      $v = old($name, $value);
      if ($v === null || $v === '') return '';
      if (in_array($name, $dateCols, true)) return substr((string)$v, 0, 10);
      if (in_array($name, $datetimeCols, true)) {
          $s = substr((string)$v, 0, 16);
          return str_replace(' ', 'T', $s);
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

    <div>
      <label class="block text-sm font-medium mb-1" for="catatan_histori">Catatan Histori</label>
      <textarea id="catatan_histori" name="catatan_histori" class="w-full rounded-lg border border-gray-300 px-3 py-2" rows="3" placeholder="Misal: ganti lamp, servis panel, dll.">{{ old('catatan_histori') }}</textarea>
    </div>

    <div class="flex items-center gap-3">
      <button class="rounded-lg bg-blue-600 text-white px-4 py-2 hover:bg-blue-700">
        {{ $mode === 'create' ? 'Simpan' : 'Update' }}
      </button>
      <a href="{{ route('inventory.proyektor.index') }}" class="rounded-lg border px-4 py-2 hover:bg-gray-50">Batal</a>
    </div>
  </form>
@endsection
