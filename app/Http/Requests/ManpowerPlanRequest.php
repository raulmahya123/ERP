<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ManpowerPlanRequest extends FormRequest
{
    public function authorize(): bool { return true; }
    public function rules(): array {
        return [
            'date' => ['required','date'],
            'shift_slot' => ['required','in:A,B,C,D,NON'],
            'department' => ['required','string','max:50'],
            'planned_headcount' => ['required','integer','min:0'],
            'planned_operators' => ['nullable','integer','min:0'],
            'planned_mechanics' => ['nullable','integer','min:0'],
            'planned_helpers'   => ['nullable','integer','min:0'],
            'planned_others'    => ['nullable','integer','min:0'],
        ];
    }
}
