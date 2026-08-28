# Backlog COMPAS

Tickets ouverts, par ordre de priorité. Chacun décrit un symptôme observé.
La cause est à trouver.

Branche : `atelier/<prénom>/<ticket>`. Un ticket est fini quand il a un test qui
échouait avant le correctif et qui passe après.

---

## T-01 — La recherche ne trouve rien dès qu'on met une majuscule

**Priorité :** haute · **Signalé par :** support

```
$ php artisan compas:ask "astreinte"   ->  2 sources
$ php artisan compas:ask "Astreinte"   ->  0 source
```

Même chose avec `Pagination`, `Incidents`, et tout terme commençant par une
majuscule. Les utilisateurs écrivent naturellement les noms de service et de
procédure avec une majuscule, et en début de phrase ils n'ont pas le choix.

**C'est bon quand :** la casse de la question n'a plus aucun effet sur les
résultats, et qu'un test le prouve.

---

## T-02 — Deux services différents, une seule source affichée

**Priorité :** haute · **Signalé par :** équipe facturation

Le corpus contient le README du service de facturation et celui du service de
notifications. Une recherche sur `"lancer en local"` ne remonte qu'un seul des
deux — l'autre disparaît, quel que soit son score.

Les deux fichiers s'appellent `README`. Ce n'est pas une coïncidence : c'est le
cas dans tous nos dépôts.

**C'est bon quand :** deux documents de même titre mais de chemins différents
apparaissent tous les deux dans les résultats.

---

## T-03 — Caractères cassés en fin de réponse

**Priorité :** moyenne · **Signalé par :** support

Quand la réponse est longue, le dernier mot avant `...` finit parfois par un
caractère parasite (`Ã`, `�`). Toujours sur un mot accentué, jamais sur un mot
sans accent.

Voir `ContextBuilder`.

**C'est bon quand :** aucune troncature ne peut produire une chaîne invalide en
UTF-8, quel que soit le budget et le texte.

---

## T-04 — Le compteur de tokens est faux

**Priorité :** moyenne

`context_tokens` annonce systématiquement moins que ce que facture le
fournisseur — l'écart va du simple au triple sur du texte français.

Deux choses à regarder : la fonction utilisée pour compter les mots, et le sens
du ratio mot/token.

Ce chiffre sert à décider quand réduire le contexte. Tant qu'il est faux, on
décide à l'aveugle.

**C'est bon quand :** l'estimation reste dans les ±25 % d'un décompte de
référence sur les cinq documents du corpus.

---

## T-05 — Les numéros de ligne cités tombent à côté

**Priorité :** moyenne · **Signalé par :** deux développeurs

On clique sur `wiki/astreinte.md:12`, on tombe sur la ligne 13. Systématique,
toujours d'un cran, toujours dans le même sens.

**C'est bon quand :** la ligne citée est celle qu'affiche l'éditeur, y compris
pour un extrait situé sur la toute première ligne du fichier.

---

## T-06 — Cinq formatters pour une seule mise en forme

**Priorité :** basse · **Dette**

`app/Services/Answering/Formatters/` contient cinq classes qui font toutes la
même chose à la ponctuation près : en-tête, corps, liste des sources, pied de
page avec le coût en tokens. `shorten()` est copiée cinq fois. Une des cinq
classes contient une méthode que personne n'appelle.

Ajouter un sixième format aujourd'hui, c'est une sixième copie.

**C'est bon quand :** ajouter un format ne demande plus que d'écrire ce qui
change vraiment d'un format à l'autre, et que la sortie des cinq formats
existants est inchangée — prouvé par des tests écrits **avant** le remaniement.

---

## T-07 — La liste des sinistres s'affiche en plusieurs secondes

**Priorité :** haute · **Signalé par :** gestion sinistres

`/sinistres` met deux à trois secondes à s'afficher, pour vingt-cinq lignes.
`/contrats`, qui affiche autant de lignes et autant de colonnes, est immédiate.

Le bandeau de bas de page donne le compteur de requêtes SQL de chaque page :

```
/contrats     4 requêtes
/sinistres  103 requêtes
```

Le compteur monte avec le nombre de lignes affichées, pas avec le nombre de
filtres. Passer à cinquante lignes par page double le chiffre.

**C'est bon quand :** le nombre de requêtes de `/sinistres` ne dépend plus du
nombre de lignes affichées, et qu'un test le fige.

---

## T-08 — L'indemnité affichée est inférieure d'un centime au barème

**Priorité :** haute · **Signalé par :** service règlement

Le barème est dans `database/seeds/documents/doc-bareme-vetuste.md`. Il donne
un exemple de référence :

```
sinistre estimé 1 234,57 €, garantie sans franchise
1 234,57 x 0,87 = 1 074,0759  ->  1 074,08 €
```

L'application affiche **1 074,07 €**. L'écart est toujours d'un centime, il est
toujours dans le même sens, et il tombe sur environ un dossier sur deux.

Un centime par dossier n'a l'air de rien. C'est une erreur de règlement, et
elle se retrouve dans `montant_regle_cents` des dossiers déjà clos.

**C'est bon quand :** l'exemple du barème donne 1 074,08 €, et qu'un test le
vérifie sur plusieurs montants dont la troisième décimale n'est pas nulle.

---

## T-09 — Le filtre par date oublie le dernier jour

**Priorité :** moyenne · **Signalé par :** gestion sinistres

Sur `/sinistres`, filtrer « déclaré du 01/08/2026 au 31/08/2026 » remonte
**1 dossier**. Pousser la borne au 01/09/2026 en remonte **5**.

Les quatre dossiers manquants ont tous été déclarés le 31 août, entre 11 h et
17 h. Ils sont bien dans la période demandée par l'utilisateur.

Le contournement que tout le monde utilise — mettre la borne au lendemain —
fait entrer les dossiers du 1er septembre déclarés à minuit pile.

**C'est bon quand :** un dossier déclaré à 23 h 59 le dernier jour de la période
apparaît, et qu'un dossier déclaré le lendemain à 00 h 00 n'apparaît pas.

---

## T-10 — La tuile « Dossiers clos » reste à zéro

**Priorité :** moyenne · **Signalé par :** direction

Le tableau de bord affiche **0 dossier clos**. La liste filtrée sur le statut
« Clos » en affiche **45**, et la base en contient bien 45.

Les deux autres tuiles de statut sont justes. Seule celle-là est à zéro, et
elle l'est depuis la mise en service.

Le wiki (`wiki-gestion-sinistre.md`) fixe la liste des statuts valides et
précise que le statut de clôture s'écrit `clos`, et pas autrement.

**C'est bon quand :** la tuile affiche le même nombre que la liste filtrée, et
qu'écrire un statut inexistant ne compile plus.

---

## T-11 — Un champ absent du formulaire modifie quand même le dossier

**Priorité :** haute · **Sécurité**

Le formulaire d'édition d'un sinistre ne propose ni le montant réglé, ni le
contrat de rattachement. Pourtant :

```
POST /sinistres/1  (champs du formulaire) + montant_regle_cents=99999999
```

enregistre un montant réglé de 999 999,99 € sur le dossier. Le même envoi avec
`contrat_id` rattache le dossier au contrat d'un autre assuré.

La validation passe : elle ne dit rien de ces champs, et ce qui n'est pas
validé est quand même écrit. `SinistreController::store()` et
`SinistreController::update()` ne traitent pas la requête de la même façon —
la différence tient à un mot.

**C'est bon quand :** un champ non validé n'atteint plus la base, et qu'un test
le prouve en postant un champ que le formulaire n'expose pas.
