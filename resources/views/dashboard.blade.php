@extends('layouts.app')
@section('titre', 'Tableau de bord — COMPAS')

@section('contenu')
  <h1>Tableau de bord</h1>
  <p class="chapeau">Portefeuille au {{ \Database\Seeders\PortefeuilleSeeder::ANCRE }}.</p>

  <div class="tuiles">
    <div class="tuile"><div class="n">{{ $enInstruction }}</div><div class="l">Dossiers en instruction</div></div>
    <div class="tuile"><div class="n">{{ $clos }}</div><div class="l">Dossiers clos</div></div>
    <div class="tuile"><div class="n">{{ $refuses }}</div><div class="l">Dossiers refusés</div></div>
    <div class="tuile"><div class="n">{{ \App\Support\Euro::format($encours) }}</div><div class="l">Estimé en cours</div></div>
    <div class="tuile"><div class="n">{{ $contratsActifs }}</div><div class="l">Contrats actifs</div></div>
    <div class="tuile"><div class="n">{{ $assures }}</div><div class="l">Assurés</div></div>
  </div>

  <div class="carte">
    <h3>Derniers dossiers déclarés</h3>
    <table>
      <thead>
        <tr><th>Référence</th><th>Assuré</th><th>Nature</th><th>Déclaré le</th><th class="num">Estimé</th><th>Statut</th></tr>
      </thead>
      <tbody>
        @foreach ($derniers as $s)
          <tr>
            <td class="ref"><a href="{{ route('sinistres.show', $s) }}">{{ $s->reference }}</a></td>
            <td>{{ $s->contrat->assure->nomComplet() }}</td>
            <td>{{ $s->natureLibelle() }}</td>
            <td>{{ $s->declare_le->format('d/m/Y H:i') }}</td>
            <td class="num">{{ \App\Support\Euro::format($s->montant_estime_cents) }}</td>
            <td><span class="p p-{{ $s->statut }}">{{ $s->statutLibelle() }}</span></td>
          </tr>
        @endforeach
      </tbody>
    </table>
  </div>

  <div class="carte">
    <h3>Répartition par nature</h3>
    <table>
      <thead><tr><th>Nature</th><th class="num">Dossiers</th></tr></thead>
      <tbody>
        @foreach ($parNature as $ligne)
          <tr>
            <td><a href="{{ route('sinistres.index', ['nature' => $ligne->nature]) }}">{{ \App\Models\Sinistre::NATURES[$ligne->nature] ?? $ligne->nature }}</a></td>
            <td class="num">{{ $ligne->total }}</td>
          </tr>
        @endforeach
      </tbody>
    </table>
  </div>

  <p class="chapeau">{{ $documents }} documents internes sont indexés. <a href="{{ route('compas') }}">Interroger COMPAS</a>.</p>
@endsection
