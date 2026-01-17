<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Voucher;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class UpdateExpiredVoucher extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'voucher:update-expired
                            {--dry-run : Run without executing}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check and update expired voucher.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $expiredVoucher = Voucher::where('status', 'active')
            ->where('expired_at', '<', now())
            ->get();

        if($expiredVoucher->isEmpty()){
            $this->info('No expired voucher.');
            return Command::SUCCESS;
        }

        $this->info("Found {$expiredVoucher->count()} expired voucher.");

        if ($this->option('dry-run')) {
            $this->table(
                ['id', 'title', 'points_required', 'category', 'actives_at', 'expired_at', 'status'],
                $expiredVoucher->map(fn ($voucher) => [
                    $voucher->id,
                    $voucher->title,
                    $voucher->points_required,
                    $voucher->category,
                    $voucher->actives_at,
                    $voucher->expired_at,
                    $voucher->status,
                ])
            );
            $this->warn('Dry run mode - no changes.');
            return Command::SUCCESS;
        }

        $expired = 0;

        foreach ($expiredVoucher as $voucher) {
            $voucher->update(['status' => 'expired']);
            $expired++;
            $this->line("✓ Expired: {$voucher->title}");
        }

        $this->info("Complete! {$expired} voucher expired.");
        Log::info("Expired vouchers updated: {$expired}");

        return Command::SUCCESS;
    }
}
