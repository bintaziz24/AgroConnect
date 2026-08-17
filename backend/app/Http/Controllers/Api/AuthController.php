<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Agriculteur;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $input = $request->all();
        if (isset($input['email'])) {
            $input['email'] = strtolower(trim($input['email']));
        }

        $validator = \Illuminate\Support\Facades\Validator::make($input, [
            'nom'       => 'required|string|max:255',
            'prenom'    => 'required|string|max:255',
            'email'     => 'required|email|unique:users,email',
            'password'  => 'required|min:8|confirmed',
            'role'      => 'required|in:client,agriculteur,livreur',
            'telephone' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => $validator->errors()->first(),
                'errors'  => $validator->errors()
            ], 422);
        }

        $email = strtolower(trim($request->email));
        $statutValidation = in_array($request->role, ['agriculteur', 'livreur']) ? 'en_attente' : 'validé';

        $user = User::create([
            'name'              => trim($request->nom . ' ' . $request->prenom),
            'email'             => $email,
            'password'          => Hash::make($request->password),
            'role'              => $request->role,
            'telephone'         => trim($request->telephone),
            'statut_validation' => $statutValidation,
        ]);

        // Automatically create Agriculteur profile if registering as agriculteur
        if ($user->role === 'agriculteur') {
            Agriculteur::create([
                'user_id'           => $user->id,
                'localisation'      => 'Sénégal',
                'statut_validation' => $statutValidation,
            ]);
        }

        $token = $user->createToken('agroconnect')->plainTextToken;

        return response()->json([
            'user'  => $user->load('agriculteur'),
            'token' => $token,
        ], 201);
    }

    public function login(Request $request)
    {
        $loginInput = strtolower(trim($request->input('email', '')));
        $password = $request->input('password', '');

        if (empty($loginInput) || empty($password)) {
            return response()->json([
                'message' => 'Veuillez saisir votre e-mail ou téléphone et votre mot de passe.',
                'errors'  => ['email' => ['Veuillez renseigner les identifiants.']]
            ], 422);
        }

        $cleanPhone = str_replace([' ', '+221', '-'], '', $loginInput);

        $user = User::where('email', $loginInput)
                    ->orWhere('telephone', $loginInput)
                    ->orWhere('telephone', $cleanPhone)
                    ->first();

        if (!$user || !Hash::check($password, $user->password)) {
            return response()->json([
                'message' => 'Identifiants incorrects. Vérifiez l\'adresse e-mail ou le mot de passe.',
                'errors'  => ['email' => ['Identifiants incorrects.']]
            ], 401);
        }

        $agriStatut = $user->agriculteur ? $user->agriculteur->statut_validation : null;
        if (in_array($user->statut_validation, ['rejeté', 'refusé', 'suspendu']) || in_array($agriStatut, ['rejeté', 'refusé', 'suspendu'])) {
            return response()->json([
                'message' => 'Accès refusé : Votre compte a été rejeté ou suspendu par l\'administration AgroConnect.',
                'errors'  => ['email' => ['Compte rejeté ou suspendu par l\'administration.']]
            ], 403);
        }

        // Auto-heal missing Agriculteur relation if role is agriculteur
        if ($user->role === 'agriculteur' && !$user->agriculteur) {
            Agriculteur::create([
                'user_id'           => $user->id,
                'localisation'      => 'Sénégal',
                'statut_validation' => $user->statut_validation ?? 'validé',
            ]);
            $user->load('agriculteur');
        }

        $token = $user->createToken('agroconnect')->plainTextToken;

        return response()->json([
            'user'  => $user,
            'token' => $token,
        ]);
    }

    public function logout(Request $request)
    {
        if ($request->user() && $request->user()->currentAccessToken()) {
            $request->user()->currentAccessToken()->delete();
        }
        return response()->json(['message' => 'Déconnecté avec succès']);
    }

    /**
     * Méthode métier UML : Réinitialiser le mot de passe utilisateur
     */
    public function reinitialiserMotDePasse(Request $request)
    {
        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
            'email'    => 'required|email|exists:users,email',
            'password' => 'required|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => $validator->errors()->first(),
                'errors'  => $validator->errors()
            ], 422);
        }

        $user = User::where('email', strtolower(trim($request->email)))->first();
        if ($user) {
            $user->password = Hash::make($request->password);
            $user->save();
            return response()->json([
                'message' => 'Votre mot de passe a été réinitialisé avec succès.'
            ]);
        }

        return response()->json(['message' => 'Utilisateur introuvable.'], 404);
    }
}