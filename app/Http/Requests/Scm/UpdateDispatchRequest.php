<?php

namespace App\Http\Requests\Scm;

use Illuminate\Foundation\Http\FormRequest;

class UpdateDispatchRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }
    public function rules(): array
    {
        return (new StoreDispatchRequest())->rules();
    }
}
