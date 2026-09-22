@extends('layouts.app')
@section('titre', $assure->nomComplet().' — COMPAS')

@section('contenu')
  <div class="fil"><a href="{{ route('assures.index') }}">Assurés</a> / {{ $assure->reference }}</div>

  <div class="entete">
    <div>
      <h1>{{ $assure->civilite }} {{ $assure->nomComplet() }}</h1>
      <p class="chapeau">{{ $assure->reference }} · né(e) le {{ $assure->date_naissance->format('d/m/Y') }}</p>
    </div>
    <a class="b b-p" href="{{ route('assures.edit', $assure) }}">Modifier</a>
  </div>

  <div class="carte">
    <h3>Coordonnées</h3>
    <dl class="defs">
      <dt>Adresse</dt><dd>{{ $assure->adresse }}<br>{{ $assure->code_postal }} {{ $assure->ville }}</dd>
      <dt>Courriel</dt><dd>{{ $assure->email }}</dd>
      <dt>Téléphone</dt><dd>{{ $assure->telephone }}</dd>
    </dl>
  </div>

  <div class="carte">
    <h3>Contrats ({{ $assure->contrats->count() }})</h3>
    <table>
      <thead><tr><th>Référence</th><th>Produit</th><th>Formule</th><th class="num">Garanties</th><th class="num">Sinistres</th><th>Statut</th></tr></thead>
      <tbody>
        @forelse ($assure->contrats as $contrat)
          <tr>
            <td class="ref"><a href="{{ route('contrats.show', $contrat) }}">{{ $contrat->reference }}</a></td>
            <td>{{ $contrat->produit }}</td>
            <td>{{ $contrat->formule }}</td>
            <td class="num">{{ $contrat->garanties->count() }}</td>
            <td class="num">{{ $contrat->sinistres->count() }}</td>
            <td><span class="p p-{{ $contrat->statut }}">{{ $contrat->statut }}</span></td>
          </tr>
        @empty
          <tr><td colspan="6" class="vide">Aucun contrat.</td></tr>
        @endforelse
      </tbody>
    </table>
    <p class="lien-suite">
      <a class="b" href="{{ route('contrats.create', ['assure_id' => $assure->id]) }}">Souscrire un contrat</a>
    </p>
  </div>

  <form method="post" action="{{ route('assures.destroy', $assure) }}"
        onsubmit="return confirm('Supprimer {{ $assure->nomComplet() }} ?')">
    @csrf @method('delete')
    <button class="b b-d" type="submit">Supprimer l'assuré</button>
  </form>
@endsection
