<aside class="w-64 shrink-0 bg-white dark:bg-gray-800 border-r border-gray-200 dark:border-gray-700 min-h-screen">
  {{-- Header --}}
  <div class="px-4 py-4 flex items-center gap-2 border-b border-gray-200 dark:border-gray-700">
    <a href="{{ route('dashboard') }}" class="inline-flex items-center gap-2">
      <x-application-logo class="h-7 w-7 text-gray-800 dark:text-gray-200"/>
      <span class="font-semibold text-gray-800 dark:text-gray-100">Direktorat Infranet</span>
    </a>
  </div>

  @php
    $u = auth()->user();

    // Permission flags
    $canPc        = $u?->can('inventory.pc.view');
    $canPrinter   = $u?->can('inventory.printer.view');
    $canProyektor = $u?->can('inventory.proyektor.view');
    $canAc        = $u?->can('inventory.ac.view');
    $canHardware  = $u?->can('inventory.hardware.view');
    $canLabkom    = $u?->can('inventory.labkom.view');   

    $canAnyAsset  = $canPc || $canPrinter || $canProyektor || $canAc; 
    $canAnyInv    = $canAnyAsset || $canHardware || $canLabkom;      

    // Active states
    $isDash   = request()->routeIs('dashboard');
    $isInvGrp = request()->routeIs('inventory.*'); // buka collapsible saat berada di halaman inventory

    $isAssetActive    = request()->routeIs('inventory.pc.*')
                       || request()->routeIs('inventory.printer.*')
                       || request()->routeIs('inventory.proyektor.*')
                       || request()->routeIs('inventory.ac.*');

    $isHardwareActive = request()->routeIs('inventory.hardware.*');
    $isLabkomActive   = request()->routeIs('inventory.labkom.*');      
  @endphp

  <nav class="p-3">
    {{-- DASHBOARD --}}
    @can('dashboard.view')
      <div class="space-y-1">
        <a href="{{ route('dashboard') }}"
           class="flex items-center gap-3 px-3 py-2 rounded-lg
                  {{ $isDash ? 'bg-gray-100 dark:bg-gray-700 text-gray-900 dark:text-white' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100/70 dark:hover:bg-gray-700/70' }}">
          <svg class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
            <path d="M3 12l9-9 9 9v9a1 1 0 0 1-1 1h-5v-6H9v6H4a1 1 0 0 1-1-1v-9z"/>
          </svg>
          <span>Dashboard</span>
        </a>
      </div>
    @endcan

    <div class="mt-6"></div>

    {{-- INVENTORY (collapsible) --}}
    @if($canAnyInv)
      <details class="group" {{ $isInvGrp ? 'open' : '' }}>
        <summary class="flex items-center justify-between cursor-pointer px-3 py-2 rounded-lg
                        {{ $isInvGrp ? 'bg-gray-100 dark:bg-gray-700 text-gray-900 dark:text-white' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100/70 dark:hover:bg-gray-700/70' }}">
          <span class="inline-flex items-center gap-3">
            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
              <path d="M3 5h18v4H3V5zm0 6h18v8H3v-8zm2 2v4h14v-4H5z"/>
            </svg>
            <span>Inventory</span>
          </span>
          <svg class="h-4 w-4 transition-transform duration-200 {{ $isInvGrp ? 'rotate-90' : 'rotate-0' }}" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
            <path d="M9 5l7 7-7 7"/>
          </svg>
        </summary>

        {{-- SUBMENUS --}}
        <div class="mt-2 ml-9 flex flex-col gap-1">

          {{-- Inventory Aset (PC/Printer/Proyektor/AC) --}}
          @if($canAnyAsset)
            <a href="{{ route('inventory') }}"
               class="px-3 py-2 rounded-lg text-sm
                      {{ $isAssetActive ? 'bg-gray-100 dark:bg-gray-700 text-gray-900 dark:text-white'
                                        : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100/70 dark:hover:bg-gray-700/70' }}">
              Inventory Aset
            </a>
          @endif

          {{-- Inventory Hardware --}}
          @if($canHardware)
            <a href="{{ route('inventory.hardware.index') }}"
               class="px-3 py-2 rounded-lg text-sm
                      {{ $isHardwareActive ? 'bg-gray-100 dark:bg-gray-700 text-gray-900 dark:text-white'
                                            : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100/70 dark:hover:bg-gray-700/70' }}">
              Inventory Hardware
            </a>
          @endif

          {{-- Inventory Labkom --}}
          @if($canLabkom)
            <a href="{{ route('inventory.labkom.index') }}"
               class="px-3 py-2 rounded-lg text-sm
                      {{ $isLabkomActive ? 'bg-gray-100 dark:bg-gray-700 text-gray-900 dark:text-white'
                                         : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100/70 dark:hover:bg-gray-700/70' }}">
              Inventory Labkom
            </a>
          @endif

        </div>
      </details>
    @endif
  </nav>
</aside>
