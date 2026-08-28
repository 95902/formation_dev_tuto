@extends('layouts.app')
@section('titre', 'Contrats — COMPAS')

@section('contenu')
  <div class="entete">
    <div><h1>Contrats</h1><p class="chapeau">{{ $contrats->total() }} contrats au portefeuille.</p></div>
    <a class="b b-p" href="{{ route('contrats.create') }}">Nouveau contrat</a>
  </div>

  <form method="get" class="filtres">
    <div class="champ">
      <label for="produit">Produit</label>
      <select id="produit" name="produit">
        <option value="">Tous</option>
        @foreach (\App\Models\Contrat::PRODUITS as $p)
          <option value="{{ $p }}" @selected(request('produit') === $p)>{{ $p }}</option>
        @endforeach
      </select>
    </div>
    <div class="champ">
      <label for="statut">Statut</label>
      <select id="statut" name="statut">
        <option value="">Tous</option>
        @foreach (\App\Models\Contrat::STATUTS as $s)
          <option value="{{ $s }}" @selected(request('statut') === $s)>{{ $s }}</option>
        @endforeach
      </select>
    </div>
    <button class="b b-p" type="submit">Filtrer</button>
    <a class="b" href="{{ route('contrats.index') }}">Effacer</a>
  </form>

  <div class="carte">
    <table>
      <thead>
        <tr><th>Référence</th><th>Assuré</th><th>Produit</th><th>Formule</th>
            <th>Effet</th><th class="num">Prime</th><th class="num">Sinistres</th><th>Statut</th></tr>
      </thead>
      <tbody>
        @forelse ($contrats as $contrat)
          <tr>
            <td class="ref"><a href="{{ route('contrats.show', $contrat) }}">{{ $contrat->reference }}</a></td>
            <td><a href="{{ route('assures.show', $contrat->assure) }}">{{ $contrat->assure->nomComplet() }}</a></td>
            <td>{{ $contrat->produit }}</td>
            <td>{{ $contrat->formule }}</td>
            <td>{{ $contrat->date_effet->format('d/m/Y') }}</td>
            <td class="num">{{ \App\Support\Euro::format($contrat->prime_annuelle_cents) }}</td>
            <td class="num">{{ $contrat->sinistres_count }}</td>
            <td><span class="p p-{{ $contrat->statut }}">{{ $contrat->statut }}</span></td>
          </tr>
        @empty
          <tr><td colspan="8" class="vide">Aucun contrat.</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>

  {{ $contrats->links() }}
@endsection
