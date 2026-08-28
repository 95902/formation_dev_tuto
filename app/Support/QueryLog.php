<?php

namespace App\Support;

/**
 * Compteur de requetes SQL de la requete HTTP en cours.
 *
 * Branche en local uniquement (voir AppServiceProvider). Il alimente le
 * bandeau de bas de page : une page qui declenche cent requetes se voit,
 * au lieu de se deviner.
 */
class QueryLog
{
    private int $count = 0;

    private float $millis = 0.0;

    public function record(float $millis): void
    {
        $this->count++;
        $this->millis += $millis;
    }

    public function count(): int
    {
        return $this->count;
    }

    public function millis(): float
    {
        return round($this->millis, 1);
    }
}
