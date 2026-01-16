<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Voucher;
use Illuminate\Support\Str;
use App\Models\user_voucher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class UserVoucherController extends Controller
{
    use AuthorizesRequests;
    public function BuyVoucher(Request $request){
        $this->authorize('buy voucher');
        $validated = $request->validate([
            'voucher_id' => 'required|exists:vouchers,id',
        ]);
        $purchase = DB::transaction(function()use($validated){
            $voucher = Voucher::lockForUpdate()->findOrFail($validated['voucher_id']);
            $uuid = Str::uuid()->toString();
            $user= User::lockForUpdate()->findOrFail(Auth::id());
                if($user->points < $voucher->points_required){
                    abort(400, 'Insufficient Fund!');
                }
                $user->decrement('points', $voucher->points_required);
                return user_voucher::create([
                    'user_id' => $user->id,
                    'voucher_id'=>$voucher->id,
                    'active_at' => now(),
                    'expired_at' => $voucher->expired_at,
                    'status' => 'active',
                    'voucher_qr' => $uuid
                ]);
            });
            return response()->json([
                '{+}' => 'Voucher Purchased',
                'Voucher Data' => $purchase
            ],200);
    }

    public function FetchActiveVoucher(){
        $this->authorize('view user voucher');
        $voucher = user_voucher::where('status', 'active')->get();
        return response()->json([
            'Vouchers'    => $voucher
        ], 200);
    }
    public function FetchAllVoucher(){
        $voucher = user_voucher::all();
        return response()->json([
            'Vouchers'    => $voucher
        ], 200);
    }
}
