<?php

namespace App\Http\Requests\Hse;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\EnvironmentalSample;

class StoreEnvironmentalSampleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', EnvironmentalSample::class) ?? false;
    }

    public function prepareForValidation(): void
    {
        $this->merge([
            'site_id' => $this->input('site_id') ?: session('site_id'),
        ]);
    }

    public function rules(): array
    {
        return [
            'site_id'       => ['nullable','uuid','exists:sites,id'],
            'sampled_at'    => ['required','date'],
            'type'          => ['required','in:air,emission,noise'],
            'location'      => ['nullable','string','max:255'],

            'parameter'     => ['required','string','max:191'],
            'value'         => ['nullable','numeric'],
            'unit'          => ['nullable','string','max:20'],
            'method'        => ['nullable','string','max:100'],
            'instrument'    => ['nullable','string','max:100'],
            'limit_value'   => ['nullable','numeric'],
            'is_compliant'  => ['nullable','boolean'],

            'meta'          => ['nullable','array'],
        ];
    }
}
