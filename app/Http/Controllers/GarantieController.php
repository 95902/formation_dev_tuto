<?php

namespace App\Http\Controllers;

use App\Models\Contrat;
use App\Models\Garantie;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class GarantieController extends Controller
{
    public function store(Request $request, Contrat $contrat): RedirectResponse
    {
        $valide = $request->validate([
            'code' => [
                'required',
                Rule::in(array_values(Garantie::CODE_PAR_NATURE)),
                Rule::unique('garanties', 'code')->where('contrat_id', $contrat->id),
            ],
            'plafond_euros' => ['required', 'numeric', 'min:0', 'max:1000000'],
            'franchise_euros' => ['required', 'numeric', 'min:0', 'max:100000'],
        ]);

        $contrat->garanties()->create([
            'code' => $valide['code'],
            'libelle' => Garantie::LIBELLES[$valide['code']],
            'plafond_cents' => (int) round($valide['plafond_euros'] * 100),
            'franchise_cents' => (int) round($valide['franchise_euros'] * 100),
            'incluse' => true,
        ]);

        return back()->with('ok', "Garantie {$valide['code']} ajoutée.");
    }

    public function destroy(Contrat $contrat, Garantie $garantie): RedirectResponse
    {
        abort_unless($garantie->contrat_id === $contrat->id, 404);

        $garantie->delete();

        return back()->with('ok', 'Garantie retirée.');
    }
}
