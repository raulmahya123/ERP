<?php

namespace App\Http\Requests\Scm;

use Illuminate\Foundation\Http\FormRequest;

class StoreTripRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        $siteId = (string) session('site_id');
        return [
            'date'      => ['required','date'],
            'shift_id'  => ['required','uuid','exists:shifts,id'],
            'unit_id'   => ['required','uuid','exists:assets,id'],
            'operator_id'=>['nullable','uuid','exists:users,id'],
            'pit_id'    => ['nullable','uuid','exists:locations,id'],
            'from_stockpile_id'=>['nullable','uuid','exists:locations,id'],
            'to_stockpile_id'  =>['nullable','uuid','exists:locations,id'],
            'commodity_id'=>['required','uuid','exists:commodities,id'],
            'material_type'=>['nullable','string','max:30'],
            'tonnage'   => ['nullable','numeric','min:0'],
            'distance_km'=>['nullable','numeric','min:0'],
            'start_time'=> ['nullable','date'],
            'end_time'  => ['nullable','date','after_or_equal:start_time'],
            'client_uid'=> ['nullable','string','max:64',"unique:scm_trips,client_uid,NULL,id,site_id,{$siteId}"],
            'notes'     => ['nullable','string'],
        ];
    }
}
