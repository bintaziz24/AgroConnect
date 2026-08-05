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
        User::firstOrCreate(
            ['email' => 'admin@agroconnect.sn'],
            [
                'name'              => 'Admin AgroConnect',
                'password'          => Hash::make('password'),
                'role'              => 'admin',
                'telephone'         => '770000000',
                'statut_validation' => 'validé',
            ]
        );

        // Client
        User::firstOrCreate(
            ['email' => 'cheikh@test.com'],
            [
                'name'              => 'Cheikh Fall',
                'password'          => Hash::make('password'),
                'role'              => 'client',
                'telephone'         => '771234567',
                'statut_validation' => 'validé',
            ]
        );

        // Livreur
        User::firstOrCreate(
            ['email' => 'modou@test.com'],
            [
                'name'              => 'Modou Ndiaye',
                'password'          => Hash::make('password'),
                'role'              => 'livreur',
                'telephone'         => '778901234',
                'statut_validation' => 'validé',
            ]
        );
    }
}