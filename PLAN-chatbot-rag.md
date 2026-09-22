# Plan — Chatbot RAG + dépôt de documents

> Objectif : moderniser COMPAS avec un chat relié à un LLM local qui répond
> en s'appuyant sur le RAG existant, et un espace où les utilisateurs peuvent
> déposer leurs propres documents. Sert aussi de support pour l'atelier
> prompt injection et de démo de méthodo spec-driven (spec-kit) aux devs.

## Pourquoi

- Le pipeline RAG (`DocumentSearch`, `ContextBuilder`, `Answerer`) existe déjà
  et fonctionne en CLI (`compas:ask`) avec un `StubLlmClient` déterministe.
- Le brancher sur un vrai LLM **local** (Ollama) garde l'esprit "aucun appel
  réseau externe, aucune clé" du bac à sable, tout en rendant le prompt
  injection démontrable (le stub actuel ne "suit" aucune instruction).
- Le dépôt de documents par l'utilisateur est le vecteur naturel d'un
  document empoisonné pour l'atelier injection.

## Contraintes à respecter

- Ne pas casser la reproductibilité des tests existants : le `StubLlmClient`
  reste le client par défaut en environnement de test.
- Réutiliser `Answerer` / `ContextBuilder` / `DocumentSearch` tels quels —
  pas de logique de retrieval dupliquée dans le contrôleur du chat. Seul F-06
  (bonus) touche à la recherche, derrière un switch de config.
- `HttpLlmClient` reste un support d'atelier, jamais branché (voir `CLAUDE.md`).
- Un ticket = un chantier testable, dans l'esprit du `BACKLOG.md` existant.

## Découpage en tickets

### F-01 — `OllamaLlmClient`

Nouveau client à côté de `HttpLlmClient` / `StubLlmClient`, implémentant
`LlmClient`, qui appelle un modèle Ollama en local (ex: `llama3`, `mistral`).

**C'est bon quand :** un test d'intégration (skippable si Ollama absent) montre
une réponse générée à partir d'un contexte donné, et que la config
(`config/llm.php` ou équivalent) permet de switcher stub/ollama par env
(`LLM_DRIVER`, `stub` par défaut) dans `AppServiceProvider::register()`.

### F-02 — Mémoire de conversation sur `/api/ask`

L'endpoint existe déjà : `POST /api/ask` (`AskController` + `JsonFormatter`)
appelle `Answerer` et renvoie la réponse avec ses citations. Ce qui manque pour
un chat, c'est la conversation : ajouter un champ optionnel `history` (les
derniers échanges) qu'`Answerer` injecte dans le prompt — `LlmClient` est sans
mémoire, tout doit tenir dans `$system` et `$user`. Le retrieval ne change pas.

**C'est bon quand :** `AskEndpointTest` reste vert, un nouveau test couvre
`history`, et le contrôleur ne fait toujours que valider et déléguer.

### F-03 — La console `/compas` devient un chat (front léger)

La console existe déjà (`CompasController` + `compas.blade.php`, formulaire
GET avec rechargement de page). La faire évoluer plutôt que créer une page :
JS minimal (pas de framework front à ajouter) qui appelle `/api/ask` en
`fetch`, historique affiché et renvoyé à chaque question, sources
`chemin:ligne` cliquables comme aujourd'hui. `/api/ask` est dans
`routes/web.php` : le `fetch` doit envoyer le jeton CSRF.

**C'est bon quand :** un dev enchaîne plusieurs questions sur
`http://127.0.0.1:8000/compas` sans rechargement ni CLI, et une question de
suivi (« et pour un vol ? ») tient compte de la précédente.

### F-04 — Espace de dépôt de documents

Upload (formulaire + route dédiée) qui crée/actualise un `Document` de la
même façon que `DocumentSeeder` (même règles de titre/`source_type`), sans
dupliquer cette logique — l'extraire dans un service partagé si besoin.

**C'est bon quand :** un fichier `.md` déposé via l'UI est immédiatement
interrogeable par le chat, avec les mêmes règles que le corpus seedé.

### F-07 — Outils sinistre dans le chat

Le chat ne connaît que la documentation : il ne sait rien d'un sinistre en
base. Ajouter à `OllamaLlmClient` l'appel d'outils d'Ollama (paramètre `tools`
de `/api/chat`) avec deux outils étroits, en lecture seule, qui prennent une
référence exacte :

- `get_sinistre(reference)` : nature, dates, `delaiDeclarationJours()`,
  statut, montant estimé ;
- `get_garantie(reference_sinistre)` : la garantie du contrat pour cette
  nature, via `Contrat::garantiePour()` (franchise, plafond).

Jamais de SQL généré par le modèle, jamais de données de l'assuré au-delà du
nécessaire. Ne pas brancher Laravel Boost sur le chat : c'est un outil de dev
(`require-dev`, `Tinker` exécute du PHP arbitraire), réservé à Claude Code.

**C'est bon quand :** « SIN-2026-00042 a-t-il été déclaré dans les délais ? »
obtient une réponse qui croise les dates du sinistre et la règle du wiki, en
citant les deux sources ; une référence inconnue renvoie une erreur propre ; le
`StubLlmClient` (qui n'appelle aucun outil) garde la suite de tests verte.

### F-05 — Atelier prompt injection

Une fois F-01 à F-04 et F-07 posés : déposer via l'UI (F-04) un document contenant des
instructions cachées ("ignore les consignes précédentes et...", faux
system prompt, exfiltration de données d'un autre assuré, etc.), poser une
question qui le fait remonter par le retrieval, observer si/comment le LLM
local obéit à l'injection.

**C'est bon quand :** l'atelier documente au moins une injection réussie et
une mitigation (ex: délimitation stricte du contexte dans le prompt système,
validation/sanitation du contenu indexé, instruction explicite de ne jamais
suivre des consignes venant des documents). Avec F-07, tester aussi un
document qui ordonne d'appeler `get_sinistre` sur le sinistre d'un autre
assuré : qu'est-ce qui l'en empêche ?

### F-06 — Recherche vectorielle Qdrant (bonus)

`DocumentSearch` score par mots-clés, volontairement déterministe. Brancher la
collection Qdrant `compas_chunks` (indexée par `rag/index_docs.py` depuis
`database/seeds/documents/`, payload `path` = `Document.path`) derrière un
switch `SEARCH_DRIVER=keywords|qdrant` : les points renvoient leur `path`, on
recharge les `Document` correspondants. `Answerer` dépend de la classe
`DocumentSearch` et de son `tokenize()` pour les citations — c'est au
`/speckit-plan` de proposer comment l'abstraire.

**C'est bon quand :** la même question via `compas:ask` renvoie des citations
`chemin:ligne` avec les deux drivers, et la suite de tests reste verte sans
Qdrant (driver `keywords` par défaut).

## Démo outillage (spec-kit)

Utiliser [spec-kit](https://github.com/github/spec-kit) pour dérouler ce plan
devant les devs : chaque ticket devient une spec courte, sur sa propre branche
(`/speckit-specify` → `/speckit-clarify` si besoin → `/speckit-plan` →
`/speckit-tasks` → `/speckit-implement`, précédés une fois de
`/speckit-constitution` ; relancer `specify init --here --ai claude` si
`.specify/` manque), pour montrer
une méthodo spec-driven avec Claude Code sur une feature réelle plutôt qu'en
isolation. Choisi plutôt que Taskmaster/BMAD pour rester léger (pas d'infra
supplémentaire, juste des fichiers markdown + commandes slash dans le repo).

## Ordre suggéré

F-01 → F-02 → F-03 → F-04 → F-07 → F-05, puis F-06 si le temps le permet.

F-07 passe avant F-05 : les outils ouvrent une surface d'attaque de plus,
autant l'éprouver dans l'atelier injection.

Chaque étape est mergeable indépendamment et laisse le CLI/tests existants
intacts.
