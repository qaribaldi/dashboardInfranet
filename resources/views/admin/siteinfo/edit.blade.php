@extends('layouts.public')

@section('title','Edit Landing • Infranet')

@section('content')
<div class="relative min-h-screen overflow-x-hidden
    bg-[radial-gradient(1200px_600px_at_-10%_-20%,#c7d2fe_0%,transparent_60%),radial-gradient(1200px_600px_at_110%_120%,#bbf7d0_0%,transparent_60%)]
    from-indigo-50 via-white to-emerald-50">

  {{-- NAVBAR mini --}}
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
        <a href="#about"   class="px-3 py-2 rounded hover:bg-gray-50" data-close>About</a>
        <a href="#info"    class="px-3 py-2 rounded hover:bg-gray-50" data-close>Informasi</a>
        <a href="#contact" class="px-3 py-2 rounded hover:bg-gray-50" data-close>Contact</a>
        <div class="border-t my-2"></div>
        <a href="{{ route('landing') }}" class="px-3 py-2 rounded border text-center hover:bg-gray-50" data-close>Lihat Landing</a>
      </nav>
    </div>
    <div id="backdrop" class="fixed inset-0 bg-black/40 z-[55] hidden"></div>
  </header>

  {{-- ===== ABOUT US ===== --}}
  <section id="about" class="relative pt-28 pb-16 md:pt-32 md:pb-20">
    <div class="pointer-events-none absolute -top-24 -left-24 h-80 w-80 rounded-full 
                bg-gradient-to-br from-indigo-300/40 via-fuchsia-300/30 to-emerald-300/30 blur-3xl"></div>
    <div class="pointer-events-none absolute -bottom-24 -right-24 h-96 w-96 rounded-full 
                bg-gradient-to-tr from-emerald-300/40 via-sky-300/30 to-indigo-300/30 blur-3xl"></div>

    <div class="relative mx-auto max-w-4xl px-4">
      <h2 class="text-center text-3xl md:text-4xl font-extrabold">Tentang Kami</h2>

      <div class="mt-8 flex justify-center">
        <form method="POST" action="{{ route('admin.siteinfo.updateSection','about') }}"
              class="relative w-full md:w-3/4 rounded-2xl border border-black/10 bg-white/70 backdrop-blur p-6 shadow-sm">
          @csrf @method('PUT')

          <div class="absolute top-3 right-3 flex items-center gap-2">
            <button type="button" onclick="focusEditor('aboutEditor')"
              class="inline-flex items-center justify-center rounded-md border px-2 py-1 text-xs bg-white/80 hover:bg-white" aria-label="Fokus About">
              ✎
            </button>
            <button type="submit"
              class="inline-flex items-center rounded-md bg-slate-900 text-white text-xs px-3 py-1.5 hover:bg-black">
              Simpan
            </button>
          </div>

          <h3 class="font-semibold text-lg">Tentang INFRANET</h3>
          <div id="aboutEditor"
               class="mt-2 text-slate-700 rounded-lg border border-transparent focus-within:border-indigo-300 p-2 outline-none"
               contenteditable="true" data-target="about_content">
            {!! old('about_content', $info->about_content ?? '') !!}
          </div>
          <textarea name="about_content" id="about_content" class="hidden"></textarea>
        </form>
      </div>
    </div>
  </section>

  {{-- ===== INFORMASI ===== --}}
  <section id="info" class="relative py-16 md:py-20">
    <div class="pointer-events-none absolute -top-24 -left-24 h-80 w-80 rounded-full 
                bg-gradient-to-br from-indigo-300/40 via-fuchsia-300/30 to-emerald-300/30 blur-3xl"></div>
    <div class="pointer-events-none absolute -bottom-24 -right-24 h-96 w-96 rounded-full 
                bg-gradient-to-tr from-emerald-300/40 via-sky-300/30 to-indigo-300/30 blur-3xl"></div>

    <div class="relative mx-auto max-w-4xl px-4">
      <h2 class="text-center text-3xl md:text-4xl font-extrabold">Informasi</h2>

      <div class="mt-8 flex justify-center">
        <form method="POST" action="{{ route('admin.siteinfo.updateSection','info') }}"
              class="relative w-full md:w-3/4 rounded-2xl border border-black/10 bg-white/70 backdrop-blur p-6 shadow-sm">
          @csrf @method('PUT')

          <div class="absolute top-3 right-3 flex items-center gap-2">
            <button type="button" onclick="focusEditor('infoEditor')"
              class="inline-flex items-center justify-center rounded-md border px-2 py-1 text-xs bg-white/80 hover:bg-white" aria-label="Fokus Informasi">
              ✎
            </button>
            <button type="submit"
              class="inline-flex items-center rounded-md bg-slate-900 text-white text-xs px-3 py-1.5 hover:bg-black">
              Simpan
            </button>
          </div>

          <h3 class="font-semibold text-lg">Tata Cara Peminjaman Aset</h3>
          <div id="infoEditor"
               class="mt-2 text-slate-700 rounded-lg border border-transparent focus-within:border-indigo-300 p-2 outline-none"
               contenteditable="true" data-target="info_content">
            {!! old('info_content', $info->info_content ?? '') !!}
          </div>
          <textarea name="info_content" id="info_content" class="hidden"></textarea>
        </form>
      </div>
    </div>
  </section>

  {{-- ===== CONTACT ===== --}}
  <section id="contact" class="relative py-16 md:py-20">
    <div class="pointer-events-none absolute -top-24 -right-24 h-80 w-80 rounded-full 
                bg-gradient-to-br from-sky-300/40 via-indigo-300/30 to-emerald-300/30 blur-3xl"></div>

    <div class="relative mx-auto max-w-6xl px-4">
      <h2 class="text-center text-3xl md:text-4xl font-extrabold">Kontak</h2>

      <div class="mt-8 grid md:grid-cols-2 gap-6">
        {{-- Hubungi Kami --}}
        <form method="POST" action="{{ route('admin.siteinfo.updateSection','contact') }}"
              class="relative rounded-2xl border border-black/10 bg-white/70 backdrop-blur p-6 shadow-sm">
          @csrf @method('PUT')

          <div class="absolute top-3 right-3 flex items-center gap-2">
            <button type="button" onclick="focusEditor('contactEditor')"
              class="inline-flex items-center justify-center rounded-md border px-2 py-1 text-xs bg-white/80 hover:bg-white" aria-label="Fokus Contact">
              ✎
            </button>
            <button type="submit"
              class="inline-flex items-center rounded-md bg-slate-900 text-white text-xs px-3 py-1.5 hover:bg-black">
              Simpan
            </button>
          </div>

          <h3 class="font-semibold text-lg">Hubungi Kami</h3>
          <div id="contactEditor"
               class="mt-4 space-y-3 text-slate-700 rounded-lg border border-transparent focus-within:border-indigo-300 p-2 outline-none"
               contenteditable="true" data-target="contact_content">
            {!! old('contact_content', $info->contact_content ?? '') !!}
          </div>
          <textarea name="contact_content" id="contact_content" class="hidden"></textarea>
        </form>

        {{-- Jam Layanan --}}
        <form method="POST" action="{{ route('admin.siteinfo.updateSection','hours') }}"
              class="relative rounded-2xl border border-black/10 bg-white/70 backdrop-blur p-6 shadow-sm">
          @csrf @method('PUT')

          <div class="absolute top-3 right-3 flex items-center gap-2">
            <button type="button" onclick="focusEditor('hoursEditor')"
              class="inline-flex items-center justify-center rounded-md border px-2 py-1 text-xs bg-white/80 hover:bg-white" aria-label="Fokus Jam">
              ✎
            </button>
            <button type="submit"
              class="inline-flex items-center rounded-md bg-slate-900 text-white text-xs px-3 py-1.5 hover:bg-black">
              Simpan
            </button>
          </div>

          <h3 class="font-semibold text-lg">Jam Layanan</h3>
          <div id="hoursEditor"
               class="mt-4 text-slate-700 text-sm rounded-lg border border-transparent focus-within:border-indigo-300 p-2 outline-none"
               contenteditable="true" data-target="service_hours_content">
            {!! old('service_hours_content', $info->service_hours_content ?? '') !!}
          </div>
          <textarea name="service_hours_content" id="service_hours_content" class="hidden"></textarea>
        </form>
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
  // Drawer
  const btn = document.getElementById('btnMenu');
  const btnClose = document.getElementById('btnClose');
  const drawer = document.getElementById('mobileMenu');
  const backdrop = document.getElementById('backdrop');
  function openMenu(){ drawer.classList.remove('translate-x-full'); backdrop.classList.remove('hidden'); }
  function closeMenu(){ drawer.classList.add('translate-x-full'); backdrop.classList.add('hidden'); }
  btn?.addEventListener('click', openMenu);
  btnClose?.addEventListener('click', closeMenu);
  backdrop?.addEventListener('click', closeMenu);
  drawer?.querySelectorAll('[data-close]').forEach(a => a.addEventListener('click', closeMenu));

  // Fokus editor dari tombol ✎
  function focusEditor(id){
    const el = document.getElementById(id);
    if (!el) return;
    el.focus();
    const sel = window.getSelection(), range = document.createRange();
    range.selectNodeContents(el); range.collapse(false);
    sel.removeAllRanges(); sel.addRange(range);
  }
  window.focusEditor = focusEditor;

  // Sinkronisasi: hanya editor di dalam FORM yang disubmit
  document.querySelectorAll('form').forEach(form => {
    form.addEventListener('submit', () => {
      const ed = form.querySelector('[contenteditable="true"]');
      if (!ed) return;
      const targetId = ed.dataset.target || '';
      const ta = form.querySelector(`#${targetId}`);
      if (ta) ta.value = ed.innerHTML.trim();
    });
  });

  // UX ring saat fokus
  document.querySelectorAll('[contenteditable="true"]').forEach(el=>{
    el.addEventListener('focus', ()=> el.classList.add('ring-1','ring-indigo-300'));
    el.addEventListener('blur',  ()=> el.classList.remove('ring-1','ring-indigo-300'));
  });
</script>
@endpush
@endsection
