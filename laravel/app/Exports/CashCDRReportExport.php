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

class CashCDRReportExport implements FromView, WithEvents, WithStrictNullComparison
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
            ->where('DateIssued', '<=', $end_date)
            ->orderBy('DateIssued','ASC');
        })->whereHas('allocation', function($q) use($account_number) {
            $q->where('AccountNo',$account_number);
        })->whereHas('fotrans', function($q){
            $q->whereNotNull('CheckNo');
        })
        ->whereNotNull('FOTrans')
        ->get();

        $data = [];
        foreach ($trans as $key => $value) {
            $voucher = $value["voucher"];
            $allocation = $value["allocation"];
            $fotrans = $value["fotrans"];

            $dateIssued = $voucher["DateIssued"];

            $data[$dateIssued]["id"]        = $dateIssued;
            $data[$dateIssued]["dateIssued"] = $dateIssued;
            $data[$dateIssued]["total"] = isset($data[$dateIssued]["total"]) ? round($data[$dateIssued]["total"],2) + round($value["Amount"],2) :  round($value["Amount"],2);

            $data[$dateIssued]["children"][]= array(
                "checkNo"   => $voucher["CheckNo"],
                "voucherNo"   => $voucher["VoucherNo"],
                "obligationNo"   => $fotrans["FO"],
                "allocationNo"   => $allocation["AllocationNo"],
                "reference"   => $allocation["Reference"],
                "description"   => $value["Specifications"],
                "object"   => $value["Object"],
                "payee"   => $voucher["Payee"],
                "purpose"   => $voucher["Purpose"],
                "amount"   => $value["Amount"]
            );
        }
        
        $data = array_values($data); 

        $grandTotal = array_sum(array_column($data, 'total'));        
        $grandTotal = round($grandTotal,2);

        //var_dump($exportData);
        return view('exports.cashCDRReport', [
            'data'                  => $data,
            'grand_total'           => $grandTotal,
            'start_date'            => $start_date,
            'end_date'              => $end_date
        ]);
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $event->sheet->getStyle('A2:K2')->applyFromArray([
                    'font' => [
                        'bold' => true,
                    ],
                ]);

                $highestRow = $event->sheet->getHighestRow();
                $range = 'A1:K' . $highestRow;

                $event->sheet->getStyle($range)->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['argb' => '000000'],
                        ],
                    ],
                ]);

                $event->sheet->getStyle('A1:K2')->getAlignment()->setHorizontal('center');
                $event->sheet->getStyle($range)->getAlignment()->setWrapText(true); 

                $event->sheet->getDelegate()->getColumnDimension('A')->setWidth(15);
                $event->sheet->getDelegate()->getColumnDimension('B')->setWidth(15);
                $event->sheet->getDelegate()->getColumnDimension('C')->setWidth(15);
                $event->sheet->getDelegate()->getColumnDimension('D')->setWidth(15);
                $event->sheet->getDelegate()->getColumnDimension('E')->setWidth(15);
                $event->sheet->getDelegate()->getColumnDimension('F')->setWidth(30);
                $event->sheet->getDelegate()->getColumnDimension('G')->setWidth(25);
                $event->sheet->getDelegate()->getColumnDimension('H')->setWidth(15);
                $event->sheet->getDelegate()->getColumnDimension('I')->setWidth(30);
                $event->sheet->getDelegate()->getColumnDimension('J')->setWidth(45);
                $event->sheet->getDelegate()->getColumnDimension('K')->setWidth(20);

                $event->sheet->getDelegate()->getStyle('K')->getNumberFormat()->setFormatCode('#,##0.00');

                //$event->sheet->getDelegate()->getColumnDimension('E')->setAutoSize(true);
            }
        ];
    }
}
