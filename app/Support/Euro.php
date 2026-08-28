<?php

namespace App\Support;

/**
 * Les montants sont stockes en centimes entiers. Cette classe ne sert
 * qu'a l'affichage : elle ne doit jamais servir a calculer.
 */
class Euro
{
    public static function format(?int $cents): string
    {
        if ($cents === null) {
            return '—';
        }

        return number_format($cents / 100, 2, ',', ' ').' €';
    }
}
