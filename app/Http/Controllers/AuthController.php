<?php

namespace App\Http\Controllers;
use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Tymon\JWTAuth\Facades\JWTAuth;
class AuthController extends Controller
{
    public function __construct()
    {
        $this->middleware('jwt.auth', ['except' => ['login', 'register']]);
    }
    public function login(Request $request)
    {
        try{
             $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);
        $credentials = $request->only('email', 'password');
        $token = JWTAuth::attempt($credentials);

        if (!$token) {
            return response()->json([
                'message' => 'Unauthorized',
            ], 401);
        }
            JWTAuth::setToken($token);
            $user = JWTAuth::user();
            $auth = User::where('id', $user->id)->first();

            //get user's role
            $roles = $auth->getRoleNames();
            $auth->Roles = $roles;

            //get all user's permissions
            $permissions = $auth->getAllPermissions()->pluck('name');
            $auth->Permissions = $permissions;

            $auth->token = $token;
            $data = $auth->makeHidden('permissions', 'roles')->toArray();

            return response()->json([
                'message' => 'You are login successfully',
                'status' => 'success',
                'user' => $data
            ], 200, [], JSON_NUMERIC_CHECK);
    }
    catch (\Exception $e){
        return response()->json([
           'message' => $e->getMessage()
        ], 500);
    }

    }


   public function register(Request $request)
{
   try{
     $validatedData = $request->validate([
        'username' => 'required|string|max:255',
        'email' => 'required|string|email|max:255|unique:users',
        'password' => 'required|string|min:6|confirmed',
    ]);

    $user = User::create([
        'username' => $validatedData['username'],
        'email' => $validatedData['email'],
        'password' => Hash::make($validatedData['password']),
    ]);
    // $user->syncPermissions(['book-list']);
    $user->assignRole('user');
    //get user's role
    $roles = $user->getRoleNames();
    $user->Roles = $roles;

    //get all user's permissions
    $permissions = $user->getAllPermissions()->pluck('name');
    $user->Permissions = $permissions;

    // $user->token = JWTAuth::fromUser($user);
    $data = $user->makeHidden('permissions', 'roles')->toArray();

    return response()->json([
       'message' => 'User created successfully',
        'user' => $data
    ], 201, [], JSON_NUMERIC_CHECK);
   }
   catch (\Exception $e){
       return response()->json([
          'message' => $e->getMessage()
       ], 500);
   }
}

     public function logout(Request $request)
    {
        try {
            JWTAuth::invalidate(JWTAuth::parseToken());
            return response()->json(['message' => 'Successfully logged out']);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    public function refresh()
    {
        return response()->json([
            'user' => JWTAuth::user(),
            'token' => JWTAuth::refresh(),
        ]);
    }

    public function me(){
        //get_current_user

        try{
                    $user = JWTAuth::user();
        $auth = User::where('id', $user->id)->first();
            //get user's role
            $roles = $auth->getRoleNames();
            $auth->Roles = $roles;
            //get all user's permissions
            $permissions = $auth->getAllPermissions()->pluck('name');
            $auth->Permissions = $permissions;
            $data = $auth->makeHidden('permissions', 'roles')->toArray();

            return response()->json([
                'message' => 'Successfully',
                'user' => $data
            ], 200, [], JSON_NUMERIC_CHECK);

        }
        catch (\Exception $e){
            return response()->json([
               'message' => $e->getMessage()
            ], 500);
        }
    }
}
