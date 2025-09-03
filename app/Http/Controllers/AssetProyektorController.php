<?php

namespace App\Http\Controllers;

use App\Models\AssetProyektor;
use App\Models\AssetHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class AssetProyektorController extends Controller
{
    private string $table = 'asset_proyektor';
    private string $pk    = 'id_proyektor';

    // Kolom standar sesuai skema + timestamps
    private array $std = [
        'id_proyektor','ruang','nama_ruang','merk','tipe_proyektor',
        'resolusi_max','vga_support','hdmi_support','kabel_hdmi','remote',
        'tahun_pembelian','keterangan_tambahan',
        'created_at','updated_at',
    ];

    private const TYPE_MAP = [
        'string'   => 'string',
        'text'     => 'text',
        'integer'  => 'integer',
        'boolean'  => 'boolean',
        'date'     => 'date',
        'datetime' => 'dateTime',
    ];

    /** Normalisasi nama kolom */
    private function normalize(string $name): string
    {
        $name = trim($name);
        if ($name === '') return ''; // <— abaikan header kosong

        $k = Str::snake($name);
        $k = preg_replace('/[^a-z0-9_]/', '_', strtolower($k));
        $k = preg_replace('/_{2,}/', '_', trim($k, '_'));

        if ($k === '') return '';         // <— tetap abaikan jika habis disaring
        if (preg_match('/^\d/', $k)) {    // jika diawali angka, prefiks aman
            $k = 'x_'.$k;
        }
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

            // Skip header kosong atau tipe tak dikenal
            if ($col === '' || !isset(self::TYPE_MAP[$type])) continue;
            // Jangan tumpang tindih dengan kolom standar
            if (in_array($col, $this->std, true)) continue;
            // Lewati jika sudah ada
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

    private function extraColumns(): array
    {
        $all = Schema::getColumnListing($this->table);
        return array_values(array_diff($all, $this->std));
    }

    // =============== INDEX ===============
    public function index(Request $request)
    {
        $columns = [
            $this->pk         => 'ID Proyektor',
            'ruang'           => 'Ruang',
            'nama_ruang'      => 'Nama Ruang',
            'merk'            => 'Merk',
            'tipe_proyektor'  => 'Tipe',
            'resolusi_max'    => 'Resolusi',
            'vga_support'     => 'VGA',
            'hdmi_support'    => 'HDMI',
            'kabel_hdmi'      => 'Kabel HDMI',
            'remote'          => 'Remote',
            'tahun_pembelian' => 'Tahun',
        ];

        $q      = trim((string) $request->query('q', ''));
        $tipe   = trim((string) $request->query('tipe', ''));
        $res    = trim((string) $request->query('res', ''));
        $merk   = trim((string) $request->query('merk', ''));
        $ruang  = trim((string) $request->query('ruang', ''));

        $base = AssetProyektor::query();

        if ($q !== '') {
            $cols = array_values(array_diff(Schema::getColumnListing($this->table), ['created_at','updated_at']));
            $like = "%{$q}%";
            $base->where(function($w) use ($cols, $like) {
                foreach ($cols as $c) $w->orWhere($c,'like',$like);
            });
        }

        if ($tipe !== '' && Schema::hasColumn($this->table,'tipe_proyektor')) {
            $base->where('tipe_proyektor',$tipe);
        }
        if ($res !== '' && Schema::hasColumn($this->table,'resolusi_max')) {
            $base->where('resolusi_max',$res);
        }
        if ($merk !== '' && Schema::hasColumn($this->table,'merk')) {
            $base->where('merk',$merk);
        }
        if ($ruang !== '' && Schema::hasColumn($this->table,'ruang')) {
            $base->where('ruang',$ruang);
        }

        $items = $base->orderBy($this->pk)->paginate(12)->appends($request->query());

        $tipeOptions  = Schema::hasColumn($this->table,'tipe_proyektor')
            ? AssetProyektor::whereNotNull('tipe_proyektor')->where('tipe_proyektor','<>','')->distinct()->orderBy('tipe_proyektor')->pluck('tipe_proyektor')
            : collect();
        $resOptions   = Schema::hasColumn($this->table,'resolusi_max')
            ? AssetProyektor::whereNotNull('resolusi_max')->where('resolusi_max','<>','')->distinct()->orderBy('resolusi_max')->pluck('resolusi_max')
            : collect();
        $merkOptions  = Schema::hasColumn($this->table,'merk')
            ? AssetProyektor::whereNotNull('merk')->where('merk','<>','')->distinct()->orderBy('merk')->pluck('merk')
            : collect();
        $ruangOptions = Schema::hasColumn($this->table,'ruang')
            ? AssetProyektor::whereNotNull('ruang')->where('ruang','<>','')->distinct()->orderBy('ruang')->pluck('ruang')
            : collect();

        $extraCols = $this->extraColumns();

        return view('inventory.proyektor.index', compact(
            'items','columns','q','tipe','res','merk','ruang',
            'tipeOptions','resOptions','merkOptions','ruangOptions','extraCols'
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

        return view('inventory.proyektor.form', [
            'mode'=>'create',
            'fields'=>$fields,
            'data'=>new AssetProyektor()
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
        AssetProyektor::create($input);

        return redirect()->route('inventory.proyektor.index')->with('success','Aset Proyektor berhasil ditambahkan.');
    }

    public function edit(AssetProyektor $proyektor)
    {
        $columns = Schema::getColumnListing($this->table);
        $skip = ['created_at','updated_at'];
        $fields = [];
        foreach ($columns as $col) {
            if (in_array($col,$skip)) continue;
            $fields[$col] = ucwords(str_replace('_',' ',$col));
        }

        return view('inventory.proyektor.form', [
            'mode'=>'edit',
            'fields'=>$fields,
            'data'=>$proyektor
        ]);
    }

    public function update(Request $request, AssetProyektor $proyektor)
    {
        $columns = Schema::getColumnListing($this->table);
        $skip = ['created_at','updated_at'];
        $writable = array_values(array_diff($columns,$skip));

        $request->validate([
            $this->pk => 'required|string|unique:'.$this->table.','.$this->pk.','.$proyektor->{$this->pk}.','.$this->pk,
            'tahun_pembelian' => 'nullable|integer',
        ]);

        $input  = $request->only($writable);
        $before = $proyektor->only(array_keys($input));

        $proyektor->fill($input);
        $dirty = $proyektor->getDirty();
        $proyektor->save();

        if (!empty($dirty)) {
            $changes = [];
            foreach ($dirty as $k => $newVal) {
                $changes[$k] = ['from' => $before[$k] ?? null, 'to' => $newVal];
            }
            $userName = auth()->user()->name ?? 'System';
            AssetHistory::create([
                'asset_type'   => 'proyektor',
                'asset_id'     => $proyektor->{$this->pk},
                'action'       => 'update',
                'changes_json' => $changes,
                'note'         => $request->input('catatan_histori'),
                'edited_by'    => $userName,
                'created_at'   => now('Asia/Jakarta'),
            ]);
        }

        return redirect()->route('inventory.proyektor.index')->with('success','Aset Proyektor berhasil diperbarui.');
    }

    public function destroy(AssetProyektor $proyektor)
    {
        $userName = auth()->user()->name ?? 'System';
        AssetHistory::create([
        'asset_type'   => 'proyektor',
        'asset_id'     => $proyektor->{$this->pk},
        'action'       => 'delete',
        'changes_json' => null,
        'note'         => null,
        'edited_by'    => $userName, // 👈 ditambah
        'created_at'   => now('Asia/Jakarta'),
    ]);
        $proyektor->delete();
        return redirect()->route('inventory.proyektor.index')->with('success','Aset Proyektor berhasil dihapus.');
    }

    public function show(AssetProyektor $proyektor)
    {
        return view('inventory.proyektor._detail', ['data' => $proyektor]);
    }

    // =============== IMPORT CSV ===============

    /** Form import CSV */
    public function importForm()
    {
        return view('inventory.proyektor.import');
    }

    /** Download template CSV (header sesuai skema) */
    public function downloadTemplate()
    {
        $headers = [
            'id_proyektor','ruang','nama_ruang','merk','tipe_proyektor',
            'resolusi_max','vga_support','hdmi_support','kabel_hdmi','remote',
            'tahun_pembelian','keterangan_tambahan',
        ];

        $sample = [
            'PJ-001','A101','Ruang A','Epson','LCD',
            '1920x1080','Ya','Ya','Ada','Ada',
            '2020','-',
        ];

        $toCsvLine = function(array $fields): string {
            $escaped = array_map(function($v) {
                $v = (string)$v;
                if (str_contains($v, '"')) $v = str_replace('"', '""', $v);
                return (strpbrk($v, ",\n\r\t\"") !== false) ? "\"$v\"" : $v;
            }, $fields);
            return implode(',', $escaped)."\n";
        };

        $csv  = $toCsvLine($headers);
        $csv .= $toCsvLine($sample);

        return response()->make($csv, 200, [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="template_asset_proyektor.csv"',
            'Cache-Control'       => 'no-store, no-cache, must-revalidate, max-age=0',
        ]);
    }

    /**
     * Import CSV:
     *  - mode: upsert | insert_only
     *  - auto_add_columns: buat kolom baru otomatis (STRING nullable)
     *  - Mengabaikan header kosong agar tidak membuat kolom bernama ''.
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

        // Deteksi delimiter dari baris pertama
        $firstLine = fgets($fh) ?: '';
        $delimiter = (substr_count($firstLine, ';') > substr_count($firstLine, ',')) ? ';' : ',';
        rewind($fh);

        // Baca header mentah
        $rawHeaders = fgetcsv($fh, 0, $delimiter);
        if (!$rawHeaders || count($rawHeaders) === 0) {
            fclose($fh);
            return back()->with('error','Header CSV tidak ditemukan.');
        }

        // Normalisasi + BUANG header kosong
        $normHeaders = array_map(fn($h) => $this->normalize((string)$h), $rawHeaders);
        $normHeaders = array_values(array_filter($normHeaders, fn($h) => $h !== '')); // <— kunci fix

        // Cek kolom yang belum ada
        $existingCols = Schema::getColumnListing($this->table);
        $unknown = array_values(array_diff($normHeaders, $existingCols));

        if (!empty($unknown)) {
            if ($allowAutoCol) {
                // Tambah kolom baru (STRING nullable)
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

        // Kolom yang bisa diisi
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

                // Kosong total? lewati
                if (count(array_filter($row, fn($v)=>trim((string)$v)!=='')) === 0) {
                    continue;
                }

                // Jika jumlah kolom > header karena ada header kosong yang dibuang,
                // kita potong ke jumlah header normalisasi:
                if (count($row) > count($normHeaders)) {
                    $row = array_slice($row, 0, count($normHeaders));
                }

                // Pad jika kurang
                if (count($row) < count($normHeaders)) {
                    $row = array_pad($row, count($normHeaders), null);
                }

                // Gabungkan header->value
                $assoc = array_combine($normHeaders, $row);

                // Wajib: id_proyektor
                $id = trim((string)($assoc['id_proyektor'] ?? ''));
                if ($id === '') {
                    $skipped++;
                    $errors[] = "Baris $rowNum: kolom 'id_proyektor' kosong — dilewati.";
                    continue;
                }

                // Casting ringan
                if (isset($assoc['tahun_pembelian'])) {
                    $tp = trim((string)$assoc['tahun_pembelian']);
                    $assoc['tahun_pembelian'] = ($tp === '' ? null : (int)$tp);
                }

                // String kosong -> null
                foreach ($assoc as $k => $v) {
                    if (is_string($v)) {
                        $v = trim($v);
                        $assoc[$k] = ($v === '') ? null : $v;
                    }
                }

                // Ambil hanya kolom yang ada di DB
                $payload = array_intersect_key($assoc, array_flip($writable));
                $payload[$this->pk] = $id;

                if ($mode === 'upsert') {
                    $exists = AssetProyektor::where($this->pk,$id)->exists();
                    AssetProyektor::updateOrCreate([$this->pk => $id], $payload);
                    $exists ? $updated++ : $inserted++;
                } else {
                    $exists = AssetProyektor::where($this->pk,$id)->exists();
                    if ($exists) {
                        $skipped++;
                        $errors[] = "Baris $rowNum: {$this->pk} '$id' sudah ada — dilewati (mode insert_only).";
                        continue;
                    }
                    AssetProyektor::create($payload);
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

        return redirect()->route('inventory.proyektor.index')->with('success', nl2br(e($msg)));
    }
}
