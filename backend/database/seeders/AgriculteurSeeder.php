<?php
namespace Database\Seeders;
use Illuminate\Database\Seeder;
use App\Models\Agriculteur;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AgriculteurSeeder extends Seeder
{
    public function run(): void
    {
        $agriculteurs = [
            ['nom' => 'Mamadou Diallo',  'email' => 'mamadou@test.com', 'tel' => '772345678', 'localisation' => 'Thiès',       'lat' => 14.7833, 'lng' => -16.9167],
            ['nom' => 'Fatou Seck',      'email' => 'fatou@test.com',   'tel' => '773456789', 'localisation' => 'Dakar',        'lat' => 14.6937, 'lng' => -17.4441],
            ['nom' => 'Ibrahima Bâ',     'email' => 'ib@test.com',      'tel' => '774567890', 'localisation' => 'Saint-Louis',  'lat' => 16.0179, 'lng' => -16.4896],
            ['nom' => 'Aïssatou Ndiaye', 'email' => 'ais@test.com',     'tel' => '775678901', 'localisation' => 'Mbour',        'lat' => 14.4149, 'lng' => -16.9648],
            ['nom' => 'Oumar Sy',        'email' => 'oumar@test.com',   'tel' => '776789012', 'localisation' => 'Ziguinchor',   'lat' => 12.5833, 'lng' => -16.2667],
        ];

        foreach ($agriculteurs as $data) {
            $user = User::create([
                'name'      => $data['nom'],
                'email'     => $data['email'],
                'password'  => Hash::make('password'),
                'role'      => 'agriculteur',
                'telephone' => $data['tel'],
            ]);

            Agriculteur::create([
                'user_id'           => $user->id,
                'localisation'      => $data['localisation'],
                'latitude'          => $data['lat'],
                'longitude'         => $data['lng'],
                'statut_validation' => 'validé',
            ]);
        }
    }
}