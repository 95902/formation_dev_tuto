---
name: nouveau-ticket
description: "Backlog COMPAS — crée un nouveau ticket dans BACKLOG.md à partir d'une description de symptôme, dans le format des tickets T-01..T-11 existants. Trigger: `/nouveau-ticket <description>`"
---

# /nouveau-ticket

Ajoute un ticket au backlog de COMPAS (`BACKLOG.md`, à la racine de `compas-lab/`), dans le même format que les tickets `T-01` à `T-11` déjà présents.

## Usage

```
/nouveau-ticket <description du symptôme observé>
```

La description peut être brute et informelle (copier-coller d'un message de support, description orale, extrait de log) — c'est au skill de la mettre en forme.

## Étapes

1. **Localise** `BACKLOG.md` (racine de `compas-lab/`, à côté de `artisan` et `composer.json` — cherche-le si le répertoire de travail courant n'est pas déjà `compas-lab/`).

2. **Repère le dernier numéro de ticket** (`## T-NN — ...`) dans le fichier et prends le suivant (`T-12` si le dernier est `T-11`, etc.).

3. **Rédige la section** en suivant *exactement* la structure des tickets existants :

   ```
   ---

   ## T-<NN> — <titre court et factuel du symptôme>

   **Priorité :** <haute|moyenne|basse>[ · **Signalé par :** <origine>]

   <corps : ce qui est observé, dans quelles conditions ; inclure un exemple
   concret — commande, requête, extrait de sortie — quand la description en
   fournit un ou permet d'en déduire un>

   **C'est bon quand :** <critère d'acceptation observable et vérifiable par
   un test>
   ```

   Variantes vues dans le fichier existant à réutiliser si pertinent : `**Dette**` ou `**Sécurité**` accolés à la priorité (ex. `**Priorité :** basse · **Dette**`, `**Priorité :** haute · **Sécurité**`).

4. **Contrainte de fond, non négociable** (voir `CLAUDE.md` du dépôt — ce backlog est un atelier de formation) : le ticket décrit **uniquement le symptôme observable**, jamais la cause. N'indique ni le fichier en cause, ni la ligne, ni le mécanisme du bug, même si tu le devines en lisant le code — cela casserait l'exercice pour la personne qui résoudra le ticket. Si la description fournie par l'utilisateur contient elle-même un diagnostic ou une cause, reformule pour ne garder que le symptôme observable.

5. **Priorité** : si l'utilisateur ne la précise pas, déduis-la du contexte (impact financier/sécurité/utilisateur direct → haute ; gêne ou dette → basse) et signale ton choix dans ta réponse plutôt que de deviner en silence sur un cas ambigu — demande dans ce cas.

6. **Titre** : court, factuel, formulé comme un fait observé (« X ne fait pas Y », « Z affiche W au lieu de V »), jamais un jugement de valeur ni une hypothèse de cause.

7. **Insère** la nouvelle section à la fin du fichier, précédée d'un séparateur `---` (comme entre tous les tickets existants). Ne renumérote et ne touche à aucun ticket existant.

8. **Confirme** à l'utilisateur le numéro et le titre du ticket créé.

## Exemple

```
/nouveau-ticket Le bouton "Exporter" de /sinistres télécharge un CSV vide dès
qu'un filtre de date est actif, même quand la liste affichée à l'écran
contient des lignes.
```

→ ajoute `## T-12 — L'export CSV de /sinistres ignore les lignes filtrées par date`
avec une priorité déduite (haute, impact utilisateur direct), l'exemple donné
en corps, et un critère de type « l'export contient le même nombre de lignes
que la liste affichée, filtres de date compris, et un test le vérifie ».
