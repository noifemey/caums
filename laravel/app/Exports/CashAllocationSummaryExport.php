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

use App\Models\Allocations;
use DB;
use DateTime;
use PhpOffice\PhpSpreadsheet\Style\Border;

class CashAllocationSummaryExport implements FromView, WithEvents, WithStrictNullComparison
{
    use Exportable;

    public function forYear($year)
    {
        $this->year = $year;
        return $this;
    }

    public function forAccount($account_number)
    {
        $this->account_number = $account_number;
        return $this;
    }

    public function view():View
    {
        $alldata = Allocations::select('*' , DB::raw('MONTH(MonthYear) AS a_month'), DB::raw('YEAR(MonthYear) AS a_year') );
        $alldata->when(request()->input('year'), function ($query, $input) {
            $query->where(DB::raw('YEAR(MonthYear)'), $input);
        });

        $alldata->when(request()->input('account_number'), function ($query, $input) {
            $query->where("AccountNo", $input["AccountNumber"]);
        });

        $alldata = $alldata->orderBy(DB::raw('MONTH(MonthYear)'))->orderBy('Date')->get();

        //var_dump(request()->input('account_number'));

        $a_summary = [];
        $grand_total = 0;

        foreach ($alldata as $key => $value) {
            $month = $value["a_month"];
            $grand_total += $value["CAReceived"];

            if(!isset($a_summary[$month]["month_name"])){
                $dateObj   = DateTime::createFromFormat('!m', $month);
                $monthName = $dateObj->format('F');
                $a_summary[$month]["month_name"] = $monthName . " " . $value["a_year"];
            }

            $a_summary[$month]["id"][] = $value["id"];
            $a_summary[$month]["received_summary"] = isset($a_summary[$month]["received_summary"]) ? $a_summary[$month]["received_summary"] + $value["CAReceived"] : $value["CAReceived"];
            $a_summary[$month]["data"][] = $value;
        }
        $allocation_summary = array_values($a_summary);
        
        $allocation_summary[] = ["month_name" => "Grand Total", "received_summary" => $grand_total, "data" => []];
        
        //return collect($allocation_summary);

        $account = request()->input('account_number');
        $account_number = ($account) ? $account["AccountNumber"] : "";
        $account_name = ($account) ? $account["AccountName"] : "";

        return view('exports.cashAllocation', [
            'data' => $allocation_summary,
            'year' => request()->input('year'),
            'account_number' =>  $account_number,
            'account_name' =>  $account_name
        ]);
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $event->sheet->getStyle('A1:F6')->applyFromArray([
                    'font' => [
                        'bold' => true,
                    ],
                ]);

                $highestRow = $event->sheet->getHighestRow();
                $range = 'A6:F' . $highestRow;

                $event->sheet->getStyle($range)->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['argb' => '000000'],
                        ],
                    ],
                ]);
                $event->sheet->getStyle($range)->getAlignment()->setWrapText(true); 
                
                $event->sheet->getDelegate()->getStyle('F')->getNumberFormat()->setFormatCode('#,##0.00');

                $event->sheet->getDelegate()->getColumnDimension('A')->setWidth(10);
                $event->sheet->getDelegate()->getColumnDimension('B')->setWidth(15);
                $event->sheet->getDelegate()->getColumnDimension('C')->setWidth(15);
                $event->sheet->getDelegate()->getColumnDimension('D')->setWidth(25);
                $event->sheet->getDelegate()->getColumnDimension('E')->setWidth(35);
                $event->sheet->getDelegate()->getColumnDimension('F')->setAutoSize(true);
            }
        ];
    }
}
