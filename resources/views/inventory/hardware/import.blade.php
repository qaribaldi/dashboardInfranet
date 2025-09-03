@extends('layouts.app')

@section('content')
  <h2 class="text-2xl font-bold mb-4">Import CSV - Inventory Hardware</h2>

  <div class="mb-4">
    <a href="{{ route('inventory.hardware.template') }}"
       class="inline-flex items-center rounded-lg border px-3 py-2 hover:bg-gray-50">
      Download Template CSV
    </a>
  </div>

  @if(session('error'))
    <div class="mb-3 rounded-lg border border-red-200 bg-red-50 px-4 py-2 text-red-800">
      {!! nl2br(e(session('error'))) !!}
    </div>
  @endif

  <form action="{{ route('inventory.hardware.importStore') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
    @csrf

    <div>
      <label class="block text-sm font-medium mb-1">File CSV</label>
      <input type="file" name="csv" accept=".csv,.txt" required
             class="w-full rounded-lg border px-3 py-2">
      <p class="text-xs text-gray-500 mt-1">
        Format: CSV delimiter koma <code>,</code> atau semicolon <code>;</code>. Baris pertama = header.
      </p>
      @error('csv') <div class="text-sm text-red-600 mt-1">{{ $message }}</div> @enderror
    </div>

    <div>
      <label class="block text-sm font-medium mb-1">Mode Import</label>
      <select name="mode" class="rounded-lg border px-3 py-2">
        <option value="upsert">Upsert (insert baru + update jika id_hardware sudah ada)</option>
        <option value="insert_only">Insert Only (lewati jika id_hardware sudah ada)</option>
      </select>
      <p class="text-xs text-gray-500 mt-1">
        <strong>Upsert:</strong> tambah+update jika <code>id_hardware</code> sama.
        <br><strong>Insert Only:</strong> hanya tambah baru.
      </p>
    </div>

    <div>
      <label class="inline-flex items-center gap-2 text-sm">
        <input type="checkbox" name="auto_add_columns" value="1" class="rounded" />
        Tambah kolom baru otomatis
      </label>
      <p class="text-xs text-gray-500 mt-1">
        Bila header CSV belum ada di database, kolom akan dibuat (string, nullable).
      </p>
    </div>

    <div class="pt-2 flex items-center gap-3">
      <button class="rounded-lg bg-blue-600 text-white px-4 py-2 hover:bg-blue-700">Import</button>
      <a href="{{ route('inventory.hardware.index') }}" class="rounded-lg border px-4 py-2 hover:bg-gray-50">Kembali</a>
    </div>
  </form>
@endsection
