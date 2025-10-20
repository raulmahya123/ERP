<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreFuelLogRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'site_id' => $this->input('site_id') ?: session('site_id'),
        ]);
    }

    public function rules(): array
    {
        $siteId = (string) $this->input('site_id');

        return [
            'site_id'      => ['required','uuid','exists:sites,id'],
            'unit_id'      => ['required','uuid','exists:assets,id'],
            'operator_id'  => ['nullable','uuid','exists:users,id'],
            'dispensed_at' => ['required','date'],
            'fuel_type'    => ['required', Rule::in(['diesel','gasoline','other'])],
            'liter'        => ['required','numeric','min:0.01','max:99999999.99'],
            'dispenser_id' => ['nullable','string','max:100'],
            'receipt_no'   => ['nullable','string','max:100'],
            'client_uid'   => [
                'nullable','string','max:64',
                Rule::unique('scm_fuel_logs','client_uid')
                    ->where(fn($q) => $q->where('site_id',$siteId)),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'fuel_type.in' => 'Tipe BBM harus salah satu dari: diesel, gasoline, other.',
        ];
    }
}
