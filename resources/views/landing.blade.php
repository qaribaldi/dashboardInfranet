@extends('layouts.public')

@section('title','Selamat Datang • Infranet')

@section('content')
<section class="relative h-screen overflow-hidden flex flex-col items-center justify-center text-center 
  bg-[radial-gradient(1200px_600px_at_-10%_-20%,#c7d2fe_0%,transparent_60%),radial-gradient(1200px_600px_at_110%_120%,#bbf7d0_0%,transparent_60%)] 
  from-indigo-50 via-white to-emerald-50">

  {{-- Dekorasi gradien besar sisi kiri/kanan (tambahan sebar) --}}
  <div class="pointer-events-none absolute -top-40 -left-40 h-[38rem] w-[38rem] rounded-full 
              bg-gradient-to-br from-indigo-300/50 via-fuchsia-300/40 to-emerald-300/40 blur-3xl"></div>
  <div class="pointer-events-none absolute -bottom-48 -right-48 h-[42rem] w-[42rem] rounded-full 
              bg-gradient-to-tr from-emerald-300/50 via-sky-300/40 to-indigo-300/40 blur-3xl"></div>

  {{-- konten --}}
  <div class="relative max-w-3xl px-6">

    {{-- lingkaran gradien lembut di belakang teks --}}
    <div class="pointer-events-none absolute inset-0 -z-10 flex items-center justify-center">
      <div class="h-56 w-56 md:h-72 md:w-72 rounded-full blur-3xl opacity-70
                  bg-[conic-gradient(at_50%_50%,#a5b4fc_0deg,#86efac_120deg,#93c5fd_240deg,#a5b4fc_360deg)]">
      </div>
    </div>

    <h1 class="text-4xl md:text-5xl font-extrabold tracking-tight text-slate-900">
      Selamat Datang di 
      <span class="bg-gradient-to-r from-indigo-600 via-sky-600 to-emerald-600 bg-clip-text text-transparent">
        Infranet
      </span>
    </h1>

    <p class="mt-4 text-slate-700/90 max-w-xl mx-auto">
      Direktorat IT Infrastructure & Network (INFRANET) - UNIKOM.
    </p>

    {{-- tombol + lingkaran gradien di belakang tombol --}}
    <div class="mt-8 relative inline-block">
      <div class="pointer-events-none absolute inset-0 -z-10 flex items-center justify-center">
        <div class="h-24 w-24 md:h-28 md:w-28 rounded-full blur-2xl opacity-80
                    bg-[radial-gradient(circle_at_center,#a7f3d0_0%,#c7d2fe_45%,transparent_60%)]">
        </div>
      </div>

      @guest
        <a href="{{ route('login') }}"
           class="inline-flex items-center rounded-xl bg-slate-900 px-6 py-3 text-white hover:bg-black transition">
          Login / Register
        </a>
      @else
        <a href="{{ route('dashboard') }}"
           class="inline-flex items-center rounded-xl bg-indigo-600 px-6 py-3 text-white hover:bg-indigo-700 transition">
          Buka Dashboard
        </a>
      @endguest
    </div>
  </div>

   

        @auth
          @if(auth()->user()->role === 'admin')
           
          @endif
        @endauth
      </div>

      {{-- TAMPILAN BACA --}}
      <div x-show="!editing" class="mt-3 text-slate-700 leading-relaxed">
        {!! nl2br(e($info->content ?? 'Tidak ada informasi terbaru.')) !!}
      </div>

    @if(session('status'))
      <div class="mt-2 text-sm text-emerald-700">{{ session('status') }}</div>
    @endif
  </div>

  {{-- footer nempel bawah, tetap center --}}
  <footer class="absolute bottom-6 left-0 right-0 text-center text-sm text-slate-600/80">
    © {{ date('Y') }} Direktorat IT Infrastructure & Network (INFRANET) • UNIKOM
  </footer>
</section>
@endsection
