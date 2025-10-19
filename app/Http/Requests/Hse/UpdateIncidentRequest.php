<?php

namespace App\Http\Requests\Hse;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\Incident;

class UpdateIncidentRequest extends FormRequest
{
    public function authorize(): bool
    {
        // param route-nya 'incident' (sesuai ->parameters(['incidents' => 'incident']))
        $incident = $this->route('incident'); // model binding
        return $incident && $this->user()?->can('update', $incident);
    }

    public function rules(): array
    {
        $incident = $this->route('incident');

        return [
            'site_id'     => ['nullable','uuid','exists:sites,id'],
            'occurred_at' => ['required','date'],
            'location'    => ['nullable','string','max:255'],
            'category'    => ['nullable','string','max:50'],
            'severity'    => ['nullable','string','max:30'],
            'description' => ['nullable','string'],
            'status'      => ['nullable','in:reported,under_investigation,action_in_progress,closed'],
            'code'        => ['required','string','max:40','unique:incidents,code,' . ($incident?->id ?? 'NULL') . ',id'],
            'tags'        => ['nullable','array'],
            'meta'        => ['nullable','array'],
        ];
    }
}
