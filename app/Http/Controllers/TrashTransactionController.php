<?php

namespace App\Http\Controllers;

use App\Models\trash_transaction;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Container\Attributes\DB;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB as FacadesDB;
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
        $validated = $request->validate([
            'trash_type' => 'required',
            'trash_weight' => 'required',
            // 'trash_transaction_qr'=>'required'
    ]);
        // $uuid = Str::uuid()->toString();
        $user = User::where('trash_transaction_qr', $uuid)->firstOrFail();
        $points = round($validated['trash_weight']*0.2,2);
        $trashtransaction = FacadesDB::transaction(function() use($points, $user, $validated){
            $transaction = trash_transaction::create([
            'trash_transaction_id' => (string)Str::uuid(),
            'trash_type' => $validated['trash_type'],
            'trash_weight'=> $validated['trash_weight'],
            'points' => $points,
            'user_id'=> $user->id,
            'petugas_id' => Auth::user()->id,
        ]);
        $user->increment('points', $points);
        return $transaction;
        });
    return response()->json([
        '{+}' => 'Transaction Success',
        'Data' => $trashtransaction
    ]);
    }
    // 'view total transactions',
    // 'view total weight',
    // 'view total point input',
    public function ViewTrashTransactionHitsory(Request $request){
        $this->authorize('view total transactions');
        $user = $request->user();
        $transaction = trash_transaction::where('petugas_id', $user->id)->whereDate('created_at', Carbon::today())->get();
        return response()->json([
            'Trash_Transaction' => $transaction
        ],200);
    }
}
