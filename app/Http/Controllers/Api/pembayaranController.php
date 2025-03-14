<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\pembayaran;

class pembayaranController extends Controller
{
    public function index()
    {
        $pembayarans = pembayaran::all();

        return response()->json([
            'success' => true,
            'data' => $pembayarans,
            'message' => 'Sukses menampilkan data'
        ]);
    }
}
