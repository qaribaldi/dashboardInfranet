@extends('layouts.app')

@section('title','Dashboard DSS')

@section('content')

@can('dashboard.view')
  <h2 class="text-2xl font-bold mb-2">Dashboard DSS</h2>

  {{-- Ambang umur aset: tampil jika ada minimal salah satu blok yang memakainya --}}
  @canany(['dashboard.view.kpi','dashboard.view.chart','dashboard.view.lokasi-rawan'])
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
  @endcanany

  {{-- Warning if user has none of the modules --}}
@canany(['dashboard.view.kpi','dashboard.view.chart','dashboard.view.lokasi-rawan','dashboard.view.history'])
  {{-- user has at least one module → no warning --}}
@else
  <div class="rounded-lg border bg-yellow-50 text-yellow-800 px-4 py-3">
    Anda belum diberi akses ke modul dashboard mana pun. Minta admin untuk memberikan anda akses.
  </div>
@endcanany


  {{-- KPI Cards --}}
  @can('dashboard.view.kpi')
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4 mb-6">
      <div class="min-w-[12rem] rounded-xl border bg-white p-4">
        <div class="text-sm text-gray-500">Total Keseluruhan Aset</div>
        <div id="kpiAll" class="text-2xl font-extrabold">-</div>
        <div class="text-xs text-gray-500 mt-1">PC + Printer + Proyektor + AC</div>
      </div>
      <div class="min-w-[12rem] rounded-xl border bg-white p-4">
        <div class="text-sm text-gray-500">Total PC</div>
        <div id="kpiPc" class="text-2xl font-bold">-</div>
        <div class="text-xs text-gray-500 mt-1">Umur <span id="ageLabelKpi">5</span> th: <span id="kpiPcOld">-</span></div>
      </div>
      <div class="min-w-[12rem] rounded-xl border bg-white p-4">
        <div class="text-sm text-gray-500">Total Printer</div>
        <div id="kpiPrinter" class="text-2xl font-semibold">-</div>
        <div class="text-xs text-gray-500 mt-1">Umur <span class="ageLabel">5</span> th: <span id="kpiPrinterOld">-</span></div>
      </div>
      <div class="min-w-[12rem] rounded-xl border bg-white p-4">
        <div class="text-sm text-gray-500">Total Proyektor</div>
        <div id="kpiProyektor" class="text-2xl font-semibold">-</div>
        <div class="text-xs text-gray-500 mt-1">Umur <span class="ageLabel">5</span> th: <span id="kpiProyektorOld">-</span></div>
      </div>
      <div class="min-w-[12rem] rounded-xl border bg-white p-4">
        <div class="text-sm text-gray-500">Total AC</div>
        <div id="kpiAc" class="text-2xl font-semibold">-</div>
        <div class="text-xs text-gray-500 mt-1">Umur <span class="ageLabel">5</span> th: <span id="kpiAcOld">-</span></div>
      </div>
    </div>

    <div class="mb-6 text-sm text-gray-500">
      <span id="lastUpdated">Terakhir diperbarui: -</span>
    </div>
  @endcan

  {{-- Charts --}}
  @can('dashboard.view.chart')
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
  @endcan

  {{-- Rekomendasi Upgrade (ikut izin KPI) --}}
  @can('dashboard.view.kpi')
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
                class="appearance-none rounded-lg border border-gray-300 px-3 pr-8 py-2 text-sm"></select>
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
  @endcan

  {{-- Lokasi (Top-5) --}}
  @can('dashboard.view.lokasi-rawan')
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
  @endcan

  {{-- Histori Perbaikan/Upgrade (30 hari) --}}
  @can('dashboard.view.history')
    <div class="mt-6 rounded-xl border bg-white">
      <div class="border-b px-4 py-3 flex items-center justify-between">
        <h3 class="font-semibold">Histori Perbaikan/Upgrade (30 hari)</h3>
        <div class="flex items-center gap-3">
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
              <th class="text-left px-3 py-2">Edited By</th>
            </tr>
          </thead>
          <tbody id="historyBody">
            <tr><td colspan="6" class="px-3 py-4 text-center text-gray-500">Memuat...</td></tr>
          </tbody>
        </table>
        <div class="mt-3 flex items-center justify-end gap-2">
          <button id="hisPrev" class="rounded border px-3 py-1.5 hover:bg-gray-50">Prev</button>
          <span id="hisPageInfo" class="text-sm text-gray-600">-</span>
          <button id="hisNext" class="rounded border px-3 py-1.5 hover:bg-gray-50">Next</button>
        </div>
      </div>
    </div>
  @endcan
@else
  <div class="rounded-lg border bg-yellow-50 text-yellow-800 px-4 py-3">
    Anda tidak memiliki akses ke Dashboard.
  </div>
@endcan
{{-- Modal Quick View / Diff (dipakai Upgrade & Histori) --}}
@canany(['dashboard.view.kpi','dashboard.view.history'])
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
@endcanany


@endsection

@push('body-end')
@can('dashboard.view.chart')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
  @endcan
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
  const fmt = new Intl.DateTimeFormat('id-ID', { dateStyle: 'medium', timeStyle: 'short', timeZone: 'Asia/Jakarta' });

  let barChart=null, pieChart=null;
  let minAge = 5;

  const PAGE_SIZE = 10;
  let pageUp = 1;
  let upgradeAll = [];
  let upgradeFiltered = [];

  const HISTORY_PAGE_SIZE = 10;
  let historyAll = [];
  let historyFiltered = [];
  let hisType = 'ALL';
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
    const barEl = document.getElementById('barChart');
    const pieEl = document.getElementById('pieChart');
    if (barEl) {
      const barCtx = barEl.getContext('2d');
      barChart = new Chart(barCtx, {
        type: 'bar',
        data: { labels: [], datasets: [] },
        options: {
          responsive: true,
          scales: { x: { stacked: true }, y: { stacked: true, beginAtZero: true, precision: 0 } },
          plugins: { legend: { position: 'bottom' } }
        }
      });
    }
    if (pieEl) {
      const pieCtx = pieEl.getContext('2d');
      pieChart = new Chart(pieCtx, {
        type: 'pie',
        data: { labels: [], datasets: [{ data: [], backgroundColor: [colors.pc, colors.printer, colors.proyektor, colors.ac], borderWidth: 1 }] },
        options: { responsive: true, plugins: { legend: { position: 'bottom' } } }
      });
    }
  }

  function setText(id, val) { const el=document.getElementById(id); if (el) el.textContent = val; }

  function updateKpis(m) {
    setText('kpiPc', m.totals?.pc ?? '-');
    setText('kpiPrinter', m.totals?.printer ?? '-');
    setText('kpiProyektor', m.totals?.proyektor ?? '-');
    setText('kpiAc', m.totals?.ac ?? '-');

    const totalAll = (m.totals?.pc || 0)+(m.totals?.printer || 0)+(m.totals?.proyektor || 0)+(m.totals?.ac || 0);
    setText('kpiAll', totalAll);

    setText('kpiPcOld', m.totals?.old?.pc ?? '-');
    setText('kpiPrinterOld', m.totals?.old?.printer ?? '-');
    setText('kpiProyektorOld', m.totals?.old?.proyektor ?? '-');
    setText('kpiAcOld', m.totals?.old?.ac ?? '-');

    const last = document.getElementById('lastUpdated');
    if (last) last.textContent = 'Terakhir diperbarui: ' + fmt.format(new Date(m.now_epoch));

    const bucketLabel = (m.age_bucket && m.age_bucket.label) ? m.age_bucket.label : (m.min_age ?? minAge);
    setText('ageLabelKpi', bucketLabel);
    setText('ageLabelNotif', bucketLabel);
    document.querySelectorAll('.ageLabel').forEach(el => el.textContent = bucketLabel);
  }

  function updateBar(m) {
    if (!barChart) return;
    barChart.data.labels = m.bar?.labels || [];
    barChart.data.datasets = [
      { label: 'PC', data: m.bar?.datasets?.pc || [], backgroundColor: colors.pc },
      { label: 'Printer', data: m.bar?.datasets?.printer || [], backgroundColor: colors.printer },
      { label: 'Proyektor', data: m.bar?.datasets?.proyektor || [], backgroundColor: colors.proyektor },
      { label: 'AC', data: m.bar?.datasets?.ac || [], backgroundColor: colors.ac },
    ];
    barChart.update();
  }

  function updatePie(m) {
    if (!pieChart) return;
    pieChart.data.labels = m.pie?.labels || [];
    pieChart.data.datasets[0].data = m.pie?.data || [];
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
      { key: 'remote', label: 'Remote' },
    ],
    ALL: [
      { key: 'lokasi', label: 'Unit / Ruang' },
      { key: 'spes',   label: 'Spesifikasi' },
    ],
  };

  function unique(arr) { return Array.from(new Set(arr)).filter(Boolean); }

  function buildFieldOptions() {
    const fieldSel = document.getElementById('filterField');
    if (!fieldSel) return;
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
    if (!sel) return;

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

  function renderUpgradePage(list) {
    const body = document.getElementById('upgradeBody');
    if (!body) return;
    const total = list.length;
    const maxPage = Math.max(1, Math.ceil(total / PAGE_SIZE));
    pageUp = Math.min(Math.max(1, pageUp), maxPage);

    setText('upgradeCount', `${total} item`);
    setText('upPageInfo', `${pageUp} / ${maxPage}`);

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

    const prevBtn = document.getElementById('upPrev');
    const nextBtn = document.getElementById('upNext');
    if (prevBtn) prevBtn.onclick = () => { pageUp--; renderUpgradePage(list); };
    if (nextBtn) nextBtn.onclick = () => { pageUp++; renderUpgradePage(list); };
  }

  function applyDropdownFilter() {
    const assetSel = document.getElementById('assetType');
    const fieldSel = document.getElementById('filterField');
    const valueSel = document.getElementById('filterValue');
    if (!assetSel || !fieldSel || !valueSel) return;

    assetType   = assetSel.value;
    filterField = fieldSel.value;
    filterValue = valueSel.value;

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

  function updateUpgradeTable(m) {
    const upBody = document.getElementById('upgradeBody');
    if (!upBody) return;

    const prevAssetType = document.getElementById('assetType')?.value || 'ALL';
    const prevField = document.getElementById('filterField')?.value || filterField;
    const prevValue = document.getElementById('filterValue')?.value || filterValue;

    upgradeAll = m.upgrade || [];
    assetType = prevAssetType;
    buildFieldOptions();
    filterField = prevField;
    buildValueOptions(prevValue);
    applyDropdownFilter();
  }

  // Lokasi
  function updateLokasiRawan(m) {
    const tbody = document.getElementById('lokasiRawanBody');
    if (!tbody) return;
    const title = document.getElementById('lokasiTitle');
    if (title) title.textContent = m.lokasi_title || 'Lokasi Rawan';

    const arr = m.lokasi_rawan || [];
    if (!arr.length) {
      tbody.innerHTML = `<tr><td colspan="6" class="px-3 py-4 text-center text-gray-500">Tidak ada data.</td></tr>`;
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

  // History
  function updateHistory(m) {
    const body = document.getElementById('historyBody');
    if (!body) return;
    historyAll = Array.isArray(m.history) ? m.history : [];
    applyHistoryFilter(true);
  }

  function applyHistoryFilter(resetPage = true) {
    const sel = document.getElementById('hisType');
    if (!sel) return;
    hisType = sel.value || 'ALL';

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
    if (!body) return;

    const total = list.length;
    const maxPage = Math.max(1, Math.ceil(total / HISTORY_PAGE_SIZE));
    pageHistory = Math.min(Math.max(1, pageHistory), maxPage);

    setText('hisPageInfo', `${pageHistory} / ${maxPage}`);

    if (!total) {
      body.innerHTML = `<tr><td colspan="6" class="px-3 py-4 text-center text-gray-500">
        Belum ada histori 30 hari terakhir.
      </td></tr>`;
      return;
    }

    const start = (pageHistory - 1) * HISTORY_PAGE_SIZE;
    const rows = list.slice(start, start + HISTORY_PAGE_SIZE);

    const dt = new Intl.DateTimeFormat('id-ID', { dateStyle: 'medium', timeStyle: 'short', timeZone: 'Asia/Jakarta' });

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
        <td class="px-3 py-2">${h.edited_by ? h.edited_by.replace(/</g,'&lt;') : '-'}</td>
      </tr>
    `).join('');

    const prev = document.getElementById('hisPrev');
    const next = document.getElementById('hisNext');
    if (prev) prev.onclick = () => { pageHistory--; renderHistoryPage(list); };
    if (next) next.onclick = () => { pageHistory++; renderHistoryPage(list); };
  }

  // Modal util
  function openModal() {
    const modal = document.getElementById('assetModal');
    if (!modal) return;
    modal.classList.remove('hidden');
    modal.classList.add('flex');
    document.body.style.overflow = 'hidden';
  }
  function closeModal() {
    const modal = document.getElementById('assetModal');
    const body  = document.getElementById('assetModalBody');
    if (!modal) return;
    modal.classList.add('hidden');
    modal.classList.remove('flex');
    document.body.style.overflow = '';
    if (body) body.innerHTML = '';
  }
  document.addEventListener('click', (e) => {
    if (e.target.id === 'assetModalBackdrop' || e.target.id === 'assetModalClose') closeModal();
  });
  document.addEventListener('keydown', (e) => { if (e.key === 'Escape') closeModal(); });

  async function openDetailModal(type, id) {
    const base = SHOW_URLS[type];
    if (!base || !id) return;
    const titleEl = document.getElementById('assetModalTitle');
    const bodyEl  = document.getElementById('assetModalBody');
    if (!titleEl || !bodyEl) return;

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

  function parseChanges(h) {
    if (Array.isArray(h.changes) && h.changes.length) {
      return h.changes.map(c => ({
        field: c.field ?? c.key ?? '',
        from:  c.from ?? '',
        to:    c.to ?? '',
      })).filter(x => x.field && (x.from !== x.to));
    }
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
    if (!titleEl || !bodyEl) return;

    titleEl.textContent = `Perubahan ${h.asset_type} • ${h.asset_id}`;

    const dt = new Intl.DateTimeFormat('id-ID', { dateStyle: 'medium', timeStyle: 'short', timeZone: 'Asia/Jakarta' });
    const when = h.ts_epoch ? dt.format(new Date(h.ts_epoch)) : '-';
    const changes = parseChanges(h);

    const headerHtml = `
      <div class="mb-3 grid grid-cols-1 md:grid-cols-2 gap-2 text-sm">
        <div><span class="text-gray-500">Waktu:</span> ${when}</div>
        <div><span class="text-gray-500">Aksi:</span> ${h.action}</div>
        <div><span class="text-gray-500">Edited By:</span> ${h.edited_by ? h.edited_by.replace(/</g,'&lt;') : '-'}</div>
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

  document.addEventListener('DOMContentLoaded', () => {
    // Inisialisasi charts bila kanvas ada
    initCharts();

    // Age bucket
    const ageSelect = document.getElementById('ageSelect');
    if (ageSelect) {
      ageSelect.addEventListener('change', () => {
        minAge = parseInt(ageSelect.value || '5', 10);
        fetchMetrics();
      });
    }

    // Upgrade filters
    const assetSel = document.getElementById('assetType');
    const fieldSel = document.getElementById('filterField');
    const valueSel = document.getElementById('filterValue');
    const applyBtn = document.getElementById('btnApplyFilter');
    const resetBtn = document.getElementById('btnResetFilter');

    if (assetSel) assetSel.addEventListener('change', () => {
      assetType = assetSel.value;
      pageUp = 1;
      buildFieldOptions();
      buildValueOptions('');
      applyDropdownFilter();
    });
    if (fieldSel) fieldSel.addEventListener('change', () => {
      filterField = fieldSel.value;
      pageUp = 1;
      buildValueOptions('');
    });
    if (applyBtn) applyBtn.addEventListener('click', applyDropdownFilter);
    if (resetBtn) resetBtn.addEventListener('click', () => {
      if (!assetSel || !valueSel) return;
      assetSel.value = 'ALL';
      assetType = 'ALL';
      pageUp = 1;
      buildFieldOptions();
      buildValueOptions('');
      valueSel.value = '';
      filterValue = '';
      upgradeFiltered = [...upgradeAll];
      renderUpgradePage(upgradeFiltered);
    });

    // Delegasi klik baris rekomendasi → detail inventory
    const upBody = document.getElementById('upgradeBody');
    if (upBody) upBody.addEventListener('click', (e) => {
      const tr = e.target.closest('tr[data-type][data-id]');
      if (!tr) return;
      openDetailModal(tr.dataset.type, tr.dataset.id);
    });

    // Filter type history
    const hisSel = document.getElementById('hisType');
    if (hisSel) hisSel.addEventListener('change', () => applyHistoryFilter(true));

    // Klik baris history → modal diff
    const hisBody = document.getElementById('historyBody');
    if (hisBody) hisBody.addEventListener('click', (e) => {
      const tr = e.target.closest('tr[data-idx]');
      if (!tr) return;
      const idx = parseInt(tr.dataset.idx, 10);
      const start = (pageHistory - 1) * HISTORY_PAGE_SIZE;
      const item = historyFiltered[start + (idx - start)];
      if (!item) return;
      openHistoryModal(item);
    });

    // First load + refresh berkala
    fetchMetrics();
    setInterval(fetchMetrics, 30000);
  });

  async function fetchMetrics() {
    const url = "{{ route('dashboard.metrics') }}" + `?min_age=${minAge}`;
    try {
      const res = await fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' }});
      const metrics = await res.json();

      // Update blok-blok yang ada saja
      updateKpis(metrics);
      updateBar(metrics);
      updatePie(metrics);
      updateUpgradeTable(metrics);
      updateLokasiRawan(metrics);
      updateHistory(metrics);
    } catch (e) {
      console.error(e);
    }
  }
</script>
@endpush
