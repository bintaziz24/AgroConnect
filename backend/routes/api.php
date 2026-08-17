<?php
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ProduitController;
use App\Http\Controllers\Api\CommandeController;
use App\Http\Controllers\Api\PanierController;
use App\Http\Controllers\Api\LivraisonController;
use App\Http\Controllers\Api\AdminController;
use App\Http\Controllers\Api\PayTechController;

// ── Routes publiques ──
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login',    [AuthController::class, 'login']);
Route::post('/reinitialiser-mot-de-passe', [AuthController::class, 'reinitialiserMotDePasse']);
Route::get('/produits',  [ProduitController::class, 'index']);
Route::get('/produits/{id}', [ProduitController::class, 'show']);
Route::get('/agriculteurs',  [ProduitController::class, 'agriculteurs']);
Route::post('/paytech/ipn',  [PayTechController::class, 'ipn']);

// ── Routes authentifiées ──
Route::middleware('auth:sanctum')->group(function () {

    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user',    function (Request $request) {
        return $request->user();
    });

    // Panier & commandes (client)
    Route::get('/panier',              [PanierController::class, 'index']);
    Route::post('/panier/ajouter',     [PanierController::class, 'ajouter']);
    Route::post('/panier/diminuer',    [PanierController::class, 'diminuer']);
    Route::delete('/panier/{id}',      [PanierController::class, 'supprimer']);
    Route::post('/commandes',          [CommandeController::class, 'store']);
    Route::get('/commandes',           [CommandeController::class, 'index']);
    Route::get('/commandes/{id}',      [CommandeController::class, 'show']);

    // Agriculteur
    Route::post('/produits',           [ProduitController::class, 'store']);
    Route::put('/produits/{id}',       [ProduitController::class, 'update']);
    Route::delete('/produits/{id}',    [ProduitController::class, 'destroy']);
    Route::get('/agriculteur/dashboard', [ProduitController::class, 'dashboard']);
    Route::get('/agriculteur/commandes', [CommandeController::class, 'agriculteurCommandes']);

    // Livraison & Profil Livreur UML
    Route::get('/livraisons',          [LivraisonController::class, 'index']);
    Route::put('/livraisons/{id}',     [LivraisonController::class, 'update']);
    Route::get('/livreur/profile',     [LivraisonController::class, 'profile']);
    Route::put('/livreur/profile',     [LivraisonController::class, 'updateProfile']);

    // Admin
    Route::get('/admin/utilisateurs',  [AdminController::class, 'utilisateurs']);
    Route::get('/admin/statistiques',  [AdminController::class, 'statistiques']);
    Route::put('/admin/valider/{id}',  [AdminController::class, 'validerAgriculteur']);
    Route::get('/admin/produits',      [AdminController::class, 'produits']);
    Route::delete('/admin/produits/{id}', [AdminController::class, 'supprimerProduit']);
    Route::get('/admin/commandes',     [AdminController::class, 'commandes']);

    // Discussions / Chat
    Route::get('/discussions',                 [\App\Http\Controllers\Api\DiscussionController::class, 'index']);
    Route::post('/discussions',                [\App\Http\Controllers\Api\DiscussionController::class, 'store']);
    Route::get('/discussions/non-lus/count',   [\App\Http\Controllers\Api\DiscussionController::class, 'compterNonLus']);
    Route::get('/discussions/{id}',            [\App\Http\Controllers\Api\DiscussionController::class, 'show']);
    Route::post('/discussions/{id}/messages',  [\App\Http\Controllers\Api\DiscussionController::class, 'envoyerMessage']);
});

// Routes WhatsApp
use App\Http\Controllers\Api\WhatsAppController;
Route::get('/whatsapp/config',               [WhatsAppController::class, 'config']);
Route::post('/whatsapp/lien-commande',       [WhatsAppController::class, 'lienCommande']);
Route::post('/whatsapp/envoyer',             [WhatsAppController::class, 'envoyerNotification']);
Route::post('/whatsapp/reponse-automatique', [WhatsAppController::class, 'reponseAutomatique']);