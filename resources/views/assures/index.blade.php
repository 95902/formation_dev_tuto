@extends('layouts.app')
@section('titre', 'Assurés — COMPAS')

@section('contenu')
  <div class="entete">
    <div><h1>Assurés</h1><p class="chapeau">{{ $assures->total() }} personnes au fichier.</p></div>
    <a class="b b-p" href="{{ route('assures.create') }}">Nouvel assuré</a>
  </div>

  <form method="get" class="filtres">
    <div class="champ large">
      <label for="q">Nom, référence ou ville</label>
      <input type="search" id="q" name="q" value="{{ request('q') }}">
    </div>
    <button class="b b-p" type="submit">Rechercher</button>
    <a class="b" href="{{ route('assures.index') }}">Effacer</a>
  </form>

  <div class="carte">
    <table>
      <thead><tr><th>Référence</th><th>Nom</th><th>Ville</th><th>Courriel</th><th>Téléphone</th><th class="num">Contrats</th></tr></thead>
      <tbody>
        @forelse ($assures as $assure)
          <tr>
            <td class="ref"><a href="{{ route('assures.show', $assure) }}">{{ $assure->reference }}</a></td>
            <td>{{ $assure->civilite }} {{ $assure->nomComplet() }}</td>
            <td>{{ $assure->code_postal }} {{ $assure->ville }}</td>
            <td>{{ $assure->email }}</td>
            <td>{{ $assure->telephone }}</td>
            <td class="num">{{ $assure->contrats_count }}</td>
          </tr>
        @empty
          <tr><td colspan="6" class="vide">Aucun assuré ne correspond.</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>

  {{ $assures->links() }}
@endsection
