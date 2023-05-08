<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Accounts;
use PDF;

class AccountsController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
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
        $you = auth()->user()->getPermissionsViaRoles();
        $accounts = Accounts::all();
        return response()->json( compact('accounts', 'you') );
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'AccountNumber'  => 'required|unique:accounts|min:1|max:256',
            'AccountName'    => 'required|min:1|max:256',
            'BankName'       => 'required|min:1|max:256'
        ]);

        $account = new Accounts();
        $account->AccountNumber = $request->input('AccountNumber');
        $account->AccountName = $request->input('AccountName');
        $account->BankName = $request->input('BankName');
        $account->save();

        return response()->json( ['status' => 'success'] );
    }

    public function update(Request $request)
    {
        $validatedData = $request->validate([
            'AccountNumber'  => 'required|min:1|max:256',
            'AccountName'    => 'required|min:1|max:256',
            'BankName'       => 'required|min:1|max:256'
        ]);

        $account = Accounts::find($request->input('account_id'));
        $account->AccountNumber  = $request->input('AccountNumber');
        $account->AccountName    = $request->input('AccountName');
        $account->BankName       = $request->input('BankName');
        $account->save();
        return response()->json( ['status' => 'success'] );
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $account = Accounts::find($id);
        if($account){
            $account->delete();
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
        $accounts = Accounts::all();
        $data = [
          'title' => 'List of Fund Accounts',
          'heading' => 'List of Fund Accounts',
          'accounts' => $accounts     
        ];
        
        $pdf = PDF::loadView('pdf/accounts', $data);
  
        return $pdf->download('accounts.pdf');
    }

}
