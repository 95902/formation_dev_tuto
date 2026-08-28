@extends('layouts.app')
@section('titre', 'Demander à COMPAS')

@section('contenu')
  <h1>Demander à COMPAS</h1>
  <p class="chapeau">
    COMPAS répond à partir de la documentation interne indexée, et cite ses sources
    sous la forme <span class="ref">chemin:ligne</span>. Il n'invente rien : si le
    contexte ne contient pas la réponse, il le dit.
  </p>

  @if ($sinistre)
    <div class="msg msg-ok">
      Question posée depuis le dossier
      <a href="{{ route('sinistres.show', $sinistre) }}">{{ $sinistre->reference }}</a>.
    </div>
  @endif

  <form method="get" class="carte">
    <div class="champ">
      <label for="question">Votre question</label>
      <input type="text" id="question" name="question" value="{{ $question }}"
             placeholder="Quel est le délai de déclaration d'un sinistre ?" autofocus>
      <div class="aide">Trois caractères minimum.</div>
    </div>
    @if ($sinistre)
      <input type="hidden" name="sinistre" value="{{ $sinistre->id }}">
    @endif
    <button class="b b-p" type="submit">Demander</button>

    <div class="pistes">
      @foreach ($suggestions as $piste)
        <a href="{{ route('compas', ['question' => $piste]) }}">{{ $piste }}</a>
      @endforeach
    </div>
  </form>

  @if ($answer)
    <h2>Réponse</h2>
    <div class="reponse">
      @foreach (preg_split('/\n+/', trim($answer->text)) as $paragraphe)
        <p>{{ $paragraphe }}</p>
      @endforeach
    </div>

    <div class="carte">
      <h3>Sources ({{ count($answer->citations) }})</h3>
      @if ($answer->hasSources())
        <ul class="sources">
          @foreach ($answer->citations as $citation)
            <li>
              <span class="lieu">{{ $citation->label() }}</span>
              <span class="extrait">{{ $citation->excerpt }}</span>
            </li>
          @endforeach
        </ul>
      @else
        <p class="vide">Aucun document du corpus ne répond à cette question.</p>
      @endif
      <p class="aide" style="margin-top:12px">
        Contexte transmis : {{ $answer->contextTokens }} tokens estimés.
      </p>
    </div>
  @endif
@endsection
