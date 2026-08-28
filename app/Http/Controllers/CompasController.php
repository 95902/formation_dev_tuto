<?php

namespace App\Http\Controllers;

use App\Models\Sinistre;
use App\Services\Answering\Answerer;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

/**
 * Console COMPAS : on pose une question, on lit la reponse et ses sources.
 *
 * C'est le meme chemin que `php artisan compas:ask`, a l'affichage pres.
 * Les numeros de ligne cites sont cliquables : c'est la que se voit, a l'ecran,
 * ce que les tests de citation mesurent.
 */
class CompasController extends Controller
{
    public function __invoke(Request $request, Answerer $answerer): View
    {
        $question = trim((string) $request->query('question', ''));

        $sinistre = $request->filled('sinistre')
            ? Sinistre::with('contrat')->find($request->integer('sinistre'))
            : null;

        $answer = mb_strlen($question) >= 3
            ? $answerer->ask($question, $request->integer('limit') ?: 5)
            : null;

        return view('compas', [
            'question' => $question,
            'answer' => $answer,
            'sinistre' => $sinistre,
            'suggestions' => [
                'Quel est le délai de déclaration d\'un sinistre ?',
                'Quelle franchise s\'applique à un dégât des eaux ?',
                'Comment est calculé l\'abattement de vétusté ?',
                'Quelle est la fenêtre de déploiement ?',
            ],
        ]);
    }
}
