<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Container\Attributes\Storage;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage as FacadesStorage;
use Illuminate\Support\Str;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class SuperAdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $uuid = Str::uuid()->toString();
        $admin = User::factory()->create([
            "name" => "useruser3",
            'email' => "useruser3@gmail.com",
            "password" => bcrypt("shqiptare"),
            'profile_qr' => $uuid
            
        ]);
        $admin->assignRole('super-admin');
    
        $qrPath = "qrcodes/users/{$uuid}.svg";
        FacadesStorage::disk('public')->put(
            $qrPath,
            QrCode::format('svg')
            ->size(200)
            ->generate("/api/profile/{$uuid}")
        );
        $admin->update([
            'profile_qr_path'=>$qrPath
        ]);
    }
}
