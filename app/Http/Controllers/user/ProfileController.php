<?php

namespace App\Http\Controllers\user;

use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class ProfileController extends Controller
{
    use AuthorizesRequests;
    public function Update(Request $request){
        $user = $request->user();
        // $user = User::findOrFail($id);
        $this->authorize('update own profile');

        $validated=$request->validate([
            'name' =>['sometimes', 'string'],
            'no_telp' =>['sometimes', 'string'],
            'email' => ['sometimes', 'email', 'unique:users,email,' . $user->id],
        ]);
        $user->update($validated);
        return response()->json([
            '{+}'=>'user updated',
            'User'=> $user
        ]); 
    }

    public function UserProfileQRScan($uuid){
        $user = User::where('profile_qr', $uuid)->firstOrFail();
        return response()->json([
            'name'=>$user->name,
            'email'=> $user->email,
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
                url("/trash_transaction/{$uuid}")
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
}
