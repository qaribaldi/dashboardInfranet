@extends('layouts.public')

@section('title','Selamat Datang • Infranet')

@section('content')
<div class="relative min-h-screen overflow-x-hidden
  bg-[radial-gradient(1200px_600px_at_-10%_-20%,#c7d2fe_0%,transparent_60%),radial-gradient(1200px_600px_at_110%_120%,#bbf7d0_0%,transparent_60%)]
  from-indigo-50 via-white to-emerald-50">

  {{-- NAVBAR --}}
  <header class="fixed top-0 left-0 right-0 z-50">
    <button id="btnMenu"
            class="fixed top-5 right-5 md:top-6 md:right-6 w-11 h-11 inline-flex items-center justify-center
                   rounded-xl border border-black/10 bg-white/70 backdrop-blur shadow-sm hover:bg-white transition"
            aria-label="Open menu">
      <span class="sr-only">Open menu</span>
      <div class="space-y-1.5">
        <span class="block w-6 h-0.5 bg-gray-900"></span>
        <span class="block w-6 h-0.5 bg-gray-900"></span>
        <span class="block w-6 h-0.5 bg-gray-900"></span>
      </div>
    </button>

    <div id="mobileMenu"
         class="fixed top-0 right-0 h-full w-[78%] max-w-xs bg-white border-l shadow-xl
                translate-x-full transition-transform duration-300 z-[60]">
      <div class="h-14 flex items-center justify-between px-4 border-b">
        <div class="font-semibold">Menu</div>
        <button id="btnClose" class="w-9 h-9 rounded-lg border hover:bg-gray-50" aria-label="Close menu">✕</button>
      </div>
      <nav class="p-4 flex flex-col gap-1 text-sm">
        <a href="#home"    class="px-3 py-2 rounded hover:bg-gray-50" data-close>Home</a>
        <a href="#about"   class="px-3 py-2 rounded hover:bg-gray-50" data-close>Tentang Kami</a>
        <a href="#info"    class="px-3 py-2 rounded hover:bg-gray-50" data-close>Informasi</a>
        <a href="#contact" class="px-3 py-2 rounded hover:bg-gray-50" data-close>Kontak</a>
        <div class="border-t my-2"></div>
        @guest
          <a href="{{ route('login') }}" class="px-3 py-2 rounded border text-center hover:bg-gray-50" data-close>Login / Register</a>
        @else
          <a href="{{ route('dashboard') }}" class="px-3 py-2 rounded bg-gray-900 text-white text-center" data-close>Buka Dashboard</a>
        @endguest
      </nav>
    </div>

    <div id="backdrop" class="fixed inset-0 bg-black/40 z-[55] hidden"></div>
  </header>

  {{-- SECTION 1: HOME --}}
  <section id="home" class="relative min-h-[92vh] flex flex-col items-center justify-center text-center">
    <div class="pointer-events-none absolute -top-40 -left-40 h-[38rem] w-[38rem] rounded-full 
                bg-gradient-to-br from-indigo-300/50 via-fuchsia-300/40 to-emerald-300/40 blur-3xl"></div>
    <div class="pointer-events-none absolute -bottom-48 -right-48 h-[42rem] w-[42rem] rounded-full 
                bg-gradient-to-tr from-emerald-300/50 via-sky-300/40 to-indigo-300/40 blur-3xl"></div>

    <div class="relative max-w-3xl px-6">
      <div class="pointer-events-none absolute inset-0 -z-10 flex items-center justify-center">
        <div class="h-56 w-56 md:h-72 md:w-72 rounded-full blur-3xl opacity-70
                    bg-[conic-gradient(at_50%_50%,#a5b4fc_0deg,#86efac_120deg,#93c5fd_240deg,#a5b4fc_360deg)]"></div>
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
    </div>
  </section>

  {{-- SECTION 2: ABOUT US (1 card) --}}
  <section id="about" class="relative py-16 md:py-20">
    <div class="pointer-events-none absolute -top-24 -left-24 h-80 w-80 rounded-full 
                bg-gradient-to-br from-indigo-300/40 via-fuchsia-300/30 to-emerald-300/30 blur-3xl"></div>
    <div class="pointer-events-none absolute -bottom-24 -right-24 h-96 w-96 rounded-full 
                bg-gradient-to-tr from-emerald-300/40 via-sky-300/30 to-indigo-300/30 blur-3xl"></div>

    <div class="relative mx-auto max-w-6xl px-4">
      <h2 class="text-center text-3xl md:text-4xl font-extrabold">Tentang Kami</h2>
      <div class="mt-8 grid place-items-center">
        <div class="w-full max-w-2xl rounded-2xl border border-black/10 bg-white/70 backdrop-blur p-6 shadow-sm">
          <div class="flex items-start gap-3">
            <div class="rounded-xl bg-indigo-50 p-3">
              <svg class="h-6 w-6 text-indigo-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
              </svg>
            </div>
            <div>
              <h3 class="font-semibold text-lg">Tentang INFRANET</h3>
              <div class="mt-1 text-slate-700">
                {!! $info->about_content ?? '' !!}
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>

  {{-- SECTION 3: INFORMASI (1 card) --}}
  <section id="info" class="relative py-16 md:py-20">
    <div class="pointer-events-none absolute -top-24 -left-24 h-80 w-80 rounded-full 
                bg-gradient-to-br from-indigo-300/40 via-fuchsia-300/30 to-emerald-300/30 blur-3xl"></div>
    <div class="pointer-events-none absolute -bottom-24 -right-24 h-96 w-96 rounded-full 
                bg-gradient-to-tr from-emerald-300/40 via-sky-300/30 to-indigo-300/30 blur-3xl"></div>

    <div class="relative mx-auto max-w-6xl px-4">
      <h2 class="text-center text-3xl md:text-4xl font-extrabold">Informasi</h2>
      <div class="mt-8 grid place-items-center">
        <div class="w-full max-w-2xl rounded-2xl border border-black/10 bg-white/70 backdrop-blur p-6 shadow-sm">
          <div class="flex items-start gap-3">
            <div class="rounded-xl bg-emerald-50 p-3">
              <svg class="h-6 w-6 text-emerald-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6l4 2M12 3a9 9 0 100 18 9 9 0 000-18z"/>
              </svg>
            </div>
            <div>
              <h3 class="font-semibold text-lg">Tata Cara Peminjaman Aset</h3>
              <div class="mt-1 text-slate-700">
                {!! $info->info_content ?? '' !!}
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>

  {{-- SECTION 4: CONTACT (2 card) --}}
  <section id="contact" class="relative py-16 md:py-20">
    <div class="pointer-events-none absolute -top-24 -right-24 h-80 w-80 rounded-full 
                bg-gradient-to-br from-sky-300/40 via-indigo-300/30 to-emerald-300/30 blur-3xl"></div>

    <div class="relative mx-auto max-w-6xl px-4">
      <h2 class="text-center text-3xl md:text-4xl font-extrabold">Kontak</h2>

      <div class="mt-8 grid md:grid-cols-2 gap-6">
        <div class="rounded-2xl border border-black/10 bg-white/70 backdrop-blur p-6 shadow-sm">
          <h3 class="font-semibold text-lg">Hubungi Kami</h3>
          <div class="mt-4 space-y-3 text-slate-700">
            {!! $info->contact_content ?? '' !!}
          </div>
        </div>

        <div class="rounded-2xl border border-black/10 bg-white/70 backdrop-blur p-6 shadow-sm">
          <h3 class="font-semibold text-lg">Jam Layanan</h3>
          <div class="mt-4 text-slate-700 text-sm">
            {!! $info->service_hours_content ?? '' !!}
          </div>
        </div>
      </div>
    </div>
  </section>

  {{-- FOOTER --}}
  <footer class="mt-10 border-t border-black/5">
    <div class="mx-auto max-w-6xl px-4 py-8 text-center text-sm text-slate-600/80">
      © {{ date('Y') }} Direktorat IT Infrastructure & Network (INFRANET) • UNIKOM
    </div>
  </footer>
</div>

@push('body-end')
<script>
  // Drawer open/close
  const btn = document.getElementById('btnMenu');
  const btnClose = document.getElementById('btnClose');
  const drawer = document.getElementById('mobileMenu');
  const backdrop = document.getElementById('backdrop');

  function openMenu(){ drawer.classList.remove('translate-x-full'); backdrop.classList.remove('hidden'); }
  function closeMenu(){ drawer.classList.add('translate-x-full'); backdrop.classList.add('hidden'); }

  btn && btn.addEventListener('click', openMenu);
  btnClose && btnClose.addEventListener('click', closeMenu);
  backdrop && backdrop.addEventListener('click', closeMenu);

  // Smooth scroll + offset kecil
  const HEADER_OFFSET = 12;
  function offsetScrollTo(hash){
    const el = document.querySelector(hash);
    if(!el) return;
    const y = el.getBoundingClientRect().top + window.pageYOffset - HEADER_OFFSET;
    window.scrollTo({ top: y, behavior: 'smooth' });
  }
  document.querySelectorAll('a[href^="#"]').forEach(a=>{
    a.addEventListener('click',(e)=>{
      const hash = a.getAttribute('href');
      if(hash && hash.length>1){
        e.preventDefault();
        offsetScrollTo(hash);
      }
    });
  });
</script>
@endpush
@endsection
