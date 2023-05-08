<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Models\Allocations;
use App\Models\Vouchers;
use App\Models\AccountCodes;
use App\Models\PapCodes;
use App\Models\Accounts;
use Illuminate\Http\Request;
use DB;
use DateTime;

class LibraryController extends Controller
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

    public function papcodes(Request $request) {

    	$papcodes = PapCodes::query();

        $papcodes->when($request->input('q'), function ($query, $input) {
            $query->where('PAPCode', "%$input%")->orWhere('PAPTitle', "%$input%");
        });

        $papcodes = $papcodes->get();

        return response()->json( compact('papcodes') );

        //return PapResource::collection($pap);
    }

    public function acountcodes(Request $request) {

    	$accountcodes = AccountCodes::query();

        $accountcodes->when($request->input('q'), function ($query, $input) {
            $query->where('AccountCode', "%$input%")->orWhere('AccountTitle', "%$input%");
        });

        $accountcodes = $accountcodes->get();

        return response()->json( compact('accountcodes') );

        //return PapResource::collection($pap);
    }
    
    public function accounts(Request $request) {

    	$accounts = Accounts::query();

        $accounts->when($request->input('q'), function ($query, $input) {
            $query->where('AccountNumber', "%$input%")->orWhere('AccountName', "%$input%");
        });

        $accounts = $accounts->get();

        return response()->json( compact('accounts') );

        //return PapResource::collection($pap);
    }

    
    public function allocations(Request $request) {

    	$allocations = Allocations::query();

        $allocations->when($request->input('q'), function ($query, $input) {
            $query->where('AllocationNo', "%$input%");
        });

        $allocations = $allocations->get();

        return response()->json( compact('allocations') );

        //return PapResource::collection($pap);
    }
}
