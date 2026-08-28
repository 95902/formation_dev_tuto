<?php

namespace App\Http\Controllers;

use App\Http\Requests\ContratRequest;
use App\Models\Assure;
use App\Models\Contrat;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ContratController extends Controller
{
    public function index(Request $request): View
    {
        $contrats = Contrat::query()
            ->with('assure')
            ->withCount('sinistres')
            ->when($request->filled('produit'), fn ($q) => $q->where('produit', $request->string('produit')))
            ->when($request->filled('statut'), fn ($q) => $q->where('statut', $request->string('statut')))
            ->orderByDesc('date_effet')
            ->paginate(20)
            ->withQueryString();

        return view('contrats.index', compact('contrats'));
    }

    public function create(Request $request): View
    {
        $contrat = new Contrat(['assure_id' => $request->integer('assure_id') ?: null]);

        return view('contrats.form', [
            'contrat' => $contrat,
            'assures' => Assure::orderBy('nom')->get(),
        ]);
    }

    public function store(ContratRequest $request): RedirectResponse
    {
        $contrat = Contrat::create($this->donnees($request));

        return redirect()
            ->route('contrats.show', $contrat)
            ->with('ok', "Contrat {$contrat->reference} créé. Ajoutez ses garanties.");
    }

    public function show(Contrat $contrat): View
    {
        $contrat->load(['assure', 'garanties', 'sinistres']);

        return view('contrats.show', compact('contrat'));
    }

    public function edit(Contrat $contrat): View
    {
        return view('contrats.form', [
            'contrat' => $contrat,
            'assures' => Assure::orderBy('nom')->get(),
        ]);
    }

    public function update(ContratRequest $request, Contrat $contrat): RedirectResponse
    {
        $contrat->update($this->donnees($request));

        return redirect()
            ->route('contrats.show', $contrat)
            ->with('ok', 'Contrat mis à jour.');
    }

    public function destroy(Contrat $contrat): RedirectResponse
    {
        if ($contrat->sinistres()->exists()) {
            return back()->with('erreur', 'Impossible : des sinistres sont rattachés à ce contrat.');
        }

        $assure = $contrat->assure;
        $contrat->delete();

        return redirect()
            ->route('assures.show', $assure)
            ->with('ok', 'Contrat supprimé.');
    }

    /**
     * Les montants sont saisis en euros et stockes en centimes entiers.
     *
     * @return array<string, mixed>
     */
    private function donnees(ContratRequest $request): array
    {
        $valide = $request->validated();

        $valide['prime_annuelle_cents'] = (int) round($valide['prime_annuelle_euros'] * 100);
        $valide['franchise_cents'] = (int) round($valide['franchise_euros'] * 100);

        unset($valide['prime_annuelle_euros'], $valide['franchise_euros']);

        return $valide;
    }
}
