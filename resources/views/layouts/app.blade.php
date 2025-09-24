<!DOCTYPE html>
<html lang="{{ str_replace('_','-', app()->getLocale()) }}">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', config('app.name','Laravel'))</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css','resources/js/app.js'])
  </head>
  <body class="font-sans antialiased">
    <div class="min-h-screen flex">
      {{-- Sidebar kiri --}}
      @include('layouts.sidebar')

      {{-- Kanan: topbar kecil + konten --}}
      <div class="flex-1 flex flex-col">
        {{-- Topbar --}}
        <header class="h-16 bg-white/60 dark:border-gray-800 flex items-center justify-end px-4">
          <x-dropdown align="right" width="48">
            <x-slot name="trigger">
              <button class="inline-flex items-center px-3 py-2 text-sm rounded-md text-black hover:bg-gray-100 dark:hover:bg-gray-700">
                <span>{{ Auth::user()->name }}</span>
                <svg class="ml-1 h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                  <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 10.94l3.71-3.71a.75.75 0 111.06 1.06l-4.24 4.24a.75.75 0 01-1.06 0L5.21 8.29a.75.75 0 01.02-1.08z" clip-rule="evenodd"/>
                </svg>
              </button>
            </x-slot>

            <x-slot name="content">
              {{-- === MENU KHUSUS ADMIN (role admin saja) === --}}
              @php
                $isAdmin = auth()->check() && method_exists(auth()->user(), 'hasRole') && auth()->user()->hasRole('admin');
              @endphp
              @if ($isAdmin)
                @if (Route::has('admin.users.index'))
                  <x-dropdown-link href="{{ route('admin.users.index') }}">
                    {{ __('Manajemen User') }}
                  </x-dropdown-link>
                @endif
                <div class="border-t my-1"></div>
              @endif

              {{-- === EDIT LANDING (berdasarkan permission, TIDAK harus admin) === --}}
              @can('siteinfo.manage')
                @if (Route::has('admin.siteinfo.edit'))
                  <x-dropdown-link href="{{ route('admin.siteinfo.edit') }}">
                    {{ __('Edit Landing Page') }}
                  </x-dropdown-link>
                @endif
                <div class="border-t my-1"></div>
              @endcan

              {{-- === LOGOUT === --}}
              <form method="POST" action="{{ route('logout') }}">
                @csrf
                <x-dropdown-link href="{{ route('logout') }}"
                  onclick="event.preventDefault(); this.closest('form').submit();">
                  {{ __('Log Out') }}
                </x-dropdown-link>
              </form>
            </x-slot>
          </x-dropdown>
        </header>

        {{-- Content --}}
        <main class="flex-1 p-6">
          @once
            @if (session('success'))
              <div class="mb-4 rounded-lg border border-green-200 bg-green-50 text-green-800 px-4 py-3">
                {{ session('success') }}
              </div>
            @endif

            @if (session('error'))
              <div class="mb-4 rounded-lg border border-red-200 bg-red-50 text-red-800 px-4 py-3">
                {{ session('error') }}
              </div>
            @endif

            @if ($errors->any())
              <div class="mb-4 rounded-lg border border-red-200 bg-red-50 text-red-800 px-4 py-3">
                <ul class="list-disc pl-5 space-y-1">
                  @foreach ($errors->all() as $e)
                    <li>{{ $e }}</li>
                  @endforeach
                </ul>
              </div>
            @endif
          @endonce

          @yield('content')
        </main>

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

            const closeBtn = modalOverlay.querySelector('button[onclick="closeModal()"]');
            closeBtn && closeBtn.focus();
          }

          function closeModal() {
            modalOverlay.classList.add('hidden');
            modalBody.innerHTML = '';
            document.body.style.overflow = '';
            if (lastFocusedEl) lastFocusedEl.focus();
          }

          modalOverlay.addEventListener('click', (e) => {
            if (e.target === modalOverlay || e.target === modalBackdrop) {
              closeModal();
            }
          });

          window.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && !modalOverlay.classList.contains('hidden')) {
              closeModal();
            }
          });

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
        
        @include('partials.toast')

        @stack('body-end')
      </div>
    </div>
  </body>
</html>
