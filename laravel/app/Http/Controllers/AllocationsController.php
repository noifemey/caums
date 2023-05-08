<?php

namespace App\Http\Controllers;

use App\Models\Allocations;
use Illuminate\Http\Request;
use DB;
use PDF;
use DateTime;
use App\Models\Accounts;

class AllocationsController extends Controller
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
        $you = auth()->user()->id;
        $allocations = Allocations::all();
        //get accounts
        $accounts = Accounts::all();
        $accounts = $accounts->toArray();
        return response()->json( compact('allocations', 'you',"accounts") );
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
            'AllocationNo'  => 'required|unique:allocations|min:1|max:256',
            'MonthYear'     => 'required',
            'AccountNo'     => 'required|min:1|max:256',
            'Date'          => 'required|min:1|max:256',
            'Reference'     => 'required|min:1|max:256',
            'Purpose'       => 'required|min:1|max:256',
            'CAIssued'      => 'required|min:1|max:256',   
            'CAReceived'    => 'required|min:1|max:256'
        ]);

        $allocation = new Allocations();
        $allocation->AllocationNo = $request->input('AllocationNo');
        $allocation->MonthYear = $request->input('MonthYear');
        $allocation->AccountNo = $request->input('AccountNo');
        $allocation->Date = $request->input('Date');
        $allocation->Reference = $request->input('Reference');
        $allocation->Purpose = $request->input('Purpose');
        $allocation->CAIssued = $request->input('CAIssued');
        $allocation->CAReceived = $request->input('CAReceived');
        $allocation->save();

        return response()->json( ['status' => 'success'] );
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Allocations  $allocations
     * @return \Illuminate\Http\Response
     */
    public function show(Allocations $allocations)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Allocations  $allocations
     * @return \Illuminate\Http\Response
     */
    public function edit(Allocations $allocations)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Allocations  $allocations
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Allocations $allocations)
    {
        $validatedData = $request->validate([
            'AllocationNo'  => 'required|min:1|max:256',
            'MonthYear'     => 'required',
            'AccountNo'     => 'required|min:1|max:256',
            'Date'          => 'required|min:1|max:256',
            'Reference'     => 'required|min:1|max:256',
            'Purpose'       => 'required|min:1|max:256',
            'CAIssued'      => 'required|min:1|max:256',   
            'CAReceived'    => 'required|min:1|max:256'
        ]);

        $allocation                 = Allocations::find($request->input('allocation_id'));
        $allocation->AllocationNo   = $request->input('AllocationNo');
        $allocation->MonthYear      = $request->input('MonthYear');
        $allocation->AccountNo      = $request->input('AccountNo');
        $allocation->Date           = $request->input('Date');
        $allocation->Reference      = $request->input('Reference');
        $allocation->Purpose        = $request->input('Purpose');
        $allocation->CAIssued       = $request->input('CAIssued');
        $allocation->CAReceived     = $request->input('CAReceived');
        $allocation->save();

        return response()->json( ['status' => 'success'] );
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Allocations  $allocations
     * @return \Illuminate\Http\Response
     */
    public function destroy(Allocations $allocations,$id)
    {
        //
        $allocation = Allocations::find($id);
        if($allocation){
            $allocation->delete();
        }
        return response()->json( ['status' => 'success'] );
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function generatePDF(Request $request)
    {
        $allocation = Allocations::all();
        $data = [
          'title' => 'List of Allocations',
          'heading' => 'List of Allocations',
          'allocations' => $allocation     
        ];
        
        $pdf = PDF::loadView('pdf/allocations', $data);
  
        return $pdf->download('allocations.pdf');
    }

}
