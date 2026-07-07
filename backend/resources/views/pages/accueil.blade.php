@extends('layouts.app')
@section('title', 'AgroConnect - Accueil')

@section('content')

{{-- HERO --}}
<section class="hero-section">
  <div class="container">
    <span class="badge rounded-pill px-3 py-2 mb-3 d-inline-block"
      style="background:rgba(255,255,255,.15);font-size:11px;letter-spacing:.08em">
      🌿 PLATEFORME AGRICOLE NUMÉRIQUE · SÉNÉGAL
    </span>
    <h1 class="mx-auto mb-3" style="max-width:700px">
      Des produits frais directement<br>du producteur à votre table
    </h1>
    <p class="mx-auto mb-4" style="max-width:500px;opacity:.85;font-size:17px;line-height:1.6">
      Connectez-vous avec les agriculteurs locaux, commandez en ligne
      et payez avec Wave ou Orange Money.
    </p>
    <div class="d-flex gap-3 justify-content-center flex-wrap">
      <a href="{{ url('/catalogue') }}" class="btn px-4 py-2"
        style="background:var(--or);color:white;font-size:15px;border-radius:10px;font-weight:500">
        Explorer le catalogue
      </a>
      <a href="{{ route('register') }}" class="btn px-4 py-2"
        style="background:rgba(255,255,255,.15);color:white;border:1.5px solid rgba(255,255,255,.4);border-radius:10px">
        Je suis agriculteur →
      </a>
    </div>
  </div>
</section>

{{-- STATS --}}
<section class="bg-white border-bottom py-5">
  <div class="container">
    <div class="row text-center g-4">
      <div class="col-6 col-md-3">
        <div class="stat-num">1 200+</div>
        <small class="text-muted">Agriculteurs inscrits</small>
      </div>
      <div class="col-6 col-md-3">
        <div class="stat-num">8 500+</div>
        <small class="text-muted">Clients actifs</small>
      </div>
      <div class="col-6 col-md-3">
        <div class="stat-num">45+</div>
        <small class="text-muted">Régions couvertes</small>
      </div>
      <div class="col-6 col-md-3">
        <div class="stat-num">98%</div>
        <small class="text-muted">Satisfaction client</small>
      </div>
    </div>
  </div>
</section>

{{-- FONCTIONNALITÉS --}}
<section class="container py-5">
  <h2 style="font-family:'Playfair Display',serif">Pourquoi choisir AgroConnect ?</h2>
  <p class="text-muted mb-4">Une plateforme pensée pour le contexte sénégalais</p>
  <div class="row g-3">
    <div class="col-6 col-md-3">
      <div class="feature-card">
        <div class="feature-icon" style="background:var(--vert-l)">🌿</div>
        <h6 class="fw-semibold">Produits frais locaux</h6>
        <small class="text-muted">Achetez directement auprès d'agriculteurs de votre région, sans intermédiaire.</small>
      </div>
    </div>
    <div class="col-6 col-md-3">
      <div class="feature-card">
        <div class="feature-icon" style="background:var(--bleu-l)">📱</div>
        <h6 class="fw-semibold">Paiement mobile money</h6>
        <small class="text-muted">Réglez vos commandes avec Wave ou Orange Money en toute sécurité.</small>
      </div>
    </div>
    <div class="col-6 col-md-3">
      <div class="feature-card">
        <div class="feature-icon" style="background:#FFF3DC">📍</div>
        <h6 class="fw-semibold">Géolocalisation</h6>
        <small class="text-muted">Trouvez les agriculteurs les plus proches de chez vous sur la carte.</small>
      </div>
    </div>
    <div class="col-6 col-md-3">
      <div class="feature-card">
        <div class="feature-icon" style="background:#FAECE7">🚚</div>
        <h6 class="fw-semibold">Livraison suivie</h6>
        <small class="text-muted">Suivez votre commande en temps réel jusqu'à votre domicile.</small>
      </div>
    </div>
  </div>
</section>

{{-- PRODUITS EN VEDETTE --}}
<section class="container pb-5">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <div>
      <h2 style="font-family:'Playfair Display',serif">Produits en vedette</h2>
      <p class="text-muted mb-0">Les produits les plus populaires cette semaine</p>
    </div>
    <a href="{{ url('/catalogue') }}" class="btn btn-vert-outline px-3">Voir tout →</a>
  </div>
  <div class="row g-3">
    @forelse($produits as $produit)
    <div class="col-6 col-md-4 col-lg-3">
      <div class="card-produit">
        <div class="prod-img">
          {{ $produit->emoji ?? '🥕' }}
        </div>
        <div class="p-3">
          <span class="badge-cat d-inline-block mb-1">
            {{ $produit->categorie->nom ?? 'Légumes' }}
          </span>
          <div class="fw-semibold">{{ $produit->nom }}</div>
          <small class="text-muted d-block mb-2">
            🌾 {{ $produit->agriculteur->nom ?? '' }}
          </small>
          <div class="d-flex justify-content-between align-items-center">
            <strong style="color:var(--vert);font-size:15px">
              {{ number_format($produit->prix) }} FCFA
            </strong>
            <form method="POST" action="{{ route('panier.ajouter', $produit->id) }}" class="m-0">
              @csrf
              <button type="submit" class="btn btn-vert btn-sm px-3"
                style="border-radius:8px">+</button>
            </form>
          </div>
        </div>
      </div>
    </div>
    @empty
    <div class="col-12 text-center py-5 text-muted">
      <div style="font-size:48px">🌾</div>
      <p>Aucun produit disponible pour le moment.</p>
    </div>
    @endforelse
  </div>
</section>

@endsection