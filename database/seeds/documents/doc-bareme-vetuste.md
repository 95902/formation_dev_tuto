# Barème de vétusté et calcul de l'indemnité

L'indemnité versée n'est pas le montant estimé des dommages. Elle s'en déduit
en deux temps.

## Étape 1 — abattement de vétusté

Un taux unique de **87 %** est appliqué au montant estimé. Autrement dit,
l'abattement de vétusté est de 13 %.

Le résultat est **arrondi au centime le plus proche**, jamais tronqué.
Un arrondi vers le bas systématique fait perdre un centime à l'assuré sur
environ un dossier sur deux ; c'est une erreur de règlement.

## Étape 2 — déduction de la franchise

On retranche ensuite la franchise opposable : celle de la garantie mobilisée
si le contrat la porte, sinon la franchise générale du contrat.

Le résultat est enfin plafonné au plafond de la garantie mobilisée.

## Exemple de référence

Sinistre estimé à **1 234,57 €**, garantie sans franchise.

```
1 234,57 x 0,87 = 1 074,0759
arrondi au centime -> 1 074,08 €
```

L'indemnité due est de **1 074,08 €**. Toute application qui affiche
1 074,07 € tronque au lieu d'arrondir.
