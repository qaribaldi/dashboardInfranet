<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\AssetPc;
use App\Models\AssetPrinter;
use App\Models\AssetProyektor;
use App\Models\AssetAc;
use App\Models\AssetHistory;
use App\Models\InventoryLabkom; // Labkom
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        return view('dashboard.index');
    }

    public function clearHistory()
    {
        AssetHistory::truncate(); // hapus semua isi tabel dan reset id

        return redirect()->route('dashboard')
            ->with('status', 'History berhasil dibersihkan!');
    }

    public function metrics(Request $request)
    {
        // Waktu acuan
        $nowJakarta  = now('Asia/Jakarta');
        $currentYear = (int) $nowJakarta->year;

        // === PARAMETER (khusus PC biasa) ===
        $pcRamLow      = (bool) $request->boolean('pc_ram_low', false);
        $pcHddOnly     = (bool) $request->boolean('pc_hdd_only', false);
        $ramThreshold  = (int)  $request->integer('pc_ram_threshold', 8); // default 8 GB

        // === BUCKET UMUR ===
        $selected  = (int) $request->integer('min_age', 5);
        $bucketMap = [
            3  => [3, 4],
            5  => [5, 6],
            7  => [7, 9],
            10 => [10, null],
        ];
        [$ageMin, $ageMax] = $bucketMap[$selected] ?? [5, 6];

        // umur k di tahun N => tahun_pembelian = N - k
        $yearTo   = $currentYear - $ageMin;
        $yearFrom = $ageMax === null ? null : $currentYear - $ageMax;

        $applyYearRange = function ($qb, string $col) use ($yearFrom, $yearTo) {
            if ($yearFrom === null) {
                return $qb->where($col, '<=', $yearTo);
            } else {
                return $qb->whereBetween($col, [$yearFrom, $yearTo]);
            }
        };

        // ===== Totals (DIREVISI) =====
        // PC asset saja
        $assetPcTotal = AssetPc::count();

        // PC Labkom (distinct id_pc supaya tidak double-count)
        $labkomPcTotal = InventoryLabkom::whereNotNull('id_pc')
            ->distinct('id_pc')
            ->count('id_pc');

        // (opsional) jumlah lab unik berdasarkan nama_lab (untuk UI lain jika diperlukan)
        $totalLabkomUnik = InventoryLabkom::whereNotNull('nama_lab')
            ->distinct('nama_lab')
            ->count('nama_lab');

        $totalPrinter   = AssetPrinter::count();
        $totalProyektor = AssetProyektor::count();
        $totalAc        = AssetAc::count();

        // Old per bucket (gabungan untuk PC)
        $oldPcNonLab = $applyYearRange(AssetPc::whereNotNull('tahun_pembelian'), 'tahun_pembelian')->count();
        $oldPcLabkom = $applyYearRange(InventoryLabkom::whereNotNull('tahun_pembelian'), 'tahun_pembelian')->count();
        $oldPc       = $oldPcNonLab + $oldPcLabkom;

        // Old Labkom: hitung DISTINCT nama_lab di rentang umur
        $oldLabkom = $applyYearRange(
            InventoryLabkom::whereNotNull('tahun_pembelian')->whereNotNull('nama_lab'),
            'tahun_pembelian'
        )
        ->distinct('nama_lab')
        ->count('nama_lab');

        $oldPrinter   = $applyYearRange(AssetPrinter::whereNotNull('tahun_pembelian'), 'tahun_pembelian')->count();
        $oldProyektor = $applyYearRange(AssetProyektor::whereNotNull('tahun_pembelian'), 'tahun_pembelian')->count();
        $oldAc        = $applyYearRange(AssetAc::whereNotNull('tahun_pembelian'), 'tahun_pembelian')->count();

        // Bar chart 8 tahun (tren pengadaan)
        $years = [];
        for ($y = $currentYear - 7; $y <= $currentYear; $y++) $years[] = $y;

        $pcByYear = AssetPc::selectRaw('tahun_pembelian as y, COUNT(*) as c')
            ->whereIn('tahun_pembelian', $years)->groupBy('tahun_pembelian')->pluck('c','y')->toArray();
        $printerByYear = AssetPrinter::selectRaw('tahun_pembelian as y, COUNT(*) as c')
            ->whereIn('tahun_pembelian', $years)->groupBy('tahun_pembelian')->pluck('c','y')->toArray();
        $proyektorByYear = AssetProyektor::selectRaw('tahun_pembelian as y, COUNT(*) as c')
            ->whereIn('tahun_pembelian', $years)->groupBy('tahun_pembelian')->pluck('c','y')->toArray();
        $acByYear = AssetAc::selectRaw('tahun_pembelian as y, COUNT(*) as c')
            ->whereIn('tahun_pembelian', $years)->groupBy('tahun_pembelian')->pluck('c','y')->toArray();

        $bar = [
            'labels'   => $years,
            'datasets' => [
                'pc'        => array_map(fn($y) => $pcByYear[$y] ?? 0, $years),
                'printer'   => array_map(fn($y) => $printerByYear[$y] ?? 0, $years),
                'proyektor' => array_map(fn($y) => $proyektorByYear[$y] ?? 0, $years),
                'ac'        => array_map(fn($y) => $acByYear[$y] ?? 0, $years),
            ],
        ];

        // Pie chart — PC dari asset saja (DIREVISI)
        $pie = [
            'labels' => ['PC','Printer','Proyektor','AC'],
            'data'   => [$assetPcTotal, $totalPrinter, $totalProyektor, $totalAc],
        ];

        // === AGREGASI STORAGE UNTUK PC (SSD/HDD) ===
        $pcStorageAgg = DB::table('asset_pc as p')
            ->leftJoin('inventory_hardware_pc as hp', 'hp.id_pc', '=', 'p.id_pc')
            ->leftJoin('inventory_hardware as h', 'h.id_hardware', '=', 'hp.id_hardware')
            ->where(function($q){
                $q->whereNull('h.jenis_hardware')->orWhere('h.jenis_hardware', 'storage');
            })
            ->groupBy('p.id_pc')
            ->selectRaw('p.id_pc,
                SUM(CASE WHEN UPPER(COALESCE(h.storage_type,"")) = "SSD" THEN 1 ELSE 0 END) as ssd_cnt,
                SUM(CASE WHEN UPPER(COALESCE(h.storage_type,"")) = "HDD" THEN 1 ELSE 0 END) as hdd_cnt')
            ->get()
            ->keyBy('id_pc');

        // ===== Kandidat upgrade (BY BUCKET UMUR) =====
        // PC biasa
        $upgradePc = $applyYearRange(
                AssetPc::whereNotNull('tahun_pembelian')->orderBy('tahun_pembelian')->limit(1000),
                'tahun_pembelian'
            )
            ->get(['id_pc as id','unit_kerja','ruang','merk','processor','total_kapasitas_ram as ram','tahun_pembelian'])
            ->map(function($r) use($currentYear, $ramThreshold, $pcStorageAgg){
                $id = $r->id;
                $agg = $pcStorageAgg->get($id);
                $ssdCnt = (int)($agg->ssd_cnt ?? 0);
                $hddCnt = (int)($agg->hdd_cnt ?? 0);
                $hasSsd = $ssdCnt > 0;
                $hddOnly = (!$hasSsd && $hddCnt > 0);

                // hitung RAM rendah (angka)
                $ramGb = (int) preg_replace('/\D+/', '', (string)$r->ram);
                $ramLow = $ramGb > 0 ? ($ramGb < $ramThreshold) : false;

                return [
                    'type'            => 'PC',
                    'id'              => $id,
                    'unit_kerja'      => $r->unit_kerja,
                    'ruang'           => $r->ruang,
                    'spes'            => "{$r->merk} / {$r->processor}",
                    'processor'       => $r->processor,
                    'ram'             => $r->ram,
                    'ram_low'         => $ramLow,
                    'has_ssd'         => $hasSsd,
                    'hdd_only'        => $hddOnly,
                    'status_warna'    => null,
                    'resolusi_max'    => null,
                    'tahun_pembelian' => $r->tahun_pembelian,
                    'umur'            => $currentYear - (int)$r->tahun_pembelian,
                ];
            });

        // Printer
        $upgradePrinter = $applyYearRange(
                AssetPrinter::whereNotNull('tahun_pembelian')->orderBy('tahun_pembelian')->limit(1000),
                'tahun_pembelian'
            )
            ->get(['id_printer as id','unit_kerja','ruang','merk','tipe','status_warna','tahun_pembelian'])
            ->map(fn($r)=>[
                'type'            => 'Printer',
                'id'              => $r->id,
                'unit_kerja'      => $r->unit_kerja,
                'ruang'           => $r->ruang,
                'spes'            => "{$r->merk} / {$r->tipe}",
                'processor'       => null,
                'ram'             => null,
                'status_warna'    => $r->status_warna,
                'resolusi_max'    => null,
                'tahun_pembelian' => $r->tahun_pembelian,
                'umur'            => $currentYear - (int)$r->tahun_pembelian,
            ]);

        // Proyektor
        $upgradeProyektor = $applyYearRange(
                AssetProyektor::whereNotNull('tahun_pembelian')->orderBy('tahun_pembelian')->limit(1000),
                'tahun_pembelian'
            )
            ->get(['id_proyektor as id','nama_ruang','ruang','merk','tipe_proyektor','resolusi_max','tahun_pembelian'])
            ->map(fn($r)=>[
                'type'            => 'Proyektor',
                'id'              => $r->id,
                'unit_kerja'      => $r->nama_ruang,
                'ruang'           => $r->ruang,
                'spes'            => "{$r->merk} / {$r->tipe_proyektor}",
                'processor'       => null,
                'ram'             => null,
                'status_warna'    => null,
                'resolusi_max'    => $r->resolusi_max,
                'tahun_pembelian' => $r->tahun_pembelian,
                'umur'            => $currentYear - (int)$r->tahun_pembelian,
            ]);

        // AC
        $upgradeAc = $applyYearRange(
                AssetAc::whereNotNull('tahun_pembelian')->orderBy('tahun_pembelian')->limit(1000),
                'tahun_pembelian'
            )
            ->get(['id_ac as id','unit_kerja','ruang','merk','tipe_asset','ukuran_pk','kondisi','remote','tahun_pembelian'])
            ->map(fn($r)=>[
                'type'            => 'AC',
                'id'              => $r->id,
                'unit_kerja'      => $r->unit_kerja,
                'ruang'           => $r->ruang,
                'spes'            => trim($r->merk.' / '.$r->tipe_asset.' / '.$r->ukuran_pk),
                'processor'       => null,
                'ram'             => null,
                'status_warna'    => null,
                'resolusi_max'    => null,
                'kondisi'         => $r->kondisi,
                'remote'          => $r->remote,
                'tahun_pembelian' => $r->tahun_pembelian,
                'umur'            => $currentYear - (int)$r->tahun_pembelian,
            ]);

        // LABKOM — umur saja
        $upgradeLabkom = $applyYearRange(
                InventoryLabkom::whereNotNull('tahun_pembelian')->orderBy('tahun_pembelian')->limit(1000),
                'tahun_pembelian'
            )
            ->get(['id_pc as id','nama_lab','ruang','merk','processor','total_kapasitas_ram','tahun_pembelian'])
            ->map(function($r) use ($currentYear) {
                return [
                    'type'            => 'Labkom',
                    'id'              => $r->id,
                    'unit_kerja'      => $r->nama_lab, // key lokasi reuse
                    'ruang'           => $r->ruang,
                    'spes'            => "{$r->merk} / {$r->processor}",
                    'processor'       => $r->processor,
                    'ram'             => is_null($r->total_kapasitas_ram) ? null : (string)$r->total_kapasitas_ram,
                    'status_warna'    => null,
                    'resolusi_max'    => null,
                    'tahun_pembelian' => $r->tahun_pembelian,
                    'umur'            => $currentYear - (int)$r->tahun_pembelian,
                ];
            });

        // Gabungan semua untuk tabel rekomendasi
        $upgradeAll = array_values(array_merge(
            $upgradePc->toArray(),
            $upgradePrinter->toArray(),
            $upgradeProyektor->toArray(),
            $upgradeAc->toArray(),
            $upgradeLabkom->toArray()
        ));

        // Distinct options (opsional)
        $lokasiOptions   = [];
        $spesOptions     = [];
        $ramOptions      = [];
        $warnaOptions    = [];
        $resolusiOptions = [];
        $kondisiOptions  = [];
        $remoteOptions   = [];

        foreach ($upgradeAll as $u) {
            $lok = trim(($u['unit_kerja'] ?? '-')).' / '.trim(($u['ruang'] ?? '-'));
            $lokasiOptions[$lok] = true;
            if (!empty($u['spes']))          $spesOptions[$u['spes']] = true;
            if (!empty($u['ram']))           $ramOptions[$u['ram']] = true; // termasuk Labkom
            if (!empty($u['status_warna']))  $warnaOptions[$u['status_warna']] = true;
            if (!empty($u['resolusi_max']))  $resolusiOptions[$u['resolusi_max']] = true;
            if (!empty($u['kondisi']))       $kondisiOptions[$u['kondisi']] = true;
            if (!empty($u['remote']))        $remoteOptions[$u['remote']] = true;
        }

        $lokasiOptions   = array_values(array_keys($lokasiOptions));
        $spesOptions     = array_values(array_keys($spesOptions));
        $ramOptions      = array_values(array_keys($ramOptions));
        $warnaOptions    = array_values(array_keys($warnaOptions));
        $resolusiOptions = array_values(array_keys($resolusiOptions));
        sort($lokasiOptions, SORT_NATURAL);
        sort($spesOptions, SORT_NATURAL);
        sort($ramOptions, SORT_NATURAL);
        sort($warnaOptions, SORT_NATURAL);
        sort($resolusiOptions, SORT_NATURAL);
        sort($kondisiOptions, SORT_NATURAL);
        sort($remoteOptions, SORT_NATURAL);

        // Server-side filter (opsional)
        $filterField = (string) $request->input('filter_field', '');
        $filterValue = trim((string) $request->input('filter_value', ''));

        $upgrade = $upgradeAll;
        if ($filterField && $filterValue !== '') {
            $upgrade = array_values(array_filter($upgradeAll, function($r) use ($filterField,$filterValue) {
                if ($filterField === 'lokasi') {
                    $lbl = trim(($r['unit_kerja'] ?? '-')).' / '.trim(($r['ruang'] ?? '-'));
                    return strcasecmp($lbl, $filterValue) === 0;
                } elseif ($filterField === 'spes') {
                    return ($r['spes'] ?? '') === $filterValue;
                } elseif ($filterField === 'ram') {
                    return ($r['ram'] ?? '') === $filterValue;
                } elseif ($filterField === 'status_warna') {
                    return ($r['status_warna'] ?? '') === $filterValue;
                } elseif ($filterField === 'resolusi_max') {
                    return ($r['resolusi_max'] ?? '') === $filterValue;
                } elseif ($filterField === 'kondisi') {
                    return ($r['kondisi'] ?? '') === $filterValue;
                } elseif ($filterField === 'remote') {
                    return ($r['remote'] ?? '') === $filterValue;
                }
                return true;
            }));
        }

        // ===== Lokasi rawan (Top-5) — panel kiri: aset biasa =====
        usort($upgrade, fn($a,$b)=> $b['umur'] <=> $a['umur']);
        $lokCounter = [];
        foreach ($upgrade as $r) {
            $typeLower = strtolower($r['type']);

            if ($typeLower === 'pc') {
                if ($pcRamLow && empty($r['ram_low']))   continue;
                if ($pcHddOnly && empty($r['hdd_only'])) continue;
            }

            // panel kiri tidak menghitung Labkom
            if ($typeLower === 'labkom') continue;

            $label = trim($r['unit_kerja'] ?? '-').' / '.trim($r['ruang'] ?? '-');
            if (!isset($lokCounter[$label])) {
                $lokCounter[$label] = ['pc'=>0,'printer'=>0,'proyektor'=>0,'ac'=>0,'total'=>0];
            }
            if (isset($lokCounter[$label][$typeLower])) $lokCounter[$label][$typeLower]++;
            $lokCounter[$label]['total']++;
        }
        uasort($lokCounter, fn($a,$b) => $b['total'] <=> $a['total']);
        $lokasiRawan = [];
        foreach (array_slice($lokCounter, 0, 5, true) as $label => $counts) {
            $lokasiRawan[] = [
                'label'      => $label,
                'pc'         => $counts['pc'],
                'printer'    => $counts['printer'],
                'proyektor'  => $counts['proyektor'],
                'ac'         => $counts['ac'],
                'total'      => $counts['total'],
            ];
        }

        // ===== Lokasi rawan (Top-5) — panel kanan: khusus Labkom =====
        $labkomOnly = $upgradeLabkom->toArray();
        usort($labkomOnly, fn($a,$b)=> $b['umur'] <=> $a['umur']);
        $labkomCounter = [];
        foreach ($labkomOnly as $r) {
            $label = trim($r['unit_kerja'] ?? '-').' / '.trim($r['ruang'] ?? '-'); // unit_kerja = nama_lab
            if (!isset($labkomCounter[$label])) $labkomCounter[$label] = ['labkom'=>0,'total'=>0];
            $labkomCounter[$label]['labkom']++;
            $labkomCounter[$label]['total']++;
        }
        uasort($labkomCounter, fn($a,$b) => $b['total'] <=> $a['total']);
        $lokasiRawanLabkom = [];
        foreach (array_slice($labkomCounter, 0, 5, true) as $label => $counts) {
            $lokasiRawanLabkom[] = [
                'label'   => $label,
                'labkom'  => $counts['labkom'],
                'total'   => $counts['total'],
            ];
        }

        // ===== BAR: Kandidat Upgrade per jenis (tetap) =====
        $ageLabel = $ageMax === null ? "≥{$ageMin}" : "{$ageMin}–{$ageMax}";
        $upgradeBar = [
            'title'  => "Kandidat Upgrade (Umur {$ageLabel} th)",
            'labels' => ['PC','Printer','Proyektor','AC'],
            'data'   => [$oldPc, $oldPrinter, $oldProyektor, $oldAc], // PC gabungan
        ];

        // ===== BAR: Labkom per Lokasi (Top-8) =====
        $labkomLocCounts = [];
        foreach ($labkomOnly as $r) {
            $label = trim($r['unit_kerja'] ?? '-').' / '.trim($r['ruang'] ?? '-');
            $labkomLocCounts[$label] = ($labkomLocCounts[$label] ?? 0) + 1;
        }
        arsort($labkomLocCounts);
        $topN = 8;
        $labkomLocLabels = array_slice(array_keys($labkomLocCounts), 0, $topN);
        $labkomLocData   = array_map(fn($k)=>$labkomLocCounts[$k], $labkomLocLabels);
        $labkomLocBar = [
            'title'  => "Labkom Perlu Upgrade per Lokasi (Umur {$ageLabel} th)",
            'labels' => $labkomLocLabels,
            'data'   => $labkomLocData,
        ];

        // ===== HISTORI (per bulan atau 30 hari, WIB) =====
        $historyMonth = trim((string) $request->input('history_month', '')); // format 'YYYY-MM' atau ''
        $periodLabel  = '30 hari'; // default label untuk judul
        $histQuery    = AssetHistory::query();

        if ($historyMonth !== '') {
            // Jika user pilih bulan tertentu → filter range 1 s/d akhir bulan tsb
            try {
                [$y, $m] = explode('-', $historyMonth);
                $start = Carbon::createFromDate((int)$y, (int)$m, 1, 'Asia/Jakarta')->startOfMonth();
                $end   = $start->copy()->endOfMonth();
                $histQuery->whereBetween('created_at', [$start, $end]);

                // Label periode, contoh: "September 2025"
                $periodLabel = $start->locale('id')->translatedFormat('F Y');
            } catch (\Throwable $e) {
                // fallback ke 30 hari terakhir jika parsing gagal
                $histQuery->where('created_at','>=',$nowJakarta->copy()->subDays(30));
                $periodLabel = '30 hari';
            }
        } else {
            // Default: 30 hari terakhir
            $histQuery->where('created_at','>=',$nowJakarta->copy()->subDays(30));
            $periodLabel = '30 hari';
        }

        $user = auth()->user();

        $canHistory = $user->can('dashboard.view.history');

        $histMap = [
            'PC'        => 'dashboard.history.pc',
            'PRINTER'   => 'dashboard.history.printer',
            'PROYEKTOR' => 'dashboard.history.proyektor',
            'AC'        => 'dashboard.history.ac',
        ];

        $allowedTypes = [];
        foreach ($histMap as $type => $perm) {
            if ($user->can($perm)) {
                $allowedTypes[] = $type;
            }
        }

        $historyDenied = false;

        if (! $canHistory || empty($allowedTypes)) {
            $historyDenied = true;
            $history = collect();
        } else {
            $histQuery->whereIn(DB::raw('UPPER(asset_type)'), $allowedTypes);
            $history = $histQuery
                ->orderBy('created_at','desc')
                ->limit(200)
                ->get()
                ->map(function($h) {
                    $details = [];
                    $changes = $h->changes_json ?? [];
                    if (is_array($changes)) {
                        foreach ($changes as $k => $pair) {
                            $from = $pair['from'] ?? null;
                            $to   = $pair['to'] ?? null;
                            $details[] = "{$k}: ".($from ?? '-')." → ".($to ?? '-');
                        }
                    }

                    $raw = $h->getRawOriginal('created_at');
                    $tsEpoch = $raw 
                        ? Carbon::createFromFormat('Y-m-d H:i:s', $raw, 'Asia/Jakarta')->getTimestamp() * 1000
                        : null;

                    return [
                        'ts_epoch'   => $tsEpoch,
                        'asset_type' => strtoupper($h->asset_type),
                        'asset_id'   => $h->asset_id,
                        'action'     => $h->action,
                        'note'       => $h->note,
                        'summary'    => implode('; ', $details),
                        'edited_by'  => $h->edited_by ?? '-',
                    ];
                });
        }

        // Judul panel lokasi
        $lokTitleMap = [
            3  => 'Lokasi yang Perlu Diperhatikan (Early Warning)',
            5  => 'Lokasi Rawan (Rekomendasi)',
            7  => 'Lokasi Prioritas Tinggi',
            10 => 'Lokasi Tertua (10+ Tahun)',
        ];
        $lokasiTitle       = $lokTitleMap[$selected] ?? 'Lokasi Rawan';
        $lokasiLabkomTitle = $lokTitleMap[$selected] ?? 'Lokasi Rawan (Labkom)';

        return response()->json([
            'now_epoch' => $nowJakarta->getTimestamp()*1000,
            'now'       => $nowJakarta->toIso8601String(),

            'min_age'   => $selected,
            'age_bucket'=> [
                'min'   => $ageMin,
                'max'   => $ageMax,
                'label' => $ageLabel,
            ],
            'totals' => [
                // DIREVISI: pc hanya dari asset
                'pc'          => $assetPcTotal,

                // DIREVISI: total PC Labkom (distinct id_pc)
                'pc_labkom'   => $labkomPcTotal,

                // (opsional) jumlah lab unik untuk kebutuhan lain di UI
                'labkom_unik' => $totalLabkomUnik,

                'printer'     => $totalPrinter,
                'proyektor'   => $totalProyektor,
                'ac'          => $totalAc,
                'old' => [
                    // Tetap: PC gabungan (asset + labkom) untuk kandidat upgrade
                    'pc'        => $oldPc,
                    'labkom'    => $oldLabkom,
                    'printer'   => $oldPrinter,
                    'proyektor' => $oldProyektor,
                    'ac'        => $oldAc,
                ],
            ],
            'bar'                  => $bar,
            'pie'                  => $pie,
            'upgrade_bar'          => $upgradeBar,
            'labkom_loc_bar'       => $labkomLocBar,
            'upgrade'              => $upgrade,
            'lokasi_rawan'         => $lokasiRawan,
            'lokasi_title'         => $lokasiTitle,
            'lokasi_rawan_labkom'  => $lokasiRawanLabkom,
            'lokasi_labkom_title'  => $lokasiLabkomTitle,
            'history'              => $history,
            'history_denied'       => $historyDenied,
            'history_month'        => $historyMonth,
            'history_period_label' => $historyMonth !== '' ? $periodLabel : '30 hari',
            'filters' => [
                'lokasi_options'   => $lokasiOptions,
                'spes_options'     => $spesOptions,
                'ram_options'      => $ramOptions,
                'warna_options'    => $warnaOptions,
                'resolusi_options' => $resolusiOptions,
                'kondisi_options'  => $kondisiOptions,
                'remote_options'   => $remoteOptions,
            ],
            'pc_params' => [
                'ram_low'        => $pcRamLow,
                'hdd_only'       => $pcHddOnly,
                'ram_threshold'  => $ramThreshold,
            ],
        ]);
    }
}
