<?php

namespace App\Http\Controllers\Admin;

use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Testing\Fluent\Concerns\Has;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class UserManagementController extends Controller
{
    use AuthorizesRequests;
    public function CreateUser(Request $request, string $role){
        if(!in_array($role, ['umkm', 'petugas'])){
            abort(404, 'Role not exist');
        }
        // abort_unless(Auth()->user()->can('manage users'), 403);
        $this->authorize('manage roles');
       $validated = $request ->validate([
           'name' => ['required', 'string'],
           'email' => ['required', 'email', 'unique:users'],
           'password' => ['required', 'min:10'],
        //    'role' => ['required', 'in:umkm,petugas'],
        ]);
        //Generate Unique UUID
        $uuid = Str::uuid()->toString();
        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'profile_qr' => $uuid,
        ]);
        $user->sendEmailVerificationNotification();
        //Store qr  
        $qrPath = "qrcodes/users/{$uuid}.svg";
        Storage::disk('public')->put(
            $qrPath,
            QrCode::format('svg')
            ->size(200)
            ->generate("api/profile/{$uuid}")
        );
        $user->update([
            'qr_code_path'=>$qrPath
        ]);
        $user->assignRole($role);
        return response()->json([
            '{+}'=>ucfirst($role). 'Created',
            'user' => $user
        ],201);
    }

     public function fetchUser(Request $request){   
        $this->authorize('see user');
        $user = $request->user();
        $userData = User::all()->filter(function ($user){
            return !$user->hasRole('super-admin');
        })->values();
        return response()->json([
            "Data"=> $userData
        ],200 );        
    }

    public function editUser(Request $request, $id){
        $user = $request->user();
        $this->authorize('manage users');
        $validated = $request->validate([
            'name' =>['sometimes', 'string'],
            'no_kk' => ['sometimes', 'string'],
            'no_telp' => ['sometimes', 'string'],
            'email' => ['sometimes', 'email', 'unique:users,email,' . $user -> id],
            'password' => ['sometimes', 'string', 'min:10'],
        ]);
        $editedUser = User::findOrFail($id);
        if (isset($validated['password'])){
            $validated['password'] = Hash::make($validated['password']);
        }
        $editedUser -> update($validated);
    return response()->json([
        '{+}' => 'User Updated!',
        'Updated Data' => $editedUser
    ],200);
    }

    public function deleteUser(Request $request, $id){
        $user = $request->user();
        $this->authorize('manage users');
        $deleteUser = User::findOrFail($id)->delete();
        return response()->json([
            '{-}' => "user deleted!",
            'User' => $deleteUser
        ]);
    }
}
