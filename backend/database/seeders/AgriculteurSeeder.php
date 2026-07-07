<?php
namespace Database\Seeders;
use Illuminate\Database\Seeder;
use App\Models\Agriculteur;
use App\Models\User;

class AgriculteurSeeder extends Seeder
{
    public function run(): void
    {
        $data = [
            ['email' => 'mamadou@test.com', 'localisation' => 'Thiès',       'lat' => 14.7833, 'lng' => -16.9167],
            ['email' => 'fatou@test.com',   'localisation' => 'Dakar',        'lat' => 14.6937, 'lng' => -17.4441],
            ['email' => 'ib@test.com',      'localisation' => 'Saint-Louis',  'lat' => 16.0179, 'lng' => -16.4896],
            ['email' => 'ais@test.com',     'localisation' => 'Mbour',        'lat' => 14.4149, 'lng' => -16.9648],
            ['email' => 'oumar@test.com',   'localisation' => 'Ziguinchor',   'lat' => 12.5833, 'lng' => -16.2667],
        ];

        foreach ($data as $item) {
            $user = User::where('email', $item['email'])->first();
            if ($user) {
                Agriculteur::create([
                    'user_id'           => $user->id,
                    'localisation'      => $item['localisation'],
                    'latitude'          => $item['lat'],
                    'longitude'         => $item['lng'],
                    'statut_validation' => 'validé',
                ]);
            }
        }
    }
}