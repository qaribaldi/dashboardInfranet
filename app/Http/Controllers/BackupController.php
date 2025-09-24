<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class BackupController extends Controller
{
    // tabel yg diizinkan di-backup => nama file csv
    private array $tables = [
        'asset_pc'            => 'asset_pc.csv',
        'asset_printer'       => 'asset_printer.csv',
        'asset_proyektor'     => 'asset_proyektor.csv',
        'asset_ac'            => 'asset_ac.csv',
        'inventory_hardware'  => 'inventory_hardware.csv',
        'inventory_labkom'    => 'inventory_labkom.csv',
        'asset_history'       => 'asset_history.csv', // hapus jika tidak mau dibackup
    ];

    public function csv(Request $request)
    {
        // cek permission
        if (! $request->user()->can('backup.download')) {
            abort(403, 'Tidak punya izin untuk backup.');
        }

        $table = $request->query('table');
        if (! $table || ! array_key_exists($table, $this->tables)) {
            abort(400, 'Parameter ?table= tidak valid.');
        }

        if (! Schema::hasTable($table)) {
            abort(404, "Tabel {$table} tidak ditemukan.");
        }

        $cols = Schema::getColumnListing($table);
        $filename = $this->tables[$table] ?? ($table . '.csv');

        $headers = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Cache-Control'       => 'no-store, no-cache, must-revalidate, max-age=0',
        ];

        return response()->stream(function () use ($table, $cols) {
            $out = fopen('php://output', 'w');

            // header kolom
            fputcsv($out, $cols);

            // data (hemat memori)
            $orderCol = $cols[0] ?? null;
            $query = DB::table($table)->select($cols);
            if ($orderCol) $query->orderBy($orderCol);

            foreach ($query->cursor() as $row) {
                $line = [];
                foreach ($cols as $c) {
                    $val = $row->{$c} ?? null;
                    if ($val instanceof \DateTimeInterface) {
                        $val = $val->format('Y-m-d H:i:s');
                    } elseif (is_bool($val)) {
                        $val = $val ? 1 : 0;
                    } elseif ($val === null) {
                        $val = '';
                    }
                    $line[] = $val;
                }
                fputcsv($out, $line);
            }

            fclose($out);
        }, 200, $headers);
    }
}
