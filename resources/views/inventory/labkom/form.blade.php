@extends('layouts.app')

@section('content')
  <h2 class="text-2xl font-bold mb-6">
    {{ $mode === 'create' ? 'Tambah' : 'Edit' }} Labkom
  </h2>

  <form
    action="{{ $mode === 'create'
        ? route('inventory.labkom.store')
        : route('inventory.labkom.update', $data->id_pc) }}"
    method="POST"
    class="space-y-5"
  >
    @csrf
    @if($mode === 'edit') @method('PUT') @endif

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
      {{-- ID PC --}}
      @if(array_key_exists('id_pc',$fields))
        <div>
          <label class="block text-sm font-medium mb-1" for="id_pc">ID PC</label>
          <input id="id_pc" name="id_pc"
                 value="{{ old('id_pc', $data->id_pc) }}"
                 class="w-full rounded-lg border border-gray-300 px-3 py-2" />
          @error('id_pc') <div class="text-sm text-red-600 mt-1">{{ $message }}</div> @enderror
        </div>
      @endif

      {{-- Nama Lab --}}
      @if(array_key_exists('nama_lab',$fields))
        <div>
          <label class="block text-sm font-medium mb-1" for="nama_lab">Nama Lab</label>
          <input id="nama_lab" name="nama_lab"
                 value="{{ old('nama_lab', $data->nama_lab) }}"
                 class="w-full rounded-lg border border-gray-300 px-3 py-2" />
          @error('nama_lab') <div class="text-sm text-red-600 mt-1">{{ $message }}</div> @enderror
        </div>
      @endif

      {{-- Kolom lain --}}
      @foreach($fields as $name => $label)
        @continue(in_array($name, ['id_pc','nama_lab']))

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

        {{-- Status jadi dropdown --}}
        @if($name === 'status')
          @php
            $statusOps = ['In use','In store','Service'];
            $currentStatus = $raw ?? '';
            $mapOldToNew = ['available'=>'In store','in_use'=>'In use','broken'=>'Service'];
            if (isset($mapOldToNew[$currentStatus])) $currentStatus = $mapOldToNew[$currentStatus];
          @endphp
          <div>
            <label class="block text-sm font-medium mb-1" for="status">Status</label>
            <select id="status" name="status"
                    class="w-full rounded-lg border border-gray-300 px-3 py-2">
              @foreach($statusOps as $opt)
                <option value="{{ $opt }}" {{ $currentStatus === $opt ? 'selected' : '' }}>
                  {{ $opt }}
                </option>
              @endforeach
            </select>
            @error('status') <div class="text-sm text-red-600 mt-1">{{ $message }}</div> @enderror
          </div>

        {{-- Default --}}
        @else
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
        @endif
      @endforeach
    </div>

    <hr class="my-6">

    <div class="flex items-center gap-3">
      <button class="rounded-lg bg-blue-600 text-white px-4 py-2 hover:bg-blue-700">
        {{ $mode === 'create' ? 'Simpan' : 'Update' }}
      </button>
      <a href="{{ route('inventory.labkom.index') }}"
         class="rounded-lg border px-4 py-2 hover:bg-gray-50">Batal</a>
    </div>
  </form>
@endsection
