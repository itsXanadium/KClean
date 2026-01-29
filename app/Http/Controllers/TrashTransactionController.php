<?php

namespace App\Http\Controllers;

use App\Models\trash_transaction;
use App\Models\User;
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

    public function showTotalTransactionToday(Request $request){
        $this->authorize('view total transaction today');
        $user = $request->user();
        $transactionToday = trash_transaction::where('petugas_id', $user->id)
            ->whereDate('created_at', today())
            ->get()
            ->sum();
        return response()->json([
            'Total Transaction Today' => $transactionToday
        ]);
    }

    public function showTotalTransaction(Request $request){
        $this->authorize('view total transaction');
        $user = $request->user();
        $totalTransaction = trash_transaction::where('petugas_id', $user->id)
            ->get()
            ->count();
        return response()->json([
            'Total Transaction' => $totalTransaction
        ]);
    }

    public function showTotalSentPoints(Request $request){
        $this->authorize('view total sent points');
        $user = $request->user();
        $totalSentPoints = trash_transaction::where('petugas_id', $user->id)
            ->get()
            ->sum();
        return response()->json([
            'Total Sent Points' => $totalSentPoints
        ]);
    }

    public function showTransactionHistory(Request $request){
        $this->authorize('view transaction history');
        $user = $request->user();
        $transactionHistory = trash_transaction::where('petugas_id', $user->id)
            ->get()
            ->paginate(10);
        return response()->json([
            'Transaction History' => $transactionHistory
        ]);
    }
}
