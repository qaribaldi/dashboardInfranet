<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AssetPc;
use App\Models\AssetPrinter;
use App\Models\AssetProyektor;
use App\Models\AssetAc;
use App\Models\AssetHistory;
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

        // Totals
        $totalPc        = AssetPc::count();
        $totalPrinter   = AssetPrinter::count();
        $totalProyektor = AssetProyektor::count();
        $totalAc        = AssetAc::count();

        // Old per bucket
        $oldPc        = $applyYearRange(AssetPc::whereNotNull('tahun_pembelian'), 'tahun_pembelian')->count();
        $oldPrinter   = $applyYearRange(AssetPrinter::whereNotNull('tahun_pembelian'), 'tahun_pembelian')->count();
        $oldProyektor = $applyYearRange(AssetProyektor::whereNotNull('tahun_pembelian'), 'tahun_pembelian')->count();
        $oldAc        = $applyYearRange(AssetAc::whereNotNull('tahun_pembelian'), 'tahun_pembelian')->count();

        // Bar chart 8 tahun
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

        // Pie chart
        $pie = [
            'labels' => ['PC','Printer','Proyektor','AC'],
            'data'   => [$totalPc, $totalPrinter, $totalProyektor, $totalAc],
        ];

        // Kandidat upgrade (by bucket)
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
                        'status_warna'    => null,
                        'resolusi_max'    => null,
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
                            'unit_kerja'      => $r->nama_ruang,
                            'ruang'           => $r->ruang,
                            'spes'            => "{$r->merk} / {$r->tipe_proyektor}",
                            'processor'       => null,
                            'ram'             => null,
                            'status_warna'    => null,
                            'resolusi_max'    => $r->resolusi_max,
                            'tahun_pembelian' => $r->tahun_pembelian,
                            'umur'            => $currentYear - (int)$r->tahun_pembelian,
                        ]),
            'ac' => $applyYearRange(
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
                    ]),
        ];

        $upgradeAll = array_values(array_merge(
            $upgradeList['pc']->toArray(),
            $upgradeList['printer']->toArray(),
            $upgradeList['proyektor']->toArray(),
            $upgradeList['ac']->toArray()
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
            if (!empty($u['ram']))           $ramOptions[$u['ram']] = true;
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

        // Lokasi rawan (top-5)
        usort($upgrade, fn($a,$b)=> $b['umur'] <=> $a['umur']);
        $lokCounter = [];
        foreach ($upgrade as $r) {
            $label = trim($r['unit_kerja'] ?? '-').' / '.trim($r['ruang'] ?? '-');
            if (!isset($lokCounter[$label])) {
                $lokCounter[$label] = ['pc'=>0,'printer'=>0,'proyektor'=>0,'ac'=>0,'total'=>0];
            }
            $type = strtolower($r['type']);
            if (isset($lokCounter[$label][$type])) $lokCounter[$label][$type]++;
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

        $lokTitleMap = [
            3  => 'Lokasi yang Perlu Diperhatikan (Early Warning)',
            5  => 'Lokasi Rawan (Rekomendasi)',
            7  => 'Lokasi Prioritas Tinggi',
            10 => 'Lokasi Tertua (10+ Tahun)',
        ];
        $lokasiTitle = $lokTitleMap[$selected] ?? 'Lokasi Rawan';

        // ===== HISTORI (30 hari, fix WIB) =====
        $history = AssetHistory::where('created_at','>=', $nowJakarta->copy()->subDays(30))
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
                // BACA mentah dari DB (string), anggap WIB, jadi epoch ms
                $raw = $h->getRawOriginal('created_at'); // "YYYY-MM-DD HH:MM:SS"
                $tsEpoch = null;
                if ($raw) {
                    $tsEpoch = Carbon::createFromFormat('Y-m-d H:i:s', $raw, 'Asia/Jakarta')->getTimestamp() * 1000;
                }

                return [
                    'ts_epoch'   => $tsEpoch,                               // epoch ms (WIB)
                    'asset_type' => strtoupper($h->asset_type),
                    'asset_id'   => $h->asset_id,
                    'action'     => $h->action,
                    'note'       => $h->note,
                    'summary'    => implode('; ', $details),
                    'edited_by'  => $h->edited_by ?? '-',                   // 👈 nama user pengedit
                ];
            });

        $ageLabel = $ageMax === null ? "≥{$ageMin}" : "{$ageMin}–{$ageMax}";

        return response()->json([
            'now_epoch' => $nowJakarta->getTimestamp()*1000, // epoch WIB (ms)
            'now'       => $nowJakarta->toIso8601String(),

            'min_age'   => $selected,
            'age_bucket'=> [
                'min'   => $ageMin,
                'max'   => $ageMax,
                'label' => $ageLabel,
            ],
            'totals' => [
                'pc'        => $totalPc,
                'printer'   => $totalPrinter,
                'proyektor' => $totalProyektor,
                'ac'        => $totalAc,
                'old' => [
                    'pc'        => $oldPc,
                    'printer'   => $oldPrinter,
                    'proyektor' => $oldProyektor,
                    'ac'        => $oldAc,
                ],
            ],
            'bar'             => $bar,
            'pie'             => $pie,
            'upgrade'         => $upgrade,
            'lokasi_rawan'    => $lokasiRawan,
            'lokasi_title'    => $lokasiTitle,
            'history'         => $history,
            'filters' => [
                'lokasi_options'   => $lokasiOptions,
                'spes_options'     => $spesOptions,
                'ram_options'      => $ramOptions,
                'warna_options'    => $warnaOptions,
                'resolusi_options' => $resolusiOptions,
                'kondisi_options'  => $kondisiOptions,
                'remote_options'   => $remoteOptions,
            ],
        ]);
    }
}
