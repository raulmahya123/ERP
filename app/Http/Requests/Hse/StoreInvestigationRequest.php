<?php

namespace App\Http\Requests\Hse;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\IncidentInvestigation;

class StoreInvestigationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', IncidentInvestigation::class) ?? false;
    }

    public function rules(): array
    {
        return [
            'incident_id'           => ['required','uuid','exists:incidents,id'],
            'lead_investigator_id'  => ['nullable','uuid','exists:users,id'],

            'started_at'            => ['nullable','date'],
            'completed_at'          => ['nullable','date','after_or_equal:started_at'],

            'method'                => ['nullable','string','max:50'],
            'findings_summary'      => ['nullable','string'],
            'root_cause'            => ['nullable','string'],
            'corrective_actions'    => ['nullable','string'],

            'status'                => ['nullable','in:open,review,closed'],
            'meta'                  => ['nullable','array'],
        ];
    }
}
