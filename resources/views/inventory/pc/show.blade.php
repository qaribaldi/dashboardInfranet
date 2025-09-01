@extends('layouts.app')

@section('content')
  <div class="flex items-center justify-between mb-6">
    <h2 class="text-2xl font-bold">Detail Aset PC - {{ $data->id_pc }}</h2>
    <div class="flex gap-2">
      <a href="{{ route('inventory.pc.edit',$data->id_pc) }}"
         class="rounded border border-gray-200 px-4 py-2 hover:bg-gray-50">Edit</a>
      <a href="{{ route('inventory.pc.index') }}"
         class="rounded border border-gray-200 px-4 py-2 hover:bg-gray-50">Kembali</a>
    </div>
  </div>

  <div class="bg-white rounded-xl border border-gray-200 p-5">
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
  </div>
@endsection
