<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    /**
     * Create a new AuthController instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['login']]);
    }


    public function reset_password(Request $request)
    {

        $validate = $request->validate([
            'old_password' => 'required',
            'new_password' => 'required'
        ]);


        $authUser = Auth::user();
        $user = User::where('id', $authUser->id)->first();
        $user->makeVisible(['password']);
       
        if (Hash::check($request->old_password, $authUser->password)) {

            $user->password = Hash::make($request->new_password);
            $user->save();
            auth()->logout();
            return response()->json(['message' => 'reset success. user logged out']);
        }

        return response()->json(['message' => 'old password didnt match']);
    }

    public function check(Request $request)
    {
        $authUser = Auth::user();
        $pass = 'user123';
        // dd($pass);

        if (Hash::check($pass, $authUser->password)) {
            return response()->json(['message' => 'The password is still using Default.'], 422);
        }

        return response()->json(['message' => 'aman'], 200);
    }


    public function register(Request $request)
    {
        // return response()->json(['hash' => Hash::make('user123')]);
        $request['password'] = Hash::make($request->password);

        $user = User::create($request->all());
        if ($user) {
            return response()->json(['message' => 'Okee udah buat']);
        }
    }


    /**
     * Get a JWT via given credentials.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function login()
    {
        $credentials = request(['email', 'password']);

        // dd(Hash::make($credentials['password']), $credentials['password']);

        if (!$token = auth()->attempt($credentials)) {
            return response()->json(['error' => 'Unauthorized', Hash::make('user123')], 401);
        }

        return $this->respondWithToken($token);
    }

    /**
     * Get the authenticated User.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function me()
    {
        return response()->json(auth()->user());
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
    protected function respondWithToken($token)
    {
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth()->factory()->getTTL() * 24 * 60
        ]);
    }
}
