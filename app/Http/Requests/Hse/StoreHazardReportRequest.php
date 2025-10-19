<?php

namespace App\Http\Requests\Hse;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\HazardReport;

class StoreHazardReportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', HazardReport::class) ?? false;
    }

    public function prepareForValidation(): void
    {
        $this->merge([
            'site_id' => $this->input('site_id') ?: session('site_id'),
        ]);
    }

    public function rules(): array
    {
        return [
            'site_id'               => ['nullable','uuid','exists:sites,id'],
            'reporter_id'           => ['nullable','uuid','exists:users,id'],
            'code'                  => ['nullable','string','max:40','unique:hazard_reports,code'],
            'observed_at'           => ['required','date'],
            'location'              => ['nullable','string','max:255'],
            'category'              => ['nullable','string','max:60'],
            'description'           => ['nullable','string'],
            'immediate_action'      => ['nullable','string'],
            'recommendation'        => ['nullable','string'],

            'likelihood_initial'    => ['nullable','integer','between:1,5'],
            'severity_initial'      => ['nullable','integer','between:1,5'],
            'risk_initial'          => ['nullable','integer','min:0'],

            'likelihood_residual'   => ['nullable','integer','between:1,5'],
            'severity_residual'     => ['nullable','integer','between:1,5'],
            'risk_residual'         => ['nullable','integer','min:0'],

            'assignee_id'           => ['nullable','uuid','exists:users,id'],
            'due_date'              => ['nullable','date'],
            'linked_incident_id'    => ['nullable','uuid','exists:incidents,id'],

            'status'                => ['nullable','in:reported,assigned,mitigated,verified,closed'],

            'verified_at'           => ['nullable','date'],
            'verified_by'           => ['nullable','uuid','exists:users,id'],
            'verification_note'     => ['nullable','string'],

            'tags'                  => ['nullable','array'],
            'meta'                  => ['nullable','array'],
        ];
    }
}
