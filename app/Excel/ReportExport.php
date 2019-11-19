<?php


namespace App\Excel;


use App\Models\Report;
use Illuminate\Database\Query\Builder;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class ReportExport implements FromQuery, WithHeadings, WithMapping, WithColumnFormatting, ShouldAutoSize
{
    use Exportable;

    protected $report;

    public function __construct(Report $report)
    {
        $this->report = $report;
    }

    /**
     * @return Builder
     */
    public function query()
    {
        return $this->report->transactions();
    }

    /**
     * @return array
     */
    public function headings(): array
    {
        return [
            '#',
            'Transaction parente',
            'Montant',
            'Type',
            'Etape',
            'Date de création',
            'Nom',
            'Prénom',
            'Mail'
        ];
    }

    /**
     * @param mixed $row
     *
     * @return array
     */
    public function map($row): array
    {
        return [
            $row->id,
            $row->parent_id,
            $row->amount/100,
            $row->type,
            $row->step,
            Date::dateTimeToExcel($row->created_at),
            $row->firstname,
            $row->lastname,
            $row->client_mail,
        ];
    }

    /**
     * @return array
     */
    public function columnFormats(): array
    {
        return [
            'C' => NumberFormat::FORMAT_CURRENCY_EUR_SIMPLE,
            'F' => NumberFormat::FORMAT_DATE_DATETIME,
        ];
    }
}
