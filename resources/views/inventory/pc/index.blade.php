@extends('layouts.app')

@section('content')
  <div class="flex items-center justify-between mb-6">
    <h2 class="text-2xl font-bold">Inventory - PC</h2>
    <div class="flex items-center gap-3">
      <form class="flex items-center gap-2">
        <label class="text-sm font-medium">Jenis Aset</label>
        <select class="border border-gray-300 rounded-lg px-3 py-2"
                onchange="window.location.href=this.value">
          <option value="{{ route('pc.index') }}" selected>PC</option>
          <option value="{{ route('printer.index') }}">Printer</option>
          <option value="{{ route('proyektor.index') }}">Proyektor</option>
        </select>
      </form>
      <a href="{{ route('pc.create') }}"
         class="inline-flex items-center rounded-lg bg-blue-600 text-white px-4 py-2 hover:bg-blue-700">Tambah</a>
    </div>
  </div>

  <div class="bg-white rounded-xl border border-gray-200 overflow-x-auto">
    <table class="min-w-full text-sm">
      <thead class="bg-gray-50">
        <tr>
          <th class="text-left px-4 py-3 font-semibold">ID PC</th>
          <th class="text-left px-4 py-3 font-semibold">Unit Kerja</th>
          <th class="text-left px-4 py-3 font-semibold">User</th>
          <th class="text-left px-4 py-3 font-semibold">Ruang</th>
          <th class="text-left px-4 py-3 font-semibold">Merk</th>
          <th class="text-left px-4 py-3 font-semibold">Processor</th>
          <th class="text-left px-4 py-3 font-semibold">Total RAM</th>
          <th class="text-left px-4 py-3 font-semibold">Storage 1</th>
          <th class="text-left px-4 py-3 font-semibold">OS</th>
          <th class="text-left px-4 py-3 font-semibold">Tahun</th>
          <th class="px-4 py-3"></th>
        </tr>
      </thead>
      <tbody>
        @forelse($items as $row)
          <tr class="border-t">
            <td class="px-4 py-2">{{ $row->id_pc }}</td>
            <td class="px-4 py-2">{{ $row->unit_kerja }}</td>
            <td class="px-4 py-2">{{ $row->user }}</td>
            <td class="px-4 py-2">{{ $row->ruang }}</td>
            <td class="px-4 py-2">{{ $row->merk }}</td>
            <td class="px-4 py-2">{{ $row->processor }}</td>
            <td class="px-4 py-2">{{ $row->total_kapasitas_ram }}</td>
            <td class="px-4 py-2">{{ $row->storage_1 }}</td>
            <td class="px-4 py-2">{{ $row->operating_sistem }}</td>
            <td class="px-4 py-2 whitespace-nowrap">{{ $row->tahun_pembelian }}</td>
            <td class="px-4 py-2 text-right whitespace-nowrap">
  <a href="javascript:void(0)"
   onclick="openModal('{{ route('pc.show',$row->id_pc) }}','Detail PC - {{ $row->id_pc }}')"
   class="mr-2 inline-flex items-center rounded border px-3 py-1.5 hover:bg-gray-50">Detail</a>

              <a href="{{ route('pc.edit',$row->id_pc) }}"
                 class="mr-2 inline-flex items-center rounded border px-3 py-1.5 hover:bg-gray-50">Edit</a>
              <form action="{{ route('pc.destroy',$row->id_pc) }}" method="POST" class="inline"
                    onsubmit="return confirm('Hapus data ini?')">
                @csrf @method('DELETE')
                <button class="inline-flex items-center rounded border border-red-300 text-red-700 px-3 py-1.5 hover:bg-red-50">Hapus</button>
              </form>
            </td>
          </tr>
        @empty
          <tr><td colspan="11" class="px-4 py-6 text-center text-gray-500">Belum ada data.</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>

  <div class="mt-4">
    {{ $items->links() }}
  </div>
@endsection
