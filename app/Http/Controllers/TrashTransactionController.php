<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;

class TrashTransactionController extends Controller
{
    //
    use AuthorizesRequests;
    public function ScanUser($uuid){
        $this->authorize('scan user qr');
        $user = User::where('profile_qr', $uuid)->firstOrFail();
    }
}
