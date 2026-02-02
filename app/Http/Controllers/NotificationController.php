<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\trash_transaction;
use App\Models\voucher_transaction;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();

        // 1. Trash Transactions (Earning Points)
        $trashTransactions = trash_transaction::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($item) {
                return [
                    'id' => 'trash-' . $item->id,
                    'type' => 'trash',
                    'title' => 'Setor Sampah',
                    'description' => "{$item->trash_type} ({$item->trash_weight} kg)",
                    'points' => $item->points,
                    'is_earning' => true,
                    'date' => $item->created_at,
                ];
            });

        // 2. Voucher Transactions (Spending/Using Points)
        $voucherTransactions = \App\Models\user_voucher::where('user_id', $user->id)
            ->with('voucher')
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($item) {
                $voucherName = $item->voucher->title ?? 'Voucher';
                $points = $item->voucher->points_required ?? 0;
                return [
                    'id' => 'voucher-' . $item->id,
                    'type' => 'voucher',
                    'title' => 'Tukar Poin',
                    'description' => $voucherName,
                    'points' => $points,
                    'is_earning' => false,
                    'date' => $item->created_at,
                ];
            });

        // 3. Voucher Usage (Redeemed at UMKM)
        $usageTransactions = voucher_transaction::where('user_id', $user->id)
            ->with(['user_voucher.voucher'])
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($item) {
                // Access nested relationship safely
                $voucherName = $item->user_voucher->voucher->title ?? 'Voucher';
                return [
                    'id' => 'usage-' . $item->id,
                    'type' => 'usage',
                    'title' => 'Voucher Digunakan',
                    'description' => $voucherName,
                    'points' => 0,
                    'is_earning' => false,
                    'date' => $item->created_at,
                ];
            });

        // Merge and Sort
        $history = $trashTransactions
            ->concat($voucherTransactions)
            ->concat($usageTransactions)
            ->sortByDesc('date')
            ->values();

        return response()->json([
            'status' => 'success',
            'data' => $history
        ]);
    }
}
