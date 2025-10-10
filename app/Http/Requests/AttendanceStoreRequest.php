<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AttendanceStoreRequest extends FormRequest
{
    public function authorize(): bool { return true; }
    public function rules(): array {
        return [
            'user_id'   => ['required','uuid'],
            'work_date' => ['required','date'],
            'shift_id'  => ['nullable','uuid'],
            'source'    => ['required','in:manual,fingerprint,mobile_gps'],
            'check_in_at'  => ['nullable','date'],
            'check_out_at' => ['nullable','date','after_or_equal:check_in_at'],
        ];
    }
}
