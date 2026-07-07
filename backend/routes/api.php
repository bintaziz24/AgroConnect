<?php
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ProduitController;
use App\Http\Controllers\Api\CommandeController;
use App\Http\Controllers\Api\PanierController;
use App\Http\Controllers\Api\LivraisonController;
use App\Http\Controllers\Api\AdminController;

// ── Routes publiques ──
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login',    [AuthController::class, 'login']);
Route::get('/produits',  [ProduitController::class, 'index']);
Route::get('/produits/{id}', [ProduitController::class, 'show']);
Route::get('/agriculteurs',  [ProduitController::class, 'agriculteurs']);

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

    // Livraison
    Route::get('/livraisons',          [LivraisonController::class, 'index']);
    Route::put('/livraisons/{id}',     [LivraisonController::class, 'update']);

    // Admin
    Route::get('/admin/utilisateurs',  [AdminController::class, 'utilisateurs']);
    Route::get('/admin/statistiques',  [AdminController::class, 'statistiques']);
    Route::put('/admin/valider/{id}',  [AdminController::class, 'validerAgriculteur']);
});