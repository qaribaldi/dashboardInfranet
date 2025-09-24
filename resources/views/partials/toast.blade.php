<div id="toast-root" class="fixed top-5 right-5 z-[9999] space-y-2 pointer-events-none"></div>

<script>
  // Simple toast (no deps)
  window.toast = function(type = 'info', message = '', opts = {}) {
    const root = document.getElementById('toast-root');
    if (!root) return;

    const colors = {
      success: 'border-green-200 bg-green-50 text-green-800',
      error:   'border-red-200 bg-red-50 text-red-800',
      warning: 'border-yellow-200 bg-yellow-50 text-yellow-800',
      info:    'border-sky-200 bg-sky-50 text-sky-800',
    };
    const icon = {
      success: 'M5 13l4 4L19 7',
      error:   'M6 18L18 6M6 6l12 12',
      warning: 'M12 9v4m0 4h.01',
      info:    'M13 16h-1v-4h-1m1-4h.01',
    };

    const wrap = document.createElement('div');
    wrap.className =
      'pointer-events-auto rounded-xl border shadow-sm px-4 py-3 flex items-start gap-3 max-w-sm ' +
      (colors[type] || colors.info);

    wrap.innerHTML = `
      <svg class="mt-0.5 h-5 w-5 flex-none" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <path stroke-linecap="round" stroke-linejoin="round" d="${icon[type] || icon.info}"></path>
      </svg>
      <div class="text-sm leading-5">${message}</div>
      <button type="button" aria-label="Close"
              class="ml-auto -mr-2 rounded-md px-2 py-1 hover:bg-black/5">
        ✕
      </button>
    `;

    const closer = wrap.querySelector('button[aria-label="Close"]');
    closer.addEventListener('click', () => root.removeChild(wrap));

    root.appendChild(wrap);

    const dur = opts.duration ?? 4500;
    if (dur > 0) setTimeout(() => {
      if (wrap.parentNode) root.removeChild(wrap);
    }, dur);
  };

  // Tangkap error JS di browser → tampilkan toast (biar gak senyap)
  window.addEventListener('error', (e) => {
    if (!e.message) return;
    toast('error', 'Terjadi kesalahan pada halaman: ' + e.message, { duration: 7000 });
  });
  window.addEventListener('unhandledrejection', (e) => {
    const msg = (e.reason && (e.reason.message || e.reason.toString())) || 'Unhandled promise rejection';
    toast('error', 'Terjadi kesalahan: ' + msg, { duration: 7000 });
  });
</script>

{{-- Emit toast dari flash/session --}}
@if (session('success'))
  <script>toast('success', @json(session('success')))</script>
@endif
@if (session('error'))
  <script>toast('error', @json(session('error')))</script>
@endif
@if ($errors->any())
  <script>
    toast('error', @json($errors->first()));
  </script>
@endif
