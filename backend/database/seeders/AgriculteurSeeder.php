<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Agriculteur;
use App\Models\Ferme;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AgriculteurSeeder extends Seeder
{
    public function run(): void
    {
        $agriculteurs = [
            [
                'nom' => 'Mamadou Diallo',
                'email' => 'mamadou@test.com',
                'tel' => '772345678',
                'localisation' => 'Thiès',
                'lat' => 14.7833,
                'lng' => -16.9167,
                'nom_ferme' => 'Ferme Bio des Niayes',
                'adresse_ferme' => 'Route de Mont-Rolland, Thiès',
                'description_ferme' => 'Spécialisée dans la culture biologique de légumes et céréales locaux.',
            ],
            [
                'nom' => 'Fatou Seck',
                'email' => 'fatou@test.com',
                'tel' => '773456789',
                'localisation' => 'Dakar',
                'lat' => 14.6937,
                'lng' => -17.4441,
                'nom_ferme' => 'Domaine Agricole de Sangalkam',
                'adresse_ferme' => 'Sangalkam, Dakar',
                'description_ferme' => 'Exploitation éco-responsable certifiée AgroConnect Sénégal.',
            ],
            [
                'nom' => 'Ibrahima Bâ',
                'email' => 'ib@test.com',
                'tel' => '774567890',
                'localisation' => 'Saint-Louis',
                'lat' => 16.0179,
                'lng' => -16.4896,
                'nom_ferme' => 'Ferme de la Vallée du Fleuve',
                'adresse_ferme' => 'Gandon, Saint-Louis',
                'description_ferme' => 'Producteur agréé de riz, fruits et tubercules frais.',
            ],
            [
                'nom' => 'Aïssatou Ndiaye',
                'email' => 'ais@test.com',
                'tel' => '775678901',
                'localisation' => 'Mbour',
                'lat' => 14.4149,
                'lng' => -16.9648,
                'nom_ferme' => 'Vergers Bio de la Petite Côte',
                'adresse_ferme' => 'Nianing, Mbour',
                'description_ferme' => 'Vergers naturels de fruits exotiques et produits maraîchers.',
            ],
            [
                'nom' => 'Oumar Sy',
                'email' => 'oumar@test.com',
                'tel' => '776789012',
                'localisation' => 'Ziguinchor',
                'lat' => 12.5833,
                'lng' => -16.2667,
                'nom_ferme' => 'Exploitation Agricole de Casamance',
                'adresse_ferme' => 'Bignona, Ziguinchor',
                'description_ferme' => 'Mangues, agrumes et produits vivriers de Casamance.',
            ],
        ];

        foreach ($agriculteurs as $data) {
            $user = User::firstOrCreate(
                ['email' => $data['email']],
                [
                    'name'              => $data['nom'],
                    'password'          => Hash::make('password'),
                    'role'              => 'agriculteur',
                    'telephone'         => $data['tel'],
                    'statut_validation' => 'validé',
                ]
            );

            $agri = Agriculteur::firstOrCreate(
                ['user_id' => $user->id],
                [
                    'localisation'      => $data['localisation'],
                    'latitude'          => $data['lat'],
                    'longitude'         => $data['lng'],
                    'statut_validation' => 'validé',
                ]
            );

            Ferme::firstOrCreate(
                ['agriculteur_id' => $agri->id],
                [
                    'nom_ferme'         => $data['nom_ferme'],
                    'adresse_ferme'     => $data['adresse_ferme'],
                    'description_ferme' => $data['description_ferme'],
                ]
            );
        }
    }
}