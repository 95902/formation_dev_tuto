@extends('layouts.app')
@section('titre', 'Sinistres — COMPAS')

@section('contenu')
  <div class="entete">
    <div>
      <h1>Sinistres</h1>
      <p class="chapeau">Dossiers du portefeuille, du plus récemment déclaré au plus ancien.</p>
    </div>
    <a class="b b-p" href="{{ route('sinistres.create') }}">Ouvrir un dossier</a>
  </div>

  <form method="get" class="filtres">
    <div class="champ large">
      <label for="q">Référence ou description</label>
      <input type="search" id="q" name="q" value="{{ request('q') }}" placeholder="SIN-2026-…">
    </div>
    <div class="champ">
      <label for="statut">Statut</label>
      <select id="statut" name="statut">
        <option value="">Tous</option>
        @foreach (\App\Models\Sinistre::STATUTS as $cle => $libelle)
          <option value="{{ $cle }}" @selected(request('statut') === $cle)>{{ $libelle }}</option>
        @endforeach
      </select>
    </div>
    <div class="champ">
      <label for="nature">Nature</label>
      <select id="nature" name="nature">
        <option value="">Toutes</option>
        @foreach (\App\Models\Sinistre::NATURES as $cle => $libelle)
          <option value="{{ $cle }}" @selected(request('nature') === $cle)>{{ $libelle }}</option>
        @endforeach
      </select>
    </div>
    <div class="champ">
      <label for="du">Déclaré du</label>
      <input type="date" id="du" name="du" value="{{ request('du') }}">
    </div>
    <div class="champ">
      <label for="au">au</label>
      <input type="date" id="au" name="au" value="{{ request('au') }}">
    </div>
    <button class="b b-p" type="submit">Filtrer</button>
    <a class="b" href="{{ route('sinistres.index') }}">Effacer</a>
  </form>

  <div class="carte">
    <table>
      <thead>
        <tr>
          <th>Référence</th><th>Assuré</th><th>Contrat</th><th>Nature</th>
          <th>Déclaré le</th><th class="num">Estimé</th><th class="num">Indemnité</th>
          <th class="num">Pièces</th><th>Statut</th>
        </tr>
      </thead>
      <tbody>
        @forelse ($sinistres as $sinistre)
          <tr>
            <td class="ref"><a href="{{ route('sinistres.show', $sinistre) }}">{{ $sinistre->reference }}</a></td>
            <td>{{ $sinistre->contrat->assure->nomComplet() }}</td>
            <td class="ref"><a href="{{ route('contrats.show', $sinistre->contrat) }}">{{ $sinistre->contrat->reference }}</a></td>
            <td>{{ $sinistre->natureLibelle() }}</td>
            <td>{{ $sinistre->declare_le->format('d/m/Y H:i') }}</td>
            <td class="num">{{ \App\Support\Euro::format($sinistre->montant_estime_cents) }}</td>
            <td class="num">{{ \App\Support\Euro::format($sinistre->indemniteCents()) }}</td>
            <td class="num">{{ $sinistre->pieces->count() }}</td>
            <td><span class="p p-{{ $sinistre->statut }}">{{ $sinistre->statutLibelle() }}</span></td>
          </tr>
        @empty
          <tr><td colspan="9" class="vide">Aucun dossier ne correspond à ces filtres.</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>

  {{ $sinistres->links() }}
@endsection
