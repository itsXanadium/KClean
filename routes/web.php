<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/api', function(){
    echo ("API IS running" );
});


// //Example Routing No 1 Role Based
// Route::middleware(['auth', 'role:super_admin'])->group(function(){
//       Route::get('/superadmin', function(){
//     echo ("API IS running" );
// });
// });
// //Example Single Permission
// Route::middleware(['auth', 'permission:view own profile'])->group(function(){
//       Route::get('/viewownprofiletest', function(){
//       return response()->json([
//             'message' => 'Permission: view own profile granted',
//             'user' => auth()->user()->email,
//             'permissions' => auth()->user()->getAllPermissions()->pluck('name'),
//         ]);
// });
// });
// //Test
// Route::get('/login-test', function () {
//     $user = \App\Models\User::where('email', 'useruser3@gmail.com')->first();
//     auth()->login($user);
//     return 'Tis Ion Antonescu';
// });