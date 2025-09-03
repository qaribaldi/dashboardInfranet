<?php

namespace App\Http\Controllers;

use App\Models\AssetPc;
use App\Models\AssetHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;


class AssetPcController extends Controller
{
    /** Nama tabel & primary key */
    private string $table = 'asset_pc';
    private string $pk    = 'id_pc';
    
    /** Kolom standar (termasuk timestamps) yang diproteksi */
    private array $std = [
        'id_pc','unit_kerja','user','jabatan','ruang','tipe_asset','merk',
        'processor','socket_processor','motherboard','jumlah_slot_ram',
        'total_kapasitas_ram','tipe_ram','ram_1','ram_2',
        'tipe_storage_1','storage_1','tipe_storage_2','storage_2','storage_3',
        'vga','optical_drive','network_adapter','power_suply',
        'operating_sistem','monitor','keyboard','mouse','tahun_pembelian',
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

    /** Helper: daftar kolom yang diproteksi (std + timestamps jika belum ada) */
    private function protectedColumns(): array
    {
        return array_unique(array_merge($this->std, ['created_at','updated_at']));
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

    /** Tambahkan kolom baru bila belum ada */
    private function ensureColumns(array $defs): array
    {
        // $defs: [ ['name'=>'umur_baterai','type'=>'integer','nullable'=>true], ... ]
        $added = [];
        foreach ($defs as $d) {
            $col      = $this->normalize($d['name'] ?? '');
            $type     = $d['type'] ?? 'string';
            $nullable = (bool)($d['nullable'] ?? true);

            if ($col === '' || !isset(self::TYPE_MAP[$type])) continue;
            if (in_array($col, $this->protectedColumns(), true)) continue; // jangan tumpang tindih kolom standar
            if (Schema::hasColumn($this->table, $col)) continue;

            Schema::table($this->table, function (Blueprint $table) use ($col, $type, $nullable) {
                $method = self::TYPE_MAP[$type];
                $colDef = $table->{$method}($col);
                if ($nullable && method_exists($colDef, 'nullable')) $colDef->nullable();
            });

            $added[] = $col;
        }
        return $added;
    }

    /** ========== MANAGE KOLOM DINAMIS ========== */

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

    /** Ubah nama kolom dinamis */
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

        if (in_array($from, $this->protectedColumns(), true)) {
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

    /** Hapus kolom dinamis */
    public function dropColumn(Request $request)
    {
        $data = $request->validate([
            'name' => ['required','string','max:64'],
        ]);

        $col = $this->normalize($data['name']);
        if ($col === '') return back()->with('error','Nama kolom tidak valid.');

        if (in_array($col, $this->protectedColumns(), true)) {
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

    /** Ambil semua kolom tabel lalu bedakan mana ekstra */
    private function extraColumns(): array
    {
        $all = Schema::getColumnListing($this->table);
        return array_values(array_diff($all, $this->protectedColumns()));
    }

    /** ========== LIST ========== */
    public function index(Request $request)
    {
        $columns = [
            'id_pc'               => 'ID PC',
            'unit_kerja'          => 'Unit Kerja',
            'user'                => 'User',
            'ruang'               => 'Ruang',
            'merk'                => 'Merk',
            'processor'           => 'Processor',
            'total_kapasitas_ram' => 'Total RAM',
            'storage_1'           => 'Storage 1',
            'operating_sistem'    => 'OS',
            'tahun_pembelian'     => 'Tahun',
        ];

        $q    = trim((string) $request->query('q', ''));
        $proc = trim((string) $request->query('proc', ''));
        $ram  = trim((string) $request->query('ram', ''));
        $sto  = trim((string) $request->query('sto', ''));

        $base = AssetPc::query();

        if ($q !== '') {
            $like = "%{$q}%";
            $base->where(function($w) use ($like) {
                $w->where('id_pc','like',$like)
                  ->orWhere('unit_kerja','like',$like)
                  ->orWhere('user','like',$like)
                  ->orWhere('ruang','like',$like)
                  ->orWhere('merk','like',$like)
                  ->orWhere('processor','like',$like)
                  ->orWhere('total_kapasitas_ram','like',$like)
                  ->orWhere('storage_1','like',$like)
                  ->orWhere('storage_2','like',$like)
                  ->orWhere('storage_3','like',$like)
                  ->orWhere('operating_sistem','like',$like)
                  ->orWhere('tahun_pembelian','like',$like);
            });
        }

        if ($proc !== '') $base->where('processor', $proc);
        if ($ram  !== '') $base->where('total_kapasitas_ram', $ram);
        if ($sto  !== '') {
            $base->where(function($w) use ($sto) {
                $w->where('storage_1', $sto)->orWhere('storage_2', $sto)->orWhere('storage_3', $sto);
            });
        }

        $items = $base->orderBy($this->pk)->paginate(12)->appends($request->query());

        $processors = AssetPc::whereNotNull('processor')->where('processor','<>','')
            ->distinct()->orderBy('processor')->pluck('processor');

        $rams = AssetPc::whereNotNull('total_kapasitas_ram')->where('total_kapasitas_ram','<>','')
            ->distinct()->orderBy('total_kapasitas_ram')->pluck('total_kapasitas_ram');

        $storages = collect()
            ->merge(AssetPc::whereNotNull('storage_1')->where('storage_1','<>','')->pluck('storage_1'))
            ->merge(AssetPc::whereNotNull('storage_2')->where('storage_2','<>','')->pluck('storage_2'))
            ->merge(AssetPc::whereNotNull('storage_3')->where('storage_3','<>','')->pluck('storage_3'))
            ->filter()->unique()->sort()->values();

        $extraCols = $this->extraColumns();

        return view('inventory.pc.index', compact(
            'items','columns','q','proc','ram','sto','processors','rams','storages','extraCols'
        ));
    }

    /** ========== FORM ========== */
    public function create()
    {
        $columns = Schema::getColumnListing($this->table);
        $skip    = ['created_at','updated_at']; // kolom yang tidak mau ditampilkan
        $fields  = [];
        foreach ($columns as $col) {
            if (in_array($col,$skip)) continue;
            $fields[$col] = ucwords(str_replace('_',' ',$col));
        }

        return view('inventory.pc.form', [
            'mode'  => 'create',
            'fields'=> $fields,
            'data'  => new AssetPc()
        ]);
    }

    public function store(Request $request)
    {
        $columns  = Schema::getColumnListing($this->table);
        $skip     = ['created_at','updated_at'];
        $writable = array_values(array_diff($columns,$skip));

        $request->validate([
            $this->pk           => 'required|string|unique:'.$this->table.','.$this->pk,
            'tahun_pembelian'   => 'nullable|integer',
        ]);

        $input = $request->only($writable);
        AssetPc::create($input);

        return redirect()->route('inventory.pc.index')->with('success','Aset PC berhasil ditambahkan.');
    }

    public function edit(AssetPc $pc)
    {
        $columns = Schema::getColumnListing($this->table);
        $skip    = ['created_at','updated_at'];
        $fields  = [];
        foreach ($columns as $col) {
            if (in_array($col,$skip)) continue;
            $fields[$col] = ucwords(str_replace('_',' ',$col));
        }

        return view('inventory.pc.form', [
            'mode'  => 'edit',
            'fields'=> $fields,
            'data'  => $pc
        ]);
    }

    public function update(Request $request, AssetPc $pc)
    {
        $columns  = Schema::getColumnListing($this->table);
        $skip     = ['created_at','updated_at'];
        $writable = array_values(array_diff($columns,$skip));

        $request->validate([
            $this->pk         => 'required|string|unique:'.$this->table.','.$this->pk.','.$pc->{$this->pk}.','.$this->pk,
            'tahun_pembelian' => 'nullable|integer',
        ]);

        $input  = $request->only($writable);
        $before = $pc->only(array_keys($input));

        $pc->fill($input);
        $dirty = $pc->getDirty();
        $pc->save();

        if (!empty($dirty)) {
            $changes = [];
            foreach ($dirty as $k => $newVal) {
                $changes[$k] = ['from' => $before[$k] ?? null, 'to' => $newVal];
            }

            $userName = auth()->user()->name ?? 'System';

            AssetHistory::create([
                'asset_type'   => 'pc',
                'asset_id'     => $pc->{$this->pk},
                'action'       => 'update',
                'changes_json' => $changes,
                'note'         => $request->input('catatan_histori'),
                'edited_by'    => $userName, 
                'created_at'   => now('Asia/Jakarta'),
            ]);
        }

        return redirect()->route('inventory.pc.index')->with('success','Aset PC berhasil diperbarui.');
    }

    public function destroy(AssetPc $pc)
    {

        $userName = auth()->user()->name ?? 'System';

    AssetHistory::create([
        'asset_type'   => 'pc',
        'asset_id'     => $pc->{$this->pk},
        'action'       => 'delete',
        'changes_json' => null,
        'note'         => null,
        'edited_by'    => $userName,  // 👈 ditambah
        'created_at'   => now('Asia/Jakarta'),
    ]);
        $pc->delete();
        return redirect()->route('inventory.pc.index')->with('success','Aset PC berhasil dihapus.');
    }

    public function show(AssetPc $pc)
    {
        $cols = array_values(array_diff(
            Schema::getColumnListing($this->table),
            ['created_at','updated_at']
        ));

        return view('inventory.pc._detail', [
            'data' => $pc,
            'cols' => $cols,
            'pk'   => $this->pk,
        ]);
    }

    /** ========== IMPORT CSV ========== */

    /** Form import CSV */
    public function importForm()
    {
        return view('inventory.pc.import');
    }

    /** Download template CSV mengikuti semua kolom tabel & langsung attachment */
    public function downloadTemplate()
    {
        // Header sesuai struktur tabel kamu (tanpa created_at/updated_at)
        $headers = [
            'id_pc','unit_kerja','user','jabatan','ruang','tipe_asset','merk',
            'processor','socket_processor','motherboard','jumlah_slot_ram',
            'total_kapasitas_ram','tipe_ram','ram_1','ram_2',
            'tipe_storage_1','storage_1','tipe_storage_2','storage_2','tipe_storage_3','storage_3',
            'vga','optical_drive','network_adapter','power_suply',
            'operating_sistem','monitor','keyboard','mouse','tahun_pembelian'
        ];

        // 1 baris contoh
        $sample = [
            'PC-001','BAAK','Budi','Staff','R101','Aset Tetap','HP',
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
            'Content-Disposition' => 'attachment; filename="template_asset_pc.csv"',
            'Cache-Control'       => 'no-store, no-cache, must-revalidate, max-age=0',
        ]);
    }

    /**
     * Proses import CSV ke tabel asset_pc
     * - mode: upsert | insert_only
     * - auto_add_columns: jika header belum ada di DB, otomatis dibuat (STRING nullable)
     */
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

        // Normalisasi header & simpan index header yang valid (tidak kosong)
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
        $skip     = ['created_at','updated_at'];
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

                // Ambil hanya nilai pada index header yang valid
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

                // Minimal wajib id_pc
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
                    $exists = AssetPc::where($this->pk,$id)->exists();
                    AssetPc::updateOrCreate(
                        [$this->pk => $id],
                        $payload
                    );
                    $exists ? $updated++ : $inserted++;
                } else {
                    // insert only — skip jika sudah ada
                    $exists = AssetPc::where($this->pk,$id)->exists();
                    if ($exists) {
                        $skipped++;
                        $errors[] = "Baris $rowNum: {$this->pk} '$id' sudah ada — dilewati (mode insert_only).";
                        continue;
                    }
                    AssetPc::create($payload);
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

        return redirect()->route('inventory.pc.index')->with('success', nl2br(e($msg)));
    }
}
