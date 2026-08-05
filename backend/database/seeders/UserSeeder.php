<?php
namespace Database\Seeders;
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Admin
        User::create([
            'name'      => 'Admin AgroConnect',
            'email'     => 'admin@agroconnect.sn',
            'password'  => Hash::make('password'),
            'role'      => 'admin',
            'telephone' => '770000000',
        ]);

        // Client
        User::create([
            'name'      => 'Cheikh Fall',
            'email'     => 'cheikh@test.com',
            'password'  => Hash::make('password'),
            'role'      => 'client',
            'telephone' => '771234567',
        ]);
    }
}