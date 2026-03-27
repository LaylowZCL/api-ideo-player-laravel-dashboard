<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class VideoReportsExport implements WithMultipleSheets
{
    public function __construct(private readonly array $payload)
    {
    }

    public function sheets(): array
    {
        return [
            new ReportArraySheetExport('Resumo', $this->payload['summary'] ?? []),
            new ReportArraySheetExport('Eventos detalhados', $this->payload['detailed_rows'] ?? []),
            new ReportArraySheetExport('Por plataforma', $this->payload['platform_rows'] ?? []),
            new ReportArraySheetExport('Por evento', $this->payload['event_rows'] ?? []),
            new ReportArraySheetExport('Linha temporal', $this->payload['timeline_rows'] ?? []),
            new ReportArraySheetExport('Top vídeos', $this->payload['top_video_rows'] ?? []),
        ];
    }
}

class ReportArraySheetExport implements FromArray, WithHeadings, ShouldAutoSize, WithStyles
{
    public function __construct(
        private readonly string $title,
        private readonly array $rows
    ) {
    }

    public function headings(): array
    {
        if (empty($this->rows)) {
            return ['Sem dados'];
        }

        return array_keys($this->rows[0]);
    }

    public function array(): array
    {
        if (empty($this->rows)) {
            return [['Não existem dados para os filtros seleccionados.']];
        }

        return array_map(fn ($row) => array_values($row), $this->rows);
    }

    public function styles(Worksheet $sheet): array
    {
        $highestColumn = $sheet->getHighestColumn();
        $highestRow = max($sheet->getHighestRow(), 1);

        $sheet->setTitle(mb_substr($this->title, 0, 31));
        $sheet->freezePane('A2');
        $sheet->setAutoFilter("A1:{$highestColumn}1");

        return [
            1 => [
                'font' => [
                    'bold' => true,
                    'color' => ['rgb' => 'FFFFFF'],
                ],
                'fill' => [
                    'fillType' => 'solid',
                    'color' => ['rgb' => '0B3D7A'],
                ],
            ],
            "A1:{$highestColumn}{$highestRow}" => [
                'alignment' => [
                    'vertical' => 'top',
                    'wrapText' => true,
                ],
            ],
        ];
    }
}
