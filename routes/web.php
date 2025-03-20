<?php

use Maatwebsite\Excel\Excel;
use App\Exports\TemplateExport;
use App\Http\Controllers\KasirController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\ExportController;
use App\Http\Controllers\StrukController;
use Symfony\Component\HttpFoundation\StreamedResponse;

Route::get('/', function () {






});
Route::get('/download-export/{filename}', function ($filename) {
    $filePath = storage_path('app/exports/' . $filename);

    if (!file_exists($filePath)) {
        abort(404, 'File tidak ditemukan.');
    }

    return response()->download($filePath, $filename);
})->name('download.export');


Route::get('/kasir/print-struk/{id}', [KasirController::class, 'printStruk'])->name('kasir.printStruk');
