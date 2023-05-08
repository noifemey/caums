<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Menus\GetSidebarMenu;

class MenuController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        // try {
        //     $user = auth()->user();
        //     if($user && !empty($user)){
        //         $roles =  $user->menuroles;
        //     }else{
        //         $roles = '';
        //     }
        // } catch (Exception $e) {
        //     $roles = '';
        // }   
        // if($request->has('menu')){
        //     $menuName = $request->input('menu');
        // }else{
        //     $menuName = 'sidebar menu';
        // }
        // $menus = new GetSidebarMenu();
        // return response()->json( $menus->get( $roles, $menuName ) );

        try {
            $user = auth()->user();
            if($user && !empty($user)){
                $roles =  $user->menuroles;
            }else{
                $roles = '';
            }
        } catch (Exception $e) {
            $roles = '';
        }   
        //$menu = [];

        if($request->has('menu')){
            $menus = array(
                array("href"=> "/","icon"=> "cil-speedometer","name"=> "Dashboard","slug"=> "link"),
                array("href"=> "/login", "icon"=> "cil-account-logout", "name"=> "Login","slug"=> "link")
            );
        }else{
            $menus = [
                array("href"=> "/","icon"=> "cil-speedometer","name"=> "Dashboard","slug"=> "link"),
                array("href"=> "/","icon"=> "cil-speedometer","name"=> "Reports","slug"=> "title"),
                array("href"=> "/reports/allocation-summary","icon"=> "cil-speedometer","name"=> "NCA and NTA Received","slug"=> "link"),
                array("href"=> "/reports/allocation-status","icon"=> "cil-speedometer","name"=> "Status of Cash Allocations, Utilized and Balances","slug"=> "link"),
                // array("href"=> "/","icon"=> "cil-speedometer","name"=> "Statement of Cash Allocations, Utilized and Balances","slug"=> "link"),
                array("href"=> "/reports/pap_statement","icon"=> "cil-speedometer","name"=> "Statement of utilization as per P.A.P for F.Y","slug"=> "link"),
                array("href"=> "/reports/pap","icon"=> "cil-speedometer","name"=> "Program, Activity and Project","slug"=> "link"),
                array("href"=> "/reports/rci","icon"=> "cil-speedometer","name"=> "Check Issued and Cancelled","slug"=> "link"),
                array("href"=> "/reports/cdr","icon"=> "cil-speedometer","name"=> "Check Disbursement Record","slug"=> "link")
            ];
        }

        if($roles == ''){
            if(!$request->has('menu')){
                $insert= [array("href"=> "/login", "icon"=> "cil-account-logout", "name"=> "Login","slug"=> "link")];
                $this->array_insert($menus,1,$insert);
            }
        }else{
            if($request->has('menu')){
                $menus = array(
                    array("href"=> "/","icon"=> "cil-speedometer","name"=> "Dashboard","slug"=> "link")
                );
            }else{
                $elements = array(
                    array("href"=> "/accounts","icon"=> "cil-puzzle","name"=> "Fund Account Entry","slug"=> "link"),
                    array("href"=> "/allocations","icon"=> "cil-puzzle","name"=> "Allocation","slug"=> "link"),
                    array("href"=> "/accountcodes","icon"=> "cil-puzzle","name"=> "Account Code","slug"=> "link"),
                    array("href"=> "/papcodes","icon"=> "cil-puzzle","name"=> "Prog/Activity/Proj.","slug"=> "link"),
                    // array("href"=> "/","icon"=> "cil-puzzle","name"=> "Unreleased Check Issued","slug"=> "link"),
                    // array("href"=> "/","icon"=> "cil-puzzle","name"=> "Staled Check","slug"=> "link"),
                );         
                $insert= [
                    array("href"=> "/vouchers", "icon"=> "cil-speedometer", "name"=> "Check Details","slug"=> "link"),
                    array("href"=> "/vouchers/transactions", "icon"=> "cil-speedometer", "name"=> "Check Data Entry","slug"=> "link"),
                    array("href"=> "/databaseEntry", "icon"=> "cil-speedometer", "name"=> "Database Entry","slug"=> "dropdown","elements"=>$elements),
                    // array("href"=> "/","icon"=> "cil-speedometer","name"=> "Payee/Creditor Inquiry","slug"=> "link"),
                ];
                $this->array_insert($menus,1,$insert);

                $roles = explode(",", $roles);

                if(in_array("admin", $roles)){ 
                    //var_dump($roles);
                    $settings = array(
                        array("href"=> "/users","icon"=> "","name"=> "Users","slug"=> "link"),
                        array("href"=> "/email","icon"=> "","name"=> "Email","slug"=> "link"),
                    );    
                    $insert = [array("href"=> "/settings","icon"=> "cil-puzzle","name"=> "Settings","slug"=> "dropdown","elements"=>$settings)];
                    //$menus = $this->($menus,1,$insert);
                    $pos = count($menus);
                    $this->array_insert($menus,$pos,$insert);
                    //array_push($menus, $insert);
                }

            }
        }

        return response()->json( $menus );
    }

    function array_insert(&$array, $position, $insert)
    {
        //return $array = array_merge(array_splice($array, max(0, $index - 1)), array($value), $array);

        if (is_int($position)) {
            array_splice($array, $position, 0, $insert);
        } else {
            $pos   = array_search($position, array_keys($array));
            $array = array_merge(
                array_slice($array, 0, $pos),
                $insert,
                array_slice($array, $pos)
            );
        }
    }

}

