<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\User;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Support\Facades\Validator;
use Ramsey\Uuid\Uuid;
use Illuminate\Http\Request;
use JWTAuth;
class AuthController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Register Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles the registration of new users as well as their
    | validation and creation. By default this controller uses a trait to
    | provide this functionality without requiring any additional code.
    |
    */

    use RegistersUsers;

    /**
     * Where to redirect users after registration.
     *
     * @var string
     */
    protected $redirectTo = '/home';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        // $this->middleware('guest');
        $this->middleware('auth:api', ['except' => ['login','register']]);
    }

    /**
     * Get a validator for an incoming registration request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    // protected function validator(array $data)
    // {
    //     return Validator::make($data, [
    //         'nickname' => 'required|string|max:255',
    //         'name' => 'required|string|max:255',
    //         'gender_id' => 'required|numeric|in:1,2',
    //         'email' => 'required|string|email|max:255|unique:users',
    //         'password' => 'required|string|min:6|confirmed',
    //     ]);
    // }

    /**
     * Create a new user instance after a valid registration.
     *
     * @param  array  $data
     * @return User
     */
    protected function register(Request $data)
    {
        // dd($data->all()['nickname']);

        $data = $data->all() ;

        $user = User::create([
            'id' => Uuid::uuid4()->toString(),
            'nickname' => $data['nickname'],
            'name' => $data['name'],
        //    'phone' => $data['mobile'],
            'gender_id' => $data['gender_id'],
            'email' => $data['email'],
            'password' => bcrypt($data['password']),
        ]);

        $user->manager_id = $user->id;
        $user->save();

        $credentials = ['email'=>$data['email'], 'password'=> $data['password']];

        $token = $this->login($credentials);
       // $token = JWTAuth::attempt($credentials);

        $success['token'] = $token;
        return response()->json([
          'success' => true,
          'password' =>  $data['password'],
          'token' => $success,
          'user' => $user
        ]);
    }

    
    /**
     * Create a new user instance after a valid registration.
     *
     * @param  array  $data
     * @return User
     */
    protected function loginh(Request $data)
    {
        dd($data->all());

        $data = $data->all() ;

        $user = User::first([
            'email' => $data['email'],
            'password' => $data['password'],
            'gender_id' => $data['gender_id'],
            'email' => $data['email'],
            'password' => bcrypt($data['password']),
        ]);

        $user->manager_id = $user->id;
        $user->save();

        return $user;
    }
    

    // public function login(Request $data) {
    //     /// validation 
    //     // dd( request(['email', 'password']));

    //     $credentials = request(['email', 'password']);
    //     if (!$token = auth('api')->attempt($credentials)) {
    //         return response()->json(['error' => 'Unauthorized'], 401);
    //     }
    //     return response()->json([
    //         'token' => $token, // Token
    //         'expires' => auth('api')->factory()->getTTL() * 60, // Expiration
    //     ]);
    // }

    /**
     * Get a JWT via given credentials.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function login($credentials =null)
    {
        $token = null;
        if(empty($credentials)){
            $credentials = request(['email', 'password']);
            if (! $token = auth('api')->attempt($credentials)) {
                return response()->json(['error' => 'Unauthorized'], 401);
            }
    
            return $this->respondWithToken($token);
        }

        if (! $token = auth('api')->attempt($credentials)) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        return $token;


    }

    /**
     * Get the authenticated User.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function me()
    {
        return response()->json(auth('api')->user());
    }

    /**
     * Log the user out (Invalidate the token).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout()
    {
        auth('api')->logout();

        return response()->json(['message' => 'Successfully logged out']);
    }

    /**
     * Refresh a token.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function refresh()
    {
        return $this->respondWithToken(auth('api')->refresh());
    }

    /**
     * Get the token array structure.
     *
     * @param  string $token
     *
     * @return \Illuminate\Http\JsonResponse
     */
    protected function respondWithToken($token)
    {
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth('api')->factory()->getTTL() * 60
        ]);
    }
}
