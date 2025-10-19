<?php

namespace App\Http\Requests\Scm;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTripRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
   public function rules(): array
{
    $siteId = (string) session('site_id');
    $id     = $this->route('trip')?->id ?? 'NULL';
    return [
        // sama seperti StoreTripRequest, tapi unique diabaikan untuk current id
        'client_uid'=> ['nullable','string','max:64',"unique:scm_trips,client_uid,{$id},id,site_id,{$siteId}"],
    ] + (new StoreTripRequest)->rules();
}

}
