@extends('layouts.app')
@section('titre', ($sinistre->exists ? 'Modifier '.$sinistre->reference : 'Ouvrir un dossier').' — COMPAS')

@section('contenu')
  <div class="fil"><a href="{{ route('sinistres.index') }}">Sinistres</a> / {{ $sinistre->exists ? $sinistre->reference : 'nouveau' }}</div>
  <h1>{{ $sinistre->exists ? 'Modifier '.$sinistre->reference : 'Ouvrir un dossier' }}</h1>

  @include('partials.erreurs')

  <form method="post" action="{{ $sinistre->exists ? route('sinistres.update', $sinistre) : route('sinistres.store') }}">
    @csrf
    @if ($sinistre->exists) @method('put') @endif

    <div class="carte">
      <div class="grille">
        <div class="champ champ-pleine">
          <label for="contrat_id">Contrat</label>
          <select id="contrat_id" name="contrat_id">
            <option value="">—</option>
            @foreach ($contrats as $contrat)
              <option value="{{ $contrat->id }}" @selected(old('contrat_id', $sinistre->contrat_id) == $contrat->id)>
                {{ $contrat->reference }} — {{ $contrat->assure->nomComplet() }} ({{ $contrat->produit }})
              </option>
            @endforeach
          </select>
          @error('contrat_id') <div class="err">{{ $message }}</div> @enderror
        </div>

        <div class="champ">
          <label for="nature">Nature</label>
          <select id="nature" name="nature">
            @foreach (\App\Models\Sinistre::NATURES as $cle => $libelle)
              <option value="{{ $cle }}" @selected(old('nature', $sinistre->nature) === $cle)>{{ $libelle }}</option>
            @endforeach
          </select>
          @error('nature') <div class="err">{{ $message }}</div> @enderror
        </div>

        <div class="champ">
          <label for="statut">Statut</label>
          <select id="statut" name="statut">
            @foreach (\App\Models\Sinistre::STATUTS as $cle => $libelle)
              <option value="{{ $cle }}" @selected(old('statut', $sinistre->statut ?? 'declare') === $cle)>{{ $libelle }}</option>
            @endforeach
          </select>
          @error('statut') <div class="err">{{ $message }}</div> @enderror
        </div>

        <div class="champ">
          <label for="survenu_le">Survenu le</label>
          <input type="date" id="survenu_le" name="survenu_le"
                 value="{{ old('survenu_le', $sinistre->survenu_le?->toDateString()) }}">
          @error('survenu_le') <div class="err">{{ $message }}</div> @enderror
        </div>

        <div class="champ">
          <label for="declare_le">Déclaré le</label>
          <input type="datetime-local" id="declare_le" name="declare_le"
                 value="{{ old('declare_le', $sinistre->declare_le?->format('Y-m-d\TH:i')) }}">
          @error('declare_le') <div class="err">{{ $message }}</div> @enderror
        </div>

        <div class="champ">
          <label for="montant_estime">Montant estimé (€)</label>
          <input type="number" step="0.01" min="0" id="montant_estime" name="montant_estime"
                 value="{{ old('montant_estime', $sinistre->montant_estime_cents ? $sinistre->montant_estime_cents / 100 : '') }}">
          @error('montant_estime_cents') <div class="err">{{ $message }}</div> @enderror
        </div>

        <div class="champ">
          <label for="gestionnaire">Gestionnaire</label>
          <input type="text" id="gestionnaire" name="gestionnaire" value="{{ old('gestionnaire', $sinistre->gestionnaire) }}">
          @error('gestionnaire') <div class="err">{{ $message }}</div> @enderror
        </div>

        <div class="champ champ-pleine">
          <label for="description">Description</label>
          <textarea id="description" name="description">{{ old('description', $sinistre->description) }}</textarea>
          <div class="aide">Dix caractères minimum. C'est ce texte que la recherche interroge.</div>
          @error('description') <div class="err">{{ $message }}</div> @enderror
        </div>
      </div>

      <div class="barre-actions">
        <button class="b b-p" type="submit">Enregistrer</button>
        <a class="b" href="{{ $sinistre->exists ? route('sinistres.show', $sinistre) : route('sinistres.index') }}">Annuler</a>
      </div>
    </div>
  </form>
@endsection
