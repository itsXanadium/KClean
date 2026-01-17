<?php

namespace App\Http\Controllers;

use App\Models\user_voucher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\voucher_transaction;
use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class VoucherTransactionController extends Controller
{
    use AuthorizesRequests;
    public function VoucherTransaction(Request $request){
        $this->authorize('scan voucher');
        $validated=$request->validate([
            'voucher_qr' => 'required'
        ]);
        $transaction = DB::transaction(function() use($validated){
            $userVoucher = user_voucher::lockForUpdate()->where('voucher_qr', $validated['voucher_qr'])->firstOrFail();
            if($userVoucher->status !=='active'){
                abort(400, 'The Voucher is no longer useable (Expired/Used)');
            }
            $voucherTransaction = voucher_transaction::create([
                "umkm_id"=> Auth::user()->id,
                "user_id" => $userVoucher->user_id,
                'user_voucher_id' => $userVoucher->id,
                'redeemed_at' => now()
            ]);
            $userVoucher->update([
                'status' => 'used',
                'used_at'=>now(),
            ]);
            return $voucherTransaction;
        });
        return response()->json([
            "{+}" => "Voucher Redeemed",
            "Data" => $transaction
        ]);
    }
}
