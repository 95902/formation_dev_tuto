# Phase 0 Research: Caractères cassés en fin de réponse (T-03)

## Contexte

`ContextBuilder::truncate()` mesure et découpe le texte source avec `strlen()` /
`substr()`, deux fonctions PHP qui opèrent sur des **octets**, pas sur des caractères.
En UTF-8, un caractère accentué courant en français (`é`, `à`, `ç`, `ù`...) est encodé
sur 2 octets ; certains symboles (`—`, `…`) le sont sur 3. Si le budget restant tombe
au milieu de la séquence d'octets d'un tel caractère, `substr` produit un fragment
d'octet isolé, sans signification en UTF-8 — d'où l'affichage de `Ã` ou `�` observé
dans le ticket. Un mot sans accent, composé uniquement de caractères ASCII (1 octet
chacun), ne peut jamais être coupé « au milieu » : chaque octet est déjà un caractère
complet, ce qui explique pourquoi le symptôme n'apparaît jamais sur ces mots.

Aucun point du Technical Context du plan n'est marqué `NEEDS CLARIFICATION` : la pile
technique (PHP 8.3, extension `mbstring` déjà chargée, PHPUnit 12) est entièrement
déterminée par l'existant du dépôt. Le seul sujet à trancher est l'**approche de
troncature sûre pour l'UTF-8**.

## Decision

Remplacer le comptage et le découpage par octets (`strlen`/`substr`) par leurs
équivalents conscients de l'encodage (`mb_strlen`/`mb_substr`, encodage `UTF-8`
explicite) à l'intérieur de `ContextBuilder::truncate()` — et, si nécessaire, dans le
calcul du budget restant en amont dans `build()`, afin que la longueur mesurée et la
longueur réellement découpée restent cohérentes entre elles.

## Rationale

- `mbstring` est une extension PHP déjà chargée dans l'environnement du projet et déjà
  présente comme dépendance transitive (`composer.lock`) : aucune nouvelle dépendance,
  conforme au Principe IV (déterminisme, pas d'appel externe).
- `mb_substr(..., 'UTF-8')` découpe systématiquement entre deux caractères complets —
  il ne peut pas produire de fragment d'octet invalide, ce qui satisfait directement le
  critère d'acceptation du ticket (« aucune troncature ne peut produire une chaîne
  invalide en UTF-8 »).
- Le changement reste localisé aux deux fonctions qui comptent/découpent le texte ;
  il ne modifie ni la signature publique de `truncate()`/`build()`, ni le format du
  suffixe `...`, ni le principe d'un budget exprimé en nombre de caractères — conforme
  à la Discipline de périmètre (Principe III) et aux hypothèses FR-003/FR-005 de la
  spec.
- Pour un texte entièrement ASCII, `mb_strlen`/`mb_substr` avec l'encodage UTF-8
  renvoient exactement les mêmes résultats que `strlen`/`substr`, car chaque caractère
  ASCII correspond à un seul octet — le comportement existant pour les textes sans
  accent (SC-003, `test_it_stops_adding_blocks_once_the_budget_is_spent`) est préservé
  sans changement observable.

## Alternatives considered

- **Détecter et réparer après coup la séquence UTF-8 invalide** (ex. `iconv` en mode
  `//IGNORE`, ou validation post-troncature suivie d'un rognage des octets de queue
  invalides) : rejeté — plus complexe, et corrige un symptôme (séquence déjà cassée)
  plutôt que d'empêcher la cassure ; risque de couper un caractère de plus que
  nécessaire de façon moins prévisible que `mb_substr`.
- **Utiliser `grapheme_substr`/`grapheme_strlen` (extension `intl`)** pour découper par
  « graphèmes visuels » plutôt que par point de code : rejeté pour ce ticket — `intl`
  ajoute une dépendance d'extension non strictement nécessaire ici (le symptôme
  concerne des caractères accentués simples, pas des émojis composés ou des séquences
  combinantes), et `mbstring` suffit à garantir une chaîne UTF-8 valide, seul critère
  d'acceptation du ticket. À reconsidérer seulement si un futur ticket vise
  explicitement les graphèmes composés.
- **Convertir le budget en octets et ajuster dynamiquement à la volée** (essayer de
  couper, vérifier la validité, reculer d'un octet si invalide, répéter) : rejeté —
  plus de code, complexité supplémentaire pour un résultat strictement équivalent à
  `mb_substr`, qui fait déjà ce travail nativement et de façon déterministe.

## Résolu

Aucun `NEEDS CLARIFICATION` restant dans le Technical Context du plan.
