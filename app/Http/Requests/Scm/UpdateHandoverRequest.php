<?php

namespace App\Http\Requests\Scm;

use Illuminate\Foundation\Http\FormRequest;

class UpdateHandoverRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }
    public function rules(): array
    {
        return (new StoreHandoverRequest())->rules();
    }
}
