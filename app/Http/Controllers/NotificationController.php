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
        // Note: Ideally we get the voucher cost, but for now just showing redemption
        $voucherTransactions = voucher_transaction::where('user_id', $user->id)
            ->with('user_voucher.voucher') // Assuming relationships exist
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($item) {
                $voucherName = $item->user_voucher->voucher->name ?? 'Voucher';
                return [
                    'id' => 'voucher-' . $item->id,
                    'type' => 'voucher',
                    'title' => 'Tukar Voucher',
                    'description' => $voucherName,
                    'points' => 0, // We need to fetch cost from voucher if needed, usually negative
                    'is_earning' => false,
                    'date' => $item->created_at,
                ];
            });

        // Merge and Sort
        $history = $trashTransactions->concat($voucherTransactions)->sortByDesc('date')->values();

        return response()->json([
            'status' => 'success',
            'data' => $history
        ]);
    }
}
