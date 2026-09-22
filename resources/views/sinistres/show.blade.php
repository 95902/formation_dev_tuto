@extends('layouts.app')
@section('titre', $sinistre->reference.' — COMPAS')

@section('contenu')
  <div class="fil"><a href="{{ route('sinistres.index') }}">Sinistres</a> / {{ $sinistre->reference }}</div>

  <div class="entete">
    <div>
      <h1>{{ $sinistre->reference }} — {{ $sinistre->natureLibelle() }}</h1>
      <p class="chapeau">
        <span class="p p-{{ $sinistre->statut }}">{{ $sinistre->statutLibelle() }}</span>
        · Contrat <a href="{{ route('contrats.show', $sinistre->contrat) }}">{{ $sinistre->contrat->reference }}</a>
        · <a href="{{ route('assures.show', $sinistre->contrat->assure) }}">{{ $sinistre->contrat->assure->nomComplet() }}</a>
      </p>
    </div>
    <div class="actions">
      <a class="b" href="{{ route('compas', ['question' => 'Quelle franchise s\'applique à un '.mb_strtolower($sinistre->natureLibelle()).' ?', 'sinistre' => $sinistre->id]) }}">Demander à COMPAS</a>
      <a class="b b-p" href="{{ route('sinistres.edit', $sinistre) }}">Modifier</a>
    </div>
  </div>

  <div class="carte">
    <h3>Dossier</h3>
    <dl class="defs">
      <dt>Survenu le</dt><dd>{{ $sinistre->survenu_le->format('d/m/Y') }}</dd>
      <dt>Déclaré le</dt><dd>{{ $sinistre->declare_le->format('d/m/Y à H:i') }}</dd>
      <dt>Délai de déclaration</dt>
      <dd>
        {{ $sinistre->delaiDeclarationJours() }} jour(s)
        @if ($sinistre->delaiDeclarationJours() > 5)
          <span class="p p-refuse">hors délai</span>
        @endif
      </dd>
      <dt>Gestionnaire</dt><dd>{{ $sinistre->gestionnaire ?? '—' }}</dd>
      <dt>Description</dt><dd>{{ $sinistre->description }}</dd>
    </dl>
  </div>

  <div class="carte">
    <h3>Règlement</h3>
    @php $garantie = $sinistre->contrat->garantiePour($sinistre->nature); @endphp
    <dl class="defs">
      <dt>Montant estimé</dt><dd>{{ \App\Support\Euro::format($sinistre->montant_estime_cents) }}</dd>
      <dt>Garantie mobilisée</dt>
      <dd>
        @if ($garantie)
          {{ $garantie->libelle }} ({{ $garantie->code }}) — plafond {{ \App\Support\Euro::format($garantie->plafond_cents) }}
        @else
          <span class="p p-refuse">aucune garantie ne couvre cette nature</span>
        @endif
      </dd>
      <dt>Franchise opposable</dt><dd>{{ \App\Support\Euro::format($sinistre->franchiseApplicableCents()) }}</dd>
      <dt>Vétusté</dt><dd>{{ number_format((1 - \App\Models\Sinistre::TAUX_VETUSTE) * 100, 0) }} %</dd>
      <dt><b>Indemnité calculée</b></dt><dd><b>{{ \App\Support\Euro::format($sinistre->indemniteCents()) }}</b></dd>
      <dt>Reste à charge</dt><dd>{{ \App\Support\Euro::format($sinistre->resteAChargeCents()) }}</dd>
      <dt>Montant réglé</dt><dd>{{ \App\Support\Euro::format($sinistre->montant_regle_cents) }}</dd>
    </dl>
  </div>

  <div class="carte">
    <h3>Pièces au dossier ({{ $sinistre->pieces->count() }})</h3>
    <table>
      <thead><tr><th>Pièce</th><th>Type</th><th>Reçue le</th><th></th></tr></thead>
      <tbody>
        @forelse ($sinistre->pieces as $piece)
          <tr>
            <td>{{ $piece->libelle }}</td>
            <td>{{ $piece->type }}</td>
            <td>{{ $piece->recue_le->format('d/m/Y') }}</td>
            <td class="num">
              <form method="post" action="{{ route('pieces.destroy', [$sinistre, $piece]) }}">
                @csrf @method('delete')
                <button class="b b-s b-d" type="submit">Retirer</button>
              </form>
            </td>
          </tr>
        @empty
          <tr><td colspan="4" class="vide">Aucune pièce reçue.</td></tr>
        @endforelse
      </tbody>
    </table>

    <form method="post" action="{{ route('pieces.store', $sinistre) }}" class="groupe-ajout">
      @csrf
      <div class="grille">
        <div class="champ">
          <label for="type">Type</label>
          <select id="type" name="type">
            @foreach (\App\Models\Piece::TYPES as $type)
              <option value="{{ $type }}">{{ \App\Models\Piece::LIBELLES[$type] }}</option>
            @endforeach
          </select>
        </div>
        <div class="champ">
          <label for="libelle">Libellé (facultatif)</label>
          <input type="text" id="libelle" name="libelle" placeholder="Reprend le type par défaut">
        </div>
        <div class="champ">
          <label for="recue_le">Reçue le</label>
          <input type="date" id="recue_le" name="recue_le" value="{{ now()->toDateString() }}">
        </div>
      </div>
      <button class="b" type="submit">Ajouter la pièce</button>
    </form>
  </div>

  <form method="post" action="{{ route('sinistres.destroy', $sinistre) }}"
        onsubmit="return confirm('Supprimer définitivement {{ $sinistre->reference }} ?')">
    @csrf @method('delete')
    <button class="b b-d" type="submit">Supprimer le dossier</button>
  </form>
@endsection
