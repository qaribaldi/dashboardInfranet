@extends('layouts.app')

@section('title','Dashboard DSS')

@section('content')
  <h2 class="text-2xl font-bold mb-2">Dashboard DSS</h2>

  {{-- Toggle prioritas (mengubah ambang umur aset) --}}
  <div class="mb-6 flex items-center gap-3">
    <label class="inline-flex items-center gap-2 text-sm">
      <input id="priorityToggle" type="checkbox" class="rounded border-gray-300">
      <span>Prioritas tinggi (umur &gt;= 7 tahun)</span>
    </label>
    <span class="text-xs text-gray-500">Mengubah rekomendasi upgrade &amp; lokasi rawan</span>
  </div>

  {{-- KPI Cards --}}
  <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
    <div class="rounded-xl border bg-white p-4">
      <div class="text-sm text-gray-500">Total PC</div>
      <div id="kpiPc" class="text-2xl font-semibold">-</div>
      <div class="text-xs text-gray-500 mt-1">Umur ≥<span id="ageLabelKpi">5</span> th: <span id="kpiPcOld">-</span></div>
    </div>
    <div class="rounded-xl border bg-white p-4">
      <div class="text-sm text-gray-500">Total Printer</div>
      <div id="kpiPrinter" class="text-2xl font-semibold">-</div>
      <div class="text-xs text-gray-500 mt-1">Umur ≥<span class="ageLabel">5</span> th: <span id="kpiPrinterOld">-</span></div>
    </div>
    <div class="rounded-xl border bg-white p-4">
      <div class="text-sm text-gray-500">Total Proyektor</div>
      <div id="kpiProyektor" class="text-2xl font-semibold">-</div>
      <div class="text-xs text-gray-500 mt-1">Umur ≥<span class="ageLabel">5</span> th: <span id="kpiProyektorOld">-</span></div>
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

  {{-- Notifikasi Upgrade --}}
  <div class="mt-6 rounded-xl border bg-white">
    <div class="border-b px-4 py-3 flex items-center justify-between">
      <h3 class="font-semibold">Rekomendasi Upgrade (Umur ≥ <span id="ageLabelNotif">5</span> tahun)</h3>
      <span id="upgradeCount" class="text-sm text-gray-500">- item</span>
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
    </div>
  </div>

  {{-- Lokasi Rawan (Top-5) --}}
  <div class="mt-6 grid grid-cols-1 lg:grid-cols-2 gap-6">
    <div class="rounded-xl border bg-white">
      <div class="border-b px-4 py-3">
        <h3 class="font-semibold">Lokasi Rawan (Top-5)</h3>
        <div class="text-xs text-gray-500">Lokasi dengan aset tua terbanyak (sesuai ambang)</div>
      </div>
      <div class="p-4">
        <table class="min-w-full text-sm">
          <thead class="bg-gray-50">
            <tr>
              <th class="text-left px-3 py-2">Lokasi (Unit / Ruang)</th>
              <th class="text-left px-3 py-2">Jumlah Aset Tua</th>
            </tr>
          </thead>
          <tbody id="lokasiRawanBody">
            <tr><td colspan="2" class="px-3 py-4 text-center text-gray-500">Memuat...</td></tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
@endsection

@push('body-end')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
  const fmt = new Intl.DateTimeFormat('id-ID', { dateStyle: 'medium', timeStyle: 'short' });

  let barChart, pieChart;
  let minAge = 5; // default; toggle -> 7

  const colors = {
    pc: 'rgba(37, 99, 235, 0.7)',       // biru
    printer: 'rgba(16, 185, 129, 0.7)', // hijau
    proyektor: 'rgba(234, 179, 8, 0.7)' // kuning
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

    // PIE dengan warna tetap per kategori
    pieChart = new Chart(pieCtx, {
      type: 'pie',
      data: {
        labels: [],
        datasets: [{
          data: [],
          backgroundColor: [colors.pc, colors.printer, colors.proyektor],
          borderWidth: 1
        }]
      },
      options: { responsive: true, plugins: { legend: { position: 'bottom' } } }
    });
  }

  function updateKpis(m) {
    document.getElementById('kpiPc').textContent = m.totals.pc;
    document.getElementById('kpiPrinter').textContent = m.totals.printer;
    document.getElementById('kpiProyektor').textContent = m.totals.proyektor;

    document.getElementById('kpiPcOld').textContent = m.totals.old.pc;
    document.getElementById('kpiPrinterOld').textContent = m.totals.old.printer;
    document.getElementById('kpiProyektorOld').textContent = m.totals.old.proyektor;

    document.getElementById('lastUpdated').textContent =
      'Terakhir diperbarui: ' + fmt.format(new Date(m.now));

    // label umur sesuai ambang
    document.getElementById('ageLabelKpi').textContent = m.min_age ?? minAge;
    document.getElementById('ageLabelNotif').textContent = m.min_age ?? minAge;
    document.querySelectorAll('.ageLabel').forEach(el => el.textContent = m.min_age ?? minAge);
  }

  function updateBar(m) {
    barChart.data.labels = m.bar.labels;
    barChart.data.datasets = [
      { label: 'PC', data: m.bar.datasets.pc, backgroundColor: colors.pc },
      { label: 'Printer', data: m.bar.datasets.printer, backgroundColor: colors.printer },
      { label: 'Proyektor', data: m.bar.datasets.proyektor, backgroundColor: colors.proyektor },
    ];
    barChart.update();
  }

  function updatePie(m) {
    pieChart.data.labels = m.pie.labels;
    pieChart.data.datasets[0].data = m.pie.data;
    pieChart.update();
  }

  function updateUpgradeTable(m) {
    const body = document.getElementById('upgradeBody');
    const list = m.upgrade || [];
    document.getElementById('upgradeCount').textContent = `${list.length} item`;

    if (!list.length) {
      body.innerHTML = `<tr><td colspan="6" class="px-3 py-4 text-center text-gray-500">Tidak ada rekomendasi upgrade.</td></tr>`;
      return;
    }

    body.innerHTML = list.map(item => `
      <tr class="border-t">
        <td class="px-3 py-2">${item.type}</td>
        <td class="px-3 py-2 font-medium">${item.id}</td>
        <td class="px-3 py-2">${(item.unit_kerja ?? '-')} / ${(item.ruang ?? '-')}</td>
        <td class="px-3 py-2">${item.spes ?? '-'}</td>
        <td class="px-3 py-2">${item.tahun_pembelian ?? '-'}</td>
        <td class="px-3 py-2">${item.umur} th</td>
      </tr>
    `).join('');
  }

  function updateLokasiRawan(m) {
    const tbody = document.getElementById('lokasiRawanBody');
    const arr = m.lokasi_rawan || [];
    if (!arr.length) {
      tbody.innerHTML = `<tr><td colspan="2" class="px-3 py-4 text-center text-gray-500">Tidak ada data.</td></tr>`;
      return;
    }
    tbody.innerHTML = arr.map(r => `
      <tr class="border-t">
        <td class="px-3 py-2">${r.label}</td>
        <td class="px-3 py-2">${r.count}</td>
      </tr>
    `).join('');
  }

  async function fetchMetrics() {
    const url = "{{ route('dashboard.metrics') }}" + `?min_age=${minAge}`;
    try {
      const res = await fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' }});
      const metrics = await res.json();
      updateKpis(metrics);
      updateBar(metrics);
      updatePie(metrics);
      updateUpgradeTable(metrics);
      updateLokasiRawan(metrics);
    } catch (e) {
      console.error(e);
    }
  }

  // toggle prioritas tinggi (>=7 tahun)
  document.addEventListener('DOMContentLoaded', () => {
    const toggle = document.getElementById('priorityToggle');
    if (toggle) {
      toggle.addEventListener('change', () => {
        minAge = toggle.checked ? 7 : 5;
        fetchMetrics();
      });
    }
  });

  // init
  initCharts();
  fetchMetrics();
  // polling setiap 30 detik
  setInterval(fetchMetrics, 30000);
</script>
@endpush
