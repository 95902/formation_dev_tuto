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
