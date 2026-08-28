<?php

namespace App\Http\Requests;

use App\Models\Contrat;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ContratRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        $id = $this->route('contrat')?->id;

        return [
            'assure_id' => ['required', 'exists:assures,id'],
            'reference' => ['required', 'string', 'max:30', Rule::unique('contrats', 'reference')->ignore($id)],
            'produit' => ['required', Rule::in(Contrat::PRODUITS)],
            'formule' => ['required', Rule::in(Contrat::FORMULES)],
            'date_effet' => ['required', 'date'],
            'date_echeance' => ['required', 'date', 'after:date_effet'],
            'statut' => ['required', Rule::in(Contrat::STATUTS)],
            'prime_annuelle_euros' => ['required', 'numeric', 'min:0', 'max:100000'],
            'franchise_euros' => ['required', 'numeric', 'min:0', 'max:100000'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'assure_id' => 'assuré',
            'date_effet' => "date d'effet",
            'date_echeance' => "date d'échéance",
            'prime_annuelle_euros' => 'prime annuelle',
            'franchise_euros' => 'franchise',
        ];
    }
}
