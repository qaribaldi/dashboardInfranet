<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>@yield('title', 'Infranet Kampus')</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    {{-- CSRF (berguna kalau nanti ada fetch POST/DELETE dari JS) --}}
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 text-gray-900">
<div class="min-h-screen flex">
    {{-- Sidebar --}}
    <aside class="w-64 bg-white border-r border-gray-200 p-4">
        <h1 class="text-lg font-bold mb-6">Infranet</h1>
        <nav class="space-y-2">
            <a href="{{ route('dashboard') }}"
               class="block px-3 py-2 rounded-lg hover:bg-gray-100 {{ request()->routeIs('dashboard') ? 'bg-gray-100 font-semibold' : '' }}">
                Dashboard
            </a>
            <div>
                <a href="{{ route('pc.index') }}"
                   class="block px-3 py-2 rounded-lg hover:bg-gray-100 {{ str_starts_with(request()->path(),'inventory') ? 'bg-gray-50 font-semibold' : '' }}">
                    Inventory
                </a>
                <!-- <div class="mt-1 ml-3 space-y-1">
                    <a class="block px-3 py-1.5 rounded hover:bg-gray-100 {{ request()->routeIs('pc.*') ? 'bg-gray-100 font-semibold' : '' }}" href="{{ route('pc.index') }}">PC</a>
                    <a class="block px-3 py-1.5 rounded hover:bg-gray-100 {{ request()->routeIs('printer.*') ? 'bg-gray-100 font-semibold' : '' }}" href="{{ route('printer.index') }}">Printer</a>
                    <a class="block px-3 py-1.5 rounded hover:bg-gray-100 {{ request()->routeIs('proyektor.*') ? 'bg-gray-100 font-semibold' : '' }}" href="{{ route('proyektor.index') }}">Proyektor</a>
                </div> -->
            </div>
        </nav>
    </aside>

    {{-- Content --}}
    <main class="flex-1 p-6">
        @if (session('success'))
            <div class="mb-4 rounded-lg border border-green-200 bg-green-50 text-green-800 px-4 py-3">
                {{ session('success') }}
            </div>
        @endif
        @yield('content')
    </main>
</div>

{{-- Modal Global --}}
<div id="modalOverlay" class="fixed inset-0 z-50 hidden">
  {{-- backdrop --}}
  <div id="modalBackdrop" class="absolute inset-0 bg-black/40"></div>

  {{-- modal card --}}
  <div class="relative mx-auto my-8 w-[95%] max-w-4xl rounded-2xl bg-white shadow-xl border border-gray-200
              max-h-[85vh] overflow-hidden">
    <div class="flex items-center justify-between border-b px-4 md:px-6 py-3">
      <h3 id="modalTitle" class="text-lg font-semibold">Detail</h3>
      <button type="button" class="rounded-lg border px-2.5 py-1.5 hover:bg-gray-50" onclick="closeModal()">
        &times; <span class="sr-only">Close</span>
      </button>
    </div>
    <div id="modalBody" class="p-4 md:p-6 overflow-y-auto max-h-[70vh]">
      {{-- konten detail akan dimuat di sini --}}
    </div>
    <div class="border-t px-4 md:px-6 py-3 flex justify-end">
      <button class="rounded-lg border px-4 py-2 hover:bg-gray-50" onclick="closeModal()">Tutup</button>
    </div>
  </div>
</div>

<script>
  const modalOverlay = document.getElementById('modalOverlay');
  const modalBackdrop = document.getElementById('modalBackdrop');
  const modalBody = document.getElementById('modalBody');
  const modalTitle = document.getElementById('modalTitle');

  let lastFocusedEl = null;

  function showLoading() {
    modalBody.innerHTML = `
      <div class="flex items-center gap-3 text-gray-500">
        <svg class="animate-spin h-5 w-5" viewBox="0 0 24 24" fill="none">
          <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
          <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"/>
        </svg>
        Memuat...
      </div>`;
  }

  async function openModal(url, title = 'Detail') {
    // simpan fokus terakhir & kunci scroll body
    lastFocusedEl = document.activeElement;
    document.body.style.overflow = 'hidden';

    modalTitle.textContent = title;
    showLoading();
    modalOverlay.classList.remove('hidden');

    try {
      const res = await fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
      const html = await res.text();
      modalBody.innerHTML = html;
    } catch (e) {
      modalBody.innerHTML = '<div class="text-red-600">Gagal memuat detail.</div>';
    }

    // fokus ke tombol tutup untuk aksesibilitas
    const closeBtn = modalOverlay.querySelector('button[onclick="closeModal()"]');
    closeBtn && closeBtn.focus();
  }

  function closeModal() {
    modalOverlay.classList.add('hidden');
    modalBody.innerHTML = '';
    document.body.style.overflow = ''; // pulihkan scroll
    // kembalikan fokus
    if (lastFocusedEl) lastFocusedEl.focus();
  }

  // klik di luar card → close
  modalOverlay.addEventListener('click', (e) => {
    if (e.target === modalOverlay || e.target === modalBackdrop) {
      closeModal();
    }
  });

  // Esc → close
  window.addEventListener('keydown', (e) => {
    if (e.key === 'Escape' && !modalOverlay.classList.contains('hidden')) {
      closeModal();
    }
  });

  // focus trap sederhana di dalam modal
  document.addEventListener('keydown', (e) => {
    if (e.key !== 'Tab' || modalOverlay.classList.contains('hidden')) return;

    const focusables = modalOverlay.querySelectorAll('a, button, input, select, textarea, [tabindex]:not([tabindex="-1"])');
    if (!focusables.length) return;
    const first = focusables[0];
    const last = focusables[focusables.length - 1];

    if (e.shiftKey) {
      if (document.activeElement === first) {
        e.preventDefault();
        last.focus();
      }
    } else {
      if (document.activeElement === last) {
        e.preventDefault();
        first.focus();
      }
    }
  });
</script>

@stack('body-end')
</body>
</html>
