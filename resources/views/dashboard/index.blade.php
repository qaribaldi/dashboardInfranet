@extends('layouts.app')

@section('title','Dashboard DSS')

@section('content')

  <h2 class="text-2xl font-bold mb-2">Dashboard DSS</h2>

  {{-- Ambang umur aset (mengubah rekomendasi & lokasi) --}}
<div class="mb-6 flex items-center gap-3">
  <label for="ageSelect" class="text-sm font-medium">Ambang umur aset</label>
  <div class="relative">
    <select id="ageSelect"
      class="appearance-none rounded-lg border border-gray-300 px-3 pr-8 py-2 text-sm">
      <option value="3">3-4 Tahun (early warning)</option>
      <option value="5" selected>5-6 Tahun (rekomendasi)</option>
      <option value="7">7-9 Tahun (prioritas tinggi)</option>
      <option value="10">≥ 10 tahun</option>
    </select>
  </div>
  <span class="text-xs text-gray-500">Mengubah rekomendasi upgrade &amp; lokasi</span>
</div>


  {{-- KPI Cards --}}
<div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
  <div class="rounded-xl border bg-white p-4">
    <div class="text-sm text-gray-500">Total PC</div>
    <div id="kpiPc" class="text-2xl font-bold">-</div>
    <div class="text-xs text-gray-500 mt-1">Umur <span id="ageLabelKpi">5</span> th: <span id="kpiPcOld">-</span></div>
  </div>
  <div class="rounded-xl border bg-white p-4">
    <div class="text-sm text-gray-500">Total Printer</div>
    <div id="kpiPrinter" class="text-2xl font-semibold">-</div>
    <div class="text-xs text-gray-500 mt-1">Umur <span class="ageLabel">5</span> th: <span id="kpiPrinterOld">-</span></div>
  </div>
  <div class="rounded-xl border bg-white p-4">
    <div class="text-sm text-gray-500">Total Proyektor</div>
    <div id="kpiProyektor" class="text-2xl font-semibold">-</div>
    <div class="text-xs text-gray-500 mt-1">Umur <span class="ageLabel">5</span> th: <span id="kpiProyektorOld">-</span></div>
  </div>
  <div class="rounded-xl border bg-white p-4">
    <div class="text-sm text-gray-500">Total AC</div>
    <div id="kpiAc" class="text-2xl font-semibold">-</div>
    <div class="text-xs text-gray-500 mt-1">Umur <span class="ageLabel">5</span> th: <span id="kpiAcOld">-</span></div>
  </div>
</div>


  <div class="mb-6 text-sm text-gray-500">
    <span id="lastUpdated">Terakhir diperbarui: -</span>
  </div>

  {{-- Charts --}}
  <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="lg:col-span-2 rounded-xl border bg-white p-4">
      <div class="flex items-center justify-between mb-3">
        <h3 class="font-semibold">Tren Pengadaan 8 Tahun Terakhir</h3>
        <div class="text-xs text-gray-500">Stacked per jenis</div>
      </div>
      <canvas id="barChart" height="120"></canvas>
    </div>
    <div class="rounded-xl border bg-white p-4">
      <div class="flex items-center justify-between mb-3">
        <h3 class="font-semibold">Proporsi Aset per Jenis</h3>
      </div>
      <canvas id="pieChart" height="120"></canvas>
    </div>
  </div>

  {{-- Notifikasi Upgrade + FILTER BAR --}}
  <div class="mt-6 rounded-xl border bg-white">
    <div class="border-b px-4 py-3 flex items-center justify-between">
      <h3 class="font-semibold">Rekomendasi Upgrade (Umur <span id="ageLabelNotif">5</span> tahun)</h3>
      <span id="upgradeCount" class="text-sm text-gray-500">- item</span>
    </div>

    <div class="px-4 pt-4">
      <div class="flex flex-col lg:flex-row lg:items-end gap-3">
        <div>
          <label class="block text-sm font-medium mb-1">Jenis Aset</label>
          <select id="assetType" class="rounded-lg border border-gray-300 px-3 py-2">
            <option value="ALL" selected>Semua</option>
            <option value="PC">PC</option>
            <option value="Printer">Printer</option>
            <option value="Proyektor">Proyektor</option>
            <option value="AC">AC</option>
          </select>
        </div>
<div>
  <label class="block text-sm font-medium mb-1">Filter Berdasarkan</label>
  <div class="relative">
    <select id="filterField"
      class="appearance-none rounded-lg border border-gray-300 px-3 pr-8 py-2 text-sm">
      <option value="all">Semua</option>
      <option value="tahun">Tahun</option>
      <option value="kategori">Kategori</option>
    </select>
  </div>
</div>
        <div class="flex-1">
          <label class="block text-sm font-medium mb-1">Nilai</label>
          <select id="filterValue" class="w-full rounded-lg border border-gray-300 px-3 py-2">
            <option value="" selected>Semua</option>
          </select>
        </div>
        <div class="flex gap-2">
          <button id="btnApplyFilter" class="rounded-lg bg-blue-600 text-white px-4 py-2 hover:bg-blue-700">Filter</button>
          <button id="btnResetFilter" class="rounded-lg border px-4 py-2 hover:bg-gray-50">Reset</button>
        </div>
      </div>
    </div>

    <div class="p-4 overflow-x-auto">
      <table class="min-w-full text-sm">
        <thead class="bg-gray-50">
          <tr>
            <th class="text-left px-3 py-2">Jenis</th>
            <th class="text-left px-3 py-2">ID</th>
            <th class="text-left px-3 py-2">Unit/Ruang</th>
            <th class="text-left px-3 py-2">Spesifikasi</th>
            <th class="text-left px-3 py-2">Tahun</th>
            <th class="text-left px-3 py-2">Umur</th>
          </tr>
        </thead>
        <tbody id="upgradeBody">
          <tr><td colspan="6" class="px-3 py-4 text-center text-gray-500">Memuat...</td></tr>
        </tbody>
      </table>
      <div class="mt-3 flex items-center justify-end gap-2">
        <button id="upPrev" class="rounded border px-3 py-1.5 hover:bg-gray-50">Prev</button>
        <span id="upPageInfo" class="text-sm text-gray-600">-</span>
        <button id="upNext" class="rounded border px-3 py-1.5 hover:bg-gray-50">Next</button>
      </div>
    </div>
  </div>

  {{-- Lokasi (judul dinamis + per-jenis) --}}
  <div class="mt-6 grid grid-cols-1 lg:grid-cols-2 gap-6">
    <div class="rounded-xl border bg-white">
      <div class="border-b px-4 py-3">
        <h3 id="lokasiTitle" class="font-semibold">Lokasi Rawan (Top-5)</h3>
        <div class="text-xs text-gray-500">Dihitung berdasarkan bucket umur yang dipilih</div>
      </div>
      <div class="p-4 overflow-x-auto">
        <table class="min-w-full text-sm">
          <thead class="bg-gray-50">
            <tr>
              <th class="text-left px-3 py-2">Lokasi (Unit / Ruang)</th>
              <th class="text-left px-3 py-2">PC</th>
              <th class="text-left px-3 py-2">Printer</th>
              <th class="text-left px-3 py-2">Proyektor</th>
              <th class="text-left px-3 py-2">AC</th>
              <th class="text-left px-3 py-2">Total</th>
            </tr>
          </thead>
         <tbody id="lokasiRawanBody">
            <tr><td colspan="6" class="px-3 py-4 text-center text-gray-500">Memuat...</td></tr>
         </tbody>
        </table>
      </div>
    </div>
  </div>

  {{-- Histori Perbaikan/Upgrade (30 hari) --}}
  <div class="border-b px-4 py-3 flex items-center justify-between">
  <h3 class="font-semibold">Histori Perbaikan/Upgrade (30 hari)</h3>

  <div class="flex items-center gap-3">
    {{-- FILTER TIPE ASET --}}
    <div class="flex items-center gap-2">
      <label for="hisType" class="text-xs text-gray-600">Tipe Aset</label>
      <select id="hisType" class="rounded border px-2 py-1 text-sm">
        <option value="ALL" selected>Semua</option>
        <option value="PC">PC</option>
        <option value="Printer">Printer</option>
        <option value="Proyektor">Proyektor</option>
        <option value="AC">AC</option>
      </select>
    </div>

  </div>
</div>

    <div class="p-4 overflow-x-auto">
      <table class="min-w-full text-sm">
        <thead class="bg-gray-50">
          <tr>
            <th class="text-left px-3 py-2">Waktu</th>
            <th class="text-left px-3 py-2">Aset</th>
            <th class="text-left px-3 py-2">Aksi</th>
            <th class="text-left px-3 py-2">Perubahan</th>
            <th class="text-left px-3 py-2">Catatan</th>
          </tr>
        </thead>
        <tbody id="historyBody">
          <tr><td colspan="5" class="px-3 py-4 text-center text-gray-500">Memuat...</td></tr>
        </tbody>
      </table>
       {{-- tombol pagination --}}
  <div class="mt-3 flex items-center justify-end gap-2">
    <button id="hisPrev" class="rounded border px-3 py-1.5 hover:bg-gray-50">Prev</button>
    <span id="hisPageInfo" class="text-sm text-gray-600">-</span>
    <button id="hisNext" class="rounded border px-3 py-1.5 hover:bg-gray-50">Next</button>
  </div>
</div>
    </div>
  </div>

  {{-- Modal Quick View / Diff --}}
  <div id="assetModal" class="fixed inset-0 hidden items-center justify-center z-[60]">
    <div id="assetModalBackdrop" class="absolute inset-0 bg-black/40"></div>
    <div class="relative bg-white rounded-xl shadow-xl w-[95vw] max-w-4xl h-[80vh] flex flex-col">
      <div class="flex items-center justify-between px-4 py-2 border-b">
        <div id="assetModalTitle" class="font-semibold">Detail</div>
        <button id="assetModalClose" class="rounded p-1 hover:bg-gray-100" aria-label="Close">✕</button>
      </div>
      <div id="assetModalBody" class="p-4 overflow-y-auto grow"></div>
    </div>
  </div>
@endsection

@push('body-end')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
  const fmt = new Intl.DateTimeFormat('id-ID', {
  dateStyle: 'medium',
  timeStyle: 'short',
  timeZone: 'Asia/Jakarta'
});

  let barChart, pieChart;
  let minAge = 5;
  const PAGE_SIZE = 10;
  let pageUp = 1;
  let upgradeAll = [];
  let upgradeFiltered = [];
  let lastMetrics = null;

const HISTORY_PAGE_SIZE = 10;
  let historyAll = [];         
  let historyFiltered = [];    
  let historyType = 'ALL';    
  let pageHistory = 1;

  let assetType = 'ALL';
  let filterField = '';
  let filterValue = '';

  const colors = {
    pc: 'rgba(37, 99, 235, 0.7)',
    printer: 'rgba(16, 185, 129, 0.7)',
    proyektor: 'rgba(234, 179, 8, 0.7)',
    ac: 'rgba(244, 63, 94, 0.7)'
  };

  const SHOW_URLS = {
    PC: "{{ url('/inventory/pc') }}",
    Printer: "{{ url('/inventory/printer') }}",
    Proyektor: "{{ url('/inventory/proyektor') }}",
    AC: "{{ url('/inventory/ac') }}",
  };

  function initCharts() {
    const barCtx = document.getElementById('barChart').getContext('2d');
    const pieCtx = document.getElementById('pieChart').getContext('2d');

    barChart = new Chart(barCtx, {
      type: 'bar',
      data: { labels: [], datasets: [] },
      options: {
        responsive: true,
        scales: { x: { stacked: true }, y: { stacked: true, beginAtZero: true, precision: 0 } },
        plugins: { legend: { position: 'bottom' } }
      }
    });

    pieChart = new Chart(pieCtx, {
      type: 'pie',
      data: { labels: [], datasets: [{ data: [], backgroundColor: [colors.pc, colors.printer, colors.proyektor, colors.ac], borderWidth: 1 }] },
      options: { responsive: true, plugins: { legend: { position: 'bottom' } } }
    });
  }

  function updateKpis(m) {
    document.getElementById('kpiPc').textContent = m.totals.pc;
    document.getElementById('kpiPrinter').textContent = m.totals.printer;
    document.getElementById('kpiProyektor').textContent = m.totals.proyektor;
    document.getElementById('kpiAc').textContent = m.totals.ac;

    document.getElementById('kpiPcOld').textContent = m.totals.old.pc;
    document.getElementById('kpiPrinterOld').textContent = m.totals.old.printer;
    document.getElementById('kpiProyektorOld').textContent = m.totals.old.proyektor;
    document.getElementById('kpiAcOld').textContent = m.totals.old.ac;

    document.getElementById('lastUpdated').textContent =
  'Terakhir diperbarui: ' + fmt.format(new Date(m.now_epoch));


    const bucketLabel = (m.age_bucket && m.age_bucket.label) ? m.age_bucket.label : (m.min_age ?? minAge);
    document.getElementById('ageLabelKpi').textContent = bucketLabel;
    document.getElementById('ageLabelNotif').textContent = bucketLabel;
    document.querySelectorAll('.ageLabel').forEach(el => el.textContent = bucketLabel);
  }

  function updateBar(m) {
    barChart.data.labels = m.bar.labels;
    barChart.data.datasets = [
      { label: 'PC', data: m.bar.datasets.pc, backgroundColor: colors.pc },
      { label: 'Printer', data: m.bar.datasets.printer, backgroundColor: colors.printer },
      { label: 'Proyektor', data: m.bar.datasets.proyektor, backgroundColor: colors.proyektor },
      { label: 'AC',        data: m.bar.datasets.ac,        backgroundColor: colors.ac },
      
    ];
    barChart.update();
  }

  function updatePie(m) {
    pieChart.data.labels = m.pie.labels;
    pieChart.data.datasets[0].data = m.pie.data;
    pieChart.options.plugins.tooltip = {
      callbacks: {
        label: (ctx) => {
          const total = ctx.dataset.data.reduce((a,b)=>a+b,0) || 1;
          const val = ctx.parsed || 0;
          const pct = ((val/total)*100).toFixed(1);
          return `${ctx.label}: ${val} (${pct}%)`;
        }
      }
    };
    pieChart.update();
  }

  const FIELD_MAP = {
    PC: [
      { key: 'lokasi', label: 'Unit / Ruang' },
      { key: 'spes',   label: 'Spesifikasi' },
      { key: 'ram',    label: 'Total RAM' },
    ],
    Printer: [
      { key: 'lokasi',       label: 'Unit / Ruang' },
      { key: 'spes',         label: 'Spesifikasi' },
      { key: 'status_warna', label: 'Status Warna' },
    ],
    Proyektor: [
      { key: 'lokasi',       label: 'Unit / Ruang' },
      { key: 'spes',         label: 'Spesifikasi' },
      { key: 'resolusi_max', label: 'Resolusi' },
    ],
    AC: [
      { key: 'lokasi', label: 'Unit / Ruang' },
      { key: 'spes',   label: 'Merk' },
      { key: 'remote',  label: 'Remote' },  
    ],
    ALL: [
      { key: 'lokasi', label: 'Unit / Ruang' },
      { key: 'spes',   label: 'Spesifikasi' },
    ],
  };

  function unique(arr) { return Array.from(new Set(arr)).filter(Boolean); }

  function buildFieldOptions() {
    const fieldSel = document.getElementById('filterField');
    const defs = FIELD_MAP[assetType] || FIELD_MAP.ALL;
    fieldSel.innerHTML = defs.map(d => `<option value="${d.key}">${d.label}</option>`).join('');
    const keys = defs.map(d => d.key);
    if (keys.includes(filterField)) {
      fieldSel.value = filterField;
    } else {
      fieldSel.value = defs[0].key;
      filterField = defs[0].key;
      filterValue = '';
    }
  }

  function buildValueOptions(preservedValue = '') {
    const sel = document.getElementById('filterValue');
    let base = [...upgradeAll];
    if (assetType !== 'ALL') base = base.filter(u => u.type === assetType);

    let options = [''];
    if (!base.length) {
      sel.innerHTML = `<option value=""></option>`;
      sel.value = '';
      return;
    }

    if (filterField === 'lokasi') {
      const vals = unique(base.map(u => `${u.unit_kerja ?? u.nama_ruang ?? '-'} / ${u.ruang ?? '-'}`));
      options = [''].concat(vals);
    } else if (filterField === 'spes') {
      const vals = unique(base.map(u => u.spes ?? '').filter(Boolean));
      options = [''].concat(vals);
    } else if (filterField === 'ram') {
      const vals = unique(base.filter(u => u.type === 'PC').map(u => u.ram ?? '').filter(Boolean));
      options = [''].concat(vals);
    } else if (filterField === 'status_warna') {
      const vals = unique(base.filter(u => u.type === 'Printer').map(u => u.status_warna ?? '').filter(Boolean));
      options = [''].concat(vals);
    } else if (filterField === 'resolusi_max') {
      const vals = unique(base.filter(u => u.type === 'Proyektor').map(u => u.resolusi_max ?? '').filter(Boolean));
      options = [''].concat(vals);
    } else if (filterField === 'remote') {
      const vals = unique(base.filter(u => u.type === 'AC').map(u => u.remote ?? '').filter(Boolean));
      options = [''].concat(vals);
    }


    sel.innerHTML = options.map(v => `<option value="${v}">${v || 'Semua'}</option>`).join('');
    if (preservedValue && options.includes(preservedValue)) {
      sel.value = preservedValue;
    } else {
      sel.value = '';
      filterValue = '';
    }
  }

  function applyDropdownFilter() {
    assetType   = document.getElementById('assetType').value;
    filterField = document.getElementById('filterField').value;
    filterValue = document.getElementById('filterValue').value;

    let list = [...upgradeAll];
    if (assetType !== 'ALL') list = list.filter(u => u.type === assetType);

    if (filterValue) {
      if (filterField === 'lokasi') {
        list = list.filter(u => {
          const loc = `${u.unit_kerja ?? u.nama_ruang ?? '-'} / ${u.ruang ?? '-'}`;
          return loc === filterValue;
        });
      } else if (filterField === 'spes') {
        list = list.filter(u => (u.spes ?? '') === filterValue);
      } else if (filterField === 'ram') {
        list = list.filter(u => (u.ram ?? '') === filterValue);
      } else if (filterField === 'status_warna') {
        list = list.filter(u => (u.status_warna ?? '') === filterValue);
      } else if (filterField === 'resolusi_max') {
        list = list.filter(u => (u.resolusi_max ?? '') === filterValue);
      } else if (filterField === 'remote') {
        list = list.filter(u => (u.remote ?? '') === filterValue);
      }

    }

    upgradeFiltered = list;
    pageUp = 1;
    renderUpgradePage(upgradeFiltered);
  }

  function renderUpgradePage(list) {
    const body = document.getElementById('upgradeBody');
    const total = list.length;
    const maxPage = Math.max(1, Math.ceil(total / PAGE_SIZE));
    pageUp = Math.min(Math.max(1, pageUp), maxPage);

    document.getElementById('upgradeCount').textContent = `${total} item`;
    document.getElementById('upPageInfo').textContent = `${pageUp} / ${maxPage}`;

    if (!total) {
      body.innerHTML = `<tr><td colspan="6" class="px-3 py-4 text-center text-gray-500">Tidak ada rekomendasi upgrade.</td></tr>`;
      return;
    }

    const start = (pageUp - 1) * PAGE_SIZE;
    const rows = list.slice(start, start + PAGE_SIZE);

    body.innerHTML = rows.map(item => `
      <tr class="border-t hover:bg-gray-50 cursor-pointer" data-type="${item.type}" data-id="${item.id}">
        <td class="px-3 py-2">${item.type}</td>
        <td class="px-3 py-2 font-medium">${item.id}</td>
        <td class="px-3 py-2">${(item.unit_kerja ?? item.nama_ruang ?? '-')} / ${(item.ruang ?? '-')}</td>
        <td class="px-3 py-2">${item.spes ?? '-'}</td>
        <td class="px-3 py-2">${item.tahun_pembelian ?? '-'}</td>
        <td class="px-3 py-2">${item.umur} th</td>
      </tr>
    `).join('');

    document.getElementById('upPrev').onclick = () => { pageUp--; renderUpgradePage(list); };
    document.getElementById('upNext').onclick = () => { pageUp++; renderUpgradePage(list); };
  }

  function updateUpgradeTable(m) {
    const prevAssetType = document.getElementById('assetType').value;
    const prevField = document.getElementById('filterField').value || filterField;
    const prevValue = document.getElementById('filterValue').value || filterValue;

    upgradeAll = m.upgrade || [];

    assetType = prevAssetType || 'ALL';
    buildFieldOptions();
    filterField = prevField;
    buildValueOptions(prevValue);

    applyDropdownFilter();
  }

  // ===== Lokasi (judul dinamis + per-jenis) =====
  function updateLokasiRawan(m) {
    document.getElementById('lokasiTitle').textContent = m.lokasi_title || 'Lokasi Rawan';
    const tbody = document.getElementById('lokasiRawanBody');
    const arr = m.lokasi_rawan || [];
    if (!arr.length) {
      tbody.innerHTML = `<tr><td colspan="5" class="px-3 py-4 text-center text-gray-500">Tidak ada data.</td></tr>`;
      return;
    }
    tbody.innerHTML = arr.map(r => `
      <tr class="border-t">
        <td class="px-3 py-2">${r.label}</td>
        <td class="px-3 py-2">${r.pc ?? 0}</td>
        <td class="px-3 py-2">${r.printer ?? 0}</td>
        <td class="px-3 py-2">${r.proyektor ?? 0}</td>
        <td class="px-3 py-2">${r.ac ?? 0}</td>
        <td class="px-3 py-2 font-medium">${r.total ?? 0}</td>
      </tr>
    `).join('');
  }

  // ===== History (klik baris => modal diff) =====
  function updateHistory(m) {
  historyAll = Array.isArray(m.history) ? m.history : [];
  applyHistoryFilter(true); // reset ke page 1
}

function applyHistoryFilter(resetPage = true) {
  const sel = document.getElementById('hisType');
  hisType = sel ? (sel.value || 'ALL') : 'ALL';

  let list = historyAll;
  if (hisType !== 'ALL') {
    const target = hisType.toUpperCase();
    list = historyAll.filter(h => (h.asset_type || '').toUpperCase() === target);
  }
  historyFiltered = list;

  if (resetPage) pageHistory = 1;
  renderHistoryPage(historyFiltered);
}

function renderHistoryPage(list) {
  const body = document.getElementById('historyBody');
  const total = list.length;
  const maxPage = Math.max(1, Math.ceil(total / HISTORY_PAGE_SIZE));
  pageHistory = Math.min(Math.max(1, pageHistory), maxPage);

  document.getElementById('hisPageInfo').textContent = `${pageHistory} / ${maxPage}`;

  if (!total) {
    body.innerHTML = `<tr><td colspan="5" class="px-3 py-4 text-center text-gray-500">
      Belum ada histori 30 hari terakhir.
    </td></tr>`;
    return;
  }

  const start = (pageHistory - 1) * HISTORY_PAGE_SIZE;
  const rows = list.slice(start, start + HISTORY_PAGE_SIZE);

  const dt = new Intl.DateTimeFormat('id-ID', {
    dateStyle: 'medium',
    timeStyle: 'short',
    timeZone: 'Asia/Jakarta'
  });

  body.innerHTML = rows.map((h, i) => `
    <tr class="border-t align-top hover:bg-gray-50 cursor-pointer" data-idx="${start + i}">
      <td class="px-3 py-2 whitespace-nowrap">${h.ts_epoch ? dt.format(new Date(h.ts_epoch)) : '-'}</td>
      <td class="px-3 py-2 font-medium">${h.asset_type} / ${h.asset_id}</td>
      <td class="px-3 py-2">
        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs
          ${h.action==='upgrade' ? 'bg-blue-100 text-blue-700' :
            (h.action==='repair' ? 'bg-emerald-100 text-emerald-700' : 'bg-gray-100 text-gray-700')}">
          ${h.action}
        </span>
      </td>
      <td class="px-3 py-2">${h.summary ? h.summary.replace(/</g,'&lt;') : '-'}</td>
      <td class="px-3 py-2">${h.note ? h.note.replace(/</g,'&lt;') : '-'}</td>
    </tr>
  `).join('');

  // Pagination handlers
  document.getElementById('hisPrev').onclick = () => { pageHistory--; renderHistoryPage(list); };
  document.getElementById('hisNext').onclick = () => { pageHistory++; renderHistoryPage(list); };
}

// ====== EVENT: dropdown filter ======
document.addEventListener('DOMContentLoaded', () => {
  const sel = document.getElementById('hisType');
  if (sel) sel.addEventListener('change', () => applyHistoryFilter(true));
});

// ====== CLICK ROW: buka modal diff dari list yang terfilter ======
document.getElementById('historyBody').addEventListener('click', (e) => {
  const tr = e.target.closest('tr[data-idx]');
  if (!tr) return;

  const idx = parseInt(tr.dataset.idx, 10);
  // idx di table = indeks global di 'historyFiltered' pada halaman saat ini.
  // Ambil item sesuai tampilan sekarang:
  const start = (pageHistory - 1) * HISTORY_PAGE_SIZE;
  const item = historyFiltered[start + (idx - start)];
  if (!item) return;

  openHistoryModal(item);
});

  async function fetchMetrics() {
    const url = "{{ route('dashboard.metrics') }}" + `?min_age=${minAge}`;
    try {
      const res = await fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' }});
      const metrics = await res.json();
      lastMetrics = metrics;

      updateKpis(metrics);
      updateBar(metrics);
      updatePie(metrics);
      updateUpgradeTable(metrics);
      updateLokasiRawan(metrics);
      updateHistory(metrics);
    } catch (e) { console.error(e); }
  }

  // ===== Modal helpers =====
  function openModal() {
    const modal = document.getElementById('assetModal');
    modal.classList.remove('hidden');
    modal.classList.add('flex');
    document.body.style.overflow = 'hidden';
  }
  function closeModal() {
    const modal = document.getElementById('assetModal');
    const body  = document.getElementById('assetModalBody');
    modal.classList.add('hidden');
    modal.classList.remove('flex');
    document.body.style.overflow = '';
    body.innerHTML = '';
  }
  document.addEventListener('click', (e) => {
    if (e.target.id === 'assetModalBackdrop' || e.target.id === 'assetModalClose') closeModal();
  });
  document.addEventListener('keydown', (e) => { if (e.key === 'Escape') closeModal(); });

  // ====== DETAIL INVENTORY: muat HTML inventory lengkap ke modal ======
  async function openDetailModal(type, id) {
    const base = SHOW_URLS[type];
    if (!base || !id) return;
    const titleEl = document.getElementById('assetModalTitle');
    const bodyEl  = document.getElementById('assetModalBody');

    titleEl.textContent = `Detail ${type} - ${id}`;
    bodyEl.innerHTML = `
      <div class="flex items-center gap-3 text-sm text-gray-500">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M12 3v3M12 18v3M4.22 4.22l2.12 2.12M15.66 15.66l2.12 2.12M3 12h3M18 12h3M4.22 19.78l2.12-2.12M15.66 8.34l2.12-2.12"/></svg>
        Memuat detail...
      </div>`;
    openModal();

    try {
      const url = `${base}/${encodeURIComponent(id)}?readonly=1`;
      const res = await fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' }});
      const html = await res.text();
      bodyEl.innerHTML = html;
    } catch (err) {
      console.error(err);
      bodyEl.innerHTML = `<div class="text-sm text-red-600">Gagal memuat detail. Buka langsung: <a class="underline" href="${base}/${id}" target="_blank" rel="noopener">${base}/${id}</a></div>`;
    }
  }

  // ====== HISTORI: parse & tampilkan hanya field yang berubah ======
  function parseChanges(h) {
    // gunakan h.changes bila tersedia (array of {field,key,from,to})
    if (Array.isArray(h.changes) && h.changes.length) {
      return h.changes.map(c => ({
        field: c.field ?? c.key ?? '',
        from:  c.from ?? '',
        to:    c.to ?? '',
      })).filter(x => x.field && (x.from !== x.to));
    }
    // fallback dari summary "field: from → to; ..."
    const out = [];
    const txt = (h.summary || '').trim();
    if (!txt) return out;
    txt.split(';').forEach(piece => {
      const s = piece.trim();
      if (!s) return;
      const idxColon = s.indexOf(':');
      if (idxColon === -1) return;
      const field = s.slice(0, idxColon).trim();
      const rest  = s.slice(idxColon+1).trim();
      const arr   = rest.split('→');
      const from  = (arr[0] || '').trim();
      const to    = (arr[1] || '').trim();
      if (field) out.push({ field, from, to });
    });
    return out;
  }

  function openHistoryModal(h) {
    const titleEl = document.getElementById('assetModalTitle');
    const bodyEl  = document.getElementById('assetModalBody');

    titleEl.textContent = `Perubahan ${h.asset_type} • ${h.asset_id}`;

const dt = new Intl.DateTimeFormat('id-ID', {
  dateStyle: 'medium',
  timeStyle: 'short',
  timeZone: 'Asia/Jakarta'
});
const when = h.ts_epoch ? dt.format(new Date(h.ts_epoch)) : '-';
const changes = parseChanges(h); // <-- tambahkan ini

    const headerHtml = `
      <div class="mb-3 grid grid-cols-1 md:grid-cols-2 gap-2 text-sm">
        <div><span class="text-gray-500">Waktu:</span> ${when}</div>
        <div><span class="text-gray-500">Aksi:</span> ${h.action}</div>
        <div class="md:col-span-2"><span class="text-gray-500">Catatan:</span> ${h.note ? h.note.replace(/</g,'&lt;') : '-'}</div>
      </div>
    `;

    let tableHtml = '';
    if (changes.length) {
      tableHtml = `
        <div class="rounded-lg border">
          <div class="px-3 py-2 border-b font-medium">Field yang diubah</div>
          <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
              <thead class="bg-gray-50">
                <tr>
                  <th class="text-left px-3 py-2">Field</th>
                  <th class="text-left px-3 py-2">Sebelum</th>
                  <th class="text-left px-3 py-2">Sesudah</th>
                </tr>
              </thead>
              <tbody>
                ${changes.map(c => `
                  <tr class="border-t">
                    <td class="px-3 py-2 whitespace-nowrap">${c.field}</td>
                    <td class="px-3 py-2 align-top">${(c.from ?? '').toString().replace(/</g,'&lt;').replace(/\n/g,'<br>')}</td>
                    <td class="px-3 py-2 align-top">${(c.to   ?? '').toString().replace(/</g,'&lt;').replace(/\n/g,'<br>')}</td>
                  </tr>
                `).join('')}
              </tbody>
            </table>
          </div>
        </div>
      `;
    } else {
      tableHtml = `<div class="text-sm text-gray-500">Tidak ada detail perubahan terstruktur.</div>`;
    }

    bodyEl.innerHTML = headerHtml + tableHtml;
    openModal();
  }

  // events
  document.addEventListener('DOMContentLoaded', () => {
    buildFieldOptions();
    buildValueOptions('');

    const ageSelect = document.getElementById('ageSelect');
    if (ageSelect) {
      ageSelect.addEventListener('change', () => {
        minAge = parseInt(ageSelect.value || '5', 10);
        fetchMetrics();
      });
    }

    document.getElementById('assetType').addEventListener('change', () => {
      assetType = document.getElementById('assetType').value;
      pageUp = 1;
      buildFieldOptions();
      buildValueOptions('');
      applyDropdownFilter();
    });

    document.getElementById('filterField').addEventListener('change', () => {
      filterField = document.getElementById('filterField').value;
      pageUp = 1;
      buildValueOptions('');
    });

    document.getElementById('btnApplyFilter').addEventListener('click', applyDropdownFilter);

    document.getElementById('btnResetFilter').addEventListener('click', () => {
      document.getElementById('assetType').value = 'ALL';
      assetType = 'ALL';
      pageUp = 1;
      buildFieldOptions();
      buildValueOptions('');
      document.getElementById('filterValue').value = '';
      filterValue = '';
      upgradeFiltered = [...upgradeAll];
      renderUpgradePage(upgradeFiltered);
    });

    // Delegasi klik baris rekomendasi → detail inventory
    document.getElementById('upgradeBody').addEventListener('click', (e) => {
      const tr = e.target.closest('tr[data-type][data-id]');
      if (!tr) return;
      openDetailModal(tr.dataset.type, tr.dataset.id);
    });

    // Delegasi klik baris histori → modal diff
    document.getElementById('historyBody').addEventListener('click', (e) => {
      const tr = e.target.closest('tr[data-idx]');
      if (!tr || !lastMetrics || !Array.isArray(lastMetrics.history)) return;
      const idx = parseInt(tr.dataset.idx, 10);
      const h = lastMetrics.history[idx];
      if (!h) return;
      openHistoryModal(h);
    });
  });

  // init
  initCharts();
  fetchMetrics();
  setInterval(fetchMetrics, 30000);
</script>
@endpush
