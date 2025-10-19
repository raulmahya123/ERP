<?php

namespace App\Http\Requests\Scm;

use Illuminate\Foundation\Http\FormRequest;

class StoreFuelLogRequest extends FormRequest
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
            'unit_id'     => ['required', 'uuid', 'exists:assets,id'],
            'operator_id' => ['nullable', 'uuid', 'exists:users,id'],
            'dispensed_at' => ['required', 'date'],
            'fuel_type'   => ['required', 'in:diesel,gasoline,other'],
            'liter'       => ['required', 'numeric', 'gt:0'],
            'dispenser_id' => ['nullable', 'string', 'max:50'],
            'receipt_no'  => ['nullable', 'string', 'max:50'],
            'client_uid'  => ['nullable', 'string', 'max:64', "unique:scm_fuel_logs,client_uid,NULL,id,site_id,{$siteId}"],
        ];
    }
}
