<?php

namespace App\Http\Controllers\Auth;

use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Auth\Events\Validated;
use Illuminate\Support\ValidatedInput;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Password;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function register(Request $request){
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'max:255', 'unique:users'],
            'no_kk' => ['required'],
            'password' => ['required', 'confirmed', 'min:10'],
        ]);
        $uuid = Str::uuid()->toString();
        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'no_kk' => $validated['no_kk'],
            'password' => Hash::make($validated['password']),
            'profile_qr' => $uuid,
            'trash_transaction_qr' => $uuid
            // 'trash_transaction_qr'=>$uuid
            ]);      
        $user->assignRole('user');
        $user->sendEmailVerificationNotification(); 
        
        $trashQR = "trash_transaction_qr/users/{$uuid}.svg";
        Storage::disk('public')->put(
            $trashQR,
            QrCode::format('svg')
            ->size(200)
            ->generate(
                url("/api/trash-transaction/{$uuid}")
            )
        );
        $Profile_qrPath = "qrcodes/users/{$uuid}.svg";
        Storage::disk('public')->put(
            $Profile_qrPath,
            QrCode::format('svg')
            ->size(200)
            ->generate(
                url("/api/profile/{$uuid}")
            )
        );
        $user->update([
            'profile_qr_path'=>$Profile_qrPath,  
            'transaction_qr_path' => $trashQR       
        ]);  
        $token = $user->createToken('token')->plainTextToken;
        return response()->json([
            'message' => 'User registered!',
            'user'=> $user,
            'Token Type' => 'Bearer',
            'token'=> $token,
        ], 201);
    }

    public function login(Request $request){
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password'=> ['required'],
        ]);
        if(!Auth::attempt($credentials)){
            throw ValidationException::withMessages([
              ['Invalid Credentials!'],
            ]);
        }
        $user = $request->user();
        $token = $user->createToken('token')->plainTextToken;
        return response()->json([
            'token' => $token,
            'user'=>[
                'name' => $user->name,
                'email' => $user->email,
                'roles' => $user->getRoleNames(),
                'profile_qr'=>$user->profile_qr,
                'points'=>$user->points,
                'permissions' => $user->getAllPermissions()->pluck('name'),
            ]
            ]);
    }

    public function logout(Request $request){
        $request->user()->tokens()->delete();
        return response()->json([
            '{+}' => 'User Logged Out!'
        ],200);
    }
}
