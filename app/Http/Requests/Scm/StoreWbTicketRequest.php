<?php

namespace App\Http\Requests\Scm;

use Illuminate\Foundation\Http\FormRequest;

class StoreWbTicketRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'ticket_no'  => ['required', 'string', 'max:100'],
            'direction'  => ['required', 'in:in,out'],
            'ticket_time' => ['required', 'date'],
            'unit_id'    => ['nullable', 'uuid', 'exists:assets,id'],
            'pit_id'     => ['nullable', 'uuid', 'exists:locations,id'],
            'stockpile_id' => ['nullable', 'uuid', 'exists:locations,id'],
            'commodity_id' => ['nullable', 'uuid', 'exists:commodities,id'],
            'gross'      => ['nullable', 'numeric', 'min:0'],
            'tare'       => ['nullable', 'numeric', 'min:0'],
            'net'        => ['nullable', 'numeric', 'min:0'],
            'notes'      => ['nullable', 'string'],
        ];
    }
}
