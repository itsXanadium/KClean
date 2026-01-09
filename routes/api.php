
<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Admin\UserManagementController;


// Route::prefix('auth')->group(function(){
//     Route::post('/login', [AuthController::class, 'login']);
// });

Route::post('login', [AuthController::class, 'login']);
Route::post('register', [AuthController::class, 'register']);
Route::middleware('auth:sanctum')->post('logout', [AuthController::class, 'logout']);


Route::middleware(['auth:sanctum', 'permission:manage users'])
   ->post('/createuser/{role}', [UserManagementController::class, 'CreateUser']);

