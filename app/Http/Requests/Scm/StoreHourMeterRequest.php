<?php

namespace App\Http\Requests\Scm;

use Illuminate\Foundation\Http\FormRequest;

class StoreHourMeterRequest extends FormRequest
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
        $siteId = (string) session('site_id');
        return [
            'date'     => ['required', 'date'],
            'shift_id' => ['required', 'uuid', 'exists:shifts,id'],
            'unit_id'  => ['required', 'uuid', 'exists:assets,id'],
            'hm_start' => ['required', 'numeric', 'min:0'],
            'hm_end'   => ['required', 'numeric', 'gte:hm_start'],
            'client_uid' => ['nullable', 'string', 'max:64', "unique:scm_hour_meters,client_uid,NULL,id,site_id,{$siteId}"],
        ];
    }
}
