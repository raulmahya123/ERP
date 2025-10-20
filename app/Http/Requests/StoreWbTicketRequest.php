<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreWbTicketRequest extends FormRequest
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
            'ticket_no'    => ['required','string','max:100',
                                Rule::unique('wb_tickets','ticket_no')
                                    ->where(fn($q) => $q->where('site_id', $siteId))],
            'direction'    => ['required', Rule::in(['in','out'])],
            'ticket_time'  => ['required','date'],

            'unit_id'      => ['nullable','uuid','exists:assets,id'],
            'pit_id'       => ['nullable','uuid','exists:locations,id'],
            'stockpile_id' => ['nullable','uuid','exists:locations,id'],
            'commodity_id' => ['nullable','uuid','exists:commodities,id'],

            'gross'        => ['nullable','numeric','min:0','max:99999999.99'],
            'tare'         => ['nullable','numeric','min:0','max:99999999.99'],
            'net'          => ['nullable','numeric','min:0','max:99999999.99'],

            'pair_id'      => ['nullable','uuid'],
            'notes'        => ['nullable','string'],
        ];
    }

    public function messages(): array
    {
        return [
            'direction.in' => 'Arah harus IN atau OUT.',
        ];
    }
}
