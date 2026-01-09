<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function login(Request $request){
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password'=> ['required'],
        ]);
        if(!Auth::attempt($credentials)){
            throw ValidationException::withMessages([
                'email'=>['Invalid Credentials!'],
            ]);
        }
        $user = $request->user();
        $token = $user->createToken('token')->plainTextToken;
        return response()->json([
            'token' => $token,
            'user'=>[
                
                // 'id'=> $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'roles' => $user->getRoleNames(),
                'profile_qr'=>$user->profile_qr,
                'points'=>$user->points,
                'permissions' => $user->getAllPermissions()->pluck('name'),
            ]
            ]);
    }
}
