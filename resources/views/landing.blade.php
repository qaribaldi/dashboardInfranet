@extends('layouts.guest')

@section('title','Aset IT • Infranet UNIKOM')

@section('content')
  <section class="relative overflow-hidden rounded-3xl border bg-gradient-to-br from-indigo-50 via-white to-emerald-50">
    <div class="pointer-events-none absolute -top-16 -left-24 h-72 w-72 rounded-full bg-indigo-300/30 blur-3xl"></div>
    <div class="pointer-events-none absolute -bottom-10 -right-10 h-72 w-72 rounded-full bg-emerald-300/30 blur-3xl"></div>

    <div class="relative mx-auto max-w-5xl px-6 py-14 md:py-18 text-center">
      <h1 class="text-4xl md:text-5xl font-extrabold tracking-tight text-slate-900">
        Selamat Datang di <span class="bg-gradient-to-r from-indigo-600 to-emerald-600 bg-clip-text text-transparent">Asset IT Infranet</span>
      </h1>
      <p class="mt-3 text-slate-600 max-w-2xl mx-auto">
        Portal internal untuk monitoring, pendataan, analisis, dan pencatatan asset it. <br>
        Khusus untuk Divisi Infranet UNIKOM
      </p>

      <div class="mt-6 flex items-center justify-center gap-3">
        @auth
          <a href="{{ route('dashboard') }}" class="rounded-xl bg-indigo-600 px-5 py-3 text-white shadow hover:bg-indigo-700">
            Buka Dashboard
          </a>
        @else
          <a href="{{ route('login') }}" class="rounded-xl bg-slate-900 px-5 py-3 text-white hover:bg-black">
            Masuk
          </a>
          
        @endauth
      </div>
    </div>
  </section>

  {{-- GRID Info singkat --}}
  <section class="mx-auto max-w-5xl px-6 mt-10">
    <div class="grid md:grid-cols-3 gap-4">
      {{-- Kotak pengumuman --}}
      <div class="md:col-span-2 rounded-2xl border bg-white p-5 hover:shadow-sm transition">
        <div class="flex items-center justify-between">
          <h2 class="text-lg font-semibold">Informasi</h2>
        </div>
        <div class="mt-3 text-slate-600 leading-relaxed">
          {{-- Tulis info penting singkat di sini --}}
          <p class="mb-2">• Jadwal audit aset semester ini dimulai pekan depan.</p>
          <p class="mb-2">• Mohon update data perangkat yang belum lengkap (tahun pembelian / lokasi).</p>
          <p>• Hubungi admin jika menemui kendala akses.</p>
        </div>
      </div>

      {{-- Kotak kontak ringkas --}}
      <div class="rounded-2xl border bg-white p-5 hover:shadow-sm transition">
        <h3 class="text-lg font-semibold">Kontak Admin</h3>
        <div class="mt-3 text-sm text-slate-600 space-y-2">
          <p>Email: <span class="font-medium">admin@unikom.ac.id</span></p>
          <p>Nomor: <span class="font-medium">0891234</span></p>
          <p>Jam Layanan: <span class="font-medium">07.00–17.00 WIB</span></p>
        </div>
        <div class="mt-4">
          @auth
            <a href="{{ route('dashboard') }}" class="inline-block rounded-lg border px-4 py-2 hover:bg-gray-50">Ke Dashboard</a>
          @endauth
        </div>
      </div>
    </div>
  </section>

  {{-- Footer mini --}}
  <footer class="mx-auto max-w-5xl px-6 mt-10 mb-6 text-center text-sm text-slate-500">
    © {{ date('Y') }} Asset IT Infranet • UNIKOM
  </footer>
@endsection
