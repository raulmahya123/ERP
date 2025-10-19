<?php

namespace App\Http\Requests\Hse;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\Incident;

class StoreIncidentRequest extends FormRequest
{
    public function authorize(): bool
    {
        // izinkan jika user boleh create Incident (policy)
        return $this->user()?->can('create', Incident::class) ?? false;
    }

    public function rules(): array
    {
        return [
            'site_id'     => ['nullable','uuid','exists:sites,id'],
            'occurred_at' => ['required','date'],
            'location'    => ['nullable','string','max:255'],
            'category'    => ['nullable','string','max:50'],
            'severity'    => ['nullable','string','max:30'],
            'description' => ['nullable','string'],
            'status'      => ['nullable','in:reported,under_investigation,action_in_progress,closed'],
            'code'        => ['nullable','string','max:40','unique:incidents,code'],
            'tags'        => ['nullable','array'],
            'meta'        => ['nullable','array'],
        ];
    }
}
