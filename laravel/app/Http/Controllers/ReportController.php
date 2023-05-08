<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Models\Allocations;
use App\Models\Vouchers;
use Illuminate\Http\Request;
use DB;
use DateTime;

use App\Exports\CashAllocationSummaryExport;
use App\Exports\CashStatusReportExport;
use App\Exports\CashPAPReportExport;
use App\Exports\CashCDRReportExport;
use App\Exports\CashRCIReportExport;
use App\Exports\CashPAPStatementExport;

class ReportController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('api');
    }
    
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
    }

    public function statusreport(Request $request){
        $start_date = !empty($request->input('date_start')) ? $request->input('date_start') : date("Y-m-d");
        $end_date = !empty($request->input('date_end')) ? $request->input('date_end'): $start_date;
        $account_number = !empty($request->input('account_number')) ? $request->input('account_number') : "2022-9018-35";

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

        return response()->json( compact('allocation_total','utilization_total','balance_total','all_allocation','all_balance','data') );
    }

    public function cdr(Request $request){
        $start_date = !empty($request->input('date_start')) ? $request->input('date_start') : date("Y-m-d");
        $end_date = !empty($request->input('date_end')) ? $request->input('date_end'): $start_date;
        $account_number = !empty($request->input('account_number')) ? $request->input('account_number') : "2022-9018-35";


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
        return response()->json( compact('grandTotal','data') );
    }

    public function rci(Request $request){        
        $start_date = !empty($request->input('date_start')) ? $request->input('date_start') : date("Y-m-d");
        $end_date = !empty($request->input('date_end')) ? $request->input('date_end'): $start_date;
        $account_number = !empty($request->input('account_number')) ? $request->input('account_number') : "2022-9018-35";

        $data = Vouchers::where('AcctNo',$account_number)
        ->where('DateIssued','>=',$start_date)
        ->where('DateIssued','<=',$end_date)
        ->orderBy('DateIssued','ASC')
        ->get();

        $grandTotal = array_sum(array_column($data->toArray(), 'CkAmount')); 
        $grandTotal = round($grandTotal,2);
        return response()->json( compact('grandTotal','data') );
    }
    
    public function pap(Request $request){        
        
        $pap_code = !empty($request->input('pap_code')) ? $request->input('pap_code') : "";
        $start_date = !empty($request->input('date_start')) ? $request->input('date_start') : date("Y-m-d");
        $end_date = !empty($request->input('date_end')) ? $request->input('date_end'): $start_date;
        $account_number = !empty($request->input('account_number')) ? $request->input('account_number') : "2022-9018-35";

         $trans = Transaction::with(['voucher','fotrans'])
         ->whereHas('voucher', function($q) use($start_date,$end_date,$account_number) {
            $q->where('DateIssued', '>=', $start_date)
            ->where('DateIssued', '<=', $end_date)
            ->where('AcctNo',$account_number);
        })
        // ->whereHas('fotrans', function($q){
        //     $q->whereNotNull('CheckNo');
        // })
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
        return response()->json( compact('grandTotal','data') );
    }

    public function allocationsummary(Request $request){
        $alldata = Allocations::select('*' , DB::raw('MONTH(MonthYear) AS a_month'), DB::raw('YEAR(MonthYear) AS a_year') );
        $alldata->when($request->input('year'), function ($query, $input) {
            $query->where(DB::raw('YEAR(MonthYear)'), $input);
        });

        $alldata->when($request->input('account_number'), function ($query, $input) {
            $query->where("AccountNo", $input);
        });

        $alldata = $alldata->orderBy(DB::raw('MONTH(MonthYear)'))->orderBy('Date')->get();

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
        return response()->json( compact('allocation_summary') );
    }

    
    public function papStatement(Request $request){        
        
        $start_date = !empty($request->input('date_start')) ? $request->input('date_start') : "2021-07-01";
        $end_date = !empty($request->input('date_end')) ? $request->input('date_end'): "2021-08-31";
        $account_number = !empty($request->input('account_number')) ? $request->input('account_number') : "2022-9018-35";

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

        $months_total[] = $all_total;

        return response()->json( compact('months_total','data') );
    }


    public function exportAllocationSummary(Request $request)
    {
        //$this->authorize('view', Allotment::class);
        
        return (new CashAllocationSummaryExport)
            ->forYear($request->input('year'))
            ->forAccount($request->input('account_number'))
            ->download('Allocation-Summary_' . date('Y-m-d_H-i-s') . '.xlsx');
    }

    public function exportStatusReport(Request $request)
    {
        //$this->authorize('view', Allotment::class);
        
        return (new CashStatusReportExport)
            ->forDateStart($request->input('date_start'))
            ->forDateEnd($request->input('date_end'))
            ->forAccount($request->input('account_number'))
            ->download('Status-Report_' . date('Y-m-d_H-i-s') . '.xlsx');
    }

    public function exportPAPReport(Request $request)
    {
        return (new CashPAPReportExport)
            ->forPapCode($request->input('pap_code'))
            ->forDateStart($request->input('date_start'))
            ->forDateEnd($request->input('date_end'))
            ->forAccount($request->input('account_number'))
            ->download('PAP-Report_' . date('Y-m-d_H-i-s') . '.xlsx');
    }

    
    public function exportCDRReport(Request $request)
    {
        return (new CashCDRReportExport)
            ->forDateStart($request->input('date_start'))
            ->forDateEnd($request->input('date_end'))
            ->forAccount($request->input('account_number'))
            ->download('CDR-Report_' . date('Y-m-d_H-i-s') . '.xlsx');
    }

    
    public function exportRCIReport(Request $request)
    {
        return (new CashRCIReportExport)
            ->forDateStart($request->input('date_start'))
            ->forDateEnd($request->input('date_end'))
            ->forAccount($request->input('account_number'))
            ->download('RCI-Report_' . date('Y-m-d_H-i-s') . '.xlsx');
    }

    public function exportPAPStatement(Request $request)
    {
        return (new CashPAPStatementExport)
            ->forDateStart($request->input('date_start'))
            ->forDateEnd($request->input('date_end'))
            ->forAccount($request->input('account_number'))
            ->download('PAP-Report_' . date('Y-m-d_H-i-s') . '.xlsx');
    }
    
}
