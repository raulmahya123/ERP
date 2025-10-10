<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ManpowerRealisationRequest extends FormRequest
{
    public function authorize(): bool { return true; }
    public function rules(): array {
        return [
            'date' => ['required','date'],
            'shift_slot' => ['required','in:A,B,C,D,NON'],
            'department' => ['required','string','max:50'],
            'actual_headcount'  => ['required','integer','min:0'],
            'actual_operators'  => ['nullable','integer','min:0'],
            'actual_mechanics'  => ['nullable','integer','min:0'],
            'actual_helpers'    => ['nullable','integer','min:0'],
            'actual_others'     => ['nullable','integer','min:0'],
            'production_tonnage'=> ['nullable','numeric','min:0'],
            'manhours'          => ['nullable','numeric','min:0'],
        ];
    }
}
