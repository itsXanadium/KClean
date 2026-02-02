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
use App\Services\AdafruitService;

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
        ]);

        $user = User::where('trash_transaction_qr', $uuid)->firstOrFail();
        $adafruit = AdafruitService::latest();
        $weight = $adafruit['value'];
        $timestamp = $adafruit['timestamp'];

        if ($weight <= 0) {
            return response()->json([
                'message' => 'Berat tidak valid, silakan timbang ulang'
            ], 422);
        }

        if (now()->diffInSeconds($timestamp) > 10) {
            return response()->json([
                'message' => 'Data timbangan sudah tidak fresh'
            ], 422);
        }

        $points = round($weight*50,2);

        $trashtransaction = FacadesDB::transaction(function() use($points, $user, $validated, $weight){
            $transaction = trash_transaction::create([
                'trash_transaction_id' => (string)Str::uuid(),
                'trash_type' => $validated['trash_type'],
                'trash_weight'=> $weight,
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
    
    public function ViewTrashTransactionHitsory(Request $request){
        $this->authorize('view total transactions');
        $user = $request->user();
        $transaction = trash_transaction::where('petugas_id', $user->id)->whereDate('created_at', Carbon::today())->latest()->get();
        return response()->json([
            'Trash_Transaction' => $transaction
        ],200);
    }

    public function TrashTransactionTotal(Request $request){
        $this->authorize('view total transactions');
        $user = $request->user();
        $transactionCount = trash_transaction::where('petugas_id', $user->id)->whereDate('created_at', Carbon::today())->count();

        return response()->json([
            'Today Total Transaction'=> $transactionCount
        ],200);
    }

    public function TotalWeightToday(Request $request){
        $this->authorize('view total weight');

        $user = $request->user();
        $weightCount = trash_transaction::where('petugas_id', $user->id)->whereDate('created_at', Carbon::today())->sum('trash_weight');
    
        return response()->json([
            'Today Total Trash Weighted'=> $weightCount
        ],200);
    }
    public function TotalPointInput(Request $request){
        $this->authorize('view total point input');

        $user = $request->user();
        $weightCount = trash_transaction::where('petugas_id', $user->id)->whereDate('created_at', Carbon::today())->sum('points');
    
        return response()->json([
            'Today Total Point Inputted'=> $weightCount
        ],200);
    }


}
