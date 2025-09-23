@extends('layouts.app')

@section('title','Atur Izin User')

@section('content')
  <h2 class="text-2xl font-bold mb-4">
    Atur Izin: {{ $user->name }} <span class="text-gray-500">({{ $user->email }})</span>
  </h2>

  <form method="POST" action="{{ route('admin.users.update', $user) }}" class="space-y-6">
    @csrf @method('PUT')

    {{-- Role --}}
    <div>
      <label class="block font-medium mb-2">Role</label>
      @php $currentRole = $user->getRoleNames()->first(); @endphp
      <select name="role" class="w-full rounded-lg border px-3 py-2">
  <option value="" disabled {{ $currentRole ? '' : 'selected' }}>— Pilih role —</option>
  @foreach($roles as $r)
    <option value="{{ $r->name }}" {{ $currentRole === $r->name ? 'selected' : '' }}>
      {{ ucfirst($r->name) }}
    </option>
  @endforeach
</select>
      <p class="text-xs text-gray-500 mt-1">Role <b>admin</b> otomatis memiliki semua izin.</p>
    </div>

    @php
      // Flatten semua permission yg dikirim controller
      $allPerms = collect($permissions)->flatten(1);
      $hasPerm  = fn(string $name) => (bool) $allPerms->firstWhere('name', $name);

      // Helper render satu checkbox jika permission ada
      $cb = function (string $name, string $label) use ($hasPerm, $user) {
          if (! $hasPerm($name)) return '';
          $checked = $user->hasPermissionTo($name) ? 'checked' : '';
          return <<<HTML
            <label class="inline-flex items-center gap-2">
              <input type="checkbox" name="permissions[]" value="{$name}" {$checked}>
              <span class="text-sm">{$label}</span>
            </label>
          HTML;
      };

      $LBL = ['view'=>'Lihat','create'=>'Tambah','edit'=>'Edit','delete'=>'Hapus'];
    @endphp

    {{-- ===== DASHBOARD ===== --}}
    <fieldset class="border rounded-xl p-4">
      <legend class="px-2 text-sm font-semibold uppercase text-gray-600">Dashboard</legend>
      <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-x-6 gap-y-3">
        {!! $cb('dashboard.view', 'dashboard.view') !!}
        {!! $cb('dashboard.view.kpi', 'dashboard.view.kpi') !!}
        {!! $cb('dashboard.view.chart', 'dashboard.view.chart') !!}
        {!! $cb('dashboard.view.lokasi-rawan', 'dashboard.view.lokasi-rawan') !!}
        {!! $cb('dashboard.view.history', 'dashboard.view.history') !!}

        <div class="col-span-full pt-2 text-xs text-gray-500">
          Hak akses baris histori per tipe aset (Jika mengaktifkan <b>dashboard.view.history</b>, wajib pilih minimal satu tipe aset di bawah):
        </div>
        {!! $cb('dashboard.history.pc', 'dashboard.history.pc (PC)') !!}
        {!! $cb('dashboard.history.printer', 'dashboard.history.printer (Printer)') !!}
        {!! $cb('dashboard.history.proyektor', 'dashboard.history.proyektor (Proyektor)') !!}
        {!! $cb('dashboard.history.ac', 'dashboard.history.ac (AC)') !!}
      </div>
    </fieldset>

    {{-- ===== INVENTORY ===== --}}
    <fieldset class="border rounded-xl p-4">
      <legend class="px-2 text-sm font-semibold uppercase text-gray-600">Inventory</legend>

      {{-- Grid kartu per entitas --}}
      <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">

        {{-- PC --}}
        <div class="rounded-lg border p-4">
          <div class="font-semibold mb-2">PC</div>
          <div class="grid gap-2 mb-3">
            {!! $cb('inventory.pc.view',   "inventory.pc.view ({$LBL['view']})") !!}
            {!! $cb('inventory.pc.create', "inventory.pc.create ({$LBL['create']})") !!}
            {!! $cb('inventory.pc.edit',   "inventory.pc.edit ({$LBL['edit']})") !!}
            {!! $cb('inventory.pc.delete', "inventory.pc.delete ({$LBL['delete']})") !!}
          </div>
          <div class="mt-2 grid gap-2 border-t pt-3">
            {!! $cb('inventory.pc.import',  'inventory.pc.import (Impor CSV/Template)') !!}
            {!! $cb('inventory.pc.export',  'inventory.pc.export (Ekspor CSV)') !!}
            {!! $cb('inventory.pc.columns', 'inventory.pc.columns (Kelola kolom)') !!}
          </div>
        </div>

        {{-- Printer --}}
        <div class="rounded-lg border p-4">
          <div class="font-semibold mb-2">Printer</div>
          <div class="grid gap-2 mb-3">
            {!! $cb('inventory.printer.view',   "inventory.printer.view ({$LBL['view']})") !!}
            {!! $cb('inventory.printer.create', "inventory.printer.create ({$LBL['create']})") !!}
            {!! $cb('inventory.printer.edit',   "inventory.printer.edit ({$LBL['edit']})") !!}
            {!! $cb('inventory.printer.delete', "inventory.printer.delete ({$LBL['delete']})") !!}
          </div>
          <div class="mt-2 grid gap-2 border-t pt-3">
            {!! $cb('inventory.printer.import',  'inventory.printer.import (Impor CSV/Template)') !!}
            {!! $cb('inventory.printer.export',  'inventory.printer.export (Ekspor CSV)') !!}
            {!! $cb('inventory.printer.columns', 'inventory.printer.columns (Kelola kolom)') !!}
          </div>
        </div>

        {{-- Proyektor --}}
        <div class="rounded-lg border p-4">
          <div class="font-semibold mb-2">Proyektor</div>
          <div class="grid gap-2 mb-3">
            {!! $cb('inventory.proyektor.view',   "inventory.proyektor.view ({$LBL['view']})") !!}
            {!! $cb('inventory.proyektor.create', "inventory.proyektor.create ({$LBL['create']})") !!}
            {!! $cb('inventory.proyektor.edit',   "inventory.proyektor.edit ({$LBL['edit']})") !!}
            {!! $cb('inventory.proyektor.delete', "inventory.proyektor.delete ({$LBL['delete']})") !!}
          </div>
          <div class="mt-2 grid gap-2 border-t pt-3">
            {!! $cb('inventory.proyektor.import',  'inventory.proyektor.import (Impor CSV/Template)') !!}
            {!! $cb('inventory.proyektor.export',  'inventory.proyektor.export (Ekspor CSV)') !!}
            {!! $cb('inventory.proyektor.columns', 'inventory.proyektor.columns (Kelola kolom)') !!}
          </div>
        </div>

        {{-- AC --}}
        <div class="rounded-lg border p-4">
          <div class="font-semibold mb-2">AC</div>
          <div class="grid gap-2 mb-3">
            {!! $cb('inventory.ac.view',   "inventory.ac.view ({$LBL['view']})") !!}
            {!! $cb('inventory.ac.create', "inventory.ac.create ({$LBL['create']})") !!}
            {!! $cb('inventory.ac.edit',   "inventory.ac.edit ({$LBL['edit']})") !!}
            {!! $cb('inventory.ac.delete', "inventory.ac.delete ({$LBL['delete']})") !!}
          </div>
          <div class="mt-2 grid gap-2 border-t pt-3">
            {!! $cb('inventory.ac.import',  'inventory.ac.import (Impor CSV/Template)') !!}
            {!! $cb('inventory.ac.export',  'inventory.ac.export (Ekspor CSV)') !!}
            {!! $cb('inventory.ac.columns', 'inventory.ac.columns (Kelola kolom)') !!}
          </div>
        </div>

        {{-- Hardware --}}
        <div class="rounded-lg border p-4">
          <div class="font-semibold mb-2">Hardware</div>
          <div class="grid gap-2 mb-3">
            {!! $cb('inventory.hardware.view',   "inventory.hardware.view ({$LBL['view']})") !!}
            {!! $cb('inventory.hardware.create', "inventory.hardware.create ({$LBL['create']})") !!}
            {!! $cb('inventory.hardware.edit',   "inventory.hardware.edit ({$LBL['edit']})") !!}
            {!! $cb('inventory.hardware.delete', "inventory.hardware.delete ({$LBL['delete']})") !!}
          </div>
          <div class="mt-2 grid gap-2 border-t pt-3">
            {!! $cb('inventory.hardware.import',  'inventory.hardware.import (Impor CSV/Template)') !!}
            {!! $cb('inventory.hardware.export',  'inventory.hardware.export (Ekspor CSV)') !!}
            {!! $cb('inventory.hardware.columns', 'inventory.hardware.columns (Kelola kolom)') !!}
          </div>
        </div>

        {{-- Labkom --}}
        <div class="rounded-lg border p-4">
          <div class="font-semibold mb-2">Labkom</div>
          <div class="grid gap-2 mb-3">
            {!! $cb('inventory.labkom.view',   "inventory.labkom.view ({$LBL['view']})") !!}
            {!! $cb('inventory.labkom.create', "inventory.labkom.create ({$LBL['create']})") !!}
            {!! $cb('inventory.labkom.edit',   "inventory.labkom.edit ({$LBL['edit']})") !!}
            {!! $cb('inventory.labkom.delete', "inventory.labkom.delete ({$LBL['delete']})") !!}
          </div>
          <div class="mt-2 grid gap-2 border-t pt-3">
            {!! $cb('inventory.labkom.import',  'inventory.labkom.import (Impor CSV/Template)') !!}
            {!! $cb('inventory.labkom.export',  'inventory.labkom.export (Ekspor CSV)') !!}
            {!! $cb('inventory.labkom.columns', 'inventory.labkom.columns (Kelola kolom)') !!}
          </div>
        </div>
        
      </div>
    </fieldset>

    <div class="flex items-center gap-3">
      <button class="px-4 py-2 rounded-lg bg-slate-900 text-white hover:bg-black">Simpan</button>
      <a href="{{ route('admin.users.index') }}" class="px-4 py-2 rounded-lg border hover:bg-gray-50">Batal</a>
    </div>
  </form>

  <script>
document.querySelector('form').addEventListener('submit', function(e){
  const historyBox = document.querySelector('input[value="dashboard.view.history"]');
  if (historyBox && historyBox.checked) {
    const required = [
      'dashboard.history.pc',
      'dashboard.history.printer',
      'dashboard.history.proyektor',
      'dashboard.history.ac'
    ];
    const ok = required.some(val => 
      document.querySelector(`input[value="${val}"]`)?.checked
    );
    if (!ok) {
      e.preventDefault();
      alert('Jika aktifkan Dashboard History, pilih minimal satu tipe aset (PC/Printer/Proyektor/AC).');
    }
  }
});
</script>

@endsection