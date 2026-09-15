# Phase 1 Data Model: Caractères cassés en fin de réponse (T-03)

Cette fonctionnalité ne modifie ni n'introduit de modèle de données persistant (pas de
migration, pas de champ Eloquent concerné). Le seul « objet » manipulé est un texte en
mémoire, décrit ici pour clarifier le contrat de la fonction corrigée.

## Extrait de texte (contexte)

Correspond à l'entité « Extrait de document (contexte) » de `spec.md`.

| Attribut | Type | Description |
|----------|------|--------------|
| `text` | `string` (UTF-8) | Contenu source à inclure dans le contexte de réponse (`Document::content`, ou une portion déjà assemblée). |
| `maxChars` | `int` | Budget restant, exprimé en **nombre de caractères UTF-8**, pas en octets. |

### Règles de validité (dérivées de FR-001 à FR-005)

- Le texte produit par la troncature DOIT être une chaîne UTF-8 valide de bout en bout
  (FR-001) — aucune séquence d'octets incomplète en sortie.
- La longueur du texte produit (contenu conservé + `...` éventuel) DOIT être inférieure
  ou égale à `maxChars` **caractères** (FR-002) — pas d'unité mixte octets/caractères.
- Si `mb_strlen(text) <= maxChars`, le texte est retourné inchangé, sans suffixe
  (FR-003).
- Sinon, le texte est coupé exactement à la frontière d'un caractère (jamais à
  l'intérieur), puis suffixé par `...` (FR-004).
- Pour un texte composé uniquement de caractères ASCII, le résultat (longueur retenue,
  présence du suffixe) est identique à celui produit avant correctif (FR-005).

### État / transitions

Pas de cycle de vie ni d'état persistant : la troncature est une fonction pure,
appelée une fois par extrait retenu lors de la construction du contexte
(`ContextBuilder::build()`), sans effet de bord.
