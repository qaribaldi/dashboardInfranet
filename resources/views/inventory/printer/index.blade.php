@extends('layouts.app')

@section('content')
  <div class="flex items-center justify-between mb-6">
    <h2 class="text-2xl font-bold">Inventory - Printer</h2>

    <div class="flex items-center gap-3">
      {{-- Dropdown jenis aset --}}
      <form class="flex items-center gap-2">
        <label class="text-sm font-medium">Jenis Aset</label>
        <select class="border border-gray-300 rounded-lg px-3 py-2"
                onchange="window.location.href=this.value">
          <option value="{{ route('inventory.pc.index') }}">PC</option>
          <option value="{{ route('inventory.printer.index') }}" selected>Printer</option>
          <option value="{{ route('inventory.proyektor.index') }}">Proyektor</option>
          <option value="{{ route('inventory.ac.index') }}">AC</option>
        </select>
      </form>

      {{-- Search bar --}}
      <form method="GET" action="{{ route('inventory.printer.index') }}" class="flex items-center gap-2">
        <input type="text" name="q" value="{{ $q ?? '' }}" placeholder="Cari…"
               class="w-64 rounded-lg border border-gray-300 px-3 py-2" />
        @if(!empty($q))
          <a href="{{ route('inventory.printer.index') }}" class="text-sm text-gray-600 hover:underline">Reset</a>
        @endif
      </form>

      {{-- Tombol-tombol (admin) --}}
      @auth
        @if(auth()->user()->role === 'admin')
          {{-- TRIGGER modal +Kolom --}}
          <button type="button" id="btnAddCol"
                  class="inline-flex items-center rounded-lg bg-blue-600 text-white px-4 py-2 hover:bg-blue-700">
            Kelola Kolom
          </button>

          {{-- + Tambah --}}
          <a href="{{ route('inventory.printer.create') }}"
             class="inline-flex items-center rounded-lg bg-blue-600 text-white px-4 py-2 hover:bg-blue-700">
            + Tambah
          </a>

          {{-- Import CSV --}}
          <a href="{{ route('inventory.printer.importForm') }}"
             class="inline-flex items-center rounded-lg border px-3 py-2 text-sm hover:bg-gray-50">
            Import CSV
          </a>
        @endif
      @endauth
    </div>
  </div>

  {{-- ====== MODAL KELOLA KOLOM (TAB: Tambah/Rename/Hapus) ====== --}}
@auth
  @if(auth()->user()->role === 'admin')
    <div id="addColModal" class="fixed inset-0 hidden items-center justify-center z-[70]">
      <div id="addColBackdrop" class="absolute inset-0 bg-black/40"></div>

      @php
        // ========= KONFIG: SESUAIKAN UNTUK HALAMAN INI =========
        // Halaman ini: PRINTER
        $table = 'asset_printer';
        // Kolom standar yang TIDAK boleh di-rename/drop
        $std = [
          'id_printer','unit_kerja','user','ruang',
          'jenis_printer','merk','tipe','status_warna',
          'kondisi','tahun_pembelian',
          // kolom yang memang disembunyikan tapi standar tabel
          'jabatan','scanner','tinta','keterangan_tambahan',
        ];
        $protected = array_merge($std, ['created_at','updated_at']);

        // Ambil kolom nyata dari DB, sisakan hanya yang boleh dikelola (dinamis)
        $allCols = \Illuminate\Support\Facades\Schema::getColumnListing($table);
        $editableCols = array_values(array_diff($allCols, $protected));
        // helper label rapi
        $lbl = fn($s) => ucwords(str_replace('_',' ', $s));

        // ROUTE untuk aksi
        $colRoutes = [
          'add'    => route('inventory.printer.columns.add'),
          'rename' => route('inventory.printer.columns.rename'),
          'drop'   => route('inventory.printer.columns.drop'),
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
          {{-- TAB: TAMBAH --}}
          <form id="tabAdd" class="tabPanel" method="POST" action="{{ $colRoutes['add'] }}">
            @csrf
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <label class="block text-sm font-medium mb-1">Nama Kolom Baru</label>
                <input name="name" type="text" required pattern="[A-Za-z][A-Za-z0-9_]*"
                       class="w-full rounded-lg border px-3 py-2" placeholder="mis: lokasi_rak">
                <p class="text-xs text-gray-500 mt-1">Huruf/angka/underscore, tidak boleh diawali angka.</p>
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

          {{-- TAB: RENAME (pakai dropdown kolom dinamis) --}}
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
                <label class="block text-sm font-medium mb-1">Nama Kolom Baru</label>
                <input name="to" type="text" required pattern="[A-Za-z][A-Za-z0-9_]*"
                       class="w-full rounded-lg border px-3 py-2" placeholder="mis: lokasi_penyimpanan"
                       {{ empty($editableCols) ? 'disabled' : '' }}>
              </div>
            </div>
            <p class="text-xs text-gray-500">Hanya kolom dinamis yang bisa diubah.</p>
            <div class="pt-3 flex items-center justify-end">
              <button class="rounded-lg bg-indigo-600 text-white px-4 py-2 hover:bg-indigo-700" {{ empty($editableCols) ? 'disabled' : '' }}>
                Ubah
              </button>
            </div>
          </form>

          {{-- TAB: DROP (pakai dropdown kolom dinamis) --}}
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
              <button class="rounded-lg bg-red-600 text-white px-4 py-2 hover:bg-red-700" {{ empty($editableCols) ? 'disabled' : '' }}>
                Hapus
              </button>
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

        function open(){ modal.classList.remove('hidden'); modal.classList.add('flex'); document.body.style.overflow='hidden'; }
        function close(){ modal.classList.add('hidden'); modal.classList.remove('flex'); document.body.style.overflow=''; }

        openBtn?.addEventListener('click', open);
        closeBtn?.addEventListener('click', close);
        backdrop?.addEventListener('click', close);
        document.addEventListener('keydown', e => { if(e.key==='Escape') close(); });

        // Tabs
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
  @endif
@endauth


  <div class="bg-white rounded-xl border border-gray-200 overflow-x-auto">
    @php
      use Illuminate\Support\Facades\Schema;
      $cols = Schema::getColumnListing('asset_printer');
      $hide = ['jabatan','scanner','tinta','keterangan_tambahan','created_at','updated_at'];
      $cols = array_values(array_diff($cols, $hide));
      $titleize = fn($s) => ucwords(str_replace('_',' ', $s));
      $totalCols = count($cols) + 1;
    @endphp

    <table class="min-w-full text-sm">
      <thead class="bg-gray-50">
        <tr>
          @foreach($cols as $c)
            <th class="text-left px-4 py-3 font-semibold">{{ $titleize($c) }}</th>
          @endforeach
          <th class="px-4 py-3"></th>
        </tr>
      </thead>
      <tbody>
        @forelse($items as $row)
          <tr class="border-t">
            @foreach($cols as $c)
              <td class="px-4 py-2">{{ data_get($row, $c) ?? '—' }}</td>
            @endforeach
            <td class="px-4 py-2 text-right whitespace-nowrap">
              <a href="javascript:void(0)"
                 onclick="openModal('{{ route('inventory.printer.show',$row->id_printer) }}','Detail Printer - {{ $row->id_printer }}')"
                 class="mr-2 inline-flex items-center rounded border px-3 py-1.5 hover:bg-gray-50">Detail</a>
              @auth
                @if(auth()->user()->role === 'admin')
                  <a href="{{ route('inventory.printer.edit',$row->id_printer) }}"
                     class="mr-2 inline-flex items-center rounded border px-3 py-1.5 hover:bg-gray-50">Edit</a>
                  <form action="{{ route('inventory.printer.destroy',$row->id_printer) }}" method="POST" class="inline"
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
            <td colspan="{{ $totalCols }}" class="px-4 py-6 text-center text-gray-500">Belum ada data.</td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </div>

  <div class="mt-4">
    {{ $items->links() }}
  </div>
@endsection
