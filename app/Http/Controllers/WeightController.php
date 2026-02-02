<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Weight;
use Illuminate\Http\Request;

class WeightController extends Controller
{
    public function latest()
    {
        $weight = Weight::latest()->first();

        return response()->json([
            'success' => true,
            'data' => [
                'value' => $weight?->value ?? 0,
                'timestamp' => $weight?->created_at,
            ]
        ]);
    }

    // Ambil histori (opsional)
    public function history(Request $request)
    {
        $limit = $request->get('limit', 20);

        $weights = Weight::latest()
            ->limit($limit)
            ->get()
            ->reverse()
            ->values();

        return response()->json([
            'success' => true,
            'data' => $weights
        ]);
    }
}