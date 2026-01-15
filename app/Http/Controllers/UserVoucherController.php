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
        $voucher = Voucher::findOrFail($validated['voucher_id']);
        $purchase = DB::transaction(function()use($voucher){
            $uuid = Str::uuid()->toString();
            $user= User::lockForUpdate()->findOrFail(Auth::id());
                if($user->points < $voucher->points_required){
                    abort(400, 'Insufficient Fund!');
                }
                $user->decrement('points', $voucher->points_required);
                return user_voucher::create([
                    'user_id' => $user->id,
                    'voucher_id'=>$voucher->id,
                    'expired_at' => $voucher->expires_at  ?? now()->addDays(7),
                    'status' => 'active',
                    'voucher_qr' => $uuid
                ]);
            });
            return response()->json([
                '{+}' => 'Voucher Purchased',
                'Voucher Data' => $purchase
            ],200);
    }
}
