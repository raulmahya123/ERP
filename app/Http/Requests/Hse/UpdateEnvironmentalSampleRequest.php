<?php

namespace App\Http\Requests\Hse;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\EnvironmentalSample;

class UpdateEnvironmentalSampleRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var \App\Models\EnvironmentalSample $sample */
        $sample = $this->route('sample');
        return $sample && $this->user()?->can('update', $sample);
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
