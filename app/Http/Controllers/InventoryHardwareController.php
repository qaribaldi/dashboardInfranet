<?php

namespace App\Http\Controllers;

use App\Models\InventoryHardware;
use App\Models\AssetPc;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class InventoryHardwareController extends Controller
{
    private string $table = 'inventory_hardware';
    private string $pk    = 'id_hardware';

    // Kolom standar
    private array $std = [
        'id_hardware','jenis_hardware','storage_type','tanggal_pembelian','vendor',
        'jumlah_stock','status','tanggal_digunakan','id_pc', // kolom lama tetap dilindungi walau pivot dipakai
        'created_at','updated_at',
    ];

    // Jenis hardware yang valid
    private const JENIS = [
        'processor','ram','storage','vga','monitor','motherboard',
        'fan_processor','network_adapter','power_supply','keyboard','mouse',
    ];

    // Opsi storage_type untuk jenis storage
    private const STORAGE_TYPES = ['ssd','hdd'];

    private const TYPE_MAP = [
        'string'   => 'string',
        'text'     => 'text',
        'integer'  => 'integer',
        'boolean'  => 'boolean',
        'date'     => 'date',
        'datetime' => 'dateTime',
    ];

    /** Ambil daftar jenis unik dari DB untuk dropdown/filter */
    private function getJenisList(): array
    {
        return DB::table($this->table)
            ->whereNotNull('jenis_hardware')
            ->where('jenis_hardware','<>','')
            ->distinct()
            ->orderBy('jenis_hardware')
            ->pluck('jenis_hardware')
            ->all();
    }

    /** Gabungkan opsi default + unik dari DB, tanpa duplikat */
private function jenisOptions(): array
{
    $fromDb = DB::table($this->table)
        ->whereNotNull('jenis_hardware')
        ->where('jenis_hardware','<>','')
        ->distinct()
        ->orderBy('jenis_hardware')
        ->pluck('jenis_hardware')
        ->all();

    // self::JENIS = daftar default bawaan (opsional)
    $all = array_merge(self::JENIS, $fromDb);

    // unik + urut alfabet
    $all = array_values(array_unique($all));
    sort($all, SORT_STRING);

    return $all;
}


    /** Normalisasi nama kolom */
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
            if (in_array($col, $this->std, true)) continue;
            if (Schema::hasColumn($this->table, $col)) continue;

            Schema::table($this->table, function (Blueprint $table) use ($col, $type, $nullable) {
                $method = self::TYPE_MAP[$type];
                $colDef = $table->{$method}($col);

                if (in_array($type, ['date','datetime'], true)) {
                    $colDef->nullable();
                } else {
                    if ($nullable && method_exists($colDef, 'nullable')) $colDef->nullable();
                }
            });

            $added[] = $col;
        }
        return $added;
    }

    /** + Kolom (admin) */
    public function addColumn(Request $request)
    {
        $data = $request->validate([
            'name'     => ['required','string','max:64'],
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

        if ($from === '' || $to === '') return back()->with('error','Nama kolom tidak valid.');

        if (in_array($from, $this->std, true)) {
            return back()->with('error','Tidak boleh mengubah nama kolom standar.');
        }

        if (!Schema::hasColumn($this->table, $from)) {
            return back()->with('error',"Kolom '$from' tidak ditemukan.");
        }
        if (Schema::hasColumn($this->table, $to)) {
            return back()->with('error',"Nama tujuan '$to' sudah dipakai.");
        }

        Schema::table($this->table, function (Blueprint $table) use ($from, $to) {
            $table->renameColumn($from, $to);
        });

        return back()->with('success',"Kolom '$from' berhasil diubah menjadi '$to'.");
    }

    /** DROP kolom dinamis */
    public function dropColumn(Request $request)
    {
        $data = $request->validate(['name' => ['required','string','max:64']]);
        $col = $this->normalize($data['name']);
        if ($col === '') return back()->with('error','Nama kolom tidak valid.');

        if (in_array($col, $this->std, true)) {
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

    // ================= INDEX =================
    public function index(Request $req)
    {
        $jenis = $req->query('jenis');
        $storageType = $req->query('storage_type');
        $q = $req->query('q');

        $base = InventoryHardware::query()->with('pcs:id_pc'); // eager load pcs

        if ($q) {
            $cols = array_values(array_diff(
                \Schema::getColumnListing('inventory_hardware'), ['created_at','updated_at']
            ));
            $base->where(function($w) use($cols, $q){
                foreach ($cols as $c) $w->orWhere($c, 'like', "%{$q}%");
            });
        }

        if ($jenis) {
            $base->where('jenis_hardware', $jenis);
            if ($jenis === 'storage' && $storageType) {
                $base->where('storage_type', $storageType);
            }
        }

        $items = $base->with(['pcs' => fn($q) => $q->select('asset_pc.id_pc')])->orderBy('id_hardware')->paginate(12)->appends($req->query());
        $jenisList = $this->getJenisList();
        return view('inventory.hardware.index', compact('items','jenisList'));
    }

    private function columnKinds(string $table): array
    {
        $cols = \Schema::getColumnListing($table);
        $dateCols = [];
        $datetimeCols = [];

        try {
            $sm = \DB::connection()->getDoctrineSchemaManager();
            $dt = $sm->listTableDetails($table);
            foreach ($cols as $c) {
                $t = $dt->getColumn($c)->getType()->getName();
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

    // ================= FORM =================
    public function create()
{
    $columns = Schema::getColumnListing($this->table);
    $skip = ['created_at','updated_at','specs'];
    $fields = [];
    foreach ($columns as $col) {
        if (in_array($col,$skip)) continue;
        $fields[$col] = ucwords(str_replace('_',' ',$col));
    }

    $statusOptions = config('inventory.status_options');
    $pcIds = AssetPc::orderBy('id_pc')->pluck('id_pc');
    $kinds = $this->columnKinds($this->table);

    return view('inventory.hardware.form', [
        'mode'          => 'create',
        'fields'        => $fields,
        'data'          => new InventoryHardware(),
        'jenisList'     => $this->jenisOptions(),   // ← ganti ini
        'storageTypes'  => self::STORAGE_TYPES,
        'statusOptions' => $statusOptions,
        'pcIds'         => $pcIds,
        'selectedPcs'   => [],
    ] + $kinds);
}


    public function store(Request $req)
{
    // Ambil nilai dari select ATAU manual
    $jenisEfektif = trim((string)($req->input('jenis_hardware_manual') ?: $req->input('jenis_hardware_select')));
    if ($jenisEfektif === '') {
        return back()->withErrors(['jenis_hardware_select' => 'Pilih dari dropdown atau isi manual.'])->withInput();
    }

    // Merge supaya validator melihat field 'jenis_hardware'
    $req->merge(['jenis_hardware' => $jenisEfektif]);

    $data = $req->validate([
        'id_hardware'        => 'required|string|unique:inventory_hardware,id_hardware',
        'jenis_hardware'     => 'required|string',
        'tanggal_pembelian'  => 'nullable|date',
        'vendor'             => 'nullable|string',
        'jumlah_stock'       => 'required|integer|min:0',
        'status'             => 'nullable|string|in:In use,In store,Service,available',
        // kalau mau dipaksa wajib saat jenis=storage, ganti ke: 'required_if:jenis_hardware,storage|in:ssd,hdd'
        'storage_type'       => 'nullable|in:ssd,hdd',
        'pcs'                => 'nullable|array',
        'pcs.*'              => 'string|max:5|distinct|exists:asset_pc,id_pc',
        'tanggal_digunakan'  => 'nullable|date',
    ]);

    if ($data['jenis_hardware'] !== 'storage') {
        $data['storage_type'] = null;
    }

    DB::transaction(function () use ($data) {
        $selectedPcs = array_values(array_unique($data['pcs'] ?? []));
        $nSelected   = count($selectedPcs);

        if ($nSelected > 0 && $data['jumlah_stock'] < $nSelected) {
            abort(422, "Stok tidak cukup. Memilih {$nSelected} PC membutuhkan stok minimal {$nSelected}.");
        }

        $hw = InventoryHardware::create([
            'id_hardware'       => $data['id_hardware'],
            'jenis_hardware'    => $data['jenis_hardware'],
            'tanggal_pembelian' => $data['tanggal_pembelian'] ?? null,
            'vendor'            => $data['vendor'] ?? null,
            'jumlah_stock'      => $data['jumlah_stock'] - $nSelected,
            'status'            => $data['status'] ?? 'available',
            'storage_type'      => $data['storage_type'] ?? null,
        ]);

        if ($nSelected > 0) {
            $tanggal = $data['tanggal_digunakan'] ?? null;
            $attach  = [];
            foreach ($selectedPcs as $idPc) {
                $attach[$idPc] = ['tanggal_digunakan' => $tanggal];
            }
            $hw->pcs()->attach($attach);
        }
    });

    return redirect()->route('inventory.hardware.index')->with('success','Hardware ditambahkan.');
}

   public function edit(InventoryHardware $hardware)
{
    $columns = Schema::getColumnListing($this->table);
    $skip = ['created_at','updated_at','specs'];
    $fields = [];
    foreach ($columns as $col) {
        if (in_array($col,$skip)) continue;
        $fields[$col] = ucwords(str_replace('_',' ',$col));
    }

    $statusOptions = config('inventory.status_options');
    $pcIds = AssetPc::orderBy('id_pc')->pluck('id_pc');
    $kinds = $this->columnKinds($this->table);

    $selectedPcs = $hardware->pcs()->pluck('asset_pc.id_pc')->all();

    return view('inventory.hardware.form', [
        'mode'          => 'edit',
        'fields'        => $fields,
        'data'          => $hardware->load('pcs'),
        'jenisList'     => $this->jenisOptions(),   // ← ganti ini
        'storageTypes'  => self::STORAGE_TYPES,
        'statusOptions' => $statusOptions,
        'pcIds'         => $pcIds,
        'selectedPcs'   => $selectedPcs,
    ] + $kinds);
}

    public function update(Request $req, InventoryHardware $hardware)
{
    $jenisEfektif = trim((string)($req->input('jenis_hardware_manual') ?: $req->input('jenis_hardware_select')));
    if ($jenisEfektif === '') {
        return back()->withErrors(['jenis_hardware_select' => 'Pilih dari dropdown atau isi manual.'])->withInput();
    }

    // Merge ke 'jenis_hardware' agar validasi & logika di bawah konsisten
    $req->merge(['jenis_hardware' => $jenisEfektif]);

    $data = $req->validate([
        'id_hardware'        => 'required|string|unique:inventory_hardware,id_hardware,'.$hardware->id_hardware.',id_hardware',
        'jenis_hardware'     => 'required|string',
        'tanggal_pembelian'  => 'nullable|date',
        'vendor'             => 'nullable|string',
        'status'             => 'nullable|string|in:In use,In store,Service,available',
        // kalau mau wajib saat storage: 'required_if:jenis_hardware,storage|in:ssd,hdd'
        'storage_type'       => 'nullable|in:ssd,hdd',
        'pcs'                => 'nullable|array',
        'pcs.*'              => 'string|max:5|distinct|exists:asset_pc,id_pc',
        'tanggal_digunakan'  => 'nullable|date',
    ]);

    if ($data['jenis_hardware'] !== 'storage') {
        $data['storage_type'] = null;
    }

    DB::transaction(function () use ($hardware, $data) {
        $old = $hardware->pcs()->pluck('asset_pc.id_pc')->toArray();
        $new = array_values(array_unique($data['pcs'] ?? []));

        $added   = array_values(array_diff($new, $old));
        $removed = array_values(array_diff($old, $new));

        $finalStock = $hardware->jumlah_stock - count($added) + count($removed);
        if ($finalStock < 0) {
            abort(422, "Stok tidak mencukupi. Penambahan alokasi membutuhkan ".count($added)." unit.");
        }

        $hardware->update([
            'id_hardware'       => $data['id_hardware'],
            'jenis_hardware'    => $data['jenis_hardware'],
            'tanggal_pembelian' => $data['tanggal_pembelian'] ?? null,
            'vendor'            => $data['vendor'] ?? null,
            'status'            => $data['status'] ?? 'available',
            'storage_type'      => $data['storage_type'] ?? null,
        ]);

        if (!empty($removed)) $hardware->pcs()->detach($removed);

        if (!empty($added)) {
            $tanggal = $data['tanggal_digunakan'] ?? null;
            $attach  = [];
            foreach ($added as $idPc) {
                $attach[$idPc] = ['tanggal_digunakan' => $tanggal];
            }
            $hardware->pcs()->attach($attach);
        }

        $hardware->update(['jumlah_stock' => $finalStock]);
    });

    return redirect()->route('inventory.hardware.index')->with('success','Hardware diperbarui.');
}


    public function destroy(InventoryHardware $hardware)
    {
        $hardware->delete();
        return redirect()->route('inventory.hardware.index')->with('success','Hardware berhasil dihapus.');
    }

    public function show(InventoryHardware $hardware)
    {
        return view('inventory.hardware._detail', ['data' => $hardware->load('pcs')]);
    }

    // ================= IMPORT CSV =================
    public function importForm()
    {
        return view('inventory.hardware.import');
    }

    public function downloadTemplate()
    {
        $headers = [
            'id_hardware','jenis_hardware','storage_type','vendor','tanggal_pembelian',
            'jumlah_stock','status','tanggal_digunakan','id_pc',
        ];

        $samples = [
            ['PR01','processor','',   'PT Sumber Jaya','2023-05-10','5','stock','',    ''],
            ['ST01','storage','ssd', 'PT Media Sejahtera','2024-01-20','12','in_use','2024-02-02','PC-001'],
        ];

        $toCsvLine = function(array $fields): string {
            $escaped = array_map(function($v) {
                $v = (string)$v;
                if (str_contains($v, '"')) $v = str_replace('"','""',$v);
                return (strpbrk($v, ",\n\r\t\"") !== false) ? "\"$v\"" : $v;
            }, $fields);
            return implode(',', $escaped)."\n";
        };

        $csv  = $toCsvLine($headers);
        foreach ($samples as $row) $csv .= $toCsvLine($row);

        return response()->make($csv, 200, [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="template_inventory_hardware.csv"',
            'Cache-Control'       => 'no-store, no-cache, must-revalidate, max-age=0',
        ]);
    }

    /**
     * Import CSV ke inventory_hardware
     * (Catatan: jalur import ini masih menulis ke kolom lama id_pc/tanggal_digunakan.
     * Jika ingin ikut pivot, perlu penyesuaian terpisah.)
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

                foreach (['tanggal_pembelian','tanggal_digunakan'] as $dk) {
                    if (isset($assoc[$dk])) {
                        $v = trim((string)$assoc[$dk]);
                        $assoc[$dk] = ($v===''? null : $v);
                    }
                }
                if (isset($assoc['jumlah_stock'])) {
                    $v = trim((string)$assoc['jumlah_stock']);
                    $assoc['jumlah_stock'] = ($v===''? null : (int)$v);
                }
                foreach ($assoc as $k=>$v) {
                    if (is_string($v)) {
                        $v = trim($v);
                        $assoc[$k] = ($v===''? null : $v);
                    }
                }

                $payload = array_intersect_key($assoc, array_flip($writable));
                $payload[$this->pk] = $id;

                if ($mode==='upsert') {
                    $exists = InventoryHardware::where($this->pk,$id)->exists();
                    InventoryHardware::updateOrCreate([$this->pk=>$id], $payload);
                    $exists ? $updated++ : $inserted++;
                } else {
                    $exists = InventoryHardware::where($this->pk,$id)->exists();
                    if ($exists) { $skipped++; $errors[]="Baris $rowNum: {$this->pk} '$id' sudah ada — dilewati (insert_only)."; continue; }
                    InventoryHardware::create($payload);
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

        return redirect()->route('inventory.hardware.index')->with('success', nl2br(e($msg)));
    }
}
