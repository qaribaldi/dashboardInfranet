<?php

namespace App\Http\Controllers;

use App\Models\AssetPrinter;
use App\Models\AssetHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class AssetPrinterController extends Controller
{
    private string $table = 'asset_printer';
    private string $pk    = 'id_printer';

    // Kolom standar sesuai tabel yang kamu kirim (+ timestamps bila ada)
    private array $std = [
        'id_printer','unit_kerja','user','jabatan','ruang',
        'jenis_printer','merk','tipe','scanner','tinta',
        'status_warna','kondisi','tahun_pembelian','keterangan_tambahan',
        'created_at','updated_at',
    ];

    /** Map tipe input -> tipe kolom MySQL/Blueprint */
    private const TYPE_MAP = [
        'string'   => 'string',
        'text'     => 'text',
        'integer'  => 'integer',
        'boolean'  => 'boolean',
        'date'     => 'date',
        'datetime' => 'dateTime',
    ];

    /** Normalisasi nama kolom ke snake_case aman */
    private function normalize(string $name): string
    {
        $k = Str::snake($name);
        $k = preg_replace('/[^a-z0-9_]/', '_', strtolower($k));
        $k = preg_replace('/_{2,}/', '_', trim($k, '_'));

        // header kosong → skip (hindari kolom "x")
        if ($k === '') return '';

        // jika diawali angka → prefix aman
        if (preg_match('/^\d/', $k)) $k = 'x_'.$k;

        return $k;
    }

    /** Tambahkan kolom baru bila belum ada */
    private function ensureColumns(array $defs): array
    {
        $added = [];
        foreach ($defs as $d) {
            $col = $this->normalize($d['name'] ?? '');
            $type = $d['type'] ?? 'string';
            $nullable = (bool)($d['nullable'] ?? true);

            if ($col === '' || !isset(self::TYPE_MAP[$type])) continue;
            if (in_array($col, $this->std, true)) continue; // jangan tumpang tindih kolom standar
            if (Schema::hasColumn($this->table, $col)) continue;

            Schema::table($this->table, function (Blueprint $table) use ($col, $type, $nullable) {
                $method = self::TYPE_MAP[$type];
                $colDef = $table->{$method}($col);
                if ($method === 'string') $colDef->nullable();
                if ($nullable && method_exists($colDef, 'nullable')) $colDef->nullable();
            });

            $added[] = $col;
        }
        return $added;
    }

    public function addColumn(Request $request)
    {
        $data = $request->validate([
            'name'     => ['required','string','max:64','regex:/^[A-Za-z][A-Za-z0-9_]*$/'],
            'type'     => ['required','in:string,text,integer,boolean,date,datetime'],
            'nullable' => ['nullable','boolean'],
        ]);

        $this->ensureColumns([[
            'name'     => $data['name'],
            'type'     => $data['type'],
            'nullable' => (bool)($data['nullable'] ?? true),
        ]]);

        return back()->with('success', 'Kolom baru berhasil ditambahkan.');
    }

    public function renameColumn(Request $request)
{
    $data = $request->validate([
        'from' => ['required','string','max:64'],
        'to'   => ['required','string','max:64','different:from','regex:/^[A-Za-z][A-Za-z0-9_]*$/'],
    ]);

    $from = $this->normalize($data['from']);
    $to   = $this->normalize($data['to']);

    if ($from === '' || $to === '') {
        return back()->with('error','Nama kolom tidak valid.');
    }

    // lindungi kolom standar
    $protected = $this->std ?? [];
    if (in_array($from, $protected, true)) {
        return back()->with('error','Tidak boleh mengubah nama kolom standar.');
    }

    if (!Schema::hasColumn($this->table, $from)) {
        return back()->with('error',"Kolom '$from' tidak ditemukan.");
    }

    if (Schema::hasColumn($this->table, $to)) {
        return back()->with('error',"Nama tujuan '$to' sudah dipakai.");
    }

    // Renaming butuh doctrine/dbal
    // composer require doctrine/dbal
    Schema::table($this->table, function (Blueprint $table) use ($from, $to) {
        $table->renameColumn($from, $to);
    });

    return back()->with('success',"Kolom '$from' berhasil diubah menjadi '$to'.");
}

/** DROP kolom dinamis */
public function dropColumn(Request $request)
{
    $data = $request->validate([
        'name' => ['required','string','max:64'],
    ]);

    $col = $this->normalize($data['name']);
    if ($col === '') return back()->with('error','Nama kolom tidak valid.');

    // lindungi kolom standar
    $protected = $this->std ?? [];
    if (in_array($col, $protected, true)) {
        return back()->with('error','Tidak boleh menghapus kolom standar.');
    }

    if (!Schema::hasColumn($this->table, $col)) {
        return back()->with('error',"Kolom '$col' tidak ditemukan.");
    }

    Schema::table($this->table, function (Blueprint $table) use ($col) {
        $table->dropColumn($col);
    });

    return back()->with('success',"Kolom '$col' berhasil dihapus.");
}

    /** Kolom ekstra (hasil +Kolom) */
    private function extraColumns(): array
    {
        $all = Schema::getColumnListing($this->table);
        return array_values(array_diff($all, $this->std));
    }

    // ================== LIST ==================
    public function index(Request $request)
    {
        // Header kolom bawaan untuk table head (view tetap aman kalau sebagian tidak ada)
        $columns = [
            $this->pk          => 'ID Printer',
            'unit_kerja'       => 'Unit Kerja',
            'user'             => 'User',
            'ruang'            => 'Ruang',
            'jenis_printer'    => 'Jenis',
            'merk'             => 'Merk',
            'tipe'             => 'Tipe',
            'scanner'          => 'Scanner',
            'tinta'            => 'Tinta',
            'status_warna'     => 'Status Warna',
            'kondisi'          => 'Kondisi',
            'tahun_pembelian'  => 'Tahun',
        ];

        $q     = trim((string) $request->query('q', ''));
        $jenis = trim((string) $request->query('jenis', ''));
        $warna = trim((string) $request->query('warna', ''));
        $merk  = trim((string) $request->query('merk',  ''));

        $base = AssetPrinter::query();

        // Pencarian bebas ke seluruh kolom (kecuali timestamps)
        if ($q !== '') {
            $cols = array_values(array_diff(Schema::getColumnListing($this->table), ['created_at','updated_at']));
            $like = "%{$q}%";
            $base->where(function($w) use ($cols, $like) {
                foreach ($cols as $c) $w->orWhere($c,'like',$like);
            });
        }

        // Filter aman: hanya diterapkan jika kolomnya ada
        if ($jenis !== '' && Schema::hasColumn($this->table,'jenis_printer')) {
            $base->where('jenis_printer',$jenis);
        }
        if ($warna !== '' && Schema::hasColumn($this->table,'status_warna')) {
            $base->where('status_warna',$warna);
        }
        if ($merk !== '' && Schema::hasColumn($this->table,'merk')) {
            $base->where('merk',$merk);
        }

        $items = $base->orderBy($this->pk)->paginate(12)->appends($request->query());

        // Opsi filter (distinct)
        $jenisOptions = Schema::hasColumn($this->table,'jenis_printer')
            ? AssetPrinter::whereNotNull('jenis_printer')->where('jenis_printer','<>','')->distinct()->orderBy('jenis_printer')->pluck('jenis_printer')
            : collect();
        $warnaOptions = Schema::hasColumn($this->table,'status_warna')
            ? AssetPrinter::whereNotNull('status_warna')->where('status_warna','<>','')->distinct()->orderBy('status_warna')->pluck('status_warna')
            : collect();
        $merkOptions = Schema::hasColumn($this->table,'merk')
            ? AssetPrinter::whereNotNull('merk')->where('merk','<>','')->distinct()->orderBy('merk')->pluck('merk')
            : collect();

        $extraCols = $this->extraColumns();

        return view('inventory.printer.index', compact(
            'items','columns','q','jenis','warna','merk','jenisOptions','warnaOptions','merkOptions','extraCols'
        ));
    }

    // ================== FORM ==================
    public function create()
    {
        $columns = Schema::getColumnListing($this->table);
        $skip = ['created_at','updated_at'];
        $fields = [];
        foreach ($columns as $col) {
            if (in_array($col,$skip)) continue;
            $fields[$col] = ucwords(str_replace('_',' ',$col));
        }

        return view('inventory.printer.form', [
            'mode'=>'create',
            'fields'=>$fields,
            'data'=>new AssetPrinter()
        ]);
    }

    public function store(Request $request)
    {
        $columns = Schema::getColumnListing($this->table);
        $skip = ['created_at','updated_at'];
        $writable = array_values(array_diff($columns,$skip));

        $request->validate([
            $this->pk => 'required|string|unique:'.$this->table.','.$this->pk,
            'tahun_pembelian' => 'nullable|integer',
        ]);

        $input = $request->only($writable);
        AssetPrinter::create($input);

        return redirect()->route('inventory.printer.index')->with('success','Aset Printer berhasil ditambahkan.');
    }

    public function edit(AssetPrinter $printer)
    {
        $columns = Schema::getColumnListing($this->table);
        $skip = ['created_at','updated_at'];
        $fields = [];
        foreach ($columns as $col) {
            if (in_array($col,$skip)) continue;
            $fields[$col] = ucwords(str_replace('_',' ',$col));
        }

        return view('inventory.printer.form', [
            'mode'=>'edit',
            'fields'=>$fields,
            'data'=>$printer
        ]);
    }

    public function update(Request $request, AssetPrinter $printer)
    {
        $columns = Schema::getColumnListing($this->table);
        $skip = ['created_at','updated_at'];
        $writable = array_values(array_diff($columns,$skip));

        $request->validate([
            $this->pk => 'required|string|unique:'.$this->table.','.$this->pk.','.$printer->{$this->pk}.','.$this->pk,
            'tahun_pembelian' => 'nullable|integer',
        ]);

        $input  = $request->only($writable);
        $before = $printer->only(array_keys($input));

        $printer->fill($input);
        $dirty = $printer->getDirty();
        $printer->save();

        if (!empty($dirty)) {
            $changes = [];
            foreach ($dirty as $k => $newVal) {
                $changes[$k] = ['from' => $before[$k] ?? null, 'to' => $newVal];
            }
            $userName = auth()->user()->name ?? 'System';
            AssetHistory::create([
                'asset_type'   => 'printer',
                'asset_id'     => $printer->{$this->pk},
                'action'       => 'update',
                'changes_json' => $changes,
                'note'         => $request->input('catatan_histori'),
                'edited_by'    => $userName,
                'created_at'   => now('Asia/Jakarta'),
            ]);
        }

        return redirect()->route('inventory.printer.index')->with('success','Aset Printer berhasil diperbarui.');
    }

    public function destroy(AssetPrinter $printer)
    {
        
        $userName = auth()->user()->name ?? 'System';
        AssetHistory::create([
        'asset_type'   => 'printer',
        'asset_id'     => $printer->{$this->pk},
        'action'       => 'delete',
        'changes_json' => null,
        'note'         => null,
        'edited_by'    => $userName, // 👈 ditambah
        'created_at'   => now('Asia/Jakarta'),
    ]);$printer->delete();
        return redirect()->route('inventory.printer.index')->with('success','Aset Printer berhasil dihapus.');
    }

    public function show(AssetPrinter $printer)
    {
        return view('inventory.printer._detail', ['data' => $printer]);
    }

    // ================== NEW: IMPORT CSV ==================

    /** Form import CSV */
    public function importForm()
    {
        return view('inventory.printer.import');
    }

    /** Download template CSV mengikuti kolom tabel & langsung attachment */
    public function downloadTemplate()
    {
        // Header sesuai tabel (tanpa created_at/updated_at)
        $headers = [
            'id_printer','unit_kerja','user','jabatan','ruang',
            'jenis_printer','merk','tipe','scanner','tinta',
            'status_warna','kondisi','tahun_pembelian','keterangan_tambahan'
        ];

        // 1 baris contoh
        $sample = [
            'PR001','BAAK','Budi','Staff','R101',
            'Inkjet','Epson','L3110','Ya','Hitam',
            'Warna','Baik','2021','—'
        ];

        // Buat CSV aman (quote jika perlu)
        $toCsvLine = function(array $fields): string {
            $escaped = array_map(function($v) {
                $v = (string)$v;
                if (str_contains($v, '"')) $v = str_replace('"', '""', $v);
                $needsQuote = strpbrk($v, ",\n\r\t\"") !== false;
                return $needsQuote ? "\"$v\"" : $v;
            }, $fields);
            return implode(',', $escaped)."\n";
        };

        $csv  = $toCsvLine($headers);
        $csv .= $toCsvLine($sample);

        return response()->make($csv, 200, [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="template_asset_printer.csv"',
            'Cache-Control'       => 'no-store, no-cache, must-revalidate, max-age=0',
        ]);
    }

    /**
     * Proses import CSV ke tabel asset_printer
     * - mode: upsert | insert_only
     * - auto_add_columns: jika header belum ada di DB, otomatis dibuat (STRING nullable)
     */
    public function importStore(Request $request)
    {
        $data = $request->validate([
            'csv' => ['required','file','mimetypes:text/plain,text/csv,application/vnd.ms-excel','max:5120'], // 5MB
            'mode' => ['required','in:upsert,insert_only'],
            'auto_add_columns' => ['nullable','boolean'],
        ]);

        $allowAutoCol = (bool)($data['auto_add_columns'] ?? false);
        $mode = $data['mode'];

        $file = $request->file('csv');
        $path = $file?->getRealPath();
        if (! $path) {
            return back()->with('error','File tidak terbaca.');
        }

        $fh = fopen($path, 'r');
        if (! $fh) {
            return back()->with('error','Gagal membuka file CSV.');
        }

        // Deteksi delimiter sederhana dari baris pertama
        $firstLine = fgets($fh) ?: '';
        $delimiter = (substr_count($firstLine, ';') > substr_count($firstLine, ',')) ? ';' : ',';
        rewind($fh);

        // Baca header
        $headers = fgetcsv($fh, 0, $delimiter);
        if (!$headers || count($headers) === 0) {
            fclose($fh);
            return back()->with('error','Header CSV tidak ditemukan.');
        }

        // Normalisasi header & simpan index yang valid (tidak kosong)
        $keepIdx = [];
        $normHeaders = [];
        foreach ($headers as $i => $h) {
            $norm = $this->normalize((string)$h);
            if ($norm !== '') {
                $keepIdx[] = $i;
                $normHeaders[] = $norm;
            }
        }

        if (count($normHeaders) === 0) {
            fclose($fh);
            return back()->with('error','Semua header kosong/tidak valid setelah normalisasi.');
        }

        // Cek kolom yang belum ada
        $existingCols = Schema::getColumnListing($this->table);
        $unknown = array_values(array_diff($normHeaders, $existingCols));

        if (!empty($unknown)) {
            if ($allowAutoCol) {
                $defs = array_map(fn($c) => ['name'=>$c,'type'=>'string','nullable'=>true], $unknown);
                $this->ensureColumns($defs);
                $existingCols = Schema::getColumnListing($this->table);
            } else {
                fclose($fh);
                return back()->with('error',
                    'CSV mengandung kolom yang tidak dikenal: '.implode(', ',$unknown).
                    '. Centang "Tambah kolom baru otomatis" atau perbaiki header CSV.'
                );
            }
        }

        // Kolom yang boleh diisi (hindari created_at/updated_at)
        $skip = ['created_at','updated_at'];
        $writable = array_values(array_diff($existingCols, $skip));

        $inserted = 0;
        $updated  = 0;
        $skipped  = 0;
        $errors   = [];
        $rowNum   = 1; // header = baris 1

        DB::beginTransaction();
        try {
            while (($row = fgetcsv($fh, 0, $delimiter)) !== false) {
                $rowNum++;

                // Lewati baris kosong total
                if (count(array_filter($row, fn($v)=>trim((string)$v)!=='')) === 0) {
                    continue;
                }

                // Ambil hanya nilai pada index header valid
                $rowKept = [];
                foreach ($keepIdx as $j => $idx) {
                    $rowKept[$j] = $row[$idx] ?? null;
                }

                // Pad jika kurang
                if (count($rowKept) < count($normHeaders)) {
                    $rowKept = array_pad($rowKept, count($normHeaders), null);
                }

                // Gabungkan header->value
                $assoc = array_combine($normHeaders, $rowKept);

                // Wajib id_printer
                $id = trim((string)($assoc[$this->pk] ?? ''));
                if ($id === '') {
                    $skipped++;
                    $errors[] = "Baris $rowNum: kolom '{$this->pk}' kosong — dilewati.";
                    continue;
                }

                // Casting ringan
                if (isset($assoc['tahun_pembelian'])) {
                    $tp = trim((string)$assoc['tahun_pembelian']);
                    $assoc['tahun_pembelian'] = ($tp === '' ? null : (int)$tp);
                }

                // Bersihkan string kosong -> null
                foreach ($assoc as $k => $v) {
                    if (is_string($v)) {
                        $v = trim($v);
                        $assoc[$k] = ($v === '') ? null : $v;
                    }
                }

                // Filter hanya kolom yang ada di DB
                $payload = array_intersect_key($assoc, array_flip($writable));

                // Pastikan primary key ada
                $payload[$this->pk] = $id;

                // INSERT / UPSERT
                if ($mode === 'upsert') {
                    $exists = AssetPrinter::where($this->pk,$id)->exists();
                    AssetPrinter::updateOrCreate(
                        [$this->pk => $id],
                        $payload
                    );
                    $exists ? $updated++ : $inserted++;
                } else {
                    // insert only — skip jika sudah ada
                    $exists = AssetPrinter::where($this->pk,$id)->exists();
                    if ($exists) {
                        $skipped++;
                        $errors[] = "Baris $rowNum: {$this->pk} '$id' sudah ada — dilewati (mode insert_only).";
                        continue;
                    }
                    AssetPrinter::create($payload);
                    $inserted++;
                }
            }

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            fclose($fh);
            return back()->with('error', 'Gagal import: '.$e->getMessage());
        }

        fclose($fh);

        // Ringkasan
        $msg = "Import selesai. Inserted: $inserted, Updated: $updated, Skipped: $skipped.";
        if (!empty($errors)) {
            $preview = implode("\n", array_slice($errors, 0, 10));
            $msg .= "\nCatatan:\n".$preview.(count($errors)>10 ? "\n(+".(count($errors)-10)." baris lagi)" : '');
        }

        return redirect()->route('inventory.printer.index')->with('success', nl2br(e($msg)));
    }
}
