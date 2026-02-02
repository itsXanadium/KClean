<?php

namespace App\Http\Controllers;

use App\Models\user_voucher;
use App\Models\Voucher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\voucher_transaction;
use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class VoucherTransactionController extends Controller
{
    use AuthorizesRequests;
    public function VoucherTransaction($uuid){
        $this->authorize('scan voucher');
        $userVoucher = user_voucher::lockForUpdate()->where('voucher_qr', $uuid)->firstOrFail();
        $transaction = DB::transaction(function() use($userVoucher){
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
            $voucherTransaction->load('user_voucher.voucher');
            return $voucherTransaction;
        });
        return response()->json([
            "{+}" => "Voucher Redeemed",
            "data" => $transaction 
        ]);
    }
    public function UserVoucherTransaction(Request $request){
        $this->authorize('use voucher');
        $validated=$request->validate([
            'voucher_qr' => 'required'
        ]);
        $transaction = DB::transaction(function() use($validated){
            $userVoucher = user_voucher::lockForUpdate()->where('voucher_qr', $validated['voucher_qr'])->firstOrFail();
            if($userVoucher->status !=='active'){
                abort(400, 'The Voucher is no longer useable (Expired/Used)');
            }
            $voucherTransaction = voucher_transaction::create([
                "umkm_id"  => $userVoucher->voucher->umkm_id,
                "user_id" => Auth::user()->id,
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
    public function checkVoucher($uuid)
    {
        $this->authorize('scan voucher');

        $userVoucher = user_voucher::with(['voucher', 'user'])
            ->where('voucher_qr', $uuid)
            ->first();

        if (!$userVoucher) {
            return response()->json(['message' => 'Voucher tidak ditemukan'], 404);
        }

        if ($userVoucher->status !== 'active') {
             return response()->json([
                'message' => 'Voucher sudah pernah digunakan',
                'data' => $userVoucher
            ], 400);
        }

        return response()->json([
            'message' => 'Voucher Valid',
            'data' => $userVoucher
        ]);
    }
}
