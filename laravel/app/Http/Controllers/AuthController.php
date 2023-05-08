<?php

namespace App\Http\Controllers;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Http;

class AuthController extends Controller
{ 
    /**
     * Create a new AuthController instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['login', 'register']]);
    }
  
    /**
     * Register new user.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function register(Request $request){
        $validate = Validator::make($request->all(), [
            'name'      => 'required',
            'email'     => 'required|email|unique:users',
            'password'  => 'required|min:4|confirmed',
        ]);        
        if ($validate->fails()){
            return response()->json([
                'status' => 'error',
                'errors' => $validate->errors()
            ], 422);
        }        
        $user = new User;
        $user->name = $request->name;
        $user->email = $request->email;
        $user->password = bcrypt($request->password);
        $user->status = 'Active';
        $user->save();       
        return response()->json(['status' => 'success'], 200);
    } 

    /**
     * Get a JWT via given credentials.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(Request $request)
    {
        //Authenticate through EPIC
        $response = Http::asForm()->post('http://172.26.144.29/hrmis/Myapi/checkcredentials', [
            'username' => $request->input('username'),
            'password' => $request->input('password'),
        ]);
        $response = json_decode($response->body());

        if($response->success){        
            $userDetails =($response->user);
            $credentials = request(['username', 'password']);

            //Check if user already exist
            $user = User::where('username', '=', $request["username"])->first();

            if($user){
                if (! $token = auth()->attempt($credentials)) {
                    $user->username = $request->input('username');
                    $user->password   = bcrypt($request->input('password'));
                    $user->save();
                }else{
                    return $this->respondWithToken($token, $request->username);
                }
            }else{
                //Store user
                $user = new User;
                $user->name = $userDetails->fullname;
                $user->username = $userDetails->username;
                $user->email = $userDetails->username;
                $user->password = bcrypt($request->input('password'));
                $user->status = 'Active';
                $user->save();    
                
                $user->assignRole('user');

                //if cash role = cash else user

                //
            }
            $token = auth()->attempt($credentials);
            return $this->respondWithToken($token, $request->username);

        }else{
            return response()->json(['error' => 'Unauthorized'], 401);
        }
    }

    /**
     * Log the user out (Invalidate the token).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout()
    {
        auth()->logout();

        return response()->json(['message' => 'Successfully logged out']);
    }

    /**
     * Refresh a token.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function refresh()
    {
        return $this->respondWithToken(auth()->refresh());
    }

    /**
     * Get the token array structure.
     *
     * @param  string $token
     *
     * @return \Illuminate\Http\JsonResponse
     */
    protected function respondWithToken($token, $username)
    {
        $user = User::select('menuroles as roles')->where('username', '=', $username)->first();

        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth()->factory()->getTTL() * 60,
            'roles' => $user->roles,
        ]);
    }
}