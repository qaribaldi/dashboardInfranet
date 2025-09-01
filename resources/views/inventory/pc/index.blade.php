@extends('layouts.app')

@section('content')

  {{-- HEADER --}}
<div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between mb-4">
  <h2 class="text-2xl font-bold">Inventory - PC</h2>

  <div class="flex items-center gap-3 w-full md:w-auto">
    {{-- Jenis Aset --}}
    <label class="text-sm font-medium">Jenis Aset</label>
    <select class="border border-gray-300 rounded-lg px-3 py-2 w-32 text-sm"
            onchange="window.location.href=this.value">
      <option value="{{ route('inventory.pc.index') }}" selected>PC</option>
      <option value="{{ route('inventory.printer.index') }}">Printer</option>
      <option value="{{ route('inventory.proyektor.index') }}">Proyektor</option>
      <option value="{{ route('inventory.ac.index') }}">AC</option>
    </select>

    {{-- Search bar --}}
      <form method="GET" class="flex items-center gap-2">
        <input type="text" name="q" value="{{ $q ?? '' }}" placeholder="Cari…"
              class="w-64 rounded-lg border border-gray-300 px-3 py-2" />
        @if(!empty($q))
          <a href="{{ route('inventory.pc.index') }}" class="text-sm text-gray-600 hover:underline">Reset</a>
        @endif
      </form>

    {{-- Toggle Filter Panel --}}
    <button type="button" id="btnToggleFilter"
            class="rounded-lg border px-3 py-2 text-sm hover:bg-gray-50">
      Filter
    </button>

    @auth
  @if(auth()->user()->role === 'admin')
    <button type="button" id="btnAddCol"
      class="inline-flex items-center rounded-lg bg-blue-600 text-white px-4 py-2 hover:bg-blue-700">+ Kolom</button>
  @endif
@endauth

    @if(auth()->user()->role === 'admin')
    <a href="{{ route('inventory.pc.create') }}"
       class="inline-flex items-center rounded-lg bg-blue-600 text-white px-4 py-2 hover:bg-blue-700">+ Tambah</a>
    @endif

    <a href="{{ route('inventory.pc.importForm') }}"
   class="inline-flex items-center rounded-lg border px-3 py-2 text-sm hover:bg-gray-50">
  Import CSV
</a>

  </div>
</div>



{{-- FILTER CHIPS (muncul hanya jika ada filter aktif) --}}
@php
  $hasFilter = ($proc ?? '')!=='' || ($ram ?? '')!=='' || ($sto ?? '')!==''; 
@endphp
@if($hasFilter)
  <div class="mb-3 flex flex-wrap items-center gap-2">
    <span class="text-sm text-gray-500 mr-1">Aktif:</span>

    @if(($proc ?? '')!=='')
      <a href="{{ route('inventory.pc.index', array_filter(['q'=>$q??'', 'ram'=>$ram??'', 'sto'=>$sto??''])) }}"
         class="inline-flex items-center gap-1 rounded-full border px-2 py-0.5 text-sm">
        Processor: {{ $proc }} <span class="text-gray-400">✕</span>
      </a>
    @endif
    @if(($ram ?? '')!=='')
      <a href="{{ route('inventory.pc.index', array_filter(['q'=>$q??'', 'proc'=>$proc??'', 'sto'=>$sto??''])) }}"
         class="inline-flex items-center gap-1 rounded-full border px-2 py-0.5 text-sm">
        RAM: {{ $ram }} <span class="text-gray-400">✕</span>
      </a>
    @endif
    @if(($sto ?? '')!=='')
      <a href="{{ route('inventory.pc.index', array_filter(['q'=>$q??'', 'proc'=>$proc??'', 'ram'=>$ram??''])) }}"
         class="inline-flex items-center gap-1 rounded-full border px-2 py-0.5 text-sm">
        Storage: {{ $sto }} <span class="text-gray-400">✕</span>
      </a>
    @endif

    <a href="{{ route('inventory.pc.index', array_filter(['q'=>$q??''])) }}"
       class="ml-2 text-sm text-blue-600 hover:underline">Reset semua</a>
  </div>
@endif

{{-- FILTER PANEL (collapsible) --}}
<div id="filterPanel" class="hidden mb-4 rounded-xl border bg-white p-3">
  <form method="GET" action="{{ route('inventory.pc.index') }}">
    {{-- pertahankan keyword search ketika apply filter --}}
    <input type="hidden" name="q" value="{{ $q ?? '' }}">

    <div class="flex flex-wrap items-end gap-3">
      <div>
        <label class="block text-sm font-medium mb-1">Processor</label>
        <select name="proc" class="rounded-lg border border-gray-300 px-2 py-1.5 w-48 text-sm">
          <option value="">Semua</option>
          @foreach($processors as $p)
            <option value="{{ $p }}" {{ ($proc ?? '')===$p ? 'selected' : '' }}>{{ $p }}</option>
          @endforeach
        </select>
      </div>

      <div>
        <label class="block text-sm font-medium mb-1">Total RAM</label>
        <select name="ram" class="rounded-lg border border-gray-300 px-2 py-1.5 w-40 text-sm">
          <option value="">Semua</option>
          @foreach($rams as $r)
            <option value="{{ $r }}" {{ ($ram ?? '')===$r ? 'selected' : '' }}>{{ $r }}</option>
          @endforeach
        </select>
      </div>

      <div>
        <label class="block text-sm font-medium mb-1">Storage</label>
        <select name="sto" class="rounded-lg border border-gray-300 px-2 py-1.5 w-48 text-sm">
          <option value="">Semua</option>
          @foreach($storages as $s)
            <option value="{{ $s }}" {{ ($sto ?? '')===$s ? 'selected' : '' }}>{{ $s }}</option>
          @endforeach
        </select>
      </div>

      <div class="flex gap-2">
        <button class="rounded-lg bg-blue-600 text-white px-3 py-2 text-sm hover:bg-blue-700">Terapkan</button>
        <a href="{{ route('inventory.pc.index', array_filter(['q'=>$q??''])) }}"
           class="rounded-lg border px-3 py-2 text-sm hover:bg-gray-50">Reset</a>
      </div>
    </div>
  </form>
</div>

<script>
  const fp = document.getElementById('filterPanel');
  const btn = document.getElementById('btnToggleFilter');
  btn?.addEventListener('click', () => fp.classList.toggle('hidden'));
</script>

  <div class="bg-white rounded-xl border border-gray-200 overflow-x-auto">
    @php
  // Pecah "Intel Core i3-3240 ..." -> ["Intel", "Core i3-3240 ..."]
  function splitBrandType(?string $text): array {
      $t = trim((string)$text);
      if ($t === '') return ['-','-'];
      $parts = preg_split('/\s+/', $t, 2);
      return [$parts[0] ?? $t, $parts[1] ?? '-'];
  }

  // Coba tebak merk RAM dari ram_1 bila ada (sering kosong; fallback "-")
  function guessRamBrand($row): string {
      $t = trim((string)($row->ram_1 ?? ''));
      if ($t === '') return '-';
      $first = strtok($t, ' ');
      // jika token pertama berawalan angka (mis. "8GB"), itu bukan merk
      return preg_match('/^\d/', $first) ? '-' : $first;
  }

  // kolom dinamis yang mau disembunyikan
  $hideDyn = ['tipe_storage_3'];

  // hasil saringan kolom dinamis
  $dynCols = array_values(array_diff(($extraCols ?? []), $hideDyn));

  // helper judul kolom
  $titleize = fn($s) => ucwords(str_replace('_',' ', $s));
@endphp

    <table class="min-w-full text-sm">
      <thead class="bg-gray-50">
  <tr>
    <th class="text-left px-4 py-3 font-semibold">ID PC</th>
    <th class="text-left px-4 py-3 font-semibold">Unit Kerja</th>
    <th class="text-left px-4 py-3 font-semibold">User</th>
    <th class="text-left px-4 py-3 font-semibold">Ruang</th>
    <th class="text-left px-4 py-3 font-semibold">Merk</th>

    <!-- Processor dipecah -->
    <th class="text-left px-4 py-3 font-semibold">Processor (Merk)</th>
    <th class="text-left px-4 py-3 font-semibold">Processor (Tipe)</th>

    <!-- Total RAM -> RAM, dipecah -->
    <th class="text-left px-4 py-3 font-semibold">RAM (Merk)</th>
    <th class="text-left px-4 py-3 font-semibold">RAM (Tipe)</th>

    <th class="text-left px-4 py-3 font-semibold">Storage 1</th>
    <th class="text-left px-4 py-3 font-semibold">OS</th>
    <th class="text-left px-4 py-3 font-semibold">Tahun</th>

     {{-- Kolom dinamis --}}
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
            <td class="px-4 py-2">{{ $row->unit_kerja }}</td>
            <td class="px-4 py-2">{{ $row->user }}</td>
            <td class="px-4 py-2">{{ $row->ruang }}</td>
            <td class="px-4 py-2">{{ $row->merk }}</td>

            <!-- Processor pecah -->
            <td class="px-4 py-2">{{ $cpuBrand }}</td>
            <td class="px-4 py-2">{{ $cpuType }}</td>

            <!-- RAM pecah -->
            <td class="px-4 py-2">{{ $ramBrand }}</td>
            <td class="px-4 py-2">{{ $ramType }}</td>

            <td class="px-4 py-2">{{ $row->storage_1 }}</td>
            <td class="px-4 py-2">{{ $row->operating_sistem }}</td>
            <td class="px-4 py-2 whitespace-nowrap">{{ $row->tahun_pembelian }}</td>

            {{-- ===== Nilai kolom dinamis (sebelum Aksi) ===== --}}
@foreach($dynCols as $col)
  <td class="px-4 py-2">
    {{ data_get($row, $col) ?? '—' }}
  </td>
@endforeach

            

            <td class="px-4 py-2 text-right whitespace-nowrap">
  <a href="javascript:void(0)"
   onclick="openModal('{{ route('inventory.pc.show',$row->id_pc) }}','Detail PC - {{ $row->id_pc }}')"
   class="mr-2 inline-flex items-center rounded border px-3 py-1.5 hover:bg-gray-50">Detail</a>
              @if(auth()->user()->role === 'admin')
              <a href="{{ route('inventory.pc.edit',$row->id_pc) }}"
                 class="mr-2 inline-flex items-center rounded border px-3 py-1.5 hover:bg-gray-50">Edit</a>
              <form action="{{ route('inventory.pc.destroy',$row->id_pc) }}" method="POST" class="inline"
                    onsubmit="return confirm('Hapus data ini?')">
                @csrf @method('DELETE')
                <button class="inline-flex items-center rounded border border-red-300 text-red-700 px-3 py-1.5 hover:bg-red-50">Hapus</button>
              </form>
              @endif
            </td>
          </tr>
        @empty
          <tr><td colspan="12" class="px-4 py-6 text-center text-gray-500">Belum ada data.</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>

  {{-- Modal Tambah Kolom (Admin) --}}
@auth
  @if(auth()->user()->role === 'admin')
    <div id="addColModal" class="fixed inset-0 hidden items-center justify-center z-[70]">
      <div id="addColBackdrop" class="absolute inset-0 bg-black/40"></div>
      <div class="relative bg-white rounded-xl shadow-xl w-[95vw] max-w-md">
        <div class="flex items-center justify-between px-4 py-3 border-b">
          <div class="font-semibold">Tambah Kolom Baru</div>
          <button type="button" id="addColClose" class="rounded p-1 hover:bg-gray-100">✕</button>
        </div>
        <form method="POST" action="{{ route('inventory.pc.columns.add') }}" class="p-4 space-y-3">
          @csrf
          <div>
            <label class="block text-sm font-medium mb-1">Nama Kolom</label>
            <input name="name" type="text" required
              class="w-full rounded-lg border px-3 py-2"
              placeholder="nama_kolom_baru">
            <p class="text-xs text-gray-500 mt-1">
              Gunakan huruf/angka/underscore
            </p>
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
          </div>

          <label class="inline-flex items-center gap-2 text-sm">
            <input type="checkbox" name="nullable" value="1" checked> Nullable
          </label>

          <div class="pt-2 flex items-center justify-end gap-2">
            <button type="button" id="addColCancel" class="rounded-lg border px-4 py-2 hover:bg-gray-50">Batal</button>
            <button class="rounded-lg bg-indigo-600 text-white px-4 py-2 hover:bg-indigo-700">Simpan</button>
          </div>
        </form>
      </div>
    </div>

    <script>
      (function(){
        const modal = document.getElementById('addColModal');
        const openBtn = document.getElementById('btnAddCol');
        const closeBtn = document.getElementById('addColClose');
        const cancelBtn = document.getElementById('addColCancel');
        const backdrop = document.getElementById('addColBackdrop');

        function open(){ modal.classList.remove('hidden'); modal.classList.add('flex'); document.body.style.overflow='hidden'; }
        function close(){ modal.classList.add('hidden'); modal.classList.remove('flex'); document.body.style.overflow=''; }

        openBtn?.addEventListener('click', open);
        closeBtn?.addEventListener('click', close);
        cancelBtn?.addEventListener('click', close);
        backdrop?.addEventListener('click', close);
        document.addEventListener('keydown', e => { if(e.key==='Escape') close(); });
      })();
    </script>
  @endif
@endauth

@if(session('success'))
  <div class="mb-3 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-2 text-emerald-800">
    {{ session('success') }}
  </div>
@endif


  <div class="mt-4">
    {{ $items->links() }}
  </div>
@endsection
