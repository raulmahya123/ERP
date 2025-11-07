<?php

namespace App\Http\Requests\Scm;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePitRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $siteId = (string) session('site_id');
        $pit    = $this->route('pit'); // route-model binding
        $pitId  = $pit?->id;

        return [
            'code'   => [
                'required','string','max:50',
                Rule::unique('pits', 'code')
                    ->ignore($pitId)
                    ->where(fn($q) => $q->where('site_id', $siteId)),
            ],
            'name'   => ['nullable','string','max:100'],
            'active' => ['nullable','boolean'],
            'extra'  => ['nullable','string','json'],
        ];
    }

    public function messages(): array
    {
        return [
            'code.required' => 'Code wajib diisi.',
            'code.unique'   => 'Code sudah dipakai di site ini.',
            'extra.json'    => 'Extra harus berupa JSON yang valid.',
        ];
    }
}
