<?php

namespace App\Http\Controllers;

use App\Models\trash_transaction;
use App\Models\User;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class TrashTransactionController extends Controller
{
    //
    use AuthorizesRequests;
    public function ScanUser($uuid){
        $this->authorize('scan user qr');
        $user = User::where('trash_transaction_qr', $uuid)->firstOrFail();
        return response()->json([
            'user'=> $user->trash_transaction_qr
        ]);
    }

    public function TrashTransaction(Request $request, $uuid){
        $this->authorize('create trash transactions');
        $user = User::where('trash_transaction_qr', $uuid)->firstOrFail();
        $uuid = Str::uuid()->toString();
        $validated = $request->validate([
            'trash_type' => 'sometimes',
            'trash_weight' => 'sometimes',
    ]);
    $trash_transaction = trash_transaction::create([
        'trash_transaction_id' => $uuid,
        'trash_type' => $validated['trash_type'],
        'trash_weight'=> $validated['trash_weight'],
        'points' => $validated['trash_weight' * 10],
        'user_id'=> $user->uuid,
        'petugas_id' => Auth::user()->id,
    ]);
    return response()->json([
        '{+}' => 'Transaction Success',
        'Data' => $trash_transaction
    ]);
    }
}
