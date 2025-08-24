<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AssetPc;
use App\Models\AssetPrinter;
use App\Models\AssetProyektor;
use App\Models\AssetHistory; // histori
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
        $now = Carbon::now('Asia/Jakarta');
        $currentYear = (int) $now->year;

        // === BUCKET UMUR ===
        // UI kirim min_age = 3 | 5 | 7 | 10
        $selected = (int) $request->integer('min_age', 5);
        // mapping: selected -> [minAge, maxAge]; maxAge=null artinya open-ended (>= minAge)
        $bucketMap = [
            3  => [3, 4],
            5  => [5, 6],
            7  => [7, 9],
            10 => [10, null],
        ];
        [$ageMin, $ageMax] = $bucketMap[$selected] ?? [5, 6];

        // Konversi umur -> rentang tahun_pembelian
        // umur k di tahun N => tahun_pembelian = N - k
        $yearTo   = $currentYear - $ageMin;                              // batas atas (lebih baru)
        $yearFrom = $ageMax === null ? null : $currentYear - $ageMax;    // batas bawah (lebih lama)

        // Helper menerapkan rentang ke query kolom tahun_pembelian
        $applyYearRange = function ($qb, string $col) use ($yearFrom, $yearTo) {
            if ($yearFrom === null) {
                return $qb->where($col, '<=', $yearTo);                  // umur >= ageMin
            } else {
                return $qb->whereBetween($col, [$yearFrom, $yearTo]);    // umur di [ageMin..ageMax]
            }
        };

        // ===== Totals per jenis (global) =====
        $totalPc        = AssetPc::count();
        $totalPrinter   = AssetPrinter::count();
        $totalProyektor = AssetProyektor::count();

        // ===== Hitung "old" SEBESAR BUCKET (BUKAN >= selected) =====
        $oldPc        = $applyYearRange(AssetPc::whereNotNull('tahun_pembelian'), 'tahun_pembelian')->count();
        $oldPrinter   = $applyYearRange(AssetPrinter::whereNotNull('tahun_pembelian'), 'tahun_pembelian')->count();
        $oldProyektor = $applyYearRange(AssetProyektor::whereNotNull('tahun_pembelian'), 'tahun_pembelian')->count();

        // ===== Bar chart: 8 tahun terakhir (global) =====
        $years = [];
        for ($y = $currentYear - 7; $y <= $currentYear; $y++) $years[] = $y;

        $pcByYear = AssetPc::selectRaw('tahun_pembelian as y, COUNT(*) as c')
            ->whereIn('tahun_pembelian', $years)->groupBy('tahun_pembelian')->pluck('c','y')->toArray();
        $printerByYear = AssetPrinter::selectRaw('tahun_pembelian as y, COUNT(*) as c')
            ->whereIn('tahun_pembelian', $years)->groupBy('tahun_pembelian')->pluck('c','y')->toArray();
        $proyektorByYear = AssetProyektor::selectRaw('tahun_pembelian as y, COUNT(*) as c')
            ->whereIn('tahun_pembelian', $years)->groupBy('tahun_pembelian')->pluck('c','y')->toArray();

        $bar = [
            'labels' => $years,
            'datasets' => [
                'pc'        => array_map(fn($y) => $pcByYear[$y] ?? 0, $years),
                'printer'   => array_map(fn($y) => $printerByYear[$y] ?? 0, $years),
                'proyektor' => array_map(fn($y) => $proyektorByYear[$y] ?? 0, $years),
            ],
        ];

        // ===== Pie chart: proporsi aset per jenis (global) =====
        $pie = [
            'labels' => ['PC','Printer','Proyektor'],
            'data'   => [$totalPc, $totalPrinter, $totalProyektor],
        ];

        // ===== Kumpulan kandidat upgrade (berdasar BUCKET) =====
        $upgradeList = [
            'pc' => $applyYearRange(
                        AssetPc::whereNotNull('tahun_pembelian')->orderBy('tahun_pembelian')->limit(1000),
                        'tahun_pembelian'
                    )
                    ->get(['id_pc as id','unit_kerja','ruang','merk','processor','total_kapasitas_ram as ram','tahun_pembelian'])
                    ->map(fn($r)=>[
                        'type'            => 'PC',
                        'id'              => $r->id,
                        'unit_kerja'      => $r->unit_kerja,
                        'ruang'           => $r->ruang,
                        'spes'            => "{$r->merk} / {$r->processor}",
                        'processor'       => $r->processor,
                        'ram'             => $r->ram,
                        'status_warna'    => null,     // khusus printer
                        'resolusi_max'    => null,     // khusus proyektor
                        'tahun_pembelian' => $r->tahun_pembelian,
                        'umur'            => $currentYear - (int)$r->tahun_pembelian,
                    ]),
            'printer' => $applyYearRange(
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
                        ]),
            'proyektor' => $applyYearRange(
                            AssetProyektor::whereNotNull('tahun_pembelian')->orderBy('tahun_pembelian')->limit(1000),
                            'tahun_pembelian'
                        )
                        ->get(['id_proyektor as id','nama_ruang','ruang','merk','tipe_proyektor','resolusi_max','tahun_pembelian'])
                        ->map(fn($r)=>[
                            'type'            => 'Proyektor',
                            'id'              => $r->id,
                            'unit_kerja'      => $r->nama_ruang,   // konsisten pakai unit_kerja/nama_ruang pada kolom yang sama
                            'ruang'           => $r->ruang,
                            'spes'            => "{$r->merk} / {$r->tipe_proyektor}",
                            'processor'       => null,
                            'ram'             => null,
                            'status_warna'    => null,
                            'resolusi_max'    => $r->resolusi_max,
                            'tahun_pembelian' => $r->tahun_pembelian,
                            'umur'            => $currentYear - (int)$r->tahun_pembelian,
                        ]),
        ];

        // Gabungkan semua jenis
        $upgradeAll = array_values(array_merge(
            $upgradeList['pc']->toArray(),
            $upgradeList['printer']->toArray(),
            $upgradeList['proyektor']->toArray()
        ));

        // ===== Siapkan OPSI DROPDOWN (distinct) =====
        $lokasiOptions   = [];
        $spesOptions     = [];
        $ramOptions      = [];
        $warnaOptions    = [];
        $resolusiOptions = [];

        foreach ($upgradeAll as $u) {
            $lok = trim(($u['unit_kerja'] ?? '-')).' / '.trim(($u['ruang'] ?? '-'));
            $lokasiOptions[$lok] = true;
            if (!empty($u['spes']))          $spesOptions[$u['spes']] = true;
            if (!empty($u['ram']))           $ramOptions[$u['ram']] = true;
            if (!empty($u['status_warna']))  $warnaOptions[$u['status_warna']] = true;
            if (!empty($u['resolusi_max']))  $resolusiOptions[$u['resolusi_max']] = true;
        }

        $lokasiOptions   = array_values(array_keys($lokasiOptions));
        $spesOptions     = array_values(array_keys($spesOptions));
        $ramOptions      = array_values(array_keys($ramOptions));
        $warnaOptions    = array_values(array_keys($warnaOptions));
        $resolusiOptions = array_values(array_keys($resolusiOptions));

        sort($lokasiOptions,   SORT_NATURAL);
        sort($spesOptions,     SORT_NATURAL);
        sort($ramOptions,      SORT_NATURAL);
        sort($warnaOptions,    SORT_NATURAL);
        sort($resolusiOptions, SORT_NATURAL);

        // ===== Server-side FILTER (opsional, tetap dipertahankan) =====
        $filterField = (string) $request->input('filter_field', '');   // lokasi | spes | ram | status_warna | resolusi_max
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
                }
                return true;
            }));
        }

        // Urutkan upgrade (umur tertua dulu)
        usort($upgrade, fn($a,$b)=> $b['umur'] <=> $a['umur']);

        // ===== LOKASI PER-JENIS (top-5, sesuai bucket & filter) =====
        $lokCounter = []; // "Unit / Ruang" => ['pc'=>0,'printer'=>0,'proyektor'=>0,'total'=>0]
        foreach ($upgrade as $r) {
            $label = trim($r['unit_kerja'] ?? '-').' / '.trim($r['ruang'] ?? '-');
            if (!isset($lokCounter[$label])) {
                $lokCounter[$label] = ['pc'=>0,'printer'=>0,'proyektor'=>0,'total'=>0];
            }
            $type = strtolower($r['type']); // 'pc'|'printer'|'proyektor'
            if (isset($lokCounter[$label][$type])) {
                $lokCounter[$label][$type]++;
            }
            $lokCounter[$label]['total']++;
        }
        // sort by total desc & slice top-5
        uasort($lokCounter, fn($a,$b) => $b['total'] <=> $a['total']);
        $lokasiRawan = [];
        foreach (array_slice($lokCounter, 0, 5, true) as $label => $counts) {
            $lokasiRawan[] = [
                'label'      => $label,
                'pc'         => $counts['pc'],
                'printer'    => $counts['printer'],
                'proyektor'  => $counts['proyektor'],
                'total'      => $counts['total'],
            ];
        }

        // Judul dinamis untuk bagian lokasi
        $lokTitleMap = [
            3  => 'Lokasi yang Perlu Diperhatikan (Early Warning)',
            5  => 'Lokasi Rawan (Rekomendasi)',
            7  => 'Lokasi Prioritas Tinggi',
            10 => 'Lokasi Tertua (10+ Tahun)',
        ];
        $lokasiTitle = $lokTitleMap[$selected] ?? 'Lokasi Rawan';

        // ===== HISTORI PERBAIKAN/UPGRADE: 30 hari terakhir =====
        $history = AssetHistory::where('created_at','>=', now('Asia/Jakarta')->subDays(30))
            ->orderBy('created_at','desc')
            ->limit(50)
            ->get()
            ->map(function($h) {
                $details = [];
                $changes = $h->changes_json ?? [];
                if (is_array($changes)) {
                    foreach ($changes as $k => $pair) {
                        $from = is_array($pair) && array_key_exists('from', $pair) ? $pair['from'] : null;
                        $to   = is_array($pair) && array_key_exists('to', $pair)   ? $pair['to']   : null;
                        $details[] = "{$k}: ".($from ?? '-')." → ".($to ?? '-');
                    }
                }
                return [
                    'ts'         => optional($h->created_at)->toIso8601String(),
                    'asset_type' => strtoupper($h->asset_type),
                    'asset_id'   => $h->asset_id,
                    'action'     => $h->action,   // upgrade / repair / update
                    'note'       => $h->note,
                    'summary'    => implode('; ', $details),
                ];
            });

        // Label bucket untuk UI
        $ageLabel = $ageMax === null ? "≥{$ageMin}" : "{$ageMin}–{$ageMax}";

        return response()->json([
            'now' => $now->toIso8601String(),
            'min_age' => $selected,
            'age_bucket' => [
                'min'   => $ageMin,
                'max'   => $ageMax,
                'label' => $ageLabel,
            ],
            'totals' => [
                'pc'        => $totalPc,
                'printer'   => $totalPrinter,
                'proyektor' => $totalProyektor,
                // jumlah dalam BUCKET
                'old' => [
                    'pc'        => $oldPc,
                    'printer'   => $oldPrinter,
                    'proyektor' => $oldProyektor,
                ],
            ],
            'bar'             => $bar,
            'pie'             => $pie,
            'upgrade'         => $upgrade,          // sudah dibatasi bucket dan filter server-side bila ada
            'lokasi_rawan'    => $lokasiRawan,      // sekarang per-jenis + total
            'lokasi_title'    => $lokasiTitle,      // judul dinamis
            'history'         => $history,
            'filters' => [
                'lokasi_options'   => $lokasiOptions,
                'spes_options'     => $spesOptions,
                'ram_options'      => $ramOptions,
                'warna_options'    => $warnaOptions,
                'resolusi_options' => $resolusiOptions,
            ],
        ]);
    }
}
