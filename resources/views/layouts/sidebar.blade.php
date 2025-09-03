<aside class="w-64 shrink-0 bg-white dark:bg-gray-800 border-r border-gray-200 dark:border-gray-700 min-h-screen">
  {{-- Header --}}
  <div class="px-4 py-4 flex items-center gap-2 border-b border-gray-200 dark:border-gray-700">
    <a href="{{ route('dashboard') }}" class="inline-flex items-center gap-2">
      <x-application-logo class="h-7 w-7 text-gray-800 dark:text-gray-200"/>
      <span class="font-semibold text-gray-800 dark:text-gray-100">Direktorat Infranet</span>
    </a>
  </div>

  @php
    $isInv = request()->routeIs('inventory.*'); // untuk buka dropdown saat berada di halaman inventory
  @endphp

  <nav class="p-3">
    {{-- DASHBOARD --}}
    <div class="space-y-1">
      <a href="{{ route('dashboard') }}"
         class="flex items-center gap-3 px-3 py-2 rounded-lg
                {{ request()->routeIs('dashboard') ? 'bg-gray-100 dark:bg-gray-700 text-gray-900 dark:text-white' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100/70 dark:hover:bg-gray-700/70' }}">
        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor">
          <path d="M3 12l9-9 9 9v9a1 1 0 0 1-1 1h-5v-6H9v6H4a1 1 0 0 1-1-1v-9z"/>
        </svg>
        <span>Dashboard</span>
      </a>
    </div>

    {{-- SPACER lebih lebar antara Dashboard dan Inventory --}}
    <div class="mt-6"></div>

    {{-- INVENTORY (collapsible) --}}
    <details class="group" {{ $isInv ? 'open' : '' }}>
      <summary class="flex items-center justify-between cursor-pointer px-3 py-2 rounded-lg
                      {{ $isInv ? 'bg-gray-100 dark:bg-gray-700 text-gray-900 dark:text-white' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100/70 dark:hover:bg-gray-700/70' }}">
        <span class="inline-flex items-center gap-3">
          <svg class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor">
            <path d="M3 5h18v4H3V5zm0 6h18v8H3v-8zm2 2v4h14v-4H5z"/>
          </svg>
          <span>Inventory</span>
        </span>
        {{-- chevron --}}
        <svg class="h-4 w-4 transition-transform duration-200 {{ $isInv ? 'rotate-90' : 'rotate-0' }}" viewBox="0 0 24 24" fill="currentColor">
          <path d="M9 5l7 7-7 7"/>
        </svg>
      </summary>

      {{-- SUBMENUS --}}
      <div class="mt-2 ml-9 flex flex-col gap-1">
        {{-- Inventory Aset (ke modul aset: default PC index / redirect inventory) --}}
        <a href="{{ route('inventory.pc.index') }}"
           class="px-3 py-2 rounded-lg text-sm
                  {{ request()->routeIs('inventory.pc.*') || request()->routeIs('inventory.printer.*') || request()->routeIs('inventory.proyektor.*') || request()->routeIs('inventory.ac.*')
                      ? 'bg-gray-100 dark:bg-gray-700 text-gray-900 dark:text-white'
                      : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100/70 dark:hover:bg-gray-700/70' }}">
          Inventory Aset
        </a>

        {{-- Inventory Hardware --}}
        <a href="{{ route('inventory.hardware.index') }}"
           class="px-3 py-2 rounded-lg text-sm
                  {{ request()->routeIs('inventory.hardware.*')
                      ? 'bg-gray-100 dark:bg-gray-700 text-gray-900 dark:text-white'
                      : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100/70 dark:hover:bg-gray-700/70' }}">
          Inventory Hardware
        </a>
      </div>
    </details>
  </nav>
</aside>
