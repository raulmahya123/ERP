<?php

namespace App\Http\Requests\Scm;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreHandoverRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }
    public function rules(): array
    {
        return [
            'handover_date' => ['required', 'date'],
            'from_shift_id' => ['required', 'uuid', Rule::exists('shifts', 'id')],
            'to_shift_id'   => ['required', 'uuid', Rule::exists('shifts', 'id'), 'different:from_shift_id'],
            'weather'       => ['nullable', Rule::in(['clear', 'cloudy', 'rain', 'storm', 'other'])],
            'issues'        => ['nullable', 'string'],
            'targets'       => ['nullable', 'string'],
            'notes'         => ['nullable', 'string'],

            'items'           => ['nullable', 'array'],
            'items.*.pit_id'  => ['required_with:items', 'uuid', Rule::exists('pits', 'id')],
            'items.*.notes'   => ['nullable', 'string'],
        ];
    }
}
