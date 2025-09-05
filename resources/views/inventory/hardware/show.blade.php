@extends('layouts.app')

@section('content')
  <div class="flex items-center justify-between mb-6">
    <h2 class="text-2xl font-bold">Detail Hardware - {{ $data->id_hardware }}</h2>
    <div class="flex gap-2">
      <a href="{{ route('inventory.hardware.edit',$data->id_hardware) }}" class="rounded border px-4 py-2 hover:bg-gray-50">Edit</a>
      <a href="{{ route('inventory.hardware.index') }}" class="rounded border px-4 py-2 hover:bg-gray-50">Kembali</a>
    </div>
  </div>

  <div class="bg-white rounded-xl border border-gray-200 p-5 space-y-6">
    @php
      $isStorage = ($data->jenis_hardware === 'storage');

      $fields = [
        'id_hardware'       => 'Id Hardware',
        'jenis_hardware'    => 'Jenis Hardware',
        'vendor'            => 'Vendor',
        'tanggal_pembelian' => 'Tanggal Pembelian',
        'jumlah_stock'      => 'Jumlah Stock',
        'status'            => 'Status',
      ];

      if ($isStorage) {
        $fields['storage_type'] = 'Storage Type';
        $fields['specs']        = 'Specs';
      }

      $fmt = function($v) {
        if (is_null($v) || $v==='') return '—';
        if ($v instanceof \Carbon\Carbon) return $v->format('Y-m-d');
        return $v;
      };
    @endphp

    {{-- MASTER --}}
    <dl class="grid grid-cols-1 md:grid-cols-2 gap-4">
      @foreach($fields as $key => $label)
        <div>
          <dt class="text-xs uppercase text-gray-500">{{ $label }}</dt>
          <dd class="text-sm">{{ $fmt($data->{$key} ?? null) }}</dd>
        </div>
      @endforeach
    </dl>

    {{-- PIVOT: DAFTAR PC --}}
    <div>
      <div class="text-xs uppercase text-gray-500 mb-2">Pemasangan (PC)</div>
      @php
        $pcs = $data->relationLoaded('pcs') ? $data->pcs : $data->pcs()->get();
      @endphp

      @if($pcs->isEmpty())
        <div class="text-sm text-gray-500">Belum ada PC terpasang.</div>
      @else
        <div class="overflow-x-auto">
          <table class="min-w-full text-sm border rounded">
            <thead class="bg-gray-50">
              <tr>
                <th class="text-left px-3 py-2">ID PC</th>
                <th class="text-left px-3 py-2">Tanggal Digunakan</th>
              </tr>
            </thead>
            <tbody>
              @foreach($pcs as $pc)
                <tr class="border-t">
                  <td class="px-3 py-2 font-medium">{{ $pc->id_pc }}</td>
                  <td class="px-3 py-2">
                    {{ $pc->pivot->tanggal_digunakan
                        ? \Illuminate\Support\Carbon::parse($pc->pivot->tanggal_digunakan)->format('Y-m-d')
                        : '—' }}
                  </td>
                </tr>
              @endforeach
            </tbody>
          </table>
        </div>
      @endif
    </div>
  </div>
@endsection
