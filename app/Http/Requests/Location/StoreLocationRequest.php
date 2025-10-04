<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreLocationRequest extends FormRequest
{
    public function authorize(): bool { return auth()->check(); }

    public function rules(): array {
        return [
            'name'            => ['required','string','max:255'],
            'longitude'       => ['required','numeric','between:-180,180'],
            'latitude'        => ['required','numeric','between:-90,90'],
            'years_of_collab' => ['nullable','integer','min:0','max:200'],
        ];
    }
}
