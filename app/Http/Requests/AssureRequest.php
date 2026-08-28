<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AssureRequest extends FormRequest
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
        $id = $this->route('assure')?->id;

        return [
            'civilite' => ['required', Rule::in(['M.', 'Mme'])],
            'nom' => ['required', 'string', 'max:100'],
            'prenom' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:150', Rule::unique('assures', 'email')->ignore($id)],
            'telephone' => ['required', 'string', 'max:20'],
            'date_naissance' => ['required', 'date', 'before:today'],
            'adresse' => ['required', 'string', 'max:200'],
            'code_postal' => ['required', 'digits:5'],
            'ville' => ['required', 'string', 'max:100'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'civilite' => 'civilité',
            'date_naissance' => 'date de naissance',
            'code_postal' => 'code postal',
        ];
    }
}
