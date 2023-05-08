<?php

namespace App\Http\Controllers;

use App\Models\AccountCodes;
use Illuminate\Http\Request;
use PDF;

class AccountCodesController extends Controller
{
    // /**
    //  * Create a new controller instance.
    //  *
    //  * @return void
    //  */
    public function __construct()
    {
        $this->middleware('auth:api');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
        $you = auth()->user()->id;
        $accountcodes = AccountCodes::all();
        return response()->json( compact('accountcodes', 'you') );
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
        //
        $validatedData = $request->validate([
            'AccountCode'  => 'required|unique:accountcodes|min:1|max:256',
            'AccountTitle' => 'required|min:1|max:256',
        ]);

        $accountcode = new AccountCodes();
        $accountcode->AccountCode = $request->input('AccountCode');
        $accountcode->AccountTitle = $request->input('AccountTitle');
        $accountcode->save();

        return response()->json( ['status' => 'success'] );
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\AccountCodes  $accountCodes
     * @return \Illuminate\Http\Response
     */
    public function show(AccountCodes $accountCodes)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\AccountCodes  $accountCodes
     * @return \Illuminate\Http\Response
     */
    public function edit(AccountCodes $accountCodes)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\AccountCodes  $accountCodes
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, AccountCodes $accountCodes)
    {
        //        
        $validatedData = $request->validate([
            'AccountCode'  => 'required|min:1|max:256',
            'AccountTitle'    => 'required|min:1|max:256'
        ]);

        $accountcode = AccountCodes::find($request->input('accountcode_id'));
        $accountcode->AccountCode  = $request->input('AccountCode');
        $accountcode->AccountTitle = $request->input('AccountTitle');
        $accountcode->save();
        return response()->json( ['status' => 'success'] );
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\AccountCodes  $accountCodes
     * @return \Illuminate\Http\Response
     */
    public function destroy(AccountCodes $accountCodes, $id)
    {
        //
        $accountcode = AccountCodes::find($id);
        if($accountcode){
            $accountcode->delete();
        }
        return response()->json( ['status' => 'success'] );
    }
    
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function generatePDF()
    {
        $accountcodes = AccountCodes::all();
        $data = [
          'title' => 'List of Account Codes',
          'heading' => 'List of Account Codes',
          'accountcodes' => $accountcodes     
        ];
        
        $pdf = PDF::loadView('pdf/accountcodes', $data);
  
        return $pdf->download('accountcodes.pdf');
    }
    
}
