<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TimesheetStoreRequest extends FormRequest
{
    public function authorize(): bool { return true; }
    public function rules(): array {
        return [
            'user_id'       => ['required','uuid'],
            'work_date'     => ['required','date'],
            'shift_id'      => ['nullable','uuid'],
            'activity_code' => ['required','string','max:50'],
            'hours'         => ['required','numeric','min:0','max:24'],
            'overtime_hours'=> ['nullable','numeric','min:0','max:24'],
            'equipment_id'  => ['nullable','uuid'],
        ];
    }
}
