<?php

namespace App\Http\Requests\Scm;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreReasonCodeRequest extends FormRequest
{
    public function authorize(): bool { return true; }
    public function rules(): array {
        $siteId = (string) session('site_id');
        return [
            'code' => [
                'required','alpha_dash','max:30',
                Rule::unique('scm_reason_codes','code')->where('site_id',$siteId),
            ],
            'name' => ['required','max:80'],
            'category' => ['required', Rule::in(['idle','standby','breakdown','no_load','quality','weather','queue','other'])],
            'is_downtime' => ['boolean'],
            'is_billable' => ['boolean'],
            'active' => ['boolean'],
        ];
    }
}
