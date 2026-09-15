# Q&A par récupération augmentée (chat « Compas »)

Pipeline qui répond à une question en langage naturel en s'appuyant
exclusivement sur un corpus de documents internes (`Document`), et en
citant ses sources sous la forme `chemin:ligne`. Point d'entrée unique :
`Answerer::ask()`.

## Vue d'ensemble du flux

```
question
   │
   ▼
DocumentSearch::search()        recherche par mots-clés dans les Document
   │  Collection<ScoredDocument>
   ▼
ContextBuilder::build()         assemble un bloc de contexte sous budget
   │  string $context
   ▼
LlmClient::complete()           reformule (StubLlmClient, déterministe)
   │  string $text
   ▼
Citation::locate() (par résultat)   associe un extrait à une ligne du fichier
   │
   ▼
Answer { question, text, citations[], contextTokens }
   │
   ▼
Formatters (Cli / Html / Json / Markdown / Slack)
```

Tout est orchestré par `App\Services\Answering\Answerer` :

```php
public function ask(string $question, int $limit = 5): Answer
```

1. `DocumentSearch::search($question, $limit)` renvoie une
   `Collection<ScoredDocument>` triée par pertinence décroissante.
2. `ContextBuilder::build($results)` concatène les documents retenus en un
   seul bloc de texte.
3. `LlmClient::complete($system, $prompt)` produit le texte de réponse ;
   `$prompt` est construit par `Answerer::prompt()` sous la forme
   `"CONTEXTE :\n{$context}\n\nQUESTION : {$question}"`. Le message système
   (`Answerer::SYSTEM`) demande explicitement de ne s'appuyer que sur le
   contexte fourni et de dire si la réponse n'y est pas.
4. Pour chaque résultat, un extrait pertinent est localisé
   (`Answerer::excerpt()`) puis transformé en `Citation` via
   `Citation::locate()`.
5. `ContextBuilder::estimateTokens($context)` fournit le
   `contextTokens` de l'`Answer` finale.

## Étape 1 — `DocumentSearch` (`app/Services/Retrieval/DocumentSearch.php`)

Recherche par mots-clés, sans embeddings — volontairement déterministe
(la colonne pgvector existe déjà en base pour un futur branchement
vectoriel, mais n'est pas utilisée aujourd'hui).

- `tokenize(string $query): array<string>` — découpe la requête sur tout
  ce qui n'est pas lettre/chiffre Unicode (`preg_split` avec
  `[^\p{L}\p{N}]+`), met chaque terme en minuscules (`mb_strtolower`), et
  ne conserve que les termes de plus de 2 caractères (`mb_strlen($p) > 2`).
- `score(Document $doc, array $terms): float` — compare les termes (déjà en
  minuscules) au contenu et au titre du document, eux-mêmes mis en
  minuscules (`mb_strtolower`). Chaque occurrence dans `content` vaut 1
  point (`substr_count`), chaque occurrence dans `title` vaut 3 points.
- `search(string $query, int $limit = 5): Collection<ScoredDocument>` —
  tokenize la requête (retourne une collection vide si aucun terme ne
  passe le filtre de `tokenize`), score tous les `Document` en base
  (`Document::all()`), écarte les scores nuls, trie par score décroissant,
  déduplique (voir ci-dessous), puis tronque à `$limit` résultats.
  `MAX_RESULTS = 50` est une constante publique documentant la limite
  haute théorique, mais n'est pas appliquée dans `search()` lui-même — la
  borne effective est le paramètre `$limit`.
- `dedupe()` (privée) — élimine les doublons sur la base du **chemin**
  (`document->path`) : le premier document rencontré pour un chemin donné
  (dans l'ordre de tri par score) est conservé, les suivants avec le même
  chemin sont écartés.

`ScoredDocument` (`app/Services/Retrieval/ScoredDocument.php`) est un
simple value object `{ document: Document, score: float }`.

## Étape 2 — `ContextBuilder` (`app/Services/Retrieval/ContextBuilder.php`)

Assemble les documents retenus en un seul bloc de texte, sous un budget de
caractères — le contexte est repayé à chaque appel, donc plus il est gros,
plus l'appel coûte cher.

- `build(Collection $results, int $budget = self::DEFAULT_BUDGET): string`
  (`DEFAULT_BUDGET = 2000`) — pour chaque résultat, préfixe un en-tête
  `"--- {chemin} ---\n"`, calcule l'espace restant dans le budget, tronque
  le contenu du document à cet espace via `truncate()`, et arrête la boucle
  dès que l'espace restant devient `<= 0`. Les blocs sont joints par
  `"\n\n"`.
- `truncate(string $text, int $maxChars): string` — si `strlen($text) <=
  $maxChars`, retourne le texte tel quel ; sinon `substr($text, 0,
  $maxChars).'...'`. La troncature opère en octets (`substr`/`strlen`), pas
  en caractères multi-octets.
- `estimateTokens(string $text): int` — estimation du coût en tokens,
  calculée comme `round(str_word_count($text) * 0.75)`. Le commentaire du
  code présente ce ratio comme « 1 token vaut environ 3/4 de mot ».
  `str_word_count` ne reconnaît que les caractères ASCII comme faisant
  partie d'un mot (les lettres accentuées ne sont pas comptées comme telles
  par défaut).

## Étape 3 — `LlmClient` (`app/Services/Llm/`)

Interface :

```php
interface LlmClient {
    public function complete(string $system, string $user): string;
}
```

Liée dans `AppServiceProvider::register()` :

```php
$this->app->bind(LlmClient::class, StubLlmClient::class);
```

### `StubLlmClient` (implémentation branchée par défaut)

Aucun appel réseau, aucune clé requise, comportement déterministe.

- `split(string $prompt): array{context, question}` — retrouve la dernière
  occurrence de `"\nQUESTION : "` (`QUESTION_MARKER`) pour séparer contexte
  et question, puis retire le préfixe `"CONTEXTE :\n"` (`CONTEXT_MARKER`)
  du contexte s'il est présent.
- `sentences(string $context): array<string>` — retire les lignes
  d'en-tête de bloc (celles commençant par `---`, insérées par
  `ContextBuilder`), aplatit le texte restant en une seule chaîne
  (espaces normalisés), découpe en phrases sur `.`/`!`/`?` suivis d'espace
  (`preg_split` avec lookbehind), et ne garde que les phrases de plus de
  30 caractères.
- `complete(string $system, string $user): string` — si aucune phrase
  n'est retenue, renvoie `"Je n'ai rien trouve dans la documentation
  interne au sujet de : {question}"`. Sinon renvoie `"D'apres la
  documentation interne : "` suivi des 3 premières phrases retenues
  (`MAX_SENTENCES = 3`), jointes par un espace.

### `HttpLlmClient` (jamais branchée)

Client HTTP réel vers un endpoint fictif
(`https://api.exemple-interne.test/v1/messages`), avec une clé API en
constante de classe. Existe uniquement comme support d'atelier ; le
conteneur de service ne le résout jamais (voir `AppServiceProvider`).

## Étape 4 — `Citation` (`app/Services/Answering/Citation.php`)

```php
readonly class Citation {
    public function __construct(
        public Document $document,
        public string $excerpt,
        public int $line,
    ) {}

    public function label(): string;    // "{document->path}:{line}"
    public static function locate(Document $document, string $excerpt): self;
}
```

`locate()` cherche la position de `$excerpt` dans `$document->content`
(`mb_strpos`). Si l'extrait n'est pas trouvé, la citation pointe sur la
ligne 1. Sinon, le numéro de ligne est calculé en comptant les sauts de
ligne (`substr_count($before, "\n")`) dans la portion de texte qui précède
l'extrait.

`Answerer::excerpt()` détermine l'extrait à localiser pour chaque résultat
de recherche : il renvoie la première ligne du document qui contient l'un
des termes tokenisés de la question (comparaison insensible à la casse via
`mb_stripos`), ou à défaut la première ligne du document.

## Objet `Answer` (`app/Services/Answering/Answer.php`)

```php
readonly class Answer {
    public function __construct(
        public string $question,
        public string $text,
        public array $citations,   // Citation[]
        public int $contextTokens,
    ) {}

    public function hasSources(): bool;   // citations !== []
}
```

## Étape 5 — Formatters (`app/Services/Answering/Formatters/`)

Cinq classes, chacune transformant un `Answer` en une représentation de
sortie différente. Elles partagent la même structure logique (question,
texte, liste de sources avec leur `label()` et un extrait raccourci à 60
caractères, puis mention du nombre de tokens de contexte) mais ne
factorisent pas de classe commune — chaque formatter réimplémente son
propre `shorten()` et sa propre mise en forme de ligne de citation.

| Formatter | Méthode publique | Sortie |
|---|---|---|
| `CliFormatter` | `format(Answer): string` | texte brut, `wordwrap` à 78 colonnes, sources en liste à tirets |
| `HtmlFormatter` | `format(Answer): string` | fragment HTML échappé (`e()`), `<p>`/`<ul>`/`<li>`/`<code>` |
| `JsonFormatter` | `toArray(Answer): array` et `format(Answer): string` | `toArray()` produit `question`, `answer`, `source_count`, `sources[]` (`label`, `path`, `line`, `excerpt`), `context_tokens` ; `format()` encode ce tableau en JSON joliment indenté (`JSON_PRETTY_PRINT`, unicode et slashes non échappés) |
| `MarkdownFormatter` | `format(Answer): string` | Markdown, code inline pour le `label()` de chaque citation |
| `SlackFormatter` | `format(Answer): string` | syntaxe mrkdwn Slack (`*gras*`, puces `•`, code inline) |

## Points d'entrée

### Interface web — `CompasController` (`GET /compas`, route `compas`)

```php
public function __invoke(Request $request, Answerer $answerer): View
```

Lit `question` (query string), déclenche `Answerer::ask()` seulement si la
question fait au moins 3 caractères (`mb_strlen($question) >= 3`), avec un
`limit` optionnel (`request->integer('limit') ?: 5`). Charge aussi,
optionnellement, un `Sinistre` (avec son `contrat`) si un paramètre
`sinistre` est fourni — cette valeur est passée à la vue mais n'influence
pas la recherche elle-même. La vue reçoit une liste statique de 4
suggestions de questions. Rendu final via la vue Blade `compas` (pas de
formatter dédié ; l'affichage HTML est géré directement dans le template).

### API JSON — `AskController` (`POST /api/ask`, route `ask`)

```php
public function __invoke(Request $request, Answerer $answerer, JsonFormatter $formatter): JsonResponse
```

Valide `question` (`required|string|min:3|max:500`) et `limit` optionnel
(`integer|min:1|max:20`, défaut 5), appelle `Answerer::ask()`, puis renvoie
`$formatter->toArray($answer)` en JSON.

### Commande Artisan — `compas:ask` (`app/Console/Commands/AskCommand.php`)

```
php artisan compas:ask {question* : La question posee} {--limit=5 : Nombre de documents retenus}
```

`question*` est un argument variadique : tous les mots passés sont
rejoints par un espace pour reconstituer la question. Utilise `Answerer`
et `CliFormatter`. Code de sortie : `SUCCESS` si `$answer->hasSources()`
est vrai, `FAILURE` sinon.

## Corpus documentaire

- Modèle `Document` (`app/Models/Document.php`), champs `title`,
  `source_type`, `path`, `content`.
- Chargé par `DocumentSeeder` (`database/seeders/DocumentSeeder.php`)
  depuis tous les fichiers `*.md` de `database/seeds/documents/` :
  - `path` stocké est `seeds/documents/{nom}.md` ;
  - `title` est déduit de la première ligne `# ...` du fichier (sinon le
    nom de fichier) ;
  - `source_type` est déduit du préfixe du nom de fichier : `wiki-*` →
    `wiki`, `README*` → `code`, tout le reste → `doc` ;
  - le chargement utilise `updateOrCreate` sur `path`, donc `migrate --seed`
    est idempotent pour ce corpus.
- `DatabaseSeeder` appelle `DocumentSeeder` puis `PortefeuilleSeeder`.

## Configuration du conteneur de services

`AppServiceProvider::register()` (`app/Providers/AppServiceProvider.php`) :

```php
$this->app->bind(LlmClient::class, StubLlmClient::class);
$this->app->singleton(QueryLog::class);
```

`HttpLlmClient` n'apparaît dans aucune liaison — il ne peut être obtenu que
si l'on modifie explicitement ce binding.
