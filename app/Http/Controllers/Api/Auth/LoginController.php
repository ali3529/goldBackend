<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class LoginController extends Controller
{

    public function login(Request $request)
    {
        $tok="eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9.eyJhdWQiOiIxIiwianRpIjoiYWRiMWYxNmQxZTljNmYyYjk4ZTk1MTc4NjBiZTVjMmIzNDg3ZjFjYzNmNDQ4M2M4ZjIyNTcwNWVlYTllZTNkNjUxY2JiYTA0MmYxODEzM2QiLCJpYXQiOjE2MzY0NzE5MzIuMTQ5OTk1LCJuYmYiOjE2MzY0NzE5MzIuMTUwMDA1LCJleHAiOjE2NjgwMDc5MzIuMTQzNzc4LCJzdWIiOiIxMCIsInNjb3BlcyI6W119.G4fuuwsvQp-dVPgq5AYjeqiFmJ_O8jXPBMdv6Bp5R0IzFFp8Cuqe4b8OxzCFAfWOfQECeJBKHLncgj-M4SGG7eVLLEcOe4BvnQXDWjSeK3ZxmdG4m8Qh9lGwbCRaTFUGA8hgfo-QXcQXd7ISRveWCA4ScKcO6YcXWS2d-JxEQ2pwIV3lY0jFowdJlYU3WOIS9jbpHPUNLZ3T53E-fVgZVTUmxtByQQFQbhxIgICbZroiS_6G7ve_oKxXVnqZThWeLBWf76FyERpDqFnoJfZ3meAdTmMYKZVr5bqK98yExR6BZ0fnQOEenm1sdtK1vbaTrL4er-7GV9CoIG4gKrqfQiw5csHI1sGnn5nVquFAvORC-4CMviXggb_61tDC43KW3qjaZc9P_TizEdBS1KFi6i-aFzSzV3YZX9Hyce0fAVZ7LhuULnolcueFIGgBoc4uAfVLpgtSZ7C8heMsrQFTYqrT_71sQurAGs7Azoj43t6AZQ5WcJysg6uxJwEhWhaSXsCER0GMkHc0Ne_17mP8YL4KpHzZ0QCeRAX-FdVb3h6VK2-ACH1Y2clwBXzukHbYRx-WFnJBW8hRIMSZgebwpmuOavqnp4C-R0-qu_piA9tYWBP_7ap0_6XrflLoSDTkJM2WmXrVTN3PF9bMdHYTSrm0gA-FuXmBn-K3VS9HMaA";

        $validator = $this->validateLoginRequest($request);

        if($validator->fails())
        {
            return response()->json( [
                'errors' => $validator->errors()->all()
            ] , 200);
        }

        $user = User::where('email', $request->email)->first();

        if($user)
        {
            if(Auth::attempt(['email' => $request->email, 'password' => $request->password])){
//                $user = Auth::user();
//                $token = $user->createToken(\Str::random(20))->accessToken;
                $date=request()->all();
//                $token=mt_rand(10000000000, 99999999999);
                $token=mt_rand(1000000000, 9999999999);
                User::where('email',$date['email'])->update(array(
                    'is_login'=>true,
                    'auth_id'=>abs($token)
                ));
                $get_token = User::where('email', $request->email)->first();
                return response()->json([
                    'token' => $get_token->auth_id,
                    'message' => 'user logged in successfully!',
                    'isLogin' => Auth::check(),
                ], 200);
            }
            else
            {
                return response()->json( [
                    'errors' => 'password is not matched!'
                ] );
            }
        }
        else
        {
            return response()->json( [
                'errors' => 'no user found with this email!'
            ] );
        }
    }

    private function validateLoginRequest(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => ['required', 'email', 'max:255'],
            'password' => ['required', 'min:8', 'string']
        ]);

        return $validator;
    }

    public function logout(Request $request)
    {
        //$token= $request['token'];
//        $token = $request->user()->token();
//
//        $token->revoke();
        $date=request()->all();

        User::where('email',$date['email'])->update(array(
            'is_login'=>false
        ));
        return response()->json( [
            'message' => 'User logged out successfully!',
//            'token' => $token
        ], 200 );
    }
}
