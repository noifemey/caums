<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStrictNullComparison;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Concerns\Exportable;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

use App\Models\Transaction;
use App\Models\Allocations;
use App\Models\Vouchers;
use DB;
use DateTime;
use PhpOffice\PhpSpreadsheet\Style\Border;

class CashRCIReportExport implements FromView, WithEvents, WithStrictNullComparison
{
    use Exportable;

    public function forDateStart($date_start)
    {
        $this->date_start = $date_start;
        return $this;
    }
    public function forDateEnd($date_end)
    {
        $this->date_end = $date_end;
        return $this;
    }

    public function forAccount($account_number)
    {
        $this->account_number = $account_number;
        return $this;
    }

    public function view():View
    {
        $start_date = !empty(request()->input('date_start')) ? request()->input('date_start') : date("Y-m-d");
        $end_date = !empty(request()->input('date_end')) ? request()->input('date_end'): $start_date;
        $account_number = !empty(request()->input('account_number')) ? request()->input('account_number') : "2022-9018-35";

        $data = Vouchers::where('AcctNo',$account_number)
        ->where('DateIssued','>=',$start_date)
        ->where('DateIssued','<=',$end_date)
        ->orderBy('DateIssued','ASC')
        ->get();

        $grandTotal = array_sum(array_column($data->toArray(), 'CkAmount')); 
        $grandTotal = round($grandTotal,2);
        
        return view('exports.cashRCIReport', [
            'data'                  => $data,
            'grand_total'           => $grandTotal,
            'start_date'            => $start_date,
            'end_date'              => $end_date,
            'account_number'        => $account_number
        ]);
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $event->sheet->getStyle('A1:G6')->applyFromArray([
                    'font' => [
                        'bold' => true,
                    ],
                ]);

                $highestRow = $event->sheet->getHighestRow();
                $range = 'A6:G' . $highestRow;

                $event->sheet->getStyle($range)->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['argb' => '000000'],
                        ],
                    ],
                ]);
                $event->sheet->getStyle($range)->getAlignment()->setWrapText(true); 

                $event->sheet->getDelegate()->getColumnDimension('A')->setWidth(15);
                $event->sheet->getDelegate()->getColumnDimension('B')->setWidth(15);
                $event->sheet->getDelegate()->getColumnDimension('C')->setWidth(15);
                $event->sheet->getDelegate()->getColumnDimension('D')->setWidth(15);
                $event->sheet->getDelegate()->getColumnDimension('E')->setWidth(25);
                $event->sheet->getDelegate()->getColumnDimension('F')->setWidth(35);
                $event->sheet->getDelegate()->getColumnDimension('G')->setWidth(20);

                $event->sheet->getDelegate()->getStyle('G')->getNumberFormat()->setFormatCode('#,##0.00');

                //$event->sheet->getDelegate()->getColumnDimension('E')->setAutoSize(true);
            }
        ];
    }
}
