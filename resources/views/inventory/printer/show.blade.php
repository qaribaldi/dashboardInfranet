@extends('layouts.app')

@section('content')
  <div class="flex items-center justify-between mb-6">
    <h2 class="text-2xl font-bold">Detail Printer - {{ $data->id_printer }}</h2>
    <div class="flex gap-2">
      <a href="{{ route('inventory.printer.edit',$data->id_printer) }}" class="rounded border px-4 py-2 hover:bg-gray-50">Edit</a>
      <a href="{{ route('inventory.printer.index') }}" class="rounded border px-4 py-2 hover:bg-gray-50">Kembali</a>
    </div>
  </div>

  <div class="bg-white rounded-xl border border-gray-200 p-5">
    <dl class="grid grid-cols-1 md:grid-cols-2 gap-4">
      @foreach((new \App\Models\AssetPrinter)->getFillable() as $f)
        <div>
          <dt class="text-xs uppercase text-gray-500">{{ str_replace('_',' ', $f) }}</dt>
          <dd class="text-sm">{{ $data->{$f} }}</dd>
        </div>
      @endforeach
    </dl>
  </div>
@endsection
