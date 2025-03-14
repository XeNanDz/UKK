<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Produk;

class produkController extends Controller
{

    public function index()
    {
        $produks = Produk::all();

        return response()->json([
            'success' => true,
            'message' => 'Sukses',
            'data' => $produks
        ]);
    }

    public function store(Request $request)
    {

    }


    public function show(string $id)
    {
        //
    }


    public function update(Request $request, string $id)
    {
        //
    }


    public function destroy(string $id)
    {
        //
    }

    public function showByBarcode($barcode)
    {
        $produk = produk::where('barcode', $barcode)->first();
        if (!$produk) {
            return response()->json([
                'success' => false,
                'message' => 'produk not found',
                'data' => null
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $produk,
            'message' => 'Success'
        ]);
    }
}
