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

class CashStatusReportExport implements FromView, WithEvents, WithStrictNullComparison
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

        $trans = Transaction::with(['voucher','allocation','fotrans'])
        ->whereHas('voucher', function($q) use($start_date,$end_date) {
            $q->where('DateIssued', '>=', $start_date)
            ->where('DateIssued', '<=', $end_date);
        })->whereHas('allocation', function($q) use($account_number) {
            $q->where('AccountNo',$account_number);
        })->whereHas('fotrans', function($q){
            $q->whereNotNull('CheckNo');
        })
        ->whereNotNull('FOTrans')
        ->orderBy('Allocation','ASC')
        ->get();

        $count = count($trans);
        $dt_list = array();
        foreach ($trans as $key => $value) {

            if($value["fotrans"] == NULL){
                continue;
            }

            $allocation_no = $value["Allocation"];

            $allocation = $value["allocation"];
            $allocation->toArray();
            $allocation_id = $allocation["id"];

            $dt_list[$allocation_id]["id"]= $allocation_id;
            $dt_list[$allocation_id]["allocation_no"] = $allocation_no;
            $dt_list[$allocation_id]["reference"] = $allocation["Reference"]; //$value["allocation"]["Reference"];
            $dt_list[$allocation_id]["purpose"] = $allocation["Purpose"]; //$value["allocation"]["Purpose"];
            $dt_list[$allocation_id]["cash_received"] = $allocation["CAReceived"]; //$value["allocation"]["CAReceived"];

            $amount = $value["Amount"];
            $dt_list[$allocation_id]["total"] = isset($dt_list[$allocation_id]["total"]) ? round($dt_list[$allocation_id]["total"],2) + round($amount, 2) : round($amount, 2);
            
        }

        $data = array();
        $utilization_total  = 0;
        $allocation_total   = 0;
        $balance_total      = 0;
        foreach ($dt_list as $key => $value) {
            $amount = $value["total"];
            $cash_received = $value["cash_received"];
            $value["balance"] = round($cash_received,2) - round($amount,2);

            $data[] = $value;
            $balance = $value["balance"];

            $utilization_total  += round($amount, 2);
            $allocation_total   += round($cash_received, 2);
            $balance_total    += round($balance, 2);
        }

        $all_allocation   = Allocations::where('AccountNo',$account_number)
        ->where('MonthYear', '>=', $start_date)
        ->where('MonthYear', '<=', $end_date)
        ->sum('CAReceived');
        $all_allocation   = round($all_allocation,2);
        $all_balance    = $all_allocation - $utilization_total;
        $all_balance    = round($all_balance,2);

        //var_dump($exportData);
        return view('exports.cashStatusReport', [
            'data'                  => $data,
            'allocation_total'      => $allocation_total,
            'utilization_total'     => $utilization_total,
            'balance_total'         => $balance_total,
            'all_allocation'        => $all_allocation,
            'all_balance'           => $all_balance,
            'start_date'            => $start_date,
            'end_date'              => $end_date,
            'account_number'        => $account_number,
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

                $event->sheet->getDelegate()->getColumnDimension('A')->setWidth(10);
                $event->sheet->getDelegate()->getColumnDimension('B')->setWidth(35);
                $event->sheet->getDelegate()->getColumnDimension('C')->setWidth(35);

                $event->sheet->getDelegate()->getStyle('F')->getNumberFormat()->setFormatCode('#,##0.00');
                $event->sheet->getDelegate()->getStyle('D')->getNumberFormat()->setFormatCode('#,##0.00');
                $event->sheet->getDelegate()->getStyle('E')->getNumberFormat()->setFormatCode('#,##0.00');

                $event->sheet->getDelegate()->getColumnDimension('D')->setAutoSize(true);
                $event->sheet->getDelegate()->getColumnDimension('E')->setAutoSize(true);
                $event->sheet->getDelegate()->getColumnDimension('F')->setAutoSize(true);
            }
        ];
    }
}
