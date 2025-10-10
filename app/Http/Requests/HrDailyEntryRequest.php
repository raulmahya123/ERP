<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class HrDailyEntryRequest extends FormRequest
{
    public function authorize(): bool { return true; }
    public function rules(): array {
        return [
            'user_id' => ['required','uuid'],
            'date'    => ['required','date'],
            'type'    => ['required','in:leave,permit,sick,shift_change'],
            'code'    => ['nullable','string','max:20'],
            'reason'  => ['nullable','string','max:2000'],
            'from_shift_id' => ['nullable','uuid'],
            'to_shift_id'   => ['nullable','uuid'],
        ];
    }
}
