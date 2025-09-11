@extends('layouts.app')

@section('content')
  <div class="flex items-center justify-between mb-6">
    <h2 class="text-2xl font-bold">Detail Labkom - {{ $data->id_pc }}</h2>
    <div class="flex gap-2">
      <a href="{{ route('inventory.labkom.edit',$data->id_pc) }}" class="rounded border px-4 py-2 hover:bg-gray-50">Edit</a>
      <a href="{{ route('inventory.labkom.index') }}" class="rounded border px-4 py-2 hover:bg-gray-50">Kembali</a>
    </div>
  </div>

  <div class="bg-white rounded-xl border border-gray-200 p-5">
    @include('inventory.labkom._detail', ['data' => $data])
  </div>
@endsection
