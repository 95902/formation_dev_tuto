@extends('layouts.app')
@section('titre', ($assure->exists ? 'Modifier '.$assure->nomComplet() : 'Nouvel assuré').' — COMPAS')

@section('contenu')
  <div class="fil"><a href="{{ route('assures.index') }}">Assurés</a> / {{ $assure->exists ? $assure->reference : 'nouveau' }}</div>
  <h1>{{ $assure->exists ? 'Modifier '.$assure->nomComplet() : 'Nouvel assuré' }}</h1>

  @include('partials.erreurs')

  <form method="post" action="{{ $assure->exists ? route('assures.update', $assure) : route('assures.store') }}">
    @csrf
    @if ($assure->exists) @method('put') @endif

    <div class="carte">
      <div class="grille">
        <div class="champ">
          <label for="civilite">Civilité</label>
          <select id="civilite" name="civilite">
            @foreach (['M.', 'Mme'] as $c)
              <option value="{{ $c }}" @selected(old('civilite', $assure->civilite) === $c)>{{ $c }}</option>
            @endforeach
          </select>
          @error('civilite') <div class="err">{{ $message }}</div> @enderror
        </div>

        <div class="champ">
          <label for="prenom">Prénom</label>
          <input type="text" id="prenom" name="prenom" value="{{ old('prenom', $assure->prenom) }}">
          @error('prenom') <div class="err">{{ $message }}</div> @enderror
        </div>

        <div class="champ">
          <label for="nom">Nom</label>
          <input type="text" id="nom" name="nom" value="{{ old('nom', $assure->nom) }}">
          @error('nom') <div class="err">{{ $message }}</div> @enderror
        </div>

        <div class="champ">
          <label for="date_naissance">Date de naissance</label>
          <input type="date" id="date_naissance" name="date_naissance" value="{{ old('date_naissance', $assure->date_naissance?->toDateString()) }}">
          @error('date_naissance') <div class="err">{{ $message }}</div> @enderror
        </div>

        <div class="champ">
          <label for="email">Courriel</label>
          <input type="email" id="email" name="email" value="{{ old('email', $assure->email) }}">
          @error('email') <div class="err">{{ $message }}</div> @enderror
        </div>

        <div class="champ">
          <label for="telephone">Téléphone</label>
          <input type="text" id="telephone" name="telephone" value="{{ old('telephone', $assure->telephone) }}">
          @error('telephone') <div class="err">{{ $message }}</div> @enderror
        </div>

        <div class="champ" style="grid-column:1/-1">
          <label for="adresse">Adresse</label>
          <input type="text" id="adresse" name="adresse" value="{{ old('adresse', $assure->adresse) }}">
          @error('adresse') <div class="err">{{ $message }}</div> @enderror
        </div>

        <div class="champ">
          <label for="code_postal">Code postal</label>
          <input type="text" id="code_postal" name="code_postal" maxlength="5" value="{{ old('code_postal', $assure->code_postal) }}">
          @error('code_postal') <div class="err">{{ $message }}</div> @enderror
        </div>

        <div class="champ">
          <label for="ville">Ville</label>
          <input type="text" id="ville" name="ville" value="{{ old('ville', $assure->ville) }}">
          @error('ville') <div class="err">{{ $message }}</div> @enderror
        </div>
      </div>

      <div class="barre-actions">
        <button class="b b-p" type="submit">Enregistrer</button>
        <a class="b" href="{{ $assure->exists ? route('assures.show', $assure) : route('assures.index') }}">Annuler</a>
      </div>
    </div>
  </form>
@endsection
