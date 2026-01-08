<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Support\Facades\Storage;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
         $uuid = Str::uuid()->toString();
        $user = User::factory()->create([
            "name" => "AV8R",
            'email' => "AV8R@gmail.com",
            "password" => bcrypt("Shqiperia"),
            'profile_qr' => $uuid
            
        ]);
        $user->assignRole('user');
    
        $qrPath = "qrcodes/users/{$uuid}.svg";
        Storage::disk('public')->put(
            $qrPath,
            QrCode::format('svg')
            ->size(200)
            ->generate("USER:{$uuid}")
        );
        $user->update([
            'qr_code_path'=>$qrPath
        ]);
    }
}
