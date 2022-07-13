<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class RegisterController extends Controller
{
    public function register(Request $request)
    {
        $data = $this->validateRegisterUser($request);

        $data['password'] = Hash::make($data['password']);

        $user = User::create($data);

        $token = $user->createToken(\Str::random(20))->accessToken;

        return response()->json([
            'token' => $token
        ], 200);
    }

    private function validateRegisterUser(Request $request)
    {
        return $request->validate( [
            'name' => 'required', 'string', 'max:255',
            'email' => 'required', 'email' , 'max:255', 'unique:users,email',
            'password' => 'required', 'min:8', 'confirmed', 'string'
        ] );
    }



}
