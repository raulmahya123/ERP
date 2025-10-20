<?php

namespace App\Http\Requests\Scm;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreDailyPlanRequest extends FormRequest
{
    public function authorize(): bool { return true; }
    public function rules(): array {
        $siteId = (string) session('site_id');
        return [
            'plan_date' => ['required','date'],
            'shift_id'  => ['required','uuid', Rule::exists('shifts','id')],
            'remarks'   => ['nullable','string'],
            // items[]
            'items'                 => ['required','array','min:1'],
            'items.*.pit_id'        => ['required','uuid', Rule::exists('pits','id')],
            'items.*.target_ton'    => ['required','numeric','min:0'],
            'items.*.target_ritase' => ['required','integer','min:0'],
            'items.*.notes'         => ['nullable','string'],
            // unique (site_id,plan_date,shift_id)
            // ini akan dicek di controller juga untuk safety (race condition)
        ];
    }
}
