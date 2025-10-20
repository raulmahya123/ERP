<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreBreakdownRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'site_id'   => ['required','uuid','exists:sites,id'],
            'unit_id'   => ['required','uuid','exists:assets,id'],
            'category'  => ['required','in:planned,unplanned'],
            'cause_code'=> ['nullable','string','max:64'],
            'start_at'  => ['required','date'],
            'end_at'    => ['nullable','date','after_or_equal:start_at'],
            'notes'     => ['nullable','string','max:2000'],
            'client_uid'=> ['nullable','string','max:64'],
        ];
    }
}
