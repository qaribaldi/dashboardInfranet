@extends('layouts.app')

@section('content')
  <div class="flex items-center justify-between mb-6">
    <h2 class="text-2xl font-bold">Inventory - Hardware</h2>

    <div class="flex items-center gap-3">
      @php
        $JENIS_LIST = [
          'processor'       => 'Processor',
          'ram'             => 'RAM',
          'storage'         => 'Storage',
          'vga'             => 'VGA',
          'monitor'         => 'Monitor',
          'motherboard'     => 'Motherboard',
          'fan_processor'   => 'Fan Processor',
          'network_adapter' => 'Network Adapter',
          'power_supply'    => 'Power Supply',
          'keyboard'        => 'Keyboard',
          'mouse'           => 'Mouse',
        ];
        $jenis        = request('jenis', '');
        $storage_type = request('storage_type', '');
        $q            = request('q', '');
      @endphp

      {{-- FILTER BAR (satu form) --}}
      <form method="GET" action="{{ route('inventory.hardware.index') }}" id="filterForm"
            class="flex flex-wrap items-end gap-3">
        <div>
          <label class="block text-sm font-medium mb-1">Jenis Hardware</label>
          <select name="jenis" id="jenisSelect" class="rounded-lg border border-gray-300 px-3 py-2">
            <option value="">Semua</option>
            @foreach($JENIS_LIST as $val => $label)
              <option value="{{ $val }}" {{ $jenis === $val ? 'selected' : '' }}>{{ $label }}</option>
            @endforeach
          </select>
        </div>

        <div id="storageWrap" class="{{ $jenis === 'storage' ? '' : 'hidden' }}">
          <label class="block text-sm font-medium mb-1">Tipe Storage</label>
          <select name="storage_type" id="storageType" class="rounded-lg border border-gray-300 px-3 py-2">
            <option value="">Semua</option>
            <option value="ssd" {{ $storage_type === 'ssd' ? 'selected' : '' }}>SSD</option>
            <option value="hdd" {{ $storage_type === 'hdd' ? 'selected' : '' }}>HDD</option>
          </select>
        </div>

        <div class="flex items-end gap-2">
          <div>
            <label class="block text-sm font-medium mb-1">Cari</label>
            <input type="text" name="q" value="{{ $q }}" placeholder="Cari…"
                   class="w-56 rounded-lg border border-gray-300 px-3 py-2" />
          </div>
          @if($q || $jenis || $storage_type)
            <div class="pb-1">
              <a href="{{ route('inventory.hardware.index') }}"
                 class="inline-flex items-center rounded-lg px-3 py-2 text-sm text-gray-600 hover:underline">Reset</a>
            </div>
          @endif
        </div>
      </form>

      {{-- Aksi sesuai IZIN (per-entity) --}}
      @can('inventory.hardware.columns')
        <button type="button" id="btnAddCol"
                class="inline-flex items-center rounded-lg bg-blue-600 text-white px-4 py-2 hover:bg-blue-700">
          Kelola Kolom
        </button>
      @endcan

      @can('inventory.hardware.create')
        <a href="{{ route('inventory.hardware.create') }}"
           class="inline-flex items-center rounded-lg bg-blue-600 text-white px-4 py-2 hover:bg-blue-700">+ Tambah</a>
      @endcan

      @can('inventory.hardware.import')
        <a href="{{ route('inventory.hardware.importForm') }}"
           class="inline-flex items-center rounded-lg border px-3 py-2 text-sm hover:bg-gray-50">Import CSV</a>
      @endcan
    </div>
  </div>

  @php
    use Illuminate\Support\Facades\Schema;

    $jenis = request('jenis', '');
    $allCols = Schema::getColumnListing('inventory_hardware');

    // Kolom yang tidak ditampilkan sebagai kolom dinamis
    $skip = ['created_at','updated_at','specs','id_pc','tanggal_digunakan'];
    if ($jenis !== 'storage') { $skip[] = 'storage_type'; }

    $cols = array_values(array_diff($allCols, $skip));
    $titleize = fn($s) => ucwords(str_replace('_',' ', $s));
  @endphp

  <div class="bg-white rounded-xl border border-gray-200 overflow-x-auto">
    <table class="min-w-full text-sm">
      <thead class="bg-gray-50">
        <tr>
          @foreach($cols as $c)
            <th class="text-left px-4 py-3 font-semibold">{{ $titleize($c) }}</th>
          @endforeach
          {{-- dua kolom tetap dari PIVOT --}}
          <th class="text-left px-4 py-3 font-semibold">Id Pc</th>
          <th class="px-4 py-3"></th>
        </tr>
      </thead>
      <tbody>
        @forelse($items as $row)
          <tr class="border-t">
            {{-- kolom dinamis dari tabel master --}}
            @foreach($cols as $c)
              <td class="px-4 py-2">{{ data_get($row, $c) ?? '—' }}</td>
            @endforeach


            {{-- Daftar PC terpasang (dari pivot) --}}
            <td class="px-4 py-2">
              @if(!empty($row->pcs) && count($row->pcs))
                <div class="flex flex-wrap gap-1">
                  @foreach($row->pcs as $pc)
                    <span class="inline-flex items-center gap-1 rounded bg-gray-100 px-2 py-0.5">
                      {{ $pc->id_pc }}
                      @if($pc->pivot->tanggal_digunakan)
                        <span class="text-[11px] text-gray-500">
                          {{ \Illuminate\Support\Carbon::parse($pc->pivot->tanggal_digunakan)->format('Y-m-d') }}
                        </span>
                      @endif
                    </span>
                  @endforeach
                </div>
              @else
                —
              @endif
            </td>

            {{-- Aksi --}}
            <td class="px-4 py-2 text-right whitespace-nowrap">
              <a href="javascript:void(0)"
                 onclick="openModal('{{ route('inventory.hardware.show',$row->id_hardware) }}','Detail Hardware - {{ $row->id_hardware }}')"
                 class="mr-2 inline-flex items-center rounded border px-3 py-1.5 hover:bg-gray-50">Detail</a>

              @can('inventory.hardware.edit')
                <a href="{{ route('inventory.hardware.edit',$row->id_hardware) }}"
                   class="mr-2 inline-flex items-center rounded border px-3 py-1.5 hover:bg-gray-50">Edit</a>
              @endcan

              @can('inventory.hardware.delete')
                <form action="{{ route('inventory.hardware.destroy',$row->id_hardware) }}" method="POST" class="inline"
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
          <tr>
            <td colspan="{{ count($cols)+3 }}" class="px-4 py-6 text-center text-gray-500">Belum ada data.</td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </div>

  {{-- ===== MODAL KELOLA KOLOM (per-entity) ===== --}}
  @can('inventory.hardware.columns')
    <div id="addColModal" class="fixed inset-0 hidden items-center justify-center z-[70]">
      <div id="addColBackdrop" class="absolute inset-0 bg-black/40"></div>

      @php
        $table = 'inventory_hardware';
        $std = [
          'id_hardware','jenis_hardware','tanggal_pembelian','vendor',
          'jumlah_stock','status','tanggal_digunakan','id_pc','storage_type'
        ];
        $protected   = array_merge($std, ['created_at','updated_at']);
        $all         = \Illuminate\Support\Facades\Schema::getColumnListing($table);
        $editableCols= array_values(array_diff($all, $protected));
        $lbl         = fn($s) => ucwords(str_replace('_',' ', $s));
        $colRoutes   = [
          'add'    => route('inventory.hardware.columns.add'),
          'rename' => route('inventory.hardware.columns.rename'),
          'drop'   => route('inventory.hardware.columns.drop'),
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
  @endcan

  <script>
    // Filter auto-submit + toggle storage type
    (function () {
      const jenis = document.getElementById('jenisSelect');
      const wrap  = document.getElementById('storageWrap');
      const stSel = document.getElementById('storageType');
      const form  = document.getElementById('filterForm');
      function toggleStorage() {
        if (jenis.value === 'storage') { wrap.classList.remove('hidden'); }
        else { wrap.classList.add('hidden'); if (stSel) stSel.value = ''; }
      }
      jenis?.addEventListener('change', () => { toggleStorage(); form?.submit(); });
      stSel?.addEventListener('change', () => form?.submit());
      toggleStorage();
    })();

    // Modal open/close + tabs
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

  <div class="mt-4">
    {{ $items->links() }}
  </div>
@endsection
