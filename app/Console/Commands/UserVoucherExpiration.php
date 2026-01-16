<?php

namespace App\Console\Commands;

use App\Models\user_voucher;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class UserVoucherExpiration extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'user_voucher:update-user-voucher-expiration
    {--dry-run : Run without executing}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Set user voucher to expired voucher after it reach the expiration date';

    /**
     * Execute the console command.
     */
    public function handle()
    {
       $expiredUserVoucher = user_voucher::where('status', 'active')
       ->where('expired_at', '<', now())
       ->get();

       if($expiredUserVoucher->isEmpty()){
        $this->info('No Voucher Expired.');
        return Command::SUCCESS;   
    }
    $this->info("Found {$expiredUserVoucher->count()}Voucher Expired");
    if($this->option('dry-run')){
        $this->table(
            ['id', 'user_id', 'voucher_id', 'status', 'used_at', 'actives_at', 'expired_at', 'voucher_qr'],
            $expiredUserVoucher->map(fn($user_voucher) =>[
                $user_voucher->id,
                $user_voucher->user_id,
                $user_voucher->voucher_id,
                $user_voucher->status,
                $user_voucher->used_at,
                $user_voucher->actives_at,
                $user_voucher->expired_at,
                $user_voucher->voucher_qr,
            ])
            );
        $this->warn('Dry run mode - no changes.');
        return Command::SUCCESS;
        }
        $expired = 0;
        foreach($expiredUserVoucher as $VoucherUser){
            $VoucherUser->update([
                'status'=>'expired',
            ]);
            $expired++;
            $this->line("Expired: {$expired}");
        }
        $this->info("Update, {$expired} Your voucher expired");
        Log::info("Expired voucher updated: {$expired}");
        return Command::SUCCESS;
    }
}
