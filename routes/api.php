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
use App\Http\Controllers\VoucherTransactionController;
use App\Http\Controllers\NotificationController;
use App\Models\trash_transaction;

// Route::prefix('auth')->group(function(){
//     Route::post('/login', [AuthController::class, 'login']);
// });

//Note: Throttle act as Rate Limiter, [attempt,cooldown(in minutes)]
// User Authentication
Route::middleware('throttle:5,1')
   ->post('login', [AuthController::class, 'login']);
Route::post('register', [AuthController::class, 'register']);
Route::middleware('auth:sanctum')->post('logout', [AuthController::class, 'logout']);
Route::middleware(['auth:sanctum', 'throttle:6,1'])
   ->post('/email/verification-notification', function (Request $request) {
      if ($request->user()->hasVerifiedEmail()) {
         return response()->json([
            '{+}' => 'Email already verified!',
         ], 200);
      }
      $request->user()->sendEmailVerificationNotification();
      return response()->json([
         '{+}' => 'Verification sent'
      ], 200);
   });
Route::get('/email/verify/{id}/{hash}', [EmailController::class, 'verify'])
   ->middleware(['signed'])
   ->name('verification.verify');

Route::middleware(['auth:sanctum', 'verified'])
   ->patch('/update-profile', [ProfileController::class, 'Update']);
// ===============================================
//Admin Route
Route::middleware(['auth:sanctum', 'verified', 'permission:manage roles'])
   ->post('/createuser/{role}', [UserManagementController::class, 'CreateUser']);
Route::middleware(['auth:sanctum', 'verified', 'permission:see user'])
   ->get('/users', [UserManagementController::class, 'fetchUser']);
Route::middleware(['auth:sanctum', 'verified', 'permission:manage users'])
   ->patch('/user/{id}', [UserManagementController::class, 'editUser']);
Route::middleware(['auth:sanctum', 'verified', 'permission:manage users'])
   ->delete('/user/{id}', [UserManagementController::class, 'deleteUser']);

Route::get('/weight/preview', function () {
    return response()->json(
        \App\Services\AdafruitService::latest()
    );
});

//Personal user Route
Route::get('profile/{uuid}', [ProfileController::class, 'UserProfileQRScan']);
//Generating Trash QR
Route::middleware(['auth:sanctum', 'verified', 'permission:generate trash transaction qr'])
   ->post('/generate_trash_transaction_qr', [ProfileController::class, 'GenerateTrashTransactionQR']);
//Voucher Route
Route::middleware(['auth:sanctum', 'verified', 'permission:buy voucher'])
   ->post('/voucher-purchase/{id}', [UserVoucherController::class, 'BuyVoucher']);
Route::middleware(['auth:sanctum', 'permission:use voucher'])
   ->post('/use-voucher', [VoucherTransactionController::class, 'UserVoucherTransaction']);
//Fetching Data
Route::middleware(['auth:sanctum', 'permission:view user voucher'])
   ->get('/user-voucher', [UserVoucherController::class, 'FetchActiveVoucher']);
Route::get('/allvoucher', [UserVoucherController::class, 'FetchAllVoucher']);
Route::middleware(['auth:sanctum'])
   ->get('/user-data', [ProfileController::class, 'fetchUserData']);
Route::middleware(['auth:sanctum'])
   ->get('/user-points', [ProfileController::class, 'fetchUserPoint']);

Route::middleware('auth:sanctum')
   ->get('/notifications', [NotificationController::class, 'index']);


//Petugas Route
Route::middleware(['auth:sanctum', 'verified', 'permission:create trash transactions'])
   ->post('/trash-transaction/{uuid}', [TrashTransactionController::class, 'TrashTransaction']);
Route::middleware(['auth:sanctum', 'permission:view total transactions'])
   ->get('/trash-transaction-history', [TrashTransactionController::class, 'ViewTrashTransactionHitsory']);
Route::middleware(['auth:sanctum', 'verified', 'permission:view total transactions'])
   ->get('/trash-transaction-total-today', [TrashTransactionController::class, 'TrashTransactionTotal']);
Route::middleware(['auth:sanctum', 'verified', 'permission:view total weight'])
   ->get('trash-weight-today', [TrashTransactionController::class, 'TotalWeightToday']);
Route::middleware(['auth:sanctum', 'verified', 'permission:view total point input'])
   ->get('point-input-today', [TrashTransactionController::class, 'TotalPointInput']);

// UMKM Route
Route::middleware(['auth:sanctum', 'verified', 'permission:view all voucher'])
   ->get('/voucher', [VoucherController::class, 'index']);
Route::middleware(['auth:sanctum', 'verified', 'permission:view active voucher'])
   ->get('/active-voucher', [VoucherController::class, 'showActiveVoucher']);
Route::middleware(['auth:sanctum', 'verified', 'permission:view active voucher', 'throttle:10,1'])
   ->get('/vouchers', [VoucherController::class, 'ActiveVoucher']);
Route::middleware(['auth:sanctum', 'verified', 'permission:view expired voucher'])
   ->get('/expired-voucher', [VoucherController::class, 'showExpiredVoucher']);
Route::middleware(['auth:sanctum', 'verified', 'permission:create voucher'])
   ->post('/voucher', [VoucherController::class, 'store']);
Route::middleware(['auth:sanctum', 'verified', 'permission:view by id'])
   ->get('/voucher/{id}', [VoucherController::class, 'show']);
Route::middleware(['auth:sanctum', 'verified', 'permission:update voucher'])
   ->put('/voucher/{id}', [VoucherController::class, 'update']);
Route::middleware(['auth:sanctum', 'verified', 'permission:delete voucher'])
   ->delete('/voucher/{id}', [VoucherController::class, 'destroy']);
Route::middleware(['auth:sanctum', 'verified', 'permission:scan voucher'])
   ->post('/voucher-redemption/{uuid}', [VoucherTransactionController::class, 'VoucherTransaction']);
Route::middleware(['auth:sanctum', 'verified', 'permission:scan voucher'])
   ->get('/voucher-check/{uuid}', [VoucherTransactionController::class, 'checkVoucher']);
Route::middleware(['auth:sanctum', 'verified', 'permission:view total voucher used'])
   ->get('/voucher-used', [VoucherController::class, 'showTotalVoucherUsed']);
Route::middleware(['auth:sanctum', 'verified', 'permission:view voucher history'])
   ->get('/voucher-history', [VoucherController::class, 'showVoucherHistory']);