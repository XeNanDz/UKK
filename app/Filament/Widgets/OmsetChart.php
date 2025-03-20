<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;
use Flowframe\Trend\Trend;
use Flowframe\Trend\TrendValue;
use App\Models\Penjualan;
use Carbon\Carbon;

class OmsetChart extends ChartWidget
{
    protected static ?string $heading = 'Pemasukan';
    protected static ?int $sort = 1;
    public ?string $filter = 'today';
    protected static string $color = 'success';

    protected function getData(): array
    {
        $activeFilter = $this->filter;

        // Tentukan rentang waktu berdasarkan filter yang dipilih
        $dateRange = match ($activeFilter) {
            'today' => [
                'start' => now()->startOfDay(),
                'end' => now()->endOfDay(),
                'period' => 'perHour'
            ],
            'week' => [
                'start' => now()->startOfWeek(),
                'end' => now()->endOfWeek(),
                'period' => 'perDay'
            ],
            'month' => [
                'start' => now()->startOfMonth(),
                'end' => now()->endOfMonth(),
                'period' => 'perDay'
            ],
            'year' => [
                'start' => now()->startOfYear(),
                'end' => now()->endOfYear(),
                'period' => 'perMonth'
            ]
        };

        // Query data penjualan berdasarkan rentang waktu
        $query = Trend::model(Penjualan::class)
            ->between(
                start: $dateRange['start'],
                end: $dateRange['end'],
            );

        // Sesuaikan periode data berdasarkan filter
        if ($dateRange['period'] === 'perHour') {
            $data = $query->perHour();
        } elseif ($dateRange['period'] === 'perDay') {
            $data = $query->perDay();
        } else {
            $data = $query->perMonth();
        }

        // Hitung total harga penjualan
        $data = $data->sum('total_harga');

        // Format label berdasarkan periode
        $labels = $data->map(function (TrendValue $value) use ($dateRange) {
            $date = Carbon::parse($value->date);

            if ($dateRange['period'] === 'perHour') {
                return $date->format('H:i'); // Format jam:menit
            } elseif ($dateRange['period'] === 'perDay') {
                return $date->format('d M'); // Format tanggal dan bulan
            }
            return $date->format('M Y'); // Format bulan dan tahun
        });

        return [
            'datasets' => [
                [
                    'label' => 'Pemasukan ' . $this->getFilters()[$activeFilter],
                    'fill' => true,
                    'data' => $data->map(fn (TrendValue $value) => $value->aggregate),
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getFilters(): ?array
    {
        return [
            'today' => 'Hari Ini',
            'week' => 'Minggu Ini',
            'month' => 'Bulan Ini',
            'year' => 'Tahun Ini',
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
