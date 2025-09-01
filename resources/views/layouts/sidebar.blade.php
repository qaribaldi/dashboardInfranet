<aside class="w-64 shrink-0 bg-white dark:bg-gray-800 border-r border-gray-200 dark:border-gray-700 min-h-screen">
  <div class="px-4 py-4 flex items-center gap-2 border-b border-gray-200 dark:border-gray-700">
    <a href="{{ route('dashboard') }}" class="inline-flex items-center gap-2">
      <x-application-logo class="h-7 w-7 text-gray-800 dark:text-gray-200"/>
      <span class="font-semibold text-gray-800 dark:text-gray-100">Direktorat Infranet</span>
    </a>
  </div>

  <nav class="p-3 space-y-1">
    {{-- Dashboard --}}
    <a href="{{ route('dashboard') }}"
       class="flex items-center gap-3 px-3 py-2 rounded-lg
              {{ request()->routeIs('dashboard') ? ' dark:text-white' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100/70 dark:hover:bg-gray-700/70' }}">
      <svg class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor"><path d="M3 12l9-9 9 9v9a1 1 0 0 1-1 1h-5v-6H9v6H4a1 1 0 0 1-1-1v-9z"/></svg>
      <span>Dashboard</span>
    </a>

    {{-- Inventory (default ke PC) --}}
    <a href="{{ route('inventory.pc.index') }}"
       class="flex items-center gap-3 px-3 py-2 rounded-lg
              {{ request()->routeIs('inventory.*') ? 'bg-gray-100 dark:bg-gray-700 text-gray-900 dark:text-white' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100/70 dark:hover:bg-gray-700/70' }}">
      <svg class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor"><path d="M3 5h18v4H3V5zm0 6h18v8H3v-8zm2 2v4h14v-4H5z"/></svg>
      <span>Inventory</span>
    </a>
  </nav>
</aside>
