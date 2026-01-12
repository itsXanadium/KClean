
<?php
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Admin\UserManagementController;
use App\Http\Controllers\Auth\EmailController;
use App\Http\Controllers\user\ProfileController;
use Illuminate\Auth\Notifications\VerifyEmail;

// Route::prefix('auth')->group(function(){
//     Route::post('/login', [AuthController::class, 'login']);
// });

// User Authentication
Route::post('login', [AuthController::class, 'login']);
Route::post('register', [AuthController::class, 'register']);
Route::middleware('auth:sanctum')->post('logout', [AuthController::class, 'logout']);
Route::middleware(['auth:sanctum', 'throttle:6,1'])
->post('email/verification-notification', function(Request $request){
   if ($request->user()->hasVerifiedEmail()){
      return response()->json([
         '{+}' => 'Email already verified!',
      ],200);
      }
      $request->user()->sendEmailVerificationNotification();
      return response()->json([
         '{+}' => 'Verification sent'
      ],200);
});
Route::get('/email/verify/{id}/{hash}', [EmailController::class, 'verify'])
    ->middleware(['signed'])
    ->name('verification.verify');
// ===============================================

//Personal user Route
Route::middleware(['auth:sanctum', 'permission:update own profile'])
   ->put('/update', [ProfileController::class, 'Update']);

Route::get('profile/{uuid}', [ProfileController::class, 'UserProfileQRScan']);
//Admin Route
Route::middleware(['auth:sanctum', 'permission:manage users'])
   ->post('/createuser/{role}', [UserManagementController::class, 'CreateUser']);

Route::middleware(['auth:sanctum', 'permission:generate trash transaction qr'])
   ->post('/generate_trash_transaction_qr', [ProfileController::class,'GenerateTrashTransactionQR']);


