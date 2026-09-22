@extends('layouts.app')
@section('titre', $contrat->reference.' — COMPAS')

@section('contenu')
  <div class="fil"><a href="{{ route('contrats.index') }}">Contrats</a> / {{ $contrat->reference }}</div>

  <div class="entete">
    <div>
      <h1>{{ $contrat->reference }}</h1>
      <p class="chapeau">
        <span class="p p-{{ $contrat->statut }}">{{ $contrat->statut }}</span>
        · {{ $contrat->produit }} — formule {{ $contrat->formule }}
        · <a href="{{ route('assures.show', $contrat->assure) }}">{{ $contrat->assure->nomComplet() }}</a>
      </p>
    </div>
    <a class="b b-p" href="{{ route('contrats.edit', $contrat) }}">Modifier</a>
  </div>

  <div class="carte">
    <h3>Conditions</h3>
    <dl class="defs">
      <dt>Date d'effet</dt><dd>{{ $contrat->date_effet->format('d/m/Y') }}</dd>
      <dt>Échéance</dt><dd>{{ $contrat->date_echeance->format('d/m/Y') }}</dd>
      <dt>Prime annuelle</dt><dd>{{ \App\Support\Euro::format($contrat->prime_annuelle_cents) }}</dd>
      <dt>Franchise générale</dt><dd>{{ \App\Support\Euro::format($contrat->franchise_cents) }}</dd>
    </dl>
  </div>

  <div class="carte">
    <h3>Garanties</h3>
    <table>
      <thead><tr><th>Code</th><th>Libellé</th><th class="num">Plafond</th><th class="num">Franchise</th><th></th></tr></thead>
      <tbody>
        @forelse ($contrat->garanties as $garantie)
          <tr>
            <td class="ref">{{ $garantie->code }}</td>
            <td>{{ $garantie->libelle }}</td>
            <td class="num">{{ \App\Support\Euro::format($garantie->plafond_cents) }}</td>
            <td class="num">{{ \App\Support\Euro::format($garantie->franchise_cents) }}</td>
            <td class="num">
              <form method="post" action="{{ route('garanties.destroy', [$contrat, $garantie]) }}">
                @csrf @method('delete')
                <button class="b b-s b-d" type="submit">Retirer</button>
              </form>
            </td>
          </tr>
        @empty
          <tr><td colspan="5" class="vide">Aucune garantie. Le contrat ne couvre rien en l'état.</td></tr>
        @endforelse
      </tbody>
    </table>

    @include('partials.erreurs')

    <form method="post" action="{{ route('garanties.store', $contrat) }}" class="groupe-ajout">
      @csrf
      <div class="grille">
        <div class="champ">
          <label for="code">Garantie</label>
          <select id="code" name="code">
            @foreach (\App\Models\Garantie::LIBELLES as $code => $libelle)
              <option value="{{ $code }}">{{ $code }} — {{ $libelle }}</option>
            @endforeach
          </select>
        </div>
        <div class="champ">
          <label for="plafond_euros">Plafond (€)</label>
          <input type="number" step="0.01" min="0" id="plafond_euros" name="plafond_euros" value="10000">
        </div>
        <div class="champ">
          <label for="franchise_euros">Franchise (€)</label>
          <input type="number" step="0.01" min="0" id="franchise_euros" name="franchise_euros" value="150">
        </div>
      </div>
      <button class="b" type="submit">Ajouter la garantie</button>
    </form>
  </div>

  <div class="carte">
    <h3>Sinistres rattachés ({{ $contrat->sinistres->count() }})</h3>
    <table>
      <thead><tr><th>Référence</th><th>Nature</th><th>Déclaré le</th><th class="num">Estimé</th><th>Statut</th></tr></thead>
      <tbody>
        @forelse ($contrat->sinistres->sortByDesc('declare_le') as $sinistre)
          <tr>
            <td class="ref"><a href="{{ route('sinistres.show', $sinistre) }}">{{ $sinistre->reference }}</a></td>
            <td>{{ $sinistre->natureLibelle() }}</td>
            <td>{{ $sinistre->declare_le->format('d/m/Y') }}</td>
            <td class="num">{{ \App\Support\Euro::format($sinistre->montant_estime_cents) }}</td>
            <td><span class="p p-{{ $sinistre->statut }}">{{ $sinistre->statutLibelle() }}</span></td>
          </tr>
        @empty
          <tr><td colspan="5" class="vide">Aucun sinistre déclaré sur ce contrat.</td></tr>
        @endforelse
      </tbody>
    </table>
    <p class="lien-suite">
      <a class="b" href="{{ route('sinistres.create', ['contrat_id' => $contrat->id]) }}">Déclarer un sinistre</a>
    </p>
  </div>

  <form method="post" action="{{ route('contrats.destroy', $contrat) }}"
        onsubmit="return confirm('Supprimer le contrat {{ $contrat->reference }} ?')">
    @csrf @method('delete')
    <button class="b b-d" type="submit">Supprimer le contrat</button>
  </form>
@endsection
