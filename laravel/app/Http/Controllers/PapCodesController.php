<?php

namespace App\Http\Controllers;

use App\Models\PapCodes;
use Illuminate\Http\Request;
use PDF;

class PapCodesController extends Controller
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
        //
        $you = auth()->user()->id;
        $papcodes = PapCodes::all();
        return response()->json( compact('papcodes', 'you') );
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
            'PAPCode'  => 'required|unique:papcodes|min:1|max:256',
            'PAPTitle' => 'required|min:1|max:256',
        ]);

        $papcode = new PapCodes();
        $papcode->PAPCode = $request->input('PAPCode');
        $papcode->PAPTitle = $request->input('PAPTitle');
        $papcode->save();

        return response()->json( ['status' => 'success'] );
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\PapCodes  $papCodes
     * @return \Illuminate\Http\Response
     */
    public function show(PapCodes $papCodes)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\PapCodes  $papCodes
     * @return \Illuminate\Http\Response
     */
    public function edit(PapCodes $papCodes)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\PapCodes  $papCodes
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, PapCodes $papCodes)
    {
        //
        $validatedData = $request->validate([
            'PAPCode'  => 'required|min:1|max:256',
            'PAPTitle'    => 'required|min:1|max:256'
        ]);

        $papcode = PapCodes::find($request->input('papcode_id'));
        $papcode->PAPCode  = $request->input('PAPCode');
        $papcode->PAPTitle = $request->input('PAPTitle');
        $papcode->save();
        return response()->json( ['status' => 'success'] );
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\PapCodes  $papCodes
     * @return \Illuminate\Http\Response
     */
    public function destroy(PapCodes $papCodes,$id)
    {
        //
        $papcode = PapCodes::find($id);
        if($papcode){
            $papcode->delete();
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
        $papcodes = PapCodes::all();
        $data = [
          'title' => 'List of PAP Codes',
          'heading' => 'List of PAP Codes',
          'papcodes' => $papcodes     
        ];
        
        $pdf = PDF::loadView('pdf/papcodes', $data);
  
        return $pdf->download('papcodes.pdf');
    }
}
