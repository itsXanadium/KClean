<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //Reset roles and Perimssions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        //Permissions
        $permissions = [
            //Personal Users
            'view own profile' ,          
            'update own profile',
            'buy voucher',
            'use voucher',
            'view user voucher',
            'view own qr',
            'generate trash transaction qr',
            'view trash transaction qr',

            //Petugas
            'scan user qr',
            'insert points',
            'create trash transactions',
            'view total transaction today',
            'view total transaction',
            'view total sent points',
            'view transaction history',

            //UMKM
            'create voucher',
            'scan voucher',
            'view active voucher',
            'view expired voucher',
            'view total voucher used',
            'view weekly voucher redeemed',
            'view voucher totals',
            'view all voucher',
            'view by id',
            'delete voucher',
            'update voucher',

            //SuperAdmin
            'manage users',
            'manage roles',
            'manage permission',
            'see user',
        ];
        foreach($permissions as $permission){
            Permission::firstOrCreate(['name'=> $permission]);
        }

        //Create SuperAdmin Role
        $superAdmin = Role::firstOrCreate(['name'=>'super-admin']);
        $superAdmin->givePermissionTo([
            'manage users',
            'manage roles',
            'manage permission',
            'see user',
        ]);
        //Create user role
        $user = Role::firstOrCreate(['name'=>'user']);
        $user->givePermissionTo([
            'view own profile' ,          
            'update own profile',
            'buy voucher',
            'use voucher',
            'view user voucher',
            'view own qr',
            'generate trash transaction qr',
            'view trash transaction qr'
        ]);
        //Create Petugas Role
        $petugas = Role::firstOrCreate(['name'=>'petugas']);
        $petugas->givePermissionTo([
            'scan user qr',
            'insert points',
            'create trash transactions',
            'view total transaction today',
            'view total transaction',
            'view total sent points',
            'view transaction history',
        ]);
        //Create UMKM Role
        $umkm = Role::firstOrCreate(['name'=>'umkm']);
        $umkm->givePermissionTo([
            'create voucher',
            'scan voucher',
            'view active voucher',
            'view expired voucher',
            'view total voucher used',
            'view weekly voucher redeemed',
            'view voucher totals',
            'view all voucher',
            'view by id',
            'update voucher',
            'delete voucher'
        ]);
    }
}
