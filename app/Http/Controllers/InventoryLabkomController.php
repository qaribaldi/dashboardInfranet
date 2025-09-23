<?php

namespace App\Http\Controllers;

use App\Models\InventoryLabkom;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class InventoryLabkomController extends Controller
{
    /** Nama tabel & primary key */
    private string $table = 'inventory_labkom';
    private string $pk    = 'id_pc';

    /** Kolom standar minimum (diproteksi dari add/rename/drop) */
    private array $std = [
        'id_pc','created_at','updated_at',
    ];

    /** Kolom TETAP yang selalu ditampilkan di index (bukan kolom dinamis) */
    private array $stdShow = [
        // yang dipakai blade: processor & ram dipecah di view (brand/tipe)
        'id_pc','nama_lab','unit_kerja','user','ruang','merk',
        'processor','tipe_ram','ram_1','storage_1','operating_sistem',
        'tahun_pembelian','status',
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

    /** Helper: daftar kolom yang diproteksi (stdShow + timestamps) */
    private function protectedColumns(): array
    {
        return array_values(array_unique(array_merge(
            $this->stdShow, ['created_at','updated_at']
        )));
    }

    /** Normalisasi nama kolom ke snake_case aman */
    private function normalize(string $name): string
    {
        $name = trim($name);
        if ($name === '') return '';
        $k = Str::snake($name);
        $k = preg_replace('/[^a-z0-9_]/', '_', strtolower($k));
        $k = preg_replace('/_{2,}/', '_', trim($k, '_'));
        if ($k === '') return '';
        if (preg_match('/^\d/', $k)) $k = 'x_'.$k;
        return $k;
    }

    /** Tambahkan kolom baru jika belum ada */
    private function ensureColumns(array $defs): array
    {
        $added = [];
        foreach ($defs as $d) {
            $col      = $this->normalize($d['name'] ?? '');
            $type     = $d['type'] ?? 'string';
            $nullable = (bool)($d['nullable'] ?? true);

            if ($col === '' || !isset(self::TYPE_MAP[$type])) continue;
            if (in_array($col, $this->protectedColumns(), true)) continue; // lindungi kolom tetap
            if (Schema::hasColumn($this->table, $col)) continue;

            Schema::table($this->table, function (Blueprint $table) use ($col, $type, $nullable) {
                $method = self::TYPE_MAP[$type];
                $colDef = $table->{$method}($col);

                // bikin nullable kalau diminta (semua tipe boleh)
                if ($nullable) {
                    $colDef->nullable();
                }
            });

            $added[] = $col;
        }
        return $added;
    }

    /** ========== Kelola kolom dinamis ========== */
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
            'nullable' => $request->boolean('nullable'),
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

        if ($from === '' || $to === '') return back()->with('error','Nama kolom tidak valid.');
        if (in_array($from, $this->protectedColumns(), true)) return back()->with('error','Tidak boleh mengubah nama kolom standar.');
        if (!Schema::hasColumn($this->table, $from)) return back()->with('error',"Kolom '$from' tidak ditemukan.");
        if (Schema::hasColumn($this->table, $to))   return back()->with('error',"Nama tujuan '$to' sudah dipakai.");

        Schema::table($this->table, function (Blueprint $table) use ($from, $to) {
            $table->renameColumn($from, $to);
        });

        return back()->with('success',"Kolom '$from' berhasil diubah menjadi '$to'.");
    }

    public function dropColumn(Request $request)
    {
        $data = $request->validate(['name' => ['required','string','max:64']]);
        $col = $this->normalize($data['name']);
        if ($col === '') return back()->with('error','Nama kolom tidak valid.');
        if (in_array($col, $this->protectedColumns(), true)) return back()->with('error','Tidak boleh menghapus kolom standar.');
        if (!Schema::hasColumn($this->table, $col)) return back()->with('error',"Kolom '$col' tidak ditemukan.");

        Schema::table($this->table, function (Blueprint $table) use ($col) {
            $table->dropColumn($col);
        });

        return back()->with('success',"Kolom '$col' berhasil dihapus.");
    }

    /** Ambil kolom dinamis (selain kolom tetap + timestamps) */
    private function extraColumns(): array
    {
        $all = Schema::getColumnListing($this->table);
        $protected = $this->protectedColumns();
        return array_values(array_diff($all, $protected));
    }

    /** Deteksi kolom date & datetime (untuk auto input type di form) */
    private function columnKinds(string $table): array
    {
        $cols = \Schema::getColumnListing($table);
        $dateCols = [];
        $datetimeCols = [];

        try {
            $sm = \DB::connection()->getDoctrineSchemaManager();
            $dt = $sm->listTableDetails($table);
            foreach ($cols as $c) {
                $t = $dt->getColumn($c)->getType()->getName(); // string/date/datetime/...
                if ($t === 'date') $dateCols[] = $c;
                if (in_array($t, ['datetime','datetimetz'])) $datetimeCols[] = $c;
            }
        } catch (\Throwable $e) {
            foreach ($cols as $c) {
                if (preg_match('/(^tanggal_|_date$)/', $c)) $dateCols[] = $c;
                if (preg_match('/(_at$|_datetime$|^waktu_)/', $c)) $datetimeCols[] = $c;
            }
        }

        $dateCols     = array_values(array_diff($dateCols, ['created_at','updated_at']));
        $datetimeCols = array_values(array_diff($datetimeCols, ['created_at','updated_at']));

        return compact('dateCols','datetimeCols');
    }

    /** ========== INDEX ========== */
    public function index(Request $req)
    {
        $q   = trim((string)$req->query('q', ''));
        $lab = trim((string)$req->query('lab', ''));

        $base = InventoryLabkom::query();

        // Pencarian bebas
        if ($q !== '') {
            $like = "%{$q}%";
            $cols = array_values(array_diff(
                \Schema::getColumnListing($this->table),
                ['created_at','updated_at']
            ));
            $base->where(function($w) use($cols, $like){
                foreach ($cols as $c) $w->orWhere($c, 'like', $like);
            });
        }

        // Filter Nama Lab
        if ($lab !== '') $base->where('nama_lab', $lab);

        $items = $base->orderBy($this->pk)
                      ->paginate(12)
                      ->appends($req->query());

        // Dropdown Nama Lab
        $labList = DB::table($this->table)
            ->select('nama_lab')
            ->whereNotNull('nama_lab')
            ->where('nama_lab','<>','')
            ->distinct()
            ->orderBy('nama_lab')
            ->pluck('nama_lab')
            ->all();

        // Kolom dinamis untuk ditampilkan di tabel
        $extraCols = $this->extraColumns();

        return view('inventory.labkom.index', [
            'items'     => $items,
            'q'         => $q,
            'lab'       => $lab,
            'labList'   => $labList,
            'extraCols' => $extraCols,
        ]);
    }

    /** ========== FORM ========== */
    public function create()
    {
        $columns = Schema::getColumnListing($this->table);
        $skip = ['created_at','updated_at'];
        $fields = [];
        foreach ($columns as $col) {
            if (in_array($col,$skip)) continue;
            $fields[$col] = ucwords(str_replace('_',' ',$col));
        }

        $kinds = $this->columnKinds($this->table);

        return view('inventory.labkom.form', [
            'mode'   => 'create',
            'fields' => $fields,
            'data'   => new InventoryLabkom(),
        ] + $kinds);
    }

    public function store(Request $req)
    {
        $req->validate([
            $this->pk => 'required|string|unique:'.$this->table.','.$this->pk,
        ]);

        $existing = Schema::getColumnListing($this->table);
        $payload  = $req->only($existing);
        foreach ($payload as $k=>$v) {
            if (is_string($v)) $payload[$k] = (trim($v)===''? null : trim($v));
        }

        InventoryLabkom::create($payload);
        return redirect()->route('inventory.labkom.index')->with('success','Labkom ditambahkan.');
    }

    public function edit(InventoryLabkom $labkom)
    {
        $columns = Schema::getColumnListing($this->table);
        $skip = ['created_at','updated_at'];
        $fields = [];
        foreach ($columns as $col) {
            if (in_array($col,$skip)) continue;
            $fields[$col] = ucwords(str_replace('_',' ',$col));
        }

        $kinds = $this->columnKinds($this->table);

        return view('inventory.labkom.form', [
            'mode'   => 'edit',
            'fields' => $fields,
            'data'   => $labkom,
        ] + $kinds);
    }

    public function update(Request $req, InventoryLabkom $labkom)
    {
        $req->validate([
            $this->pk => 'required|string|unique:'.$this->table.','.$this->pk.','.$labkom->{$this->pk}.','.$this->pk,
        ]);

        $existing = Schema::getColumnListing($this->table);
        $payload  = $req->only($existing);
        foreach ($payload as $k=>$v) {
            if (is_string($v)) $payload[$k] = (trim($v)===''? null : trim($v));
        }

        $labkom->update($payload);
        return redirect()->route('inventory.labkom.index')->with('success','Labkom diperbarui.');
    }

    public function destroy(InventoryLabkom $labkom)
    {
        $labkom->delete();
        return redirect()->route('inventory.labkom.index')->with('success','Labkom dihapus.');
    }

    public function show(InventoryLabkom $labkom)
    {
        return view('inventory.labkom._detail', ['data' => $labkom]);
    }

    /** ========== IMPORT CSV ========== */
    public function importForm()
    {
        return view('inventory.labkom.import');
    }

    public function downloadTemplate()
    {
         $headers = [
            'id_pc','nama_lab','unit_kerja','user','jabatan','ruang','tipe_asset','merk',
            'processor','socket_processor','motherboard','jumlah_slot_ram',
            'total_kapasitas_ram','tipe_ram','ram_1','ram_2',
            'tipe_storage_1','storage_1','tipe_storage_2','storage_2','tipe_storage_3','storage_3',
            'vga','optical_drive','network_adapter','power_suply',
            'operating_sistem','monitor','keyboard','mouse','tahun_pembelian'
        ];

        // 1 baris contoh
        $sample = [
            'PC-001','Lab 0','BAAK','Budi','Staff','R101','Aset Tetap','HP',
            'Intel Core i3-3240','LGA1155','Asus H61M','2',
            '8GB','DDR3','4GB Samsung','4GB Samsung',
            'SSD','240GB SSD','HDD','500GB HDD','-','-',
            'GTX 750','DVD-RW','Realtek','Corsair 450W',
            'Windows 10','Samsung 19"','Logitech','Logitech','2019'
        ];

        // Buat CSV
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
            'Content-Disposition' => 'attachment; filename="template_asset_labkom.csv"',
            'Cache-Control'       => 'no-store, no-cache, must-revalidate, max-age=0',
        ]);
    }

    /** Import CSV ke inventory_labkom */
    public function importStore(Request $request)
    {
        $data = $request->validate([
            'csv' => ['required','file','mimetypes:text/plain,text/csv,application/vnd.ms-excel','max:5120'],
            'mode' => ['required','in:upsert,insert_only'],
            'auto_add_columns' => ['nullable','boolean'],
        ]);

        $allowAutoCol = (bool)($data['auto_add_columns'] ?? false);
        $mode = $data['mode'];

        $file = $request->file('csv');
        $path = $file?->getRealPath();
        if (!$path) return back()->with('error','File tidak terbaca.');

        $fh = fopen($path, 'r');
        if (!$fh) return back()->with('error','Gagal membuka file CSV.');

        $first = fgets($fh) ?: '';
        $delim = (substr_count($first,';') > substr_count($first,',')) ? ';' : ',';
        rewind($fh);

        $raw = fgetcsv($fh, 0, $delim);
        if (!$raw || !count($raw)) { fclose($fh); return back()->with('error','Header CSV tidak ditemukan.'); }

        $norm = array_map(fn($h)=>$this->normalize((string)$h), $raw);
        $norm = array_values(array_filter($norm, fn($h)=>$h!==''));
        if (!count($norm)) { fclose($fh); return back()->with('error','Semua header kosong/tidak valid.'); }

        $existing = Schema::getColumnListing($this->table);
        $unknown  = array_values(array_diff($norm, $existing));
        if (!empty($unknown)) {
            if ($allowAutoCol) {
                $defs = array_map(fn($c)=>['name'=>$c,'type'=>'string','nullable'=>true], $unknown);
                $this->ensureColumns($defs);
                $existing = Schema::getColumnListing($this->table);
            } else {
                fclose($fh);
                return back()->with('error',
                    'CSV mengandung kolom yang tidak dikenal: '.implode(', ',$unknown).
                    '. Centang "Tambah kolom baru otomatis" atau perbaiki header CSV.'
                );
            }
        }

        $skip = ['created_at','updated_at'];
        $writable = array_values(array_diff($existing,$skip));

        $inserted=0; $updated=0; $skipped=0; $errors=[]; $rowNum=1;

        DB::beginTransaction();
        try {
            while (($row = fgetcsv($fh, 0, $delim)) !== false) {
                $rowNum++;
                if (count(array_filter($row, fn($v)=>trim((string)$v)!=='')) === 0) continue;

                if (count($row) > count($norm))   $row = array_slice($row, 0, count($norm));
                if (count($row) < count($norm))   $row = array_pad($row, count($norm), null);

                $assoc = array_combine($norm, $row);

                $id = trim((string)($assoc[$this->pk] ?? ''));
                if ($id==='') { $skipped++; $errors[]="Baris $rowNum: kolom '{$this->pk}' kosong — dilewati."; continue; }

                foreach ($assoc as $k=>$v) {
                    if (is_string($v)) {
                        $v = trim($v);
                        $assoc[$k] = ($v===''? null : $v);
                    }
                }

                $payload = array_intersect_key($assoc, array_flip($writable));
                $payload[$this->pk] = $id;

                if ($mode==='upsert') {
                    $exists = InventoryLabkom::where($this->pk,$id)->exists();
                    InventoryLabkom::updateOrCreate([$this->pk=>$id], $payload);
                    $exists ? $updated++ : $inserted++;
                } else {
                    $exists = InventoryLabkom::where($this->pk,$id)->exists();
                    if ($exists) { $skipped++; $errors[]="Baris $rowNum: {$this->pk} '$id' sudah ada — dilewati (insert_only)."; continue; }
                    InventoryLabkom::create($payload);
                    $inserted++;
                }
            }

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            fclose($fh);
            return back()->with('error','Gagal import: '.$e->getMessage());
        }

        fclose($fh);

        $msg = "Import selesai. Inserted: $inserted, Updated: $updated, Skipped: $skipped.";
        if (!empty($errors)) {
            $preview = implode("\n", array_slice($errors,0,10));
            $msg .= "\nCatatan:\n".$preview.(count($errors)>10 ? "\n(+".(count($errors)-10)." baris lagi)" : '');
        }

        return redirect()->route('inventory.labkom.index')->with('success', nl2br(e($msg)));
    }
}
