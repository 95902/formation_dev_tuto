<!doctype html>
<html lang="fr">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>@yield('titre', 'COMPAS')</title>
<link rel="stylesheet" href="{{ asset('css/app.css') }}">
</head>
<body>
<div class="appli">

  <nav class="rail">
    <div class="marque">COMPAS</div>
    <div class="sous">Gestion sinistres</div>

    <a href="{{ route('dashboard') }}" class="{{ request()->routeIs('dashboard') ? 'on' : '' }}">Tableau de bord</a>

    <div class="groupe">Portefeuille</div>
    <a href="{{ route('sinistres.index') }}" class="{{ request()->routeIs('sinistres.*') ? 'on' : '' }}">Sinistres</a>
    <a href="{{ route('contrats.index') }}" class="{{ request()->routeIs('contrats.*') ? 'on' : '' }}">Contrats</a>
    <a href="{{ route('assures.index') }}" class="{{ request()->routeIs('assures.*') ? 'on' : '' }}">Assurés</a>

    <div class="groupe">Assistance</div>
    <a href="{{ route('compas') }}" class="{{ request()->routeIs('compas') ? 'on' : '' }}">Demander à COMPAS</a>
  </nav>

  <main class="page">
    @if (session('ok'))
      <div class="msg msg-ok">{{ session('ok') }}</div>
    @endif
    @if (session('erreur'))
      <div class="msg msg-err">{{ session('erreur') }}</div>
    @endif

    @yield('contenu')
  </main>

</div>

@isset($queryLog)
  <div class="sonde" title="Requêtes SQL déclenchées par cette page">
    <b class="{{ $queryLog->count() > 30 ? 'chaud' : '' }}">{{ $queryLog->count() }}</b> requêtes ·
    {{ $queryLog->millis() }} ms
  </div>
@endisset

</body>
</html>
