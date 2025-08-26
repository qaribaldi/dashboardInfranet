@extends('layouts.app')

@section('content')
  <div class="flex items-center justify-between mb-6">
    <h2 class="text-2xl font-bold">Inventory - AC</h2>
    <div class="flex items-center gap-3">
      {{-- Dropdown jenis aset --}}
      <form class="flex items-center gap-2">
        <label class="text-sm font-medium">Jenis Aset</label>
        <select class="border border-gray-300 rounded-lg px-3 py-2" onchange="window.location.href=this.value">
          <option value="{{ route('inventory.pc.index') }}">PC</option>
          <option value="{{ route('inventory.printer.index') }}">Printer</option>
          <option value="{{ route('inventory.proyektor.index') }}">Proyektor</option>
          <option value="{{ route('inventory.ac.index') }}" selected>AC</option>
        </select>
      </form>

      {{-- Search bar --}}
      <form method="GET" action="{{ route('inventory.ac.index') }}" class="flex items-center gap-2">
        <input type="text" name="q" value="{{ $q ?? '' }}" placeholder="Cari…"
              class="w-64 rounded-lg border border-gray-300 px-3 py-2" />
        @if(!empty($q))
          <a href="{{ route('inventory.ac.index') }}" class="text-sm text-gray-600 hover:underline">Reset</a>
        @endif
      </form>

      {{-- Tombol tambah --}}
      @if(auth()->user()->role === 'admin')
      <a href="{{ route('inventory.ac.create') }}"
        class="inline-flex items-center rounded-lg bg-blue-600 text-white px-4 py-2 hover:bg-blue-700">Tambah (+)</a>
      @endif
    </div>
  </div>

  <div class="bg-white rounded-xl border border-gray-200 overflow-x-auto">
    <table class="min-w-full text-sm">
      <thead class="bg-gray-50">
        <tr>
          <th class="text-left px-4 py-3 font-semibold">ID AC</th>
          <th class="text-left px-4 py-3 font-semibold">Unit Kerja</th>
          <th class="text-left px-4 py-3 font-semibold">Ruang</th>
          <th class="text-left px-4 py-3 font-semibold">Merk</th>
          <th class="text-left px-4 py-3 font-semibold">Ukuran PK</th>
          <th class="text-left px-4 py-3 font-semibold">Kondisi</th>
          <th class="text-left px-4 py-3 font-semibold">Remote</th>
          <th class="text-left px-4 py-3 font-semibold">Tahun</th>
          <th class="px-4 py-3"></th>
        </tr>
      </thead>
      <tbody>
        @forelse($items as $row)
          <tr class="border-t">
            <td class="px-4 py-2">{{ $row->id_ac }}</td>
            <td class="px-4 py-2">{{ $row->unit_kerja }}</td>
            <td class="px-4 py-2">{{ $row->ruang }}</td>
            <td class="px-4 py-2">{{ $row->merk }}</td>
            <td class="px-4 py-2">{{ $row->ukuran_pk }}</td>
            <td class="px-4 py-2">{{ $row->kondisi }}</td>
            <td class="px-4 py-2">{{ $row->remote }}</td>
            <td class="px-4 py-2 whitespace-nowrap">{{ $row->tahun_pembelian }}</td>
            <td class="px-4 py-2 text-right whitespace-nowrap">

              <a href="javascript:void(0)"
                 onclick="openModal('{{ route('inventory.ac.show', $row->id_ac) }}','Detail AC - {{ $row->id_ac }}')"
                 class="mr-2 inline-flex items-center rounded border px-3 py-1.5 hover:bg-gray-50">Detail</a>

              @if(auth()->user()->role === 'admin')
              <a href="{{ route('inventory.ac.edit',$row->id_ac) }}"
                 class="mr-2 inline-flex items-center rounded border px-3 py-1.5 hover:bg-gray-50">Edit</a>

              <form action="{{ route('inventory.ac.destroy',$row->id_ac) }}" method="POST" class="inline"
                    onsubmit="return confirm('Hapus data ini?')">
                @csrf @method('DELETE')
                <button class="inline-flex items-center rounded border border-red-300 text-red-700 px-3 py-1.5 hover:bg-red-50">Hapus</button>
              </form>
              @endif
            </td>
          </tr>
        @empty
          <tr><td colspan="9" class="px-4 py-6 text-center text-gray-500">Belum ada data.</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>

  <div class="mt-4">
    {{ $items->links() }}
  </div>
@endsection
