<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AssetPc;
use App\Models\AssetPrinter;
use App\Models\AssetProyektor;

class InventoryController extends Controller
{
    public function index(Request $request)
    {
        $type = $request->query('type', 'pc'); // default pc

        // Peta type → [Model, routePrefix]
        $map = [
            'pc'        => [AssetPc::class,        'pc'],
            'printer'   => [AssetPrinter::class,   'printer'],
            'proyektor' => [AssetProyektor::class, 'proyektor'],
        ];

        if (! array_key_exists($type, $map)) {
            $type = 'pc';
        }

        [$modelClass, $routePrefix] = $map[$type];
        $assets = $modelClass::query()->latest()->paginate(10);

        return view('inventory.index', [
            'assets'      => $assets,
            'type'        => $type,
            'routePrefix' => $routePrefix,
        ]);
    }
}
