
<?php
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\VoucherController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\EmailController;
use Illuminate\Auth\Notifications\VerifyEmail;
use App\Http\Controllers\user\ProfileController;
use App\Http\Controllers\TrashTransactionController;
use App\Http\Controllers\Admin\UserManagementController;
use App\Http\Controllers\UserVoucherController;

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
// Genereate Trash QR
Route::middleware(['auth:sanctum', 'permission:generate trash transaction qr'])
   ->post('/generate_trash_transaction_qr', [ProfileController::class,'GenerateTrashTransactionQR']);
// Buying Voucher
Route::middleware(['auth:sanctum', 'permission:buy voucher'])
   ->post('/voucher-purchase', [UserVoucherController::class, 'BuyVoucher']);
   
//Petugas Route
Route::middleware(['auth:sanctum', 'permission:create trash transactions'])
   ->post('/trash-transaction', [TrashTransactionController::class,'TrashTransaction']);

// UMKM Route
Route::middleware(['auth:sanctum', 'permission:view all voucher'])
   ->get('/voucher', [VoucherController::class, 'index']);
Route::middleware(['auth:sanctum', 'permission:view active voucher'])
   ->get('/active_voucher', [VoucherController::class, 'showActiveVoucher']);
Route::middleware(['auth:sanctum', 'permission:view expired voucher'])
   ->get('/expired_voucher', [VoucherController::class, 'showExpiredVoucher']);
Route::middleware(['auth:sanctum', 'permission:create voucher'])
   ->post('/voucher', [VoucherController::class, 'store']);
Route::middleware(['auth:sanctum', 'permission:view by id'])
   ->get('/voucher/{id}', [VoucherController::class, 'show']);
Route::middleware(['auth:sanctum', 'permission:update voucher'])
   ->put('/voucher/{id}', [VoucherController::class, 'update']);
Route::middleware(['auth:sanctum', 'permission:delete voucher'])
   ->delete('/voucher/{id}', [VoucherController::class, 'destroy']);

