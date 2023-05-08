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

class CashPAPStatementExport implements FromView, WithEvents, WithStrictNullComparison
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

        $trans = Transaction::with(['voucher','fotrans','pap'])
        ->whereHas('voucher', function($q) use($start_date,$end_date,$account_number) {
           $q->where('DateIssued', '>=', $start_date)
           ->where('DateIssued', '<=', $end_date)
           ->where('AcctNo',$account_number);
       })
       ->whereHas('fotrans', function($q){
           $q->whereNotNull('CheckNo');
       })
       ->whereNotNull('FOTrans')
       ->orderBy('PPA')
       ->get();

       $errors = [];
       $list = [];
       $grandTotal = 0;
       foreach ($trans as $key => $value) {
           //$grandTotal += round($value["Amount"],2);
           $vouchers = $value["voucher"];
           $ppa = $value['PPA'];
           $pap_details = $value['pap'];
           $date_issued = $vouchers['DateIssued'];
           $month = date("n",strtotime($date_issued));
           $month = (int)$month;

           if($pap_details){
               
               if(!isset($list[$ppa]["pap_title"])){
                   $list[$ppa]["pap_title"] = $pap_details['PAPTitle'];
               }
               if(!isset($list[$ppa][$month])){
                   $list[$ppa][$month] = [
                       'monthNum'          => $month,
                       'monthName'         => date('F', mktime(0, 0, 0, $month, 10)), // March,
                       'PAP'               => $value['PPA'],
                       'PAP_Title'         => $pap_details['PAPTitle'],
                       'Amount'            => round($value["Amount"],2)
                   ];
               }else{
                   $list[$ppa][$month]['Amount'] = round($list[$ppa][$month]['Amount'],2) + round($value["Amount"],2);
               }
           }else{
               $errors[] = $ppa;
           }
       }

       $data = [];
       $monthList = array('January','February','March','April','May','June','July','August','September','October','November','December');
       $all_total = [];

       foreach ($list as $key => $value) {

           $pap_title = $value["pap_title"];

           $month_value = array(
               'pap' => $key,
               'pap_title' => $pap_title,
               'January' => 0.00,
               'February' => 0.00,
               'March' => 0.00,
               'April' => 0.00,
               'May' => 0.00,
               'June' => 0.00,
               'July' => 0.00,
               'August' => 0.00,
               'September' => 0.00,
               'October' => 0.00,
               'November' => 0.00,
               'December' => 0.00,
               'Total' => 0.00,
           );
           foreach ($monthList as $m_key => $m_value) {
               $month = $m_key + 1;
               if(isset($value[$month])){
                   $month_value[$m_value] = round($value[$month]['Amount'],2);
               }

               $month_value["Total"] = round($month_value["Total"],2) + round($month_value[$m_value],2);

               $grandTotal += round($month_value[$m_value],2);
               $all_total[$m_value] = isset($all_total[$m_value]) ? round($all_total[$m_value],2) + round($month_value[$m_value],2) : round($month_value[$m_value],2);

           }
           $data[] = $month_value;
       }
       $all_total["Total"] = round($grandTotal,2);
        
        return view('exports.cashPAPStatement', [
            'data'                  => $data,
            'all_total'             => $all_total,
            'start_date'            => $start_date,
            'end_date'              => $end_date,
            'account_number'        => $account_number
        ]);
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $event->sheet->getStyle('A1:O6')->applyFromArray([
                    'font' => [
                        'bold' => true,
                    ],
                ]);

                $highestRow = $event->sheet->getHighestRow();
                $range = 'A6:O' . $highestRow;

                $event->sheet->getStyle($range)->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['argb' => '000000'],
                        ],
                    ],
                ]);
                
                $event->sheet->getStyle($range)->getAlignment()->setWrapText(true); 

                $columns = ['A','B'];

                collect($columns)->each(function ($column) use ($event) {
                    $event->sheet->getDelegate()->getColumnDimension($column)->setWidth(30);
                });

                
                $columns = ['C','D','E','F','G','H','I','J','K','L','M','N','O'];

                collect($columns)->each(function ($column) use ($event) {
                    //$event->sheet->getDelegate()->getColumnDimension($column)->setWidth(15);
                    $event->sheet->getDelegate()->getColumnDimension($column)->setAutoSize(true);
                    $event->sheet->getDelegate()->getStyle($column)->getNumberFormat()->setFormatCode('#,##0.00');
                });

                $event->sheet->getStyle('O')->applyFromArray([
                    'font' => [
                        'bold' => true,
                    ],
                ]);

                //$event->sheet->getDelegate()->getColumnDimension('E')->setAutoSize(true);
            }
        ];
    }
}
