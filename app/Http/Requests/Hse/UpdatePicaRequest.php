<?php

namespace App\Http\Requests\Hse;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\Pica;

class UpdatePicaRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var \App\Models\Pica $pica */
        $pica = $this->route('pica');
        return $pica && $this->user()?->can('update', $pica);
    }

    public function rules(): array
    {
        return [
            'related_incident_id'   => ['nullable','uuid','exists:incidents,id'],
            'related_hazard_id'     => ['nullable','uuid','exists:hazard_reports,id'],

            'title'                 => ['required','string','max:200'],
            'problem_statement'     => ['nullable','string'],
            'root_cause'            => ['nullable','string'],
            'preventive_action'     => ['nullable','string'],

            'owner_id'              => ['nullable','uuid','exists:users,id'],
            'due_date'              => ['nullable','date'],

            'status'                => ['nullable','in:open,in_progress,pending_review,effective,ineffective,closed'],
            'closed_at'             => ['nullable','date'],
            'effectiveness_review'  => ['nullable','string'],

            'meta'                  => ['nullable','array'],
        ];
    }
}
