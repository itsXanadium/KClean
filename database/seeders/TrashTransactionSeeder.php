<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;
use App\Models\User;
use App\Models\trash_transaction;

class TrashTransactionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Specific users as requested
        $petugas = User::find(3); 
        $customer = User::find(2);
        
        if (!$petugas || !$customer) {
            $this->command->error("User with ID 22 (Warga) or 23 (Petugas) not found. Please ensure they exist.");
            return;
        }

        $this->command->info("Seeding transactions for Petugas: " . $petugas->name . " and Customer: " . $customer->name);

        $trashTypes = ['Sampah Organik', 'Sampah Anorganik'];
        
        for ($i = 0; $i < 20; $i++) {
            $weight = rand(1, 20); // 1 to 20 kg
            $points = round($weight * 0.2, 2); // Calculation from controller
            $type = $trashTypes[array_rand($trashTypes)];

            trash_transaction::create([
                'trash_transaction_id' => Str::uuid(),
                'trash_type' => $type,
                'trash_weight' => $weight,
                'points' => $points,
                'user_id' => $customer->id,
                'petugas_id' => $petugas->id,
                'created_at' => Carbon::now()->subMinutes(rand(1, 480)), // Last 8 hours from NOW
                'updated_at' => Carbon::now(),
            ]);
            
            // Also update user points? 
            // The controller does: $user->increment('points', $points);
            // We should replicate that to keep consistent state
            $customer->increment('points', $points);
        }

        $this->command->info("Created 20 transactions for today.");
    }
}
