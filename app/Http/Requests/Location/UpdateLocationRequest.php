<?php

namespace App\Http\Requests\Location;

class UpdateLocationRequest extends StoreLocationRequest
{
    // (opsional) Kalau butuh aturan berbeda saat update, override di sini.
    // public function rules(): array
    // {
    //     $rules = parent::rules();
    //     // contoh kalau ada field unique:
    //     // $id = $this->route('location'); // atau sesuai nama route-model
    //     // $rules['code'] = ['required','string',"unique:locations,code,{$id}"];
    //     // return $rules;
    //     return $rules;
    // }
}
