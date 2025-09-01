@extends('layouts.app')

@section('content')
  <div class="flex items-center justify-between mb-6">
    <h2 class="text-2xl font-bold">Inventory - AC</h2>

    <div class="flex items-center gap-3">
      {{-- Dropdown jenis aset --}}
      <form class="flex items-center gap-2">
        <label class="text-sm font-medium">Jenis Aset</label>
        <select class="border border-gray-300 rounded-lg px-3 py-2"
                onchange="window.location.href=this.value">
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
          <a href="{{ route('inventory.ac.index') }}"
             class="text-sm text-gray-600 hover:underline">Reset</a>
        @endif
      </form>

      {{-- Tombol + Kolom (admin) --}}
      @auth
        @if(auth()->user()->role === 'admin')
          <button type="button" id="btnAddCol"
                  class="inline-flex items-center rounded-lg bg-blue-600 text-white px-4 py-2 hover:bg-blue-700">
            + Kolom
          </button>
        @endif
      @endauth

      {{-- Tombol + Tambah (admin) --}}
      @auth
        @if(auth()->user()->role === 'admin')
          <a href="{{ route('inventory.ac.create') }}"
             class="inline-flex items-center rounded-lg bg-blue-600 text-white px-4 py-2 hover:bg-blue-700">
            + Tambah
          </a>
        @endif
      @endauth

      {{-- Tombol Import CSV (admin) --}}
      @auth
        @if(auth()->user()->role === 'admin')
          <a href="{{ route('inventory.ac.importForm') }}"
             class="inline-flex items-center rounded-lg border px-3 py-2 text-sm hover:bg-gray-50">
            Import CSV
          </a>
        @endif
      @endauth
    </div>
  </div>

  <div class="bg-white rounded-xl border border-gray-200 overflow-x-auto">
    @php
      use Illuminate\Support\Facades\Schema;

      // Ambil SEMUA kolom dari tabel asset_ac sesuai urutan DB
      $cols = Schema::getColumnListing('asset_ac');

      // Sembunyikan kolom tertentu
      $hide = ['tipe_asset', 'keterangan', 'created_at', 'updated_at'];
      $cols = array_values(array_diff($cols, $hide));

      // Helper judul kolom
      $titleize = fn($s) => ucwords(str_replace('_',' ', $s));

      // Kolom untuk baris kosong
      $totalCols = count($cols) + 1; // +1 untuk kolom Aksi
    @endphp

    <table class="min-w-full text-sm">
      <thead class="bg-gray-50">
        <tr>
          @foreach($cols as $c)
            <th class="text-left px-4 py-3 font-semibold">{{ $titleize($c) }}</th>
          @endforeach
          <th class="px-4 py-3"></th> {{-- Aksi --}}
        </tr>
      </thead>
      <tbody>
        @forelse($items as $row)
          <tr class="border-t">
            @foreach($cols as $c)
              <td class="px-4 py-2">{{ data_get($row, $c) ?? '—' }}</td>
            @endforeach

            {{-- Aksi selalu paling kanan --}}
            <td class="px-4 py-2 text-right whitespace-nowrap">
              <a href="javascript:void(0)"
                 onclick="openModal('{{ route('inventory.ac.show', $row->id_ac) }}','Detail AC - {{ $row->id_ac }}')"
                 class="mr-2 inline-flex items-center rounded border px-3 py-1.5 hover:bg-gray-50">
                Detail
              </a>

              @auth
                @if(auth()->user()->role === 'admin')
                  <a href="{{ route('inventory.ac.edit',$row->id_ac) }}"
                     class="mr-2 inline-flex items-center rounded border px-3 py-1.5 hover:bg-gray-50">
                    Edit
                  </a>
                  <form action="{{ route('inventory.ac.destroy',$row->id_ac) }}" method="POST" class="inline"
                        onsubmit="return confirm('Hapus data ini?')">
                    @csrf @method('DELETE')
                    <button class="inline-flex items-center rounded border border-red-300 text-red-700 px-3 py-1.5 hover:bg-red-50">
                      Hapus
                    </button>
                  </form>
                @endif
              @endauth
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="{{ $totalCols }}" class="px-4 py-6 text-center text-gray-500">
              Belum ada data.
            </td>
          </tr>
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
          <form method="POST" action="{{ route('inventory.ac.columns.add') }}" class="p-4 space-y-3">
            @csrf
            <div>
              <label class="block text-sm font-medium mb-1">Nama Kolom</label>
              <input name="name" type="text" required
                     class="w-full rounded-lg border px-3 py-2"
                     placeholder="nama_kolom_baru">
              <p class="text-xs text-gray-500 mt-1">Gunakan huruf/angka/underscore</p>
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
          const modal    = document.getElementById('addColModal');
          const openBtn  = document.getElementById('btnAddCol');
          const closeBtn = document.getElementById('addColClose');
          const cancelBtn= document.getElementById('addColCancel');
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

  <div class="mt-4">
    {{ $items->links() }}
  </div>
@endsection
