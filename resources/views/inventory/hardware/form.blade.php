@extends('layouts.app')

@section('content')
  <h2 class="text-2xl font-bold mb-6">{{ $mode === 'create' ? 'Tambah' : 'Edit' }} Hardware</h2>

  <form action="{{ $mode === 'create'
      ? route('inventory.hardware.store')
      : route('inventory.hardware.update',$data->id_hardware) }}"
        method="POST" class="space-y-5">
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
      <div>
        <label class="block text-sm font-medium mb-1" for="tanggal_pembelian">Tanggal Pembelian</label>
        <input type="date" id="tanggal_pembelian" name="tanggal_pembelian"
               value="{{ old('tanggal_pembelian', $data->tanggal_pembelian) }}"
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
               class="w-full rounded-lg border border-gray-300 px-3 py-2" />
        @error('jumlah_stock') <div class="text-sm text-red-600 mt-1">{{ $message }}</div> @enderror
      </div>
      @endif

      {{-- Status --}}
      @if(array_key_exists('status',$fields))
      <div>
        <label class="block text-sm font-medium mb-1" for="status">Status</label>
        <select id="status" name="status" class="w-full rounded-lg border border-gray-300 px-3 py-2">
          @php $statusOps = ['available'=>'Available','in_use'=>'In Use','broken'=>'Broken']; @endphp
          @foreach($statusOps as $val=>$label)
            <option value="{{ $val }}" {{ old('status', $data->status)===$val ? 'selected' : '' }}>{{ $label }}</option>
          @endforeach
        </select>
        <p class="text-xs text-gray-500 mt-1">Jika <b>In Use</b>, isi Tanggal Digunakan &amp; ID PC.</p>
        @error('status') <div class="text-sm text-red-600 mt-1">{{ $message }}</div> @enderror
      </div>
      @endif

      {{-- Tanggal Digunakan (muncul jika status = in_use) --}}
      @if(array_key_exists('tanggal_digunakan',$fields))
      <div id="field_tanggal_digunakan" class="{{ old('status', $data->status)==='in_use' ? '' : 'hidden' }}">
        <label class="block text-sm font-medium mb-1" for="tanggal_digunakan">Tanggal Digunakan</label>
        <input type="date" id="tanggal_digunakan" name="tanggal_digunakan"
               value="{{ old('tanggal_digunakan', $data->tanggal_digunakan) }}"
               class="w-full rounded-lg border border-gray-300 px-3 py-2" />
        @error('tanggal_digunakan') <div class="text-sm text-red-600 mt-1">{{ $message }}</div> @enderror
      </div>
      @endif

      {{-- ID PC (muncul jika status = in_use) --}}
      @if(array_key_exists('id_pc',$fields))
      <div id="field_id_pc" class="{{ old('status', $data->status)==='in_use' ? '' : 'hidden' }}">
        <label class="block text-sm font-medium mb-1" for="id_pc">ID PC</label>
        <input id="id_pc" name="id_pc" value="{{ old('id_pc', $data->id_pc) }}"
               class="w-full rounded-lg border border-gray-300 px-3 py-2" />
        @error('id_pc') <div class="text-sm text-red-600 mt-1">{{ $message }}</div> @enderror
      </div>
      @endif

      {{-- Kolom dinamis lain (jika ada) --}}
      @foreach($fields as $name => $label)
        @continue(in_array($name, ['id_hardware','jenis_hardware','storage_type','vendor','tanggal_pembelian','jumlah_stock','status','tanggal_digunakan','id_pc']))
        <div>
          <label class="block text-sm font-medium mb-1" for="{{ $name }}">{{ $label }}</label>
          <input id="{{ $name }}" name="{{ $name }}" value="{{ old($name, $data->{$name}) }}"
                 class="w-full rounded-lg border border-gray-300 px-3 py-2" />
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

  <script>
    const jenisEl       = document.getElementById('jenis_hardware');
    const storageWrap   = document.getElementById('field_storage_type');
    const storageSelect = document.getElementById('storage_type');

    const statusEl      = document.getElementById('status');
    const usedDateWrap  = document.getElementById('field_tanggal_digunakan');
    const usedDateInput = document.getElementById('tanggal_digunakan');
    const idPcWrap      = document.getElementById('field_id_pc');
    const idPcInput     = document.getElementById('id_pc');

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
      setHidden(storageWrap, !isStorage, storageSelect, true); // required kalau storage
      if (!isStorage && storageSelect) storageSelect.value = '';
    }

    function onStatusChange() {
      const inUse = (statusEl?.value === 'in_use');
      setHidden(usedDateWrap, !inUse, usedDateInput, true);
      setHidden(idPcWrap,     !inUse, idPcInput,     true);
      if (!inUse) {
        if (usedDateInput) usedDateInput.value = '';
        if (idPcInput)     idPcInput.value     = '';
      }
    }

    // init saat halaman dibuka
    document.addEventListener('DOMContentLoaded', () => {
      onJenisChange();
      onStatusChange();
      jenisEl?.addEventListener('change', onJenisChange);
      statusEl?.addEventListener('change', onStatusChange);
    });
  </script>
@endsection
