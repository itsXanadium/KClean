<?php

namespace App\Http\Controllers\user;

use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Hash;

class ProfileController extends Controller
{
    use AuthorizesRequests;
    public function Update(Request $request){
        $user = $request->user();
        // $user = User::findOrFail($id);
        // $this->authorize('update own profile', $user);

        $validated=$request->validate([
            'name' =>['sometimes', 'string'],
            'no_kk' => ['sometimes', 'string'],
            'no_telp' => ['sometimes', 'string'],
            'email' => ['sometimes', 'email', 'unique:users,email,' . $user -> id],
            'password' => ['sometimes', 'confirmed', 'min:10'],
            'avatar' => ['sometimes', 'string'],
        ]);
        if(isset($validated['password'])){
            $validated['password'] = Hash::make($validated['password']);
        }
        $user->update($validated);
        return response()->json([
            '{+}'=>'user updated',
            'User'=> $user
        ]); 
    }

    public function UserProfileQRScan($uuid){
        $user = User::where('profile_qr', $uuid)
                    ->orWhere('trash_transaction_qr', $uuid)
                    ->firstOrFail();
        
        return response()->json([
            'id' => $user->id, // Added ID for frontend display
            'name'=>$user->name,
            'email'=> $user->email,
            'avatar'=> $user->avatar, // Returning avatar for the UI
        ]);
    }
    public function GenerateTrashTransactionQR(Request $request){
        $user =$request->user();
        $uuid = Str::uuid()->toString();
        // $qr= User::create([
        //     'trash_transaction_qr'=>$uuid
        // ]);
        $Trash_transaction_qrPath = "trash_transaction_qr/users/{$uuid}.svg";
        Storage::disk('public')->put(
            $Trash_transaction_qrPath,
            QrCode::format('svg')
            ->size(200)
            ->generate(
                url("/trash-transaction/{$uuid}")
            )
        );
        $user->update([
            'trash_transaction_qr'=>$uuid,
            'transaction_qr_path'=>$Trash_transaction_qrPath
        ]);
        return response()->json([
            '{+}'=> 'Trash Transaction QR Generated!',
            'QR' =>  $Trash_transaction_qrPath
            ]);
    }

    public function fetchUserData(Request $request){
        $user = $request->user();
        $userData = User::where('id', $user->id)->first();
        return response()->json([
            "Data"=> $userData
        ],200 );        
    }

    public function fetchUserPoint(Request $request){
        $user = $request->user();

        $userPoints = User::where('id', $user->id)->get('points');

        return response()->json([
            "User_Points" => $userPoints
        ],200);
    }
}
