<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});
//Example Routing No 1 Role Based
// Route::middleware(['auth', 'role:super_admin'])->group(function(){
//       Route::get('/superadmin', function(){
//     echo ("API IS running" );
// });
// });
//Example Single Permission
Route::middleware(['auth', 'permission:view own profile'])->group(function(){
      Route::get('/viewownprofiletest', function(){
      return response()->json([
            'message' => 'Permission: view own profile granted',
            'user' => auth()->user()->email,
            'permissions' => auth()->user()->getAllPermissions()->pluck('name'),
        ]);
});
});

Route::get('/api', function(){
    echo ("API IS running" );
});

//Test
Route::get('/login-test', function () {
    $user = \App\Models\User::where('email', 'AV8R@gmail.com')->first();
    auth()->login($user);
    return 'Tis Useruser3';
});