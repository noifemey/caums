<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Transaction;
use App\Models\Allocations;
use App\Models\Vouchers;
use App\Models\PapCodes;
use App\Models\Accounts;
use App\Models\AccountCodes;
use App\Models\FoTrans;

use DB;
use DateTime;

class VoucherController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        //
        // if(empty($request->input('check_number')) && empty($request->input('date_start')) && empty($request->input('date_end')) && empty($request->input('account_number')) ){
        //     $data = [];
        //     return response()->json( compact('data') );
        // }

        $checkNo = !empty($request->input('check_number')) ? $request->input('check_number') : "";
        $start_date = !empty($request->input('date_start')) ? $request->input('date_start') : date("Y-m-d");
        $end_date = !empty($request->input('date_end')) ? $request->input('date_end'): $start_date;
        $account_number = !empty($request->input('account_number')) ? $request->input('account_number') : "2022-9018-35";
        $payee = !empty($request->input('payee')) ? $request->input('payee') : "";

        $vouchers = Vouchers::with(['transactions','transactions.allocation','transactions.fotrans'])
        ->when($request->input('check_number'), function ($query, $input) {
            $query->where('CheckNo', 'LIKE','%' . $input . '%');
        })
        ->when($request->input('dv_number'), function ($query, $input) {
            $query->where('VoucherNo', 'LIKE','%' . $input . '%');
        })
        ->when($request->input('date_start'), function ($query, $input) {
            $query->where('DateIssued', '>=', $input);
        })
        ->when($request->input('date_end'), function ($query, $input) {
            $query->where('DateIssued', '<=', $input);
        })
        ->when($request->input('account_number'), function ($query, $input) {
            $query->where('AcctNo',$input);
        })
        ->when($request->input('payee'), function ($query, $input) {
            $query->where('Payee', 'LIKE','%' . $input . '%');
        })
        ->orderBy('DateIssued','ASC')
        ->get();

        $count = count($vouchers);
        $data_list = array();
        $obligations = array();
        foreach ($vouchers as $key => $value) {
            
            $chk_no = $value["CheckNo"];

            $data_list[$chk_no]["id"]= $value["id"];
            $data_list[$chk_no]["check_no"] = $chk_no;
            $data_list[$chk_no]["check_date"] = $value["DateIssued"];
            $data_list[$chk_no]["voucher_no"] = $value["VoucherNo"];
            $data_list[$chk_no]["payee"] = $value["Payee"]; 
            $data_list[$chk_no]["acct_no"] = $value["AcctNo"]; 
            $data_list[$chk_no]["check_amount"] = $value["CkAmount"];
            $data_list[$chk_no]["purpose"] = $value["Purpose"];
            
            $data_list[$chk_no]["amount"] = 0;

            $data_list[$chk_no]["children"] = [];

            $transactions = $value["transactions"];
            if($transactions){
                foreach ($transactions as $t_key => $t_value) {
                    $fotrans = $t_value["fotrans"];
                    $fotrans_id = "";
                    $fotrans_fo = "";
                    if($fotrans){
                        $fotrans_id = $fotrans['FOTrans'];
                        $fotrans_fo = $fotrans['FO'];

                        $ob_no = $fotrans['FO'];
                        $obligations[$ob_no] = isset( $obligations[$ob_no]) ? round($obligations[$ob_no],2) + round($t_value["Amount"],2) : round($t_value["Amount"],2); 
                    }


                    $data_list[$chk_no]["amount"] = isset($data_list[$chk_no]["amount"]) ? round($data_list[$chk_no]["amount"],2) + round($t_value["Amount"],2) : round($t_value["Amount"],2); 

        
                    $transaction = [
                        "id"  => $t_value['TransNo'],
                        "fotrans_id"  => $fotrans_id,
                        "obligation_no" => $fotrans_fo,
                        "object" => $t_value['Object'],
                        "amount" => $t_value['Amount'],
                        "Description" => $t_value['Specifications'],
                        "allocation" => $t_value['Allocation'],
                        "ppa" => $t_value['PPA']
                    ];
        
                    $data_list[$chk_no]["children"][] = $transaction; 
                }
            }
        }
        $data = [];
        foreach ($data_list as $key => $value) {
            $obligation_list = $value["children"];

            foreach ($obligation_list as $ob_key => $ob_value) {
                # code...
                
                $ob_no = $ob_value["obligation_no"];
                if(isset($obligations[$ob_no])){
                    $value["children"][$ob_key]["total_amount"] = $obligations[$ob_no];
                }else{
                    $value["children"][$ob_key]["total_amount"] = 0;
                }
            }

            $data[] = $value;
        }
        return response()->json( compact('data') );

        // $trans = Transaction::with(['voucher','allocation','fotrans'])
        // ->whereHas('voucher', function($q) use($start_date,$end_date,$request) {
        //     $q->when($request->input('date_start'), function ($query, $input) {
        //         $query->where('DateIssued', '>=', $input);
        //     });
        //     $q->when($request->input('date_end'), function ($query, $input) {
        //         $query->where('DateIssued', '<=', $input);
        //     });
        //     $q->when($request->input('account_number'), function ($query, $input) {
        //         $query->where('AcctNo',$input);
        //     });
        //     $q->when($request->input('payee'), function ($query, $input) {
        //         $query->where('Payee', 'LIKE','%' . $input . '%');
        //     });
        //     $q->orderBy('DateIssued','ASC');
        // })
        // ->whereHas('fotrans', function($q){
        //     $q->whereNotNull('CheckNo');
        // })
        // ->when($request->input('check_number'), function ($query, $input) {
        //     $query->where('CheckNo', 'LIKE','%' . $input . '%');
        // })
        // ->whereNotNull('FOTrans')
        // ->get();

        // $count = count($trans);
        // $data_list = array();
        // $obligations = array();
        // foreach ($trans as $key => $value) {

        //     if($value["fotrans"] == NULL){
        //         continue;
        //     }

        //     // $allocation_no = $value["Allocation"];

        //     // $allocation = $value["allocation"];
        //     $voucher = $value["voucher"];
        //     $fotrans = $value["fotrans"];
            
        //     $chk_no = $value["CheckNo"];
        //     //$allocation_id = $allocation["id"];

        //     $data_list[$chk_no]["id"]= $voucher["id"];
        //     $data_list[$chk_no]["check_no"] = $chk_no;
        //     $data_list[$chk_no]["check_date"] = $voucher["DateIssued"];
        //     $data_list[$chk_no]["voucher_no"] = $voucher["VoucherNo"];
        //     $data_list[$chk_no]["payee"] = $voucher["Payee"]; 
        //     $data_list[$chk_no]["acct_no"] = $voucher["AcctNo"]; 
        //     $data_list[$chk_no]["check_amount"] = $voucher["CkAmount"];
        //     $data_list[$chk_no]["purpose"] = $voucher["Purpose"];

        //     $data_list[$chk_no]["amount"] = isset($data_list[$chk_no]["amount"]) ? round($data_list[$chk_no]["amount"],2) + round($value["Amount"],2) : round($value["Amount"],2); 

        //     $ob_no = $fotrans['FO'];
        //     $obligations[$ob_no] = isset( $obligations[$ob_no]) ? round($obligations[$ob_no],2) + round($value["Amount"],2) : round($value["Amount"],2); 

        //     $transaction = [
        //         "id"  => $value['TransNo'],
        //         "fotrans_id"  => $fotrans['FOTrans'],
        //         "obligation_no" => $fotrans['FO'],
        //         "object" => $value['Object'],
        //         "amount" => $value['Amount'],
        //         "Description" => $value['Specifications'],
        //         "allocation" => $value['Allocation'],
        //         "ppa" => $value['PPA']
        //     ];

        //     $data_list[$chk_no]["children"][] = $transaction;       
        // }

        // $data = [];
        // foreach ($data_list as $key => $value) {
        //     $obligation_list = $value["children"];

        //     foreach ($obligation_list as $ob_key => $ob_value) {
        //         # code...
        //         $ob_no = $ob_value["obligation_no"];
        //         $value["children"][$ob_key]["total_amount"] = $obligations[$ob_no];
        //     }

        //     $data[] = $value;
        // }

        // return response()->json( compact('data') );
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // $all = $request->all();
        // return response()->json( compact('all') );

        $validatedData = $request->validate([
            'check_no'          => 'required|unique:vouchers,CheckNo|min:1|max:256',
            'voucher_no'        => 'required',
            'account_no'        => 'required',
            'date_issued'       => 'required',
            'voucher_amount'    => 'required',
            'payee'             => 'required',
            'purpose'           => 'required',
            'obligations.*.obligation_no'           => 'required',
            'obligations.*.items.*.amount'          => 'required',
            'obligations.*.items.*.object_code'     => 'required',
            'obligations.*.items.*.allocation'      => 'required',
            'obligations.*.items.*.ppa'             => 'required'
        ]);

        //Save DV(Voucher table)
        $today                  = date("Y-m-d");
        $voucher                = new Vouchers();
        $voucher->VoucherNo     = $request->input('voucher_no');
        $voucher->CheckNo       = $request->input('check_no');
        $voucher->DateIssued    = $request->input('date_issued');
        $voucher->Payee         = $request->input('payee');
        $voucher->Purpose       = $request->input('purpose');
        $voucher->CkAmount      = $request->input('voucher_amount');
        $voucher->Received      = 1;
        $voucher->AcctNo        = $request->input('account_no');
        $voucher->DateEncoded   = $today;
        $voucher->save();

        //Save Obligation (fotrans table)
        $obligations = $request->input('obligations');
        $checkNo = $voucher->CheckNo;
        foreach ($obligations as $key => $value) {
            $obli_data = array(
                "CheckNo" => $checkNo,
                "FO" => $value["obligation_no"]
            );
            $obli_id = FoTrans::insertGetId($obli_data);

            $ors_list = [];
            $ors_value = $value["items"];
            foreach ($ors_value as $key => $value) {
                $allocation = $value["allocation"];
                $object = $value["object_code"];
                $ppa = $value["ppa"];
                $ors_list[] = array(
                    'FOTrans' => $obli_id, 
                    'CheckNo' => $checkNo, 
                    'Object' => $object['AccountCode'], 
                    'Amount' => $value['amount'],
                    'Specifications' => $value['description'], 
                    'Allocation' =>  $allocation['AllocationNo'], 
                    'PPA' =>  $ppa['PAPCode'], 
                    'DateEncoded' => $today, 
                );
            }
            $transaction = Transaction::insert($ors_list);
        }
        //save ORS (transaction table)
        return response()->json( array('success' => true) );
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit(Request $request)
    {
        $validatedData = $request->validate([
            'check_no' => 'required'
        ]);

        $checkNo = $request->input('check_no');

        $trans = Transaction::with(['voucher','fotrans'])
        ->where('CheckNo', '=', $checkNo)
        ->get();

        $count = count($trans);

        $data = ["success" => false, "data" => []];


        if($count > 0){
            $voucher_id =  0;
            $check_no =  "";
            $voucher_no =  "";
            $account_no =  "";
            $date_issued =  "";
            $voucher_amount =  "";
            $payee =  "";
            $purpose =  "";
            $obligations = [];

            foreach ($trans as $key => $value) {

                if($voucher_id == 0){
                    if(isset($value["voucher"])){
                        $voucher = $value["voucher"];
                        $date=date_create($voucher["DateIssued"]);
                        $voucher_id =  $voucher["id"];
                        $check_no   =  $voucher["CheckNo"];
                        $voucher_no =  $voucher["VoucherNo"];
                        $account_no =  $voucher["AcctNo"];
                        $date_issued =  date_format($date,"Y-m-d");
                        $voucher_amount =  $voucher["CkAmount"];
                        $payee =  $voucher["Payee"];
                        $purpose =  $voucher["Purpose"];
                    }
                }

                $fotrans = $value["fotrans"];
                if($fotrans){
                    $f_id = $fotrans["FOTrans"];
    
                    $obligations[$f_id]["obligation_id"] = $f_id;
                    $obligations[$f_id]["obligation_no"] = $fotrans["FO"];
                    $obligations[$f_id]["total_amount"] = isset($obligations[$f_id]["total_amount"]) ? $obligations[$f_id]["total_amount"] + $value["Amount"] : $value["Amount"];

                    $item = [
                        "trans_id"  =>  $value["TransNo"],
                        "obligation_no"  => $fotrans["FO"],
                        "object_code"  => $value["Object"],
                        "total_amount" => 0,
                        "description"  => $value["Specifications"],
                        "amount"  => $value["Amount"],
                        "allocation"  => $value["Allocation"],
                        "ppa"  => $value["PPA"],
                    ];

                    $obligations[$f_id]["items"][] = $item;

                }else{
                    $obligations = array([
                        "obligation_id" => 0,
                        "obligation_no" => '',
                        "total_amount" => 0,
                        "items" => array([
                            "trans_id"  =>  0,
                            "obligation_no"  => '',
                            "object_code"  => '',
                            "total_amount" =>  '',
                            "description"  => '',
                            "amount"  => '',
                            "allocation"  => '',
                            "ppa"  => '',
                        ],)
                    ]);
                }
            }
            $obligations = array_values($obligations);
            $dt = array(
                "voucher_id" => $voucher_id,
                "check_no" => $check_no,
                "voucher_no" => $voucher_no,
                "account_no" => $account_no,
                "date_issued" => $date_issued,
                "voucher_amount" => $voucher_amount,
                "payee" => $payee,
                "purpose" => $purpose,
                "obligations" => $obligations,
            );
            $data = ["success" => true, "data" => $dt];
        }

        return response()->json( compact('data') );
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        $validatedData = $request->validate([
            'check_no'          => 'required',
            'voucher_no'        => 'required',
            'account_no'        => 'required',
            'date_issued'       => 'required',
            'voucher_amount'    => 'required',
            'payee'             => 'required',
            'purpose'           => 'required',
            'obligations.*.obligation_no'   => 'required',
            'obligations.*.items.*.amount'  => 'required',
            'obligations.*.items.*.object_code'  => 'required',
            'obligations.*.items.*.allocation'  => 'required',
            'obligations.*.items.*.ppa'  => 'required'
        ]);


        //Save DV(Voucher table)
        $today                  = date("Y-m-d");
        $voucher                = Vouchers::find($request->input('voucher_id'));
        if($voucher == null){
            $voucher            = new Vouchers();
        }

        $voucher->VoucherNo     = $request->input('voucher_no');
        $voucher->CheckNo       = $request->input('check_no');
        $voucher->DateIssued    = $request->input('date_issued');
        $voucher->Payee         = $request->input('payee');
        $voucher->Purpose       = $request->input('purpose');
        $voucher->CkAmount      = $request->input('voucher_amount');
        $voucher->Received      = 1;
        $voucher->AcctNo        = $request->input('account_no');
        $voucher->save();

        //Save Obligation (fotrans table)
        $obligations = $request->input('obligations');
        $checkNo = $voucher->CheckNo;
        foreach ($obligations as $key => $value) {
            $obli_data = array(
                "CheckNo" => $checkNo,
                "FO" => $value["obligation_no"]
            );

            if($value["obligation_id"] == 0 || empty($value["obligation_id"])){
                $obli_id = FoTrans::insertGetId($obli_data);
            }else{
                $oblg = FoTrans::where('FOTrans', '=', $value["obligation_id"])->first(); //find($value["obligation_id"]);
                $oblg->CheckNo = $checkNo;
                $oblg->FO   = $value["obligation_no"];
                $oblg->save();

                $obli_id = $value["obligation_id"];
            }
            //trans_id
            $ors_list = [];
            $ors_value = $value["items"];
            foreach ($ors_value as $key => $value) {
                $allocation = $value["allocation"];
                $object = $value["object_code"];
                $ppa = $value["ppa"];

                if($value["trans_id"] == 0 || empty($value["trans_id"])){
                    $ors_list = array(
                        'FOTrans' => $obli_id, 
                        'CheckNo' => $checkNo, 
                        'Object' => $object['AccountCode'], 
                        'Amount' => $value['amount'],
                        'Specifications' => $value['description'], 
                        'Allocation' =>  $allocation['AllocationNo'], 
                        'PPA' =>  $ppa['PAPCode'], 
                        'DateEncoded' => $today, 
                    );
                    $transaction = Transaction::insert($ors_list);
                }else{
                    
                    // $transaction = Transaction::find($value["trans_id"]);
                    $transaction = Transaction::where('TransNo', '=', $value["trans_id"])->first()
                    ->update([
                        "FOTrans" => $obli_id,
                        "CheckNo"   => $checkNo,
                        "Object" =>  $object['AccountCode'],
                        "Amount" => $value['amount'],
                        "Specifications" => $value['description'],
                        "Allocation" => $allocation['AllocationNo'],
                        "PPA" => $ppa['PAPCode']] );
                    // $transaction->FOTrans = $obli_id;
                    // $transaction->CheckNo   = $checkNo;
                    // $transaction->Object =  $object['AccountCode'];
                    // $transaction->Amount = $value['amount'];
                    // $transaction->Specifications = $value['description'];
                    // $transaction->Allocation = $allocation['AllocationNo'];
                    // $transaction->PPA = $ppa['PAPCode'];
                    // $transaction->save();
                }
            }
        }
        //save ORS (transaction table)
        return response()->json( array('success' => true) );

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request)
    {
        //
        $CheckNo = $request->input("CheckNo");
        if($CheckNo){
            $deleteCheck = Transaction::where('CheckNo', $CheckNo)->delete();
            $deleteObl = FoTrans::where('CheckNo',$CheckNo)->delete();
            $deleteVoucher = Vouchers::where('CheckNo',$CheckNo)->delete();
        }

        return response()->json( array('success' => true) );
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function deleteObligation(Request $request)
    {
        //
        $obligation_id = $request->input("obligation_id");
        if($obligation_id){
            $deleteTrans = Transaction::where('FOTrans', $obligation_id)->delete();
            $deleteObl = FoTrans::where('FOTrans',$obligation_id)->delete();
        }

        return response()->json( array('success' => true) );
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function deleteTran(Request $request)
    {
        //
        var_dump($request->all());
        $TransNo = $request->input("TransNo");
        if($TransNo){
            $deleteTrans = Transaction::where('TransNo', $TransNo)->delete();
        }

        return response()->json( array('success' => true) );
    }

    public function libraries(){
        //get allocations
        $allocations = Allocations::all();
        $allocations = $allocations->toArray();

        //get Object Codes
        $objectCodes = AccountCodes::all();
        $objectCodes = $objectCodes->toArray();
        
        //get PAP Codes
        $ppaCodes = PapCodes::all();
        $ppaCodes = $ppaCodes->toArray();

        //get accounts
        $accounts = Accounts::all();
        $accounts = $accounts->toArray();

        return response()->json( compact('allocations','objectCodes','ppaCodes','accounts') );
    }
}
