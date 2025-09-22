@extends('layouts.public')

@section('title','Selamat Datang • Infranet')

@section('content')
{{-- ====== WRAPPER: gradient sampai bawah halaman ====== --}}
<div class="relative min-h-screen overflow-x-hidden
  bg-[radial-gradient(1200px_600px_at_-10%_-20%,#c7d2fe_0%,transparent_60%),radial-gradient(1200px_600px_at_110%_120%,#bbf7d0_0%,transparent_60%)]
  from-indigo-50 via-white to-emerald-50">

{{-- ============ NAVBAR (publik) ============ --}}
<header class="fixed top-0 left-0 right-0 z-50">
  {{-- Tombol hamburger: selalu di pojok kanan layar --}}
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

  {{-- Drawer menu (slide dari kanan) --}}
  <div id="mobileMenu"
       class="fixed top-0 right-0 h-full w-[78%] max-w-xs bg-white border-l shadow-xl
              translate-x-full transition-transform duration-300 z-[60]">
    <div class="h-14 flex items-center justify-between px-4 border-b">
      <div class="font-semibold">Menu</div>
      <button id="btnClose" class="w-9 h-9 rounded-lg border hover:bg-gray-50" aria-label="Close menu">✕</button>
    </div>
    <nav class="p-4 flex flex-col gap-1 text-sm">
      <a href="#home" class="px-3 py-2 rounded hover:bg-gray-50" data-close>Home</a>
      <a href="#about" class="px-3 py-2 rounded hover:bg-gray-50" data-close>About Us</a>
      <a href="#research" class="px-3 py-2 rounded hover:bg-gray-50" data-close>Our Research</a>
      <div class="border-t my-2"></div>
      @guest
        <a href="{{ route('login') }}" class="px-3 py-2 rounded border text-center hover:bg-gray-50" data-close>Login / Register</a>
      @else
        <a href="{{ route('dashboard') }}" class="px-3 py-2 rounded bg-gray-900 text-white text-center" data-close>Buka Dashboard</a>
      @endguest
    </nav>
  </div>

  {{-- backdrop --}}
  <div id="backdrop" class="fixed inset-0 bg-black/40 z-[55] hidden"></div>
</header>


  {{-- ====== HERO (konten utama, dekorasi tetap) ====== --}}
  <section id="home" class="relative min-h-[92vh] flex flex-col items-center justify-center text-center">

    {{-- Dekorasi gradien besar sisi kiri/kanan --}}
    <div class="pointer-events-none absolute -top-40 -left-40 h-[38rem] w-[38rem] rounded-full 
                bg-gradient-to-br from-indigo-300/50 via-fuchsia-300/40 to-emerald-300/40 blur-3xl"></div>
    <div class="pointer-events-none absolute -bottom-48 -right-48 h-[42rem] w-[42rem] rounded-full 
                bg-gradient-to-tr from-emerald-300/50 via-sky-300/40 to-indigo-300/40 blur-3xl"></div>

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
  </section>

  {{-- ====== ABOUT US ====== --}}
  <section id="about" class="py-16 md:py-20">
    <div class="mx-auto max-w-6xl px-4">
      <div class="text-center">
        <h2 class="text-3xl md:text-4xl font-extrabold">About Us</h2>
        <p>Lorem, ipsum dolor sit amet consectetur adipisicing elit. Adipisci quo a inventore aspernatur ex deserunt repellat sit reiciendis praesentium nam ducimus corporis magnam vero nemo corrupti quisquam, numquam eveniet amet. Lorem, ipsum dolor sit amet consectetur adipisicing elit. Aliquid ducimus possimus ut dolorum porro iste aspernatur, cum neque maiores libero est corrupti, distinctio itaque, officia quia quisquam repudiandae inventore? Officiis? Lorem ipsum dolor sit amet consectetur adipisicing elit. Est, accusamus! A dolore in perferendis voluptatem quis sunt commodi ea voluptates laudantium tempora itaque, doloribus quasi accusamus omnis cumque dicta aliquam. Lorem ipsum dolor sit amet consectetur adipisicing elit. Totam veniam consequatur rerum laboriosam vel, fugiat dicta officia eveniet culpa. Vel ipsa, aliquid ad esse eaque explicabo suscipit ratione corporis non.</p>
      </div>
    </div>
  </section>

  {{-- ====== OUR RESEARCH (What We Do) ====== --}}
  <section id="research" class="py-16 md:py-20">
    <div class="mx-auto max-w-6xl px-4">
      <div class="text-center">
        <h2 class="text-3xl md:text-4xl font-extrabold">What We Do</h2>
      </div>

      <div class="mt-10 grid md:grid-cols-2 gap-x-10 gap-y-12">
        {{-- Item 1 --}}
        <div class="flex flex-col gap-3">
          <div class="rounded-xl border overflow-hidden">
            <div class="aspect-[16/9] w-full bg-indigo-50 grid place-content-center text-indigo-400">
              {{-- <img src="{{ asset('images/landing/networking.png') }}" class="w-full h-full object-cover" alt="Networking"> --}}
              <span class="text-sm">Illustration / Poster</span>
            </div>
          </div>
          <div>
            <div class="text-[11px] tracking-widest font-semibold text-gray-600">NETWORKING</div>
            <p class="mt-1 text-sm text-slate-700">
              Secara berkala maintenance dan peningkatan infrastruktur teknologi informasi yang dimiliki UNIKOM
              dengan tetap memperhatikan efisiensi dan optimalisasi.
            </p>
          </div>
        </div>

        {{-- Item 2 --}}
        <div class="flex flex-col gap-3">
          <div class="rounded-xl border overflow-hidden">
            <div class="aspect-[16/9] w-full bg-emerald-50 grid place-content-center text-emerald-400">
              {{-- <img src="{{ asset('images/landing/itsupport.png') }}" class="w-full h-full object-cover" alt="IT Support"> --}}
              <span class="text-sm">Illustration / Poster</span>
            </div>
          </div>
          <div>
            <div class="text-[11px] tracking-widest font-semibold text-gray-600">IT SUPPORT</div>
            <p class="mt-1 text-sm text-slate-700">
              Melakukan maintenance & peningkatan infrastruktur TI yang dimiliki UNIKOM dengan fokus pada pengalaman pengguna.
            </p>
          </div>
        </div>

        {{-- Item 3 --}}
        <div class="flex flex-col gap-3">
          <div class="rounded-xl border overflow-hidden">
            <div class="aspect-[16/9] w-full bg-yellow-50 grid place-content-center text-yellow-500">
              {{-- <img src="{{ asset('images/landing/smart-infranet.png') }}" class="w-full h-full object-cover" alt="Smart Infranet"> --}}
              <span class="text-sm">Illustration / Poster</span>
            </div>
          </div>
          <div>
            <div class="text-[11px] tracking-widest font-semibold text-gray-600">SMART INFRANET</div>
            <p class="mt-1 text-sm text-slate-700">
              Riset berkelanjutan terhadap kebutuhan dan pemanfaatan IT infrastructure and network.
            </p>
          </div>
        </div>

        {{-- Item 4 --}}
        <div class="flex flex-col gap-3">
          <div class="rounded-xl border overflow-hidden">
            <div class="aspect-[16/9] w-full bg-teal-50 grid place-content-center text-teal-500">
              {{-- <img src="{{ asset('images/landing/labkom.png') }}" class="w-full h-full object-cover" alt="Labkom"> --}}
              <span class="text-sm">Illustration / Poster</span>
            </div>
          </div>
          <div>
            <div class="text-[11px] tracking-widest font-semibold text-gray-600">LABKOM</div>
            <p class="mt-1 text-sm text-slate-700">
              Monitoring & controlling maintenance laboratorium komputer di lingkungan UNIKOM.
            </p>
          </div>
        </div>

        {{-- Item 5 (lebar setengah) --}}
        <div class="flex flex-col gap-3 md:col-span-2">
          <div class="rounded-xl border overflow-hidden md:w-1/2">
            <div class="aspect-[16/9] w-full bg-sky-50 grid place-content-center text-sky-500">
              {{-- <img src="{{ asset('images/landing/asset-infra.png') }}" class="w-full h-full object-cover" alt="Asset Infrastructure"> --}}
              <span class="text-sm">Illustration / Poster</span>
            </div>
          </div>
          <div class="md:w-1/2">
            <div class="text-[11px] tracking-widest font-semibold text-gray-600">ASSET INFRASTRUCTURE</div>
            <p class="mt-1 text-sm text-slate-700">
              Pendataan aset PC, proyektor, printer, dan AC di lingkungan civitas UNIKOM.
            </p>
          </div>
        </div>
      </div>
    </div>
  </section>

{{-- ====== FOOTER GLOBAL (paling bawah) ====== --}}
<footer class="mt-10 border-t border-black/5">
  <div class="mx-auto max-w-6xl px-4 py-8 text-center">
    <div class="flex items-center justify-center gap-5 mb-3">
      {{-- IG --}}
      <button type="button" class="opacity-70 hover:opacity-100 transition" aria-label="Instagram" title="Instagram">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="currentColor" viewBox="0 0 24 24">
          <path d="M7 2h10a5 5 0 0 1 5 5v10a5 5 0 0 1-5 5H7a5 5 0 0 1-5-5V7a5 5 0 0 1 5-5Zm0 2a3 3 0 0 0-3 3v10a3 3 0 0 0 3 3h10a3 3 0 0 0 3-3V7a3 3 0 0 0-3-3H7Zm5 3.5a5.5 5.5 0 1 1 0 11 5.5 5.5 0 0 1 0-11Zm0 2a3.5 3.5 0 1 0 0 7 3.5 3.5 0 0 0 0-7Zm5.75-.75a1.25 1.25 0 1 1 0 2.5 1.25 1.25 0 0 1 0-2.5Z"/>
        </svg>
      </button>

      {{-- YouTube --}}
      <button type="button" class="opacity-70 hover:opacity-100 transition" aria-label="YouTube" title="YouTube">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" viewBox="0 0 24 24" fill="currentColor">
          <path d="M23.5 6.2a3.1 3.1 0 0 0-2.2-2.2C19.5 3.5 12 3.5 12 3.5s-7.5 0-9.3.5a3.1 3.1 0 0 0-2.2 2.2C0 8 0 12 0 12s0 4 .5 5.8a3.1 3.1 0 0 0 2.2 2.2c1.8.5 9.3.5 9.3.5s7.5 0 9.3-.5a3.1 3.1 0 0 0 2.2-2.2C24 16 24 12 24 12s0-4-.5-5.8ZM9.6 15.5v-7l6.4 3.5-6.4 3.5Z"/>
        </svg>
      </button>

      {{-- X (Twitter) --}}
      <button type="button" class="opacity-70 hover:opacity-100 transition" aria-label="X" title="X">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="currentColor" viewBox="0 0 24 24">
          <path d="M13.3 10.6 20.8 3h-1.8l-6.5 6.9L7 3H2l8.1 11.6L2 21h1.8l7-7.4 6 7.4H22l-8.7-10.4Zm-2.5 2.6-.8-1.1L4 4.3h2.3l4.5 6.2.8 1.1 6.6 9.1h-2.3l-5.1-7.4Z"/>
        </svg>
      </button>
    </div>

    <div class="text-sm text-slate-600/80">
      © {{ date('Y') }} Direktorat IT Infrastructure & Network (INFRANET) • UNIKOM
    </div>
  </div>
</footer>

</div>

{{-- ====== JS: drawer + smooth scroll offset ====== --}}
@push('body-end')
<script>
  // Pastikan layout 'public' punya @stack('body-end') sebelum </body>

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
  drawer && drawer.querySelectorAll('[data-close]').forEach(a => a.addEventListener('click', closeMenu));

  // Smooth scroll + sedikit offset
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
