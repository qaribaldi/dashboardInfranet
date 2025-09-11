@extends('layouts.app')

@section('title','Inventory - Labkom')

@section('content')

@php
  // fallback supaya tidak undefined kalau controller belum mengirim
  $q        = $q        ?? request('q','');
  $lab      = $lab      ?? request('lab','');
  $labList  = $labList  ?? [];
  $extraCols= $extraCols?? [];

  // daftar kolom "bawaan" yang TIDAK mau dianggap dinamis
  $baseCols = [
    // kolom tetap yang tampil di tabel
    'id_pc','nama_lab','unit_kerja','user','merk',
    'processor','tipe_ram','ram_1','storage_1','operating_sistem',
    'tahun_pembelian','status',
    // kolom bawaan lain (jangan tampil sebagai dinamis)
    'jabatan','tipe_asset','socket_processor','motherboard',
    'jumlah_slot_ram','total_kapasitas_ram','ram_2',
    'tipe_storage_1','tipe_storage_2','tipe_storage_3',
    'storage_2','storage_3','vga','optical_drive','network_adapter',
    'power_suply','monitor','keyboard','mouse',
    'created_at','updated_at',
  ];

  // dynCols = benar-benar kolom tambahan user (extraCols minus baseCols)
  $dynCols  = array_values(array_diff($extraCols, $baseCols));
@endphp

{{-- HEADER --}}
<div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between mb-4">
  <h2 class="text-2xl font-bold">Inventory - Labkom</h2>

  <div class="flex items-center gap-3 w-full md:w-auto">
    {{-- Filter: Nama Lab --}}
    <form method="GET" id="labFilterForm" class="flex items-center gap-2">
      <label for="labSelect" class="text-sm font-medium">Nama Lab</label>
      <select id="labSelect" name="lab"
              class="border border-gray-300 rounded-lg px-3 py-2 w-48 text-sm"
              onchange="document.getElementById('labFilterForm').submit()">
        <option value="">Semua</option>
        @foreach(($labList ?? []) as $labName)
          <option value="{{ $labName }}" {{ ($lab ?? '')===$labName ? 'selected' : '' }}>
            {{ $labName }}
          </option>
        @endforeach
      </select>
      {{-- ikutkan query pencarian saat ganti dropdown --}}
      <input type="hidden" name="q" value="{{ $q ?? '' }}">
    </form>

    {{-- Search bar --}}
    <form method="GET" class="flex items-center gap-2">
      <input type="text" name="q" value="{{ $q ?? '' }}" placeholder="Cari…"
             class="w-64 rounded-lg border border-gray-300 px-3 py-2" />
      @if(!empty($q) || !empty($lab))
        <a href="{{ route('inventory.labkom.index') }}" class="text-sm text-gray-600 hover:underline">Reset</a>
      @endif
    </form>

    {{-- Kelola kolom --}}
    @can('inventory.labkom.columns')
      <button type="button" id="btnAddCol"
              class="inline-flex items-center rounded-lg bg-blue-600 text-white px-4 py-2 hover:bg-blue-700">
        Kelola Kolom
      </button>
    @endcan

    {{-- Tambah --}}
    @can('inventory.labkom.create')
      <a href="{{ route('inventory.labkom.create') }}"
         class="inline-flex items-center rounded-lg bg-blue-600 text-white px-4 py-2 hover:bg-blue-700">+ Tambah</a>
    @endcan

    {{-- Import CSV --}}
    @can('inventory.labkom.import')
      <a href="{{ route('inventory.labkom.importForm') }}"
         class="inline-flex items-center rounded-lg border px-3 py-2 text-sm hover:bg-gray-50">Import CSV</a>
    @endcan
  </div>
</div>

<div class="bg-white rounded-xl border border-gray-200 overflow-x-auto">
  @php
    function splitBrandType(?string $text): array {
      $t = trim((string)$text);
      if ($t === '') return ['-','-'];
      $parts = preg_split('/\s+/', $t, 2);
      return [$parts[0] ?? $t, $parts[1] ?? '-'];
    }
    function guessRamBrand($row): string {
      $t = trim((string)($row->ram_1 ?? ''));
      if ($t === '') return '-';
      $first = strtok($t, ' ');
      return preg_match('/^\d/', $first) ? '-' : $first;
    }
    $titleize = fn($s) => ucwords(str_replace('_',' ', $s));
  @endphp

  <table class="min-w-full text-sm">
    <thead class="bg-gray-50">
      <tr>
        <th class="text-left px-4 py-3 font-semibold">ID PC</th>
        <th class="text-left px-4 py-3 font-semibold">Nama Lab</th>
        <th class="text-left px-4 py-3 font-semibold">Unit Kerja</th>
        <th class="text-left px-4 py-3 font-semibold">User</th>
        <th class="text-left px-4 py-3 font-semibold">Merk</th>
        <th class="text-left px-4 py-3 font-semibold">Processor (Merk)</th>
        <th class="text-left px-4 py-3 font-semibold">Processor (Tipe)</th>
        <th class="text-left px-4 py-3 font-semibold">RAM (Merk)</th>
        <th class="text-left px-4 py-3 font-semibold">RAM (Tipe)</th>
        <th class="text-left px-4 py-3 font-semibold">Storage 1</th>
        <th class="text-left px-4 py-3 font-semibold">OS</th>
        <th class="text-left px-4 py-3 font-semibold">Tahun</th>
        <th class="text-left px-4 py-3 font-semibold">Status</th>
        @foreach($dynCols as $col)
          <th class="text-left px-4 py-3 font-semibold">{{ $titleize($col) }}</th>
        @endforeach
        <th class="px-4 py-3"></th>
      </tr>
    </thead>
    <tbody>
      @forelse($items as $row)
        @php
          [$cpuBrand, $cpuType] = splitBrandType($row->processor);
          $ramBrand = guessRamBrand($row);
          $ramType  = $row->tipe_ram ?: '-';
        @endphp
        <tr class="border-t">
          <td class="px-4 py-2">{{ $row->id_pc }}</td>
          <td class="px-4 py-2">{{ $row->nama_lab }}</td>
          <td class="px-4 py-2">{{ $row->unit_kerja }}</td>
          <td class="px-4 py-2">{{ $row->user }}</td>
          <td class="px-4 py-2">{{ $row->merk }}</td>
          <td class="px-4 py-2">{{ $cpuBrand }}</td>
          <td class="px-4 py-2">{{ $cpuType }}</td>
          <td class="px-4 py-2">{{ $ramBrand }}</td>
          <td class="px-4 py-2">{{ $ramType }}</td>
          <td class="px-4 py-2">{{ $row->storage_1 }}</td>
          <td class="px-4 py-2">{{ $row->operating_sistem }}</td>
          <td class="px-4 py-2 whitespace-nowrap">{{ $row->tahun_pembelian }}</td>
          <td class="px-4 py-2 whitespace-nowrap">{{ $row->status }}</td>
          @foreach($dynCols as $col)
            <td class="px-4 py-2">{{ data_get($row, $col) ?? '—' }}</td>
          @endforeach
          <td class="px-4 py-2 text-right whitespace-nowrap">
            <a href="javascript:void(0)"
               onclick="openModal('{{ route('inventory.labkom.show',$row->id_pc) }}','Detail Labkom - {{ $row->id_pc }}')"
               class="mr-2 inline-flex items-center rounded border px-3 py-1.5 hover:bg-gray-50">Detail</a>

            @can('inventory.labkom.edit')
              <a href="{{ route('inventory.labkom.edit',$row->id_pc) }}"
                 class="mr-2 inline-flex items-center rounded border px-3 py-1.5 hover:bg-gray-50">Edit</a>
            @endcan

            @can('inventory.labkom.delete')
              <form action="{{ route('inventory.labkom.destroy',$row->id_pc) }}" method="POST" class="inline"
                    onsubmit="return confirm('Hapus data ini?')">
                @csrf @method('DELETE')
                <button class="inline-flex items-center rounded border border-red-300 text-red-700 px-3 py-1.5 hover:bg-red-50">
                  Hapus
                </button>
              </form>
            @endcan
          </td>
        </tr>
      @empty
        <tr><td colspan="{{ 14 + count($dynCols) }}" class="px-4 py-6 text-center text-gray-500">Belum ada data.</td></tr>
      @endforelse
    </tbody>
  </table>
</div>

{{-- ===== MODAL KELOLA KOLOM (khusus Labkom) ===== --}}
@can('inventory.labkom.columns')
  <div id="addColModal" class="fixed inset-0 hidden items-center justify-center z-[70]">
    <div id="addColBackdrop" class="absolute inset-0 bg-black/40"></div>

    @php
      $table = 'inventory_labkom';
      // lindungi SEMUA kolom bawaan agar tidak bisa di-rename/drop
      $stdProtected = $baseCols; // pakai array yang sama dari atas
      $all          = \Illuminate\Support\Facades\Schema::getColumnListing($table);
      $editableCols = array_values(array_diff($all, $stdProtected));
      $lbl          = fn($s) => ucwords(str_replace('_',' ', $s));
      $colRoutes    = [
        'add'    => route('inventory.labkom.columns.add'),
        'rename' => route('inventory.labkom.columns.rename'),
        'drop'   => route('inventory.labkom.columns.drop'),
      ];
    @endphp

    <div class="relative bg-white rounded-xl shadow-xl w-[95vw] max-w-xl">
      <div class="flex items-center justify-between px-4 py-3 border-b">
        <div class="font-semibold">Kelola Kolom</div>
        <button type="button" id="addColClose" class="rounded p-1 hover:bg-gray-100">✕</button>
      </div>

      <div class="px-4 pt-3">
        <div class="flex gap-2 text-sm mb-3">
          <button class="tabBtn px-3 py-1.5 rounded border bg-gray-100" data-tab="tabAdd">Tambah</button>
          <button class="tabBtn px-3 py-1.5 rounded border" data-tab="tabRename">Ubah Nama</button>
          <button class="tabBtn px-3 py-1.5 rounded border" data-tab="tabDrop">Hapus</button>
        </div>
      </div>

      <div class="p-4 space-y-5">
        {{-- ADD --}}
        <form id="tabAdd" class="tabPanel" method="POST" action="{{ $colRoutes['add'] }}">
          @csrf
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium mb-1">Nama Kolom Baru</label>
              <input name="name" type="text" required pattern="[A-Za-z][A-Za-z0-9_]*"
                     class="w-full rounded-lg border px-3 py-2" placeholder="mis: lokasi_aset">
              <p class="text-xs text-gray-500 mt-1">Huruf/angka/underscore, tidak diawali angka.</p>
            </div>
            <div>
              <label class="block text-sm font-medium mb-1">Tipe Data</label>
              <select name="type" class="w-full rounded-lg border px-3 py-2">
                <option value="string">STRING (varchar)</option>
                <option value="text">TEXT</option>
                <option value="integer">INTEGER</option>
                <option value="boolean">BOOLEAN</option>
                <option value="date">DATE</option>
                <option value="datetime">DATETIME</option>
              </select>
              <label class="mt-2 inline-flex items-center gap-2 text-sm">
                <input type="checkbox" name="nullable" value="1" checked> Nullable
              </label>
            </div>
          </div>
          <div class="pt-3 flex items-center justify-end">
            <button class="rounded-lg bg-indigo-600 text-white px-4 py-2 hover:bg-indigo-700">Simpan</button>
          </div>
        </form>

        {{-- RENAME --}}
        <form id="tabRename" class="tabPanel hidden" method="POST" action="{{ $colRoutes['rename'] }}">
          @csrf
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium mb-1">Pilih Kolom</label>
              <select name="from" class="w-full rounded-lg border px-3 py-2" {{ empty($editableCols) ? 'disabled' : '' }}>
                @forelse($editableCols as $c)
                  <option value="{{ $c }}">{{ $lbl($c) }}</option>
                @empty
                  <option value="">(Tidak ada kolom dinamis)</option>
                @endforelse
              </select>
            </div>
            <div>
              <label class="block text-sm font-medium mb-1">Nama Baru</label>
              <input name="to" type="text" required pattern="[A-Za-z][A-Za-z0-9_]*"
                     class="w-full rounded-lg border px-3 py-2" placeholder="mis: lokasi_penyimpanan"
                     {{ empty($editableCols) ? 'disabled' : '' }}>
            </div>
          </div>
          <p class="text-xs text-gray-500">Hanya kolom dinamis yang bisa diubah.</p>
          <div class="pt-3 flex items-center justify-end">
            <button class="rounded-lg bg-indigo-600 text-white px-4 py-2 hover:bg-indigo-700" {{ empty($editableCols) ? 'disabled' : '' }}>Ubah</button>
          </div>
        </form>

        {{-- DROP --}}
        <form id="tabDrop" class="tabPanel hidden" method="POST" action="{{ $colRoutes['drop'] }}"
              onsubmit="return confirm('Hapus kolom ini? Data pada kolom tersebut akan hilang.')">
          @csrf @method('DELETE')
          <div>
            <label class="block text-sm font-medium mb-1">Pilih Kolom</label>
            <select name="name" class="w-full rounded-lg border px-3 py-2" {{ empty($editableCols) ? 'disabled' : '' }}>
              @forelse($editableCols as $c)
                <option value="{{ $c }}">{{ $lbl($c) }}</option>
              @empty
                <option value="">(Tidak ada kolom dinamis)</option>
              @endforelse
            </select>
          </div>
          <p class="text-xs text-gray-500 mt-2">Hanya kolom dinamis yang bisa dihapus.</p>
          <div class="pt-3 flex items-center justify-end">
            <button class="rounded-lg bg-red-600 text-white px-4 py-2 hover:bg-red-700" {{ empty($editableCols) ? 'disabled' : '' }}>Hapus</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <script>
    (function(){
      const modal    = document.getElementById('addColModal');
      const openBtn  = document.getElementById('btnAddCol');
      const closeBtn = document.getElementById('addColClose');
      const backdrop = document.getElementById('addColBackdrop');

      function open(){ modal?.classList.remove('hidden'); modal?.classList.add('flex'); document.body.style.overflow='hidden'; }
      function close(){ modal?.classList.add('hidden'); modal?.classList.remove('flex'); document.body.style.overflow=''; }

      openBtn?.addEventListener('click', open);
      closeBtn?.addEventListener('click', close);
      backdrop?.addEventListener('click', close);
      document.addEventListener('keydown', e => { if(e.key==='Escape') close(); });

      const tabs = document.querySelectorAll('.tabBtn');
      const panels = document.querySelectorAll('.tabPanel');
      tabs.forEach(btn => btn.addEventListener('click', (ev) => {
        ev.preventDefault();
        tabs.forEach(b => b.classList.remove('bg-gray-100'));
        btn.classList.add('bg-gray-100');
        const id = btn.dataset.tab;
        panels.forEach(p => p.classList.toggle('hidden', p.id !== id));
      }));
    })();
  </script>
@endcan

<div class="mt-4">
  {{ $items->links() }}
</div>
@endsection
