<?php

namespace App\Http\Controllers;

use App\Models\Voucher;
use App\Models\User;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class VoucherController extends Controller
{
    use AuthorizesRequests;
    public function index(Request $request)
    {
        $this->authorize('view all voucher');
        $user = $request->user();
        $voucher = Voucher::where('umkm_id', $user->id)
            ->get();
        return response()->json([
            'Vouchers' => $voucher
        ], 200);
    }

    public function store(Request $request)
    {
        //validate form
        $this->authorize('create voucher');
        $request->validate([
            'title' => 'required',
            'points_required' => 'required',
            'category' => 'required',
            'voucher_image' => 'required|image|mimes:jpg,jpeg,png|max:2048',
            'actives_at' => 'required',
            'expired_at' => 'required',
            'limit' => 'required',
            'discount_price' => 'required',
        ]);

        //upload image
        // $image = $request->file('voucher_image');
        // $image->storeAs('voucher', $image->hashName());
        $image = $request->file('voucher_image');
        $image->storeAs('voucher', $image->hashName(), 'public');
        //create voucher
        $voucher = Voucher::create([
            'title' => $request->title,
            'points_required' => $request->points_required,
            'category' => $request->category,
            'voucher_image' => $image->hashName(),
            'actives_at' => $request->actives_at,
            'expired_at' => $request->expired_at,
            'umkm_id' => Auth::user()->id,
            'limit' => $request->limit,
            'discount_price' => $request->discount_price
        ]);

        if ($voucher) {
            return response()->json([
                'message' => 'Voucher berhasil dibuat!',
                'data' => $voucher
            ], 201);
        } else {
            return response()->json([
                'message' => 'Voucher gagal dibuat!'
            ], 400);
        }
    }

    public function show($id)
    {
        $this->authorize('view by id');
        $voucher = Voucher::findOrFail($id);
        if ($voucher) {
            return response()->json([
                'message' => 'Voucher berhasil ditemukan!',
                'data' => $voucher
            ], 200);
        } else {
            return response()->json([
                'message' => 'Voucher tidak ditemukan!'
            ], 404);
        }
    }

    public function showActiveVoucher(Request $request)
    {
        $this->authorize('view active voucher');
        $user = $request->user();
        $voucher = Voucher::where('umkm_id', $user->id)
            ->where('status', 'active')
            ->sum('limit');
        return response()->json([
            'Vouchers' => $voucher
        ], 200);
    }

    public function ActiveVoucher(Request $request)
    {
        $this->authorize('view active voucher');
        $user = $request->user();
        $voucher = Voucher::where('status', 'active')->get();
        return response()->json([
            'Vouchers' => $voucher
        ], 200);
    }

    public function showExpiredVoucher(Request $request)
    {
        $this->authorize('view expired voucher');
        $user = $request->user();
        $voucher = Voucher::where('umkm_id', $user->id)
            ->where('status', 'expired')
            ->count();
        return response()->json([
            'Vouchers' => $voucher
        ], 200);
    }

    public function showTotalVoucherUsed()
    {
        $this->authorize('view total voucher used');

    }

    public function update(Request $request, $id)
    {
        $this->authorize('update voucher');
        $validated = $request->validate([
            'title' => 'required|sometimes',
            'points_required' => 'required|sometimes',
            'category' => 'required|sometimes',
            'voucher_image' => 'image|mimes:jpg,jpeg,png|max:2048|sometimes',
            'expires_at' => 'required|sometimes',
            'umkm_id' => 'required|sometimes',
        ]);

        //get voucher by ID
        $voucher = Voucher::findOrFail($id);

        $voucher->update($validated);
        return response()->json([
            '{+}' => 'voucher updated',
            'Voucher' => $voucher
        ]);
    }

    public function destroy($id)
    {
        //get voucher by ID
        $this->authorize('delete voucher');
        $voucher = Voucher::findOrFail($id);

        //delete image
        Storage::delete('voucher/' . $voucher->image);

        //delete voucher
        $voucher->delete();
        return response()->json(['message' => 'Voucher berhasil dihapus!'], 200);
    }
}
