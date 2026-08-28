@extends('layouts.app')
@section('titre', ($contrat->exists ? 'Modifier '.$contrat->reference : 'Nouveau contrat').' — COMPAS')

@section('contenu')
  <div class="fil"><a href="{{ route('contrats.index') }}">Contrats</a> / {{ $contrat->exists ? $contrat->reference : 'nouveau' }}</div>
  <h1>{{ $contrat->exists ? 'Modifier '.$contrat->reference : 'Nouveau contrat' }}</h1>

  @include('partials.erreurs')

  <form method="post" action="{{ $contrat->exists ? route('contrats.update', $contrat) : route('contrats.store') }}">
    @csrf
    @if ($contrat->exists) @method('put') @endif

    <div class="carte">
      <div class="grille">
        <div class="champ" style="grid-column:1/-1">
          <label for="assure_id">Assuré</label>
          <select id="assure_id" name="assure_id">
            <option value="">—</option>
            @foreach ($assures as $assure)
              <option value="{{ $assure->id }}" @selected(old('assure_id', $contrat->assure_id) == $assure->id)>
                {{ $assure->nomComplet() }} ({{ $assure->reference }})
              </option>
            @endforeach
          </select>
          @error('assure_id') <div class="err">{{ $message }}</div> @enderror
        </div>

        <div class="champ">
          <label for="reference">Référence</label>
          <input type="text" id="reference" name="reference" value="{{ old('reference', $contrat->reference) }}" placeholder="CTR-2026-…">
          @error('reference') <div class="err">{{ $message }}</div> @enderror
        </div>

        <div class="champ">
          <label for="produit">Produit</label>
          <select id="produit" name="produit">
            @foreach (\App\Models\Contrat::PRODUITS as $p)
              <option value="{{ $p }}" @selected(old('produit', $contrat->produit) === $p)>{{ $p }}</option>
            @endforeach
          </select>
          @error('produit') <div class="err">{{ $message }}</div> @enderror
        </div>

        <div class="champ">
          <label for="formule">Formule</label>
          <select id="formule" name="formule">
            @foreach (\App\Models\Contrat::FORMULES as $f)
              <option value="{{ $f }}" @selected(old('formule', $contrat->formule) === $f)>{{ $f }}</option>
            @endforeach
          </select>
          @error('formule') <div class="err">{{ $message }}</div> @enderror
        </div>

        <div class="champ">
          <label for="statut">Statut</label>
          <select id="statut" name="statut">
            @foreach (\App\Models\Contrat::STATUTS as $s)
              <option value="{{ $s }}" @selected(old('statut', $contrat->statut ?? 'actif') === $s)>{{ $s }}</option>
            @endforeach
          </select>
          @error('statut') <div class="err">{{ $message }}</div> @enderror
        </div>

        <div class="champ">
          <label for="date_effet">Date d'effet</label>
          <input type="date" id="date_effet" name="date_effet" value="{{ old('date_effet', $contrat->date_effet?->toDateString()) }}">
          @error('date_effet') <div class="err">{{ $message }}</div> @enderror
        </div>

        <div class="champ">
          <label for="date_echeance">Échéance</label>
          <input type="date" id="date_echeance" name="date_echeance" value="{{ old('date_echeance', $contrat->date_echeance?->toDateString()) }}">
          @error('date_echeance') <div class="err">{{ $message }}</div> @enderror
        </div>

        <div class="champ">
          <label for="prime_annuelle_euros">Prime annuelle (€)</label>
          <input type="number" step="0.01" min="0" id="prime_annuelle_euros" name="prime_annuelle_euros"
                 value="{{ old('prime_annuelle_euros', $contrat->prime_annuelle_cents ? $contrat->prime_annuelle_cents / 100 : '') }}">
          @error('prime_annuelle_euros') <div class="err">{{ $message }}</div> @enderror
        </div>

        <div class="champ">
          <label for="franchise_euros">Franchise générale (€)</label>
          <input type="number" step="0.01" min="0" id="franchise_euros" name="franchise_euros"
                 value="{{ old('franchise_euros', $contrat->franchise_cents ? $contrat->franchise_cents / 100 : '') }}">
          <div class="aide">Utilisée quand la garantie mobilisée n'en porte pas.</div>
          @error('franchise_euros') <div class="err">{{ $message }}</div> @enderror
        </div>
      </div>

      <div class="barre-actions">
        <button class="b b-p" type="submit">Enregistrer</button>
        <a class="b" href="{{ $contrat->exists ? route('contrats.show', $contrat) : route('contrats.index') }}">Annuler</a>
      </div>
    </div>
  </form>
@endsection
