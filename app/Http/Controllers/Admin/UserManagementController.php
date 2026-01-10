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
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class UserManagementController extends Controller
{
    use AuthorizesRequests;
    public function CreateUser(Request $request, string $role){
        if(!in_array($role, ['umkm', 'petugas'])){
            abort(404, 'Role not exist');
        }

        // abort_unless(Auth()->user()->can('manage users'), 403);
        $this->authorize('manage users');
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
            ->generate("USER:{$uuid}")
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
}
