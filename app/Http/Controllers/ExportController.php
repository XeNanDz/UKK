<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExportController extends Controller
{
    public function download($filename)
    {
        $filePath = storage_path('app/exports/' . $filename);

        if (!file_exists($filePath)) {
            abort(404, 'File not found.');
        }

        return response()->download($filePath, $filename)->deleteFileAfterSend(true);
    }
}
