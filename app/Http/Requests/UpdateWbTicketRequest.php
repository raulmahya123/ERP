<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateWbTicketRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'site_id'      => ['required','uuid','exists:sites,id'],
            'ticket_no'    => ['nullable','string','max:100'],
            'direction'    => ['required','in:in,out'],
            'ticket_time'  => ['required','date'],

            'unit_id'      => ['nullable','uuid','exists:assets,id'],
            'pit_id'       => ['nullable','uuid', Rule::exists('locations','id')->where('type','pit')],
            'stockpile_id' => ['nullable','uuid', Rule::exists('locations','id')->where('type','stockpile')],
            'commodity_id' => ['nullable','uuid','exists:commodities,id'],

            'gross'        => ['nullable','numeric','min:0'],
            'tare'         => ['nullable','numeric','min:0'],
            'net'          => ['nullable','numeric','min:0'],
            'pair_id'      => ['nullable','string','max:100'],
            'notes'        => ['nullable','string'],
        ];
    }
}
