<?php

namespace App\Http\Requests\Location;

use Illuminate\Foundation\Http\FormRequest;

class StoreLocationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'name'            => ['required', 'string', 'max:255'],
            'longitude'       => ['required', 'numeric', 'between:-180,180'],
            'latitude'        => ['required', 'numeric', 'between:-90,90'],
            'years_of_collab' => ['nullable', 'integer', 'min:0', 'max:200'],
        ];
    }

    // (opsional) pesan & atribut agar lebih ramah
    public function messages(): array
    {
        return [
            'name.required'      => 'Nama wajib diisi.',
            'longitude.between'  => 'Longitude harus di antara -180 sampai 180.',
            'latitude.between'   => 'Latitude harus di antara -90 sampai 90.',
        ];
    }

    public function attributes(): array
    {
        return [
            'name'            => 'nama lokasi',
            'years_of_collab' => 'tahun kerja sama',
        ];
    }
}
