@extends('layouts.app')

@section('content')
  <h2 class="text-2xl font-bold mb-6">{{ $mode === 'create' ? 'Tambah' : 'Edit' }} Hardware</h2>

  <form action="{{ $mode === 'create'
      ? route('inventory.hardware.store')
      : route('inventory.hardware.update',$data->id_hardware) }}"
        method="POST" class="space-y-5"
        data-mode="{{ $mode }}"
        data-initial-selected-count="{{ isset($selectedPcs) ? count($selectedPcs) : 0 }}">
    @csrf
    @if($mode === 'edit') @method('PUT') @endif

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
      {{-- ID Hardware --}}
      @if(array_key_exists('id_hardware',$fields))
      <div>
        <label class="block text-sm font-medium mb-1" for="id_hardware">ID Hardware</label>
        <input id="id_hardware" name="id_hardware" value="{{ old('id_hardware', $data->id_hardware) }}"
               class="w-full rounded-lg border border-gray-300 px-3 py-2" />
        @error('id_hardware') <div class="text-sm text-red-600 mt-1">{{ $message }}</div> @enderror
      </div>
      @endif

      {{-- Jenis Hardware --}}
      @if(array_key_exists('jenis_hardware',$fields))
      <div>
        <label class="block text-sm font-medium mb-1" for="jenis_hardware">Jenis Hardware</label>
        <select id="jenis_hardware" name="jenis_hardware" class="w-full rounded-lg border border-gray-300 px-3 py-2">
          <option value="">— pilih —</option>
          @foreach($jenisList as $j)
            <option value="{{ $j }}" {{ old('jenis_hardware', $data->jenis_hardware)===$j ? 'selected' : '' }}>
              {{ ucwords(str_replace('_',' ',$j)) }}
            </option>
          @endforeach
        </select>
        @error('jenis_hardware') <div class="text-sm text-red-600 mt-1">{{ $message }}</div> @enderror
      </div>
      @endif

      {{-- Storage Type (khusus storage) --}}
      @if(array_key_exists('storage_type',$fields))
      <div id="field_storage_type" class="{{ old('jenis_hardware', $data->jenis_hardware)==='storage' ? '' : 'hidden' }}">
        <label class="block text-sm font-medium mb-1" for="storage_type">Tipe Storage</label>
        <select id="storage_type" name="storage_type" class="w-full rounded-lg border border-gray-300 px-3 py-2">
          <option value="">— pilih —</option>
          @foreach($storageTypes as $st)
            <option value="{{ $st }}" {{ old('storage_type', $data->storage_type)===$st ? 'selected' : '' }}>
              {{ strtoupper($st) }}
            </option>
          @endforeach
        </select>
        <p class="text-xs text-gray-500 mt-1">Wajib diisi jika jenis = <b>storage</b>.</p>
        @error('storage_type') <div class="text-sm text-red-600 mt-1">{{ $message }}</div> @enderror
      </div>
      @endif

      {{-- Vendor --}}
      @if(array_key_exists('vendor',$fields))
      <div>
        <label class="block text-sm font-medium mb-1" for="vendor">Vendor</label>
        <input id="vendor" name="vendor" value="{{ old('vendor', $data->vendor) }}"
               class="w-full rounded-lg border border-gray-300 px-3 py-2" />
        @error('vendor') <div class="text-sm text-red-600 mt-1">{{ $message }}</div> @enderror
      </div>
      @endif

      {{-- Tanggal Pembelian --}}
      @if(array_key_exists('tanggal_pembelian',$fields))
      @php
        $tpRaw = old('tanggal_pembelian', $data->tanggal_pembelian ?? null);
        $tpVal = $tpRaw ? \Illuminate\Support\Carbon::parse($tpRaw)->format('Y-m-d') : '';
      @endphp
      <div>
        <label class="block text-sm font-medium mb-1" for="tanggal_pembelian">Tanggal Pembelian</label>
        <input type="date" id="tanggal_pembelian" name="tanggal_pembelian"
               value="{{ $tpVal }}"
               class="w-full rounded-lg border border-gray-300 px-3 py-2" />
        @error('tanggal_pembelian') <div class="text-sm text-red-600 mt-1">{{ $message }}</div> @enderror
      </div>
      @endif

      {{-- Jumlah Stock --}}
      @if(array_key_exists('jumlah_stock',$fields))
      <div>
        <label class="block text-sm font-medium mb-1" for="jumlah_stock">Jumlah Stock</label>
        <input type="number" min="0" id="jumlah_stock" name="jumlah_stock"
               value="{{ old('jumlah_stock', $data->jumlah_stock) }}"
               class="w-full rounded-lg border border-gray-300 px-3 py-2"
               {{ $mode === 'edit' ? 'readonly' : '' }} />
        @if($mode === 'edit')
          <p class="text-xs text-gray-500 mt-1">Pada mode <b>Edit</b>, stok akan otomatis berubah mengikuti penambahan/penghapusan PC.</p>
        @else
          <p class="text-xs text-gray-500 mt-1">Stok awal akan dikurangi sebanyak jumlah PC yang kamu pilih.</p>
        @endif
        <div id="stock_preview" class="text-xs mt-1"></div>
        @error('jumlah_stock') <div class="text-sm text-red-600 mt-1">{{ $message }}</div> @enderror
      </div>
      @endif

      {{-- Status --}}
      @if(array_key_exists('status',$fields))
      @php
        $statusOps = $statusOptions ?? ['In use','In store','Service','available'];
        $currentStatus = old('status', $data->status ?? '');
        $mapOldToNew = ['available'=>'In store','in_use'=>'In use','broken'=>'Service'];
        if (isset($mapOldToNew[$currentStatus])) $currentStatus = $mapOldToNew[$currentStatus];
      @endphp
      <div>
        <label class="block text-sm font-medium mb-1" for="status">Status</label>
        <select id="status" name="status" class="w-full rounded-lg border border-gray-300 px-3 py-2">
          @foreach($statusOps as $opt)
            <option value="{{ $opt }}" {{ $currentStatus === $opt ? 'selected' : '' }}>{{ $opt }}</option>
          @endforeach
        </select>
        <p class="text-xs text-gray-500 mt-1">
          Jika <b>In use</b>, isi <b>Tanggal Digunakan</b> &amp; pilih <b>ID PC</b> (bisa banyak).
        </p>
        @error('status') <div class="text-sm text-red-600 mt-1">{{ $message }}</div> @enderror
      </div>
      @endif

      {{-- Tanggal Digunakan (untuk PC yang BARU ditambahkan) --}}
      <div id="field_tanggal_digunakan" class="{{ ($currentStatus === 'In use') ? '' : 'hidden' }}">
        @php
          $tdRaw = old('tanggal_digunakan', null); // pada edit, tanggal lama tetap di pivot
          $tdVal = $tdRaw ? \Illuminate\Support\Carbon::parse($tdRaw)->format('Y-m-d') : '';
        @endphp
        <label class="block text-sm font-medium mb-1" for="tanggal_digunakan">Tanggal Digunakan</label>
        <input type="date" id="tanggal_digunakan" name="tanggal_digunakan"
               value="{{ $tdVal }}"
               class="w-full rounded-lg border border-gray-300 px-3 py-2" {{ ($currentStatus === 'In use') ? '' : 'disabled' }} />
        <p class="text-xs text-gray-500 mt-1">Saat <b>Edit</b>, tanggal ini hanya diterapkan ke PC yang <b>baru ditambahkan</b>.</p>
        @error('tanggal_digunakan') <div class="text-sm text-red-600 mt-1">{{ $message }}</div> @enderror
      </div>

      {{-- MULTI-SELECT: ID PC (pakai Choices.js) --}}
      <div id="field_pcs" class="{{ ($currentStatus === 'In use') ? '' : 'hidden' }}">
        <label class="block text-sm font-medium mb-1" for="pcs">ID PC (bisa pilih banyak)</label>
        @php
          $selected = old('pcs', $selectedPcs ?? []);
        @endphp
        <select id="pcs" name="pcs[]" multiple>
          @foreach(($pcIds ?? []) as $pid)
            <option value="{{ $pid }}" {{ in_array($pid, $selected, true) ? 'selected' : '' }}>{{ $pid }}</option>
          @endforeach
        </select>
        <p class="text-xs text-gray-500 mt-1">
          Klik untuk memilih; item terpilih akan muncul sebagai chip (tanpa perlu tahan Shift/Ctrl).
        </p>
        @error('pcs') <div class="text-sm text-red-600 mt-1">{{ $message }}</div> @enderror
        @error('pcs.*') <div class="text-sm text-red-600 mt-1">{{ $message }}</div> @enderror
      </div>

      {{-- Kolom dinamis lain --}}
      @foreach($fields as $name => $label)
        @continue(in_array($name, [
          'id_hardware','jenis_hardware','storage_type','vendor',
          'tanggal_pembelian','jumlah_stock','status','tanggal_digunakan','id_pc'
        ]))

        @php
          $isDate = in_array($name, $dateCols ?? []);
          $isDt   = in_array($name, $datetimeCols ?? []);
          $raw    = old($name, $data->{$name} ?? null);

          if ($isDate) {
              $val = $raw ? \Illuminate\Support\Carbon::parse($raw)->format('Y-m-d') : '';
          } elseif ($isDt) {
              $val = $raw ? \Illuminate\Support\Carbon::parse($raw)->format('Y-m-d\TH:i') : '';
          } else {
              $val = $raw;
          }
        @endphp

        <div>
          <label class="block text-sm font-medium mb-1" for="{{ $name }}">{{ $label }}</label>

          @if($isDate)
            <input type="date" id="{{ $name }}" name="{{ $name }}"
                   value="{{ $val }}" class="w-full rounded-lg border border-gray-300 px-3 py-2" />
          @elseif($isDt)
            <input type="datetime-local" id="{{ $name }}" name="{{ $name }}"
                   value="{{ $val }}" class="w-full rounded-lg border border-gray-300 px-3 py-2" />
          @else
            <input id="{{ $name }}" name="{{ $name }}" value="{{ $val }}"
                   class="w-full rounded-lg border border-gray-300 px-3 py-2" />
          @endif

          @error($name) <div class="text-sm text-red-600 mt-1">{{ $message }}</div> @enderror
        </div>
      @endforeach
    </div>

    <div>
      <label for="catatan_histori" class="block text-sm font-medium mb-1">Catatan Histori</label>
      <textarea id="catatan_histori" name="catatan_histori"
        class="w-full rounded-lg border border-gray-300 px-3 py-2" rows="3"
        placeholder="Misal: dipasang ke PC-001, ganti SSD, klaim garansi, dll.">{{ old('catatan_histori') }}</textarea>
    </div>

    <div class="flex items-center gap-3">
      <button class="rounded-lg bg-blue-600 text-white px-4 py-2 hover:bg-blue-700">
        {{ $mode === 'create' ? 'Simpan' : 'Update' }}
      </button>
      <a href="{{ route('inventory.hardware.index') }}" class="rounded-lg border px-4 py-2 hover:bg-gray-50">Batal</a>
    </div>
  </form>

  {{-- Choices.js (CDN) --}}
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/choices.js/public/assets/styles/choices.min.css" />
  <script src="https://cdn.jsdelivr.net/npm/choices.js/public/assets/scripts/choices.min.js"></script>

  <script>
    const jenisEl       = document.getElementById('jenis_hardware');
    const storageWrap   = document.getElementById('field_storage_type');
    const storageSelect = document.getElementById('storage_type');

    const statusEl      = document.getElementById('status');
    const usedDateWrap  = document.getElementById('field_tanggal_digunakan');
    const usedDateInput = document.getElementById('tanggal_digunakan');
    const pcsWrap       = document.getElementById('field_pcs');
    const pcsSelect     = document.getElementById('pcs');

    const stokInput     = document.getElementById('jumlah_stock');
    const stockPreview  = document.getElementById('stock_preview');
    const formEl        = document.querySelector('form[method="POST"]');
    const mode          = formEl?.dataset?.mode || 'create';
    const initialSelCnt = parseInt(formEl?.dataset?.initialSelectedCount || '0', 10);

    let choicesInstance = null;

    function setHidden(el, hidden, inputEl = null, required = false) {
      if (!el) return;
      el.classList.toggle('hidden', hidden);
      if (inputEl) {
        inputEl.disabled = hidden;
        inputEl.required = !hidden && required;
      }
    }

    function onJenisChange() {
      const isStorage = (jenisEl?.value === 'storage');
      setHidden(storageWrap, !isStorage, storageSelect, true);
      if (!isStorage && storageSelect) storageSelect.value = '';
    }

    function onStatusChange() {
      const inUse = (statusEl?.value === 'In use');
      setHidden(usedDateWrap, !inUse, usedDateInput, false);
      setHidden(pcsWrap,      !inUse, pcsSelect,     false);
      if (!inUse && choicesInstance) {
        // Tidak menghapus pilihan; controller akan mengabaikan jika bukan In use.
      }
      updateStockPreview();
    }

    function initChoices() {
      if (!pcsSelect) return;
      choicesInstance = new Choices(pcsSelect, {
        removeItemButton: true,
        placeholder: true,
        placeholderValue: 'Pilih satu atau lebih PC…',
        searchPlaceholderValue: 'Cari PC…',
        shouldSort: false,
        itemSelectText: '',
      });
      pcsSelect.addEventListener('change', updateStockPreview);
    }

    function getSelectedCount() {
      if (!pcsSelect) return 0;
      // Saat sudah diinisialisasi Choices, <select> tetap sinkron dengan value terpilih
      return Array.from(pcsSelect.selectedOptions || []).length;
    }

    function updateStockPreview() {
      if (!stockPreview) return;

      const inUse = (statusEl?.value === 'In use');
      const selected = getSelectedCount();

      if (mode === 'create') {
        const stokAwal = parseInt(stokInput?.value || '0', 10);
        if (inUse && stokInput) {
          const final = stokAwal - selected;
          stockPreview.textContent =
            `Perkiraan stok akhir: ${final} (stok awal ${stokAwal} − ${selected} PC dipilih)`;
          stockPreview.className =
            'text-xs mt-1 ' + (final < 0 ? 'text-red-600' : 'text-gray-600');
        } else {
          stockPreview.textContent = '';
        }
      } else {
        // edit: preview berdasarkan selisih (added - removed)
        const delta = selected - initialSelCnt;
        if (inUse) {
          const now = parseInt(stokInput?.value || '0', 10);
          const final = now - Math.max(delta, 0) + Math.max(-delta, 0); // tampilkan ilustrasi saja
          const sign = (delta > 0 ? `− ${delta}` : delta < 0 ? `+ ${-delta}` : '± 0');
          stockPreview.textContent =
            `Stok akan disesuaikan: ${sign} unit (berdasarkan perubahan pilihan PC).`;
          stockPreview.className = 'text-xs mt-1 text-gray-600';
        } else {
          stockPreview.textContent = '';
        }
      }
    }

    document.addEventListener('DOMContentLoaded', () => {
      onJenisChange();
      onStatusChange();
      initChoices();
      updateStockPreview();

      jenisEl?.addEventListener('change', onJenisChange);
      statusEl?.addEventListener('change', onStatusChange);
      stokInput?.addEventListener('input', updateStockPreview);
    });
  </script>
@endsection
