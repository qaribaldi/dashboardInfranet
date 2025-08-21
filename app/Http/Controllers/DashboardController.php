<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AssetPc;
use App\Models\AssetPrinter;
use App\Models\AssetProyektor;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        // view akan memuat Chart.js & polling ke route metrics()
        return view('dashboard.index');
    }

    public function metrics(Request $request)
{
    $now = \Carbon\Carbon::now('Asia/Jakarta');
    $currentYear = (int) $now->year;

    // PRIORITAS: default 5 tahun; jika toggle "prioritas tinggi" aktif → 7 tahun
    $minAge = (int) $request->integer('min_age', 5);  // 5 atau 7
    $threshold = $currentYear - $minAge;

    // ===== Totals per jenis =====
    $totalPc = \App\Models\AssetPc::count();
    $totalPrinter = \App\Models\AssetPrinter::count();
    $totalProyektor = \App\Models\AssetProyektor::count();

    // ===== Old counts (umur >= minAge) =====
    $oldPc = \App\Models\AssetPc::whereNotNull('tahun_pembelian')->where('tahun_pembelian', '<=', $threshold)->count();
    $oldPrinter = \App\Models\AssetPrinter::whereNotNull('tahun_pembelian')->where('tahun_pembelian', '<=', $threshold)->count();
    $oldProyektor = \App\Models\AssetProyektor::whereNotNull('tahun_pembelian')->where('tahun_pembelian', '<=', $threshold)->count();

    // ===== Bar chart: 8 tahun terakhir =====
    $years = [];
    for ($y = $currentYear - 7; $y <= $currentYear; $y++) $years[] = $y;

    $pcByYear = \App\Models\AssetPc::selectRaw('tahun_pembelian as y, COUNT(*) as c')
        ->whereIn('tahun_pembelian', $years)->groupBy('tahun_pembelian')->pluck('c','y')->toArray();
    $printerByYear = \App\Models\AssetPrinter::selectRaw('tahun_pembelian as y, COUNT(*) as c')
        ->whereIn('tahun_pembelian', $years)->groupBy('tahun_pembelian')->pluck('c','y')->toArray();
    $proyektorByYear = \App\Models\AssetProyektor::selectRaw('tahun_pembelian as y, COUNT(*) as c')
        ->whereIn('tahun_pembelian', $years)->groupBy('tahun_pembelian')->pluck('c','y')->toArray();

    $bar = [
        'labels' => $years,
        'datasets' => [
            'pc' => array_map(fn($y) => $pcByYear[$y] ?? 0, $years),
            'printer' => array_map(fn($y) => $printerByYear[$y] ?? 0, $years),
            'proyektor' => array_map(fn($y) => $proyektorByYear[$y] ?? 0, $years),
        ],
    ];

    // ===== Pie chart: proporsi aset per jenis =====
    $pie = [
        'labels' => ['PC','Printer','Proyektor'],
        'data' => [$totalPc, $totalPrinter, $totalProyektor],
    ];

    // ===== Rekomendasi upgrade (umur >= minAge) =====
    $upgradeList = [
        'pc' => \App\Models\AssetPc::whereNotNull('tahun_pembelian')->where('tahun_pembelian','<=',$threshold)
                ->orderBy('tahun_pembelian')->limit(150)
                ->get(['id_pc as id','unit_kerja','ruang','merk','processor','tahun_pembelian'])
                ->map(fn($r)=>[
                    'type'=>'PC','id'=>$r->id,'unit_kerja'=>$r->unit_kerja,'ruang'=>$r->ruang,
                    'spes'=>"{$r->merk} / {$r->processor}",
                    'tahun_pembelian'=>$r->tahun_pembelian,
                    'umur'=>$currentYear - (int)$r->tahun_pembelian
                ]),
        'printer' => \App\Models\AssetPrinter::whereNotNull('tahun_pembelian')->where('tahun_pembelian','<=',$threshold)
                ->orderBy('tahun_pembelian')->limit(150)
                ->get(['id_printer as id','unit_kerja','ruang','merk','tipe','tahun_pembelian'])
                ->map(fn($r)=>[
                    'type'=>'Printer','id'=>$r->id,'unit_kerja'=>$r->unit_kerja,'ruang'=>$r->ruang,
                    'spes'=>"{$r->merk} / {$r->tipe}",
                    'tahun_pembelian'=>$r->tahun_pembelian,
                    'umur'=>$currentYear - (int)$r->tahun_pembelian
                ]),
        'proyektor' => \App\Models\AssetProyektor::whereNotNull('tahun_pembelian')->where('tahun_pembelian','<=',$threshold)
                ->orderBy('tahun_pembelian')->limit(150)
                ->get(['id_proyektor as id','nama_ruang','ruang','merk','tipe_proyektor','tahun_pembelian'])
                ->map(fn($r)=>[
                    'type'=>'Proyektor','id'=>$r->id,'unit_kerja'=>$r->nama_ruang,'ruang'=>$r->ruang,
                    'spes'=>"{$r->merk} / {$r->tipe_proyektor}",
                    'tahun_pembelian'=>$r->tahun_pembelian,
                    'umur'=>$currentYear - (int)$r->tahun_pembelian
                ]),
    ];

    // Flatten & sort
    $upgrade = array_values(array_merge(
        $upgradeList['pc']->toArray(),
        $upgradeList['printer']->toArray(),
        $upgradeList['proyektor']->toArray()
    ));
    usort($upgrade, fn($a,$b)=> $b['umur'] <=> $a['umur']);

    // ===== LOKASI RAWAN: top-5 ruangan/unit dengan aset tua terbanyak (>= minAge) =====
    $counter = [];

    // helper untuk tambah hitungan per lokasi
    $bump = function(string $unit=null, string $ruang=null) use (&$counter) {
        $unit = $unit ?: '-';
        $ruang = $ruang ?: '-';
        $label = trim($unit).' / '.trim($ruang);
        $counter[$label] = ($counter[$label] ?? 0) + 1;
    };

    foreach ($upgradeList['pc'] as $r)       { $bump($r['unit_kerja'] ?? null, $r['ruang'] ?? null); }
    foreach ($upgradeList['printer'] as $r)  { $bump($r['unit_kerja'] ?? null, $r['ruang'] ?? null); }
    foreach ($upgradeList['proyektor'] as $r){ $bump($r['unit_kerja'] ?? null, $r['ruang'] ?? null); }

    arsort($counter);
    $lokasiRawan = [];
    foreach (array_slice($counter, 0, 5, true) as $label => $count) {
        $lokasiRawan[] = ['label' => $label, 'count' => $count];
    }

    return response()->json([
        'now' => $now->toIso8601String(),
        'min_age' => $minAge,
        'threshold_year' => $threshold,
        'totals' => [
            'pc' => $totalPc,
            'printer' => $totalPrinter,
            'proyektor' => $totalProyektor,
            'old' => [
                'pc' => $oldPc,
                'printer' => $oldPrinter,
                'proyektor' => $oldProyektor,
            ],
        ],
        'bar' => $bar,
        'pie' => $pie,
        'upgrade' => $upgrade,
        'lokasi_rawan' => $lokasiRawan,
    ]);
}

}
