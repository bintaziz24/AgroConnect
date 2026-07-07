<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>@yield('title', 'AgroConnect')</title>
  @vite(['resources/js/app.js', 'resources/css/agroconnect.css'])
</head>
<body>

  {{-- NAVBAR --}}
  <nav class="navbar navbar-expand-lg navbar-agroconnect sticky-top px-4">
    <a class="navbar-brand" href="{{ url('/') }}">
      <span style="color:var(--or)">●</span> AgroConnect
    </a>
    <button class="navbar-toggler" type="button"
      data-bs-toggle="collapse" data-bs-target="#navMenu"
      aria-controls="navMenu" aria-expanded="false">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navMenu">
      <ul class="navbar-nav me-auto mb-2 mb-lg-0">
        <li class="nav-item">
          <a class="nav-link {{ request()->is('/') ? 'fw-bold' : '' }}" href="{{ url('/') }}">Accueil</a>
        </li>
        <li class="nav-item">
          <a class="nav-link {{ request()->is('catalogue') ? 'fw-bold' : '' }}" href="{{ url('/catalogue') }}">Catalogue</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="#">Agriculteurs</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="#">Comment ça marche</a>
        </li>
      </ul>
      <div class="d-flex gap-2 align-items-center">
        @guest
          <a href="{{ route('login') }}" class="btn btn-vert-outline px-3">Se connecter</a>
          <a href="{{ route('register') }}" class="btn btn-vert px-3">S'inscrire</a>
        @else
          <a href="{{ url('/panier') }}" class="btn btn-vert-outline px-3">🧺 Panier</a>
          <form method="POST" action="{{ route('logout') }}" class="d-inline m-0">
            @csrf
            <button type="submit" class="btn btn-vert px-3">Déconnexion</button>
          </form>
        @endguest
      </div>
    </div>
  </nav>

  {{-- MESSAGES FLASH --}}
  @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show m-3" role="alert">
      {{ session('success') }}
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
  @endif
  @if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show m-3" role="alert">
      {{ session('error') }}
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
  @endif

  {{-- CONTENU --}}
  @yield('content')

  {{-- FOOTER --}}
  <footer style="background:var(--vert-d);color:white;padding:2.5rem 2rem;margin-top:3rem">
    <div class="container">
      <div class="row g-4">
        <div class="col-md-4">
          <div style="font-family:'Playfair Display',serif;font-size:20px;margin-bottom:.5rem">
            <span style="color:var(--or)">●</span> AgroConnect
          </div>
          <small style="opacity:.7">Plateforme agricole numérique · Sénégal</small>
        </div>
        <div class="col-md-4">
          <div class="fw-semibold mb-2">Liens rapides</div>
          <div><a href="{{ url('/') }}" style="color:rgba(255,255,255,.7);text-decoration:none;font-size:13px">Accueil</a></div>
          <div><a href="{{ url('/catalogue') }}" style="color:rgba(255,255,255,.7);text-decoration:none;font-size:13px">Catalogue</a></div>
        </div>
        <div class="col-md-4">
          <div class="fw-semibold mb-2">Contact</div>
          <small style="opacity:.7">📧 contact@agroconnect.sn</small><br>
          <small style="opacity:.7">📞 +221 77 000 00 00</small>
        </div>
      </div>
      <hr style="border-color:rgba(255,255,255,.2);margin:1.5rem 0">
      <div class="text-center" style="font-size:12px;opacity:.6">
        © 2025 AgroConnect · Tous droits réservés
      </div>
    </div>
  </footer>

</body>
</html>