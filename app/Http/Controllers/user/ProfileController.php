<?php

namespace App\Http\Controllers\user;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class ProfileController extends Controller
{
    use AuthorizesRequests;
    public function Update(Request $request){
        $user = $request->user();
        // $user = User::findOrFail($id);
        $this->authorize('update own profile');

        $validated=$request->validate([
            'name' =>['sometimes', 'string'],
            'no_telp' =>['sometimes', 'string'],
            'email' => ['sometimes', 'email', 'unique:users,email,' . $user->id],
        ]);
        $user->update($validated);
        return response()->json([
            '{+}'=>'user updated',
            'User'=> $user
        ]);
    }
}
