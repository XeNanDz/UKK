<?php
namespace App\Filament\Exports;

use App\Models\Penjualan;
use App\Models\Pelanggan;
use Filament\Actions\Exports\Exporter;
use OpenSpout\Common\Entity\Style\Color;
use OpenSpout\Common\Entity\Style\Style;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Models\Export;
use Filament\Actions\Exports\Enums\ExportFormat;
use OpenSpout\Common\Entity\Style\CellAlignment;
use OpenSpout\Common\Entity\Style\CellVerticalAlignment;
use Filament\Notifications\Notification;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PenjualanExporter extends Exporter
{
    protected static ?string $model = Penjualan::class;

    // Relasi di-load biar muncul di export
    public static function with(): array
    {
        return ['pelanggan', 'user', 'pembayaran'];
    }

    // Custom folder dan disk export
    public static function getDiskName(): string
    {
        return 'local'; // Bisa juga 'public' kalau mau langsung diakses via URL
    }

    public static function getDirectory(): ?string
    {
        return 'exports'; // Folder tujuan export: storage/app/exports
    }

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('user.name')
                ->label('Nama Kasir'),
            ExportColumn::make('pelanggan.nama_pelanggan')
                ->label('Nama Pelanggan'),
            ExportColumn::make('diskon')
                ->label('Diskon')
                ->suffix('%'),
            ExportColumn::make('total_harga')
                ->label('Total')
                ->prefix('Rp. '),
            ExportColumn::make('pembayaran.metode_pembayaran')
                ->label('Metode Pembayaran'),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $filename = $export->file_name;
        $url = route('download.export', ['filename' => $filename]); // Pastikan route sudah ada

        Notification::make()
            ->title('Ekspor Selesai')
            ->body("Ekspor data penjualan telah selesai. <a href='{$url}' target='_blank'>Klik di sini untuk mengunduh</a>.")
            ->success()
            ->send();

        return "Ekspor selesai. <a href='{$url}' target='_blank'>Klik di sini untuk mengunduh</a>.";
    }

    public function getXlsxHeaderCellStyle(): ?Style
    {
        return (new Style())
            ->setFontBold()
            ->setFontColor(Color::BLACK)
            ->setBackgroundColor(Color::YELLOW)
            ->setCellAlignment(CellAlignment::CENTER)
            ->setCellVerticalAlignment(CellVerticalAlignment::CENTER)
            ->setShouldWrapText(true);
    }

    public function getFileName(Export $export): string
    {
        return "Pemasukan-" . now()->format('d-m-Y') . '.xlsx';
    }

    public function getFormats(): array
    {
        return [
            ExportFormat::Xlsx,
        ];
    }

    public function getDownloadResponse(Export $export): StreamedResponse
    {
        $filePath = storage_path('app/exports/' . $export->file_name); // Sesuai folder yang kita set

        if (!file_exists($filePath)) {
            throw new \Exception('File ekspor tidak ditemukan.');
        }

        return response()->streamDownload(function () use ($filePath) {
            echo file_get_contents($filePath);

            // Hapus file setelah diunduh
            if (file_exists($filePath)) {
                unlink($filePath);
            }
        }, $this->getFileName($export));
    }
}
