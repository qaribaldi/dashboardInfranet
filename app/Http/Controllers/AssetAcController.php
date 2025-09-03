<?php

namespace App\Http\Controllers;

use App\Models\AssetAc;
use App\Models\AssetHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;        
use Illuminate\Support\Facades\Storage;   
use Illuminate\Support\Facades\Auth;

class AssetAcController extends Controller
{
    private string $table = 'asset_ac';
    private string $pk    = 'id_ac';

    private array $std = [
        'id_ac','unit_kerja','user','jabatan','ruang','tipe_asset','merk',
        'spes','remote','tahun_pembelian','created_at','updated_at',
    ];

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

        // Jika kosong (mis. header CSV kosong/ekstra delimiter), kembalikan '' agar bisa di-skip
        if ($k === '') return '';

        // Jika diawali angka, beri prefix aman
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
            if (in_array($col, $this->std, true)) continue;
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
            'name'     => ['required','string','max:64','regex:/^[A-Za-z][A-Za-z0-9_]*$/'], // diperketat
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

    private function extraColumns(): array
    {
        $all = Schema::getColumnListing($this->table);
        return array_values(array_diff($all, $this->std));
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

    // =============== INDEX ===============
    public function index(Request $request)
    {
        $columns = [
            $this->pk    => 'ID AC',
            'unit_kerja' => 'Unit Kerja',
            'user'       => 'User',
            'ruang'      => 'Ruang',
            'merk'       => 'Merk',
            'spes'       => 'Merk/Series',
            'remote'     => 'Remote',
            'tahun_pembelian' => 'Tahun',
        ];

        $q      = trim((string) $request->query('q', ''));
        $spes   = trim((string) $request->query('spes', ''));
        $remote = trim((string) $request->query('remote', ''));

        $base = AssetAc::query();

        if ($q !== '') {
            $cols = array_values(array_diff(Schema::getColumnListing($this->table), ['created_at','updated_at']));
            $like = "%{$q}%";
            $base->where(function($w) use ($cols, $like) {
                foreach ($cols as $c) $w->orWhere($c,'like',$like);
            });
        }

        if ($spes !== '' && Schema::hasColumn($this->table,'spes')) {
            $base->where('spes',$spes);
        }
        if ($remote !== '' && Schema::hasColumn($this->table,'remote')) {
            $base->where('remote',$remote);
        }

        $items = $base->orderBy($this->pk)->paginate(12)->appends($request->query());

        $spesOptions = Schema::hasColumn($this->table,'spes')
            ? AssetAc::whereNotNull('spes')->where('spes','<>','')->distinct()->orderBy('spes')->pluck('spes')
            : collect();
        $remoteOptions = Schema::hasColumn($this->table,'remote')
            ? AssetAc::whereNotNull('remote')->where('remote','<>','')->distinct()->orderBy('remote')->pluck('remote')
            : collect();

        $extraCols = $this->extraColumns();

        return view('inventory.ac.index', compact(
            'items','columns','q','spes','remote','spesOptions','remoteOptions','extraCols'
        ));
    }

    // =============== FORM ===============
    public function create()
    {
        $columns = Schema::getColumnListing($this->table);
        $skip = ['created_at','updated_at'];
        $fields = [];
        foreach ($columns as $col) {
            if (in_array($col,$skip)) continue;
            $fields[$col] = ucwords(str_replace('_',' ',$col));
        }

        return view('inventory.ac.form', [
            'mode'=>'create',
            'fields'=>$fields,
            'data'=>new AssetAc()
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
        AssetAc::create($input);

        return redirect()->route('inventory.ac.index')->with('success','Aset AC berhasil ditambahkan.');
    }

    public function edit(AssetAc $ac)
    {
        $columns = Schema::getColumnListing($this->table);
        $skip = ['created_at','updated_at'];
        $fields = [];
        foreach ($columns as $col) {
            if (in_array($col,$skip)) continue;
            $fields[$col] = ucwords(str_replace('_',' ',$col));
        }

        return view('inventory.ac.form', [
            'mode'=>'edit',
            'fields'=>$fields,
            'data'=>$ac
        ]);
    }

    public function update(Request $request, AssetAc $ac)
    {
        $columns = Schema::getColumnListing($this->table);
        $skip = ['created_at','updated_at'];
        $writable = array_values(array_diff($columns,$skip));

        $request->validate([
            $this->pk => 'required|string|unique:'.$this->table.','.$this->pk.','.$ac->{$this->pk}.','.$this->pk,
            'tahun_pembelian' => 'nullable|integer',
        ]);

        $input  = $request->only($writable);
        $before = $ac->only(array_keys($input));

        $ac->fill($input);
        $dirty = $ac->getDirty();
        $ac->save();

        if (!empty($dirty)) {
            $changes = [];
            foreach ($dirty as $k => $newVal) {
                $changes[$k] = ['from' => $before[$k] ?? null, 'to' => $newVal];
            }
            $userName = auth()->user()->name ?? 'System';
            AssetHistory::create([
                'asset_type'   => 'ac',
                'asset_id'     => $ac->id_ac,
                'action'       => 'update',
                'changes_json' => $changes,
                'note'         => $request->input('catatan_histori'),
                'edited_by'    => $userName,
                'created_at'   => now('Asia/Jakarta'),
            ]);
        }

        return redirect()->route('inventory.ac.index')->with('success','Aset AC berhasil diperbarui.');
    }

    public function destroy(AssetAc $ac)
    {
        $ac->delete();
        return redirect()->route('inventory.ac.index')->with('success','Aset AC berhasil dihapus.');
    }

    public function show(AssetAc $ac)
    {
        return view('inventory.ac._detail', ['data' => $ac]);
    }

    // =============== NEW: IMPORT CSV ===============

    /** Form import CSV */
    public function importForm()
    {
        return view('inventory.ac.import');
    }

    /** Download template CSV mengikuti kolom tabel & langsung attachment */
    public function downloadTemplate()
    {
        // Header sesuai tabel (tanpa created_at/updated_at)
        $headers = [
            'id_ac','unit_kerja','ruang','tipe_asset','merk',
            'ukuran_pk','kondisi','remote','tahun_pembelian','keterangan'
        ];

        // 1 baris contoh
        $sample = [
            'AC01','Lab Komputer 1','1103','AC','LG HS-C186C4A1','2 PK','Normal',
            'Ada','2019','-'
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
            'Content-Disposition' => 'attachment; filename="template_asset_ac.csv"',
            'Cache-Control'       => 'no-store, no-cache, must-revalidate, max-age=0',
        ]);
    }

    /**
     * Proses import CSV ke tabel asset_ac
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

                // Lewati baris kosong
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

                // Wajib id_ac
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
                    $exists = AssetAc::where($this->pk,$id)->exists();
                    AssetAc::updateOrCreate(
                        [$this->pk => $id],
                        $payload
                    );
                    $exists ? $updated++ : $inserted++;
                } else {
                    // insert only — skip jika sudah ada
                    $exists = AssetAc::where($this->pk,$id)->exists();
                    if ($exists) {
                        $skipped++;
                        $errors[] = "Baris $rowNum: {$this->pk} '$id' sudah ada — dilewati (mode insert_only).";
                        continue;
                    }
                    AssetAc::create($payload);
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

        $msg = "Import selesai. Inserted: $inserted, Updated: $updated, Skipped: $skipped.";
        if (!empty($errors)) {
            $preview = implode("\n", array_slice($errors, 0, 10));
            $msg .= "\nCatatan:\n".$preview.(count($errors)>10 ? "\n(+".(count($errors)-10)." baris lagi)" : '');
        }

        return redirect()->route('inventory.ac.index')->with('success', nl2br(e($msg)));
    }
}
