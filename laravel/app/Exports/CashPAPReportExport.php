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

class CashPAPReportExport implements FromView, WithEvents, WithStrictNullComparison
{
    use Exportable;

    public function forPapCode($pap_code)
    {
        $this->pap_code = $pap_code;
        return $this;
    }

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
        $pap_code = !empty(request()->input('pap_code')) ? request()->input('pap_code') : "";
        $start_date = !empty(request()->input('date_start')) ? request()->input('date_start') : date("Y-m-d");
        $end_date = !empty(request()->input('date_end')) ? request()->input('date_end'): $start_date;
        $account_number = !empty(request()->input('account_number')) ? request()->input('account_number') : "2022-9018-35";

        $trans = Transaction::with(['voucher','fotrans'])
         ->whereHas('voucher', function($q) use($start_date,$end_date,$account_number) {
            $q->where('DateIssued', '>=', $start_date)
            ->where('DateIssued', '<=', $end_date)
            ->where('AcctNo',$account_number);
        })
        ->whereNotNull('FOTrans')
        ->where('PPA',$pap_code)
        ->get();

        $data = [];
        $grandTotal = 0;
        foreach ($trans as $key => $value) {
            $grandTotal += round($value["Amount"],2);
            $vouchers = $value["voucher"];

            $data[] = [
                'CheckNo'           => $vouchers["CheckNo"], 
                'DateIssued'        => $vouchers["DateIssued"],
                'Payee'             => $vouchers['Payee'],
                'Specifications'    => $value['Specifications'],
                'Object'            => $value['Object'],
                'PPA'               => $value['PPA'],
                'Amount'            => round($value["Amount"],2),
                'Allocation'        => $value['Allocation']
            ];
        }

        $grandTotal = round($grandTotal,2);

        //var_dump($exportData);
        return view('exports.cashPAPReport', [
            'data'                  => $data,
            'grand_total'           => $grandTotal,
            'pap_code'              => $pap_code,
            'start_date'            => $start_date,
            'end_date'              => $end_date
        ]);
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $event->sheet->getStyle('A1:H1')->applyFromArray([
                    'font' => [
                        'bold' => true,
                    ],
                ]);

                $highestRow = $event->sheet->getHighestRow();
                $range = 'A1:H' . $highestRow;

                $event->sheet->getStyle($range)->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['argb' => '000000'],
                        ],
                    ],
                ]);

                $event->sheet->getDelegate()->getColumnDimension('A')->setWidth(15);
                $event->sheet->getDelegate()->getColumnDimension('B')->setWidth(15);
                $event->sheet->getDelegate()->getColumnDimension('C')->setWidth(30);
                $event->sheet->getDelegate()->getColumnDimension('D')->setWidth(30);
                $event->sheet->getDelegate()->getColumnDimension('E')->setWidth(15);
                $event->sheet->getDelegate()->getColumnDimension('F')->setWidth(25);
                $event->sheet->getDelegate()->getColumnDimension('G')->setWidth(25);
                $event->sheet->getDelegate()->getColumnDimension('H')->setWidth(25);

                $event->sheet->getDelegate()->getStyle('G')->getNumberFormat()->setFormatCode('#,##0.00');

                //$event->sheet->getDelegate()->getColumnDimension('E')->setAutoSize(true);
            }
        ];
    }
}
