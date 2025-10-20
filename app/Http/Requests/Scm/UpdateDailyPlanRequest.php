<?php

namespace App\Http\Requests\Scm;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateDailyPlanRequest extends FormRequest
{
    public function authorize(): bool { return true; }
    public function rules(): array {
        return (new StoreDailyPlanRequest())->rules();
    }
}
