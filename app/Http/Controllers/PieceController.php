<?php

namespace App\Http\Controllers;

use App\Models\Piece;
use App\Models\Sinistre;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PieceController extends Controller
{
    public function store(Request $request, Sinistre $sinistre): RedirectResponse
    {
        $valide = $request->validate([
            'type' => ['required', Rule::in(Piece::TYPES)],
            'libelle' => ['nullable', 'string', 'max:150'],
            'recue_le' => ['required', 'date'],
        ]);

        $sinistre->pieces()->create([
            'type' => $valide['type'],
            'libelle' => $valide['libelle'] ?: Piece::LIBELLES[$valide['type']],
            'recue_le' => $valide['recue_le'],
        ]);

        return back()->with('ok', 'Pièce enregistrée.');
    }

    public function destroy(Sinistre $sinistre, Piece $piece): RedirectResponse
    {
        abort_unless($piece->sinistre_id === $sinistre->id, 404);

        $piece->delete();

        return back()->with('ok', 'Pièce retirée.');
    }
}
