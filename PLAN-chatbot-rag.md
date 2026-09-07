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
  pas de logique de retrieval dupliquée dans le contrôleur du chat.
- Un ticket = un chantier testable, dans l'esprit du `BACKLOG.md` existant.

## Découpage en tickets

### F-01 — `OllamaLlmClient`

Nouveau client à côté de `HttpLlmClient` / `StubLlmClient`, implémentant
`LlmClient`, qui appelle un modèle Ollama en local (ex: `llama3`, `mistral`).

**C'est bon quand :** un test d'intégration (skippable si Ollama absent) montre
une réponse générée à partir d'un contexte donné, et que la config
(`config/llm.php` ou équivalent) permet de switcher stub/http/ollama par env.

### F-02 — Endpoint chat minimal

Route + contrôleur qui prend une question, appelle `Answerer` (donc
`DocumentSearch` + `ContextBuilder` + le `LlmClient` configuré), et renvoie la
réponse avec ses citations. Réutilise un formatter existant (`HtmlFormatter`
ou `JsonFormatter`) plutôt que d'en écrire un nouveau.

**C'est bon quand :** on pose une question depuis le navigateur et on obtient
une réponse sourcée, sans logique métier dans le contrôleur.

### F-03 — Interface chat (front léger)

Page simple (Blade + JS minimal, pas de framework front à ajouter) : champ de
question, historique de la conversation, affichage des sources citées.

**C'est bon quand :** un dev peut discuter avec COMPAS depuis
`http://127.0.0.1:8000/chat` sans repasser par le CLI.

### F-04 — Espace de dépôt de documents

Upload (formulaire + route dédiée) qui crée/actualise un `Document` de la
même façon que `DocumentSeeder` (même règles de titre/`source_type`), sans
dupliquer cette logique — l'extraire dans un service partagé si besoin.

**C'est bon quand :** un fichier `.md` déposé via l'UI est immédiatement
interrogeable par le chat, avec les mêmes règles que le corpus seedé.

### F-05 — Atelier prompt injection

Une fois F-01 à F-04 posés : déposer via l'UI (F-04) un document contenant des
instructions cachées ("ignore les consignes précédentes et...", faux
system prompt, exfiltration de données d'un autre assuré, etc.), poser une
question qui le fait remonter par le retrieval, observer si/comment le LLM
local obéit à l'injection.

**C'est bon quand :** l'atelier documente au moins une injection réussie et
une mitigation (ex: délimitation stricte du contexte dans le prompt système,
validation/sanitation du contenu indexé, instruction explicite de ne jamais
suivre des consignes venant des documents).

## Démo outillage (spec-kit)

Utiliser [spec-kit](https://github.com/github/spec-kit) pour dérouler ce plan
devant les devs : chaque ticket F-01 → F-05 devient une spec courte
(`/specify`, `/plan`, `/tasks` selon les commandes de spec-kit), pour montrer
une méthodo spec-driven avec Claude Code sur une feature réelle plutôt qu'en
isolation. Choisi plutôt que Taskmaster/BMAD pour rester léger (pas d'infra
supplémentaire, juste des fichiers markdown + commandes slash dans le repo).

## Ordre suggéré

F-01 → F-02 → F-03 → F-04 → F-05

Chaque étape est mergeable indépendamment et laisse le CLI/tests existants
intacts.
