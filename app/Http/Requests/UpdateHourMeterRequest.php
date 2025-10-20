<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateHourMeterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'site_id' => $this->input('site_id') ?: session('site_id'),
        ]);
    }

    public function rules(): array
    {
        $siteId  = (string) $this->input('site_id');
        $date    = (string) $this->input('date');
        $shiftId = (string) $this->input('shift_id');
        $unitId  = (string) $this->input('unit_id');

        // Ambil model dari route param {hour_meter}
        $current = $this->route('hour_meter');

        return [
            'site_id'   => ['required','uuid','exists:sites,id'],
            'date'      => ['required','date'],
            'shift_id'  => ['required','uuid','exists:shifts,id'],
            'unit_id'   => ['required','uuid','exists:assets,id'],

            'hm_start'  => ['required','numeric','min:0','max:99999999.9'],
            'hm_end'    => ['required','numeric','min:0','max:99999999.9','gte:hm_start'],
            'hm_delta'  => ['nullable','numeric','min:0','max:99999999.9'],
            'anomaly'   => ['sometimes','boolean'],

            'client_uid'=> [
                'nullable','string','max:64',
                Rule::unique('scm_hour_meters','client_uid')
                    ->ignore($current?->id) // abaikan diri sendiri
                    ->where(fn($q) => $q->where('site_id', $siteId)),
            ],

            // anti-duplikat kombinasi (kecuali dirinya sendiri)
            'composite_unique' => [
                Rule::unique('scm_hour_meters','id')
                    ->ignore($current?->id)
                    ->where(fn($q) =>
                        $q->where('site_id', $siteId)
                          ->where('date', $date)
                          ->where('shift_id', $shiftId)
                          ->where('unit_id', $unitId)
                    ),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'hm_end.gte'              => 'HM End harus ≥ HM Start.',
            'client_uid.unique'       => 'client_uid sudah dipakai di site ini.',
            'composite_unique.unique' => 'Hour Meter untuk kombinasi (site, tanggal, shift, unit) sudah ada.',
        ];
    }

    protected function passedValidation(): void
    {
        $this->merge([
            'anomaly' => (bool) $this->boolean('anomaly'),
        ]);
    }
}
