<?php

namespace App\Http\Requests\Scm;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreDispatchRequest extends FormRequest
{
    public function authorize(): bool { return true; }
    public function rules(): array {
        return [
            'work_date'     => ['required','date'],
            'shift_id'      => ['required','uuid', Rule::exists('shifts','id')],
            'pit_id'        => ['required','uuid', Rule::exists('pits','id')],
            'asset_id'      => ['required','uuid', Rule::exists('assets','id')],
            'operator_id'   => ['required','uuid', Rule::exists('users','id')],
            'route_id'      => ['nullable','uuid'],
            'planned_start' => ['nullable','date_format:H:i'],
            'planned_end'   => ['nullable','date_format:H:i','after:planned_start'],
            'status'        => ['required', Rule::in(['planned','in_progress','done','cancelled'])],
            'notes'         => ['nullable','string'],
        ];
    }
}
