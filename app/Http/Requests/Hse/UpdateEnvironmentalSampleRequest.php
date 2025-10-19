<?php

namespace App\Http\Requests\Hse;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Models\EnvironmentalSample;

class UpdateEnvironmentalSampleRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var \App\Models\EnvironmentalSample|null $sample */
        $sample = $this->route('sample'); // pastikan route param {sample}
        return (bool) $this->user()?->can('update', $sample ?? EnvironmentalSample::class);
    }

    public function prepareForValidation(): void
    {
        // Hanya normalisasi field yang DIKIRIM (hindari menimpa nilai lama)
        $payload = [];

        if ($this->has('site_id'))      $payload['site_id']     = $this->input('site_id');
        if ($this->has('type'))         $payload['type']        = strtolower(trim((string) $this->input('type')));
        if ($this->has('location'))     $payload['location']    = $this->safeTrim($this->input('location'));
        if ($this->has('parameter'))    $payload['parameter']   = $this->safeTrim($this->input('parameter'));
        if ($this->has('unit'))         $payload['unit']        = $this->safeTrim($this->input('unit'));
        if ($this->has('method'))       $payload['method']      = $this->safeTrim($this->input('method'));
        if ($this->has('instrument'))   $payload['instrument']  = $this->safeTrim($this->input('instrument'));

        if ($this->has('value'))        $payload['value']       = $this->normalizeNumber($this->input('value'));
        if ($this->has('limit_value'))  $payload['limit_value'] = $this->normalizeNumber($this->input('limit_value'));

        if ($this->has('is_compliant')) $payload['is_compliant'] = $this->normalizeBool($this->input('is_compliant'));

        // sampled_at biarkan apa adanya (validasi di rules)
        if (!empty($payload)) $this->merge($payload);
    }

    public function rules(): array
    {
        return [
            'site_id'     => ['sometimes','nullable','uuid', Rule::exists('sites','id')],
            'sampled_at'  => ['sometimes','date'],
            'type'        => ['sometimes', Rule::in(['air','emission','noise'])],
            'location'    => ['sometimes','nullable','string','max:255'],

            'parameter'   => ['sometimes','string','max:120'],
            'value'       => ['sometimes','nullable','numeric'],
            'unit'        => ['sometimes','nullable','string','max:20'],
            'method'      => ['sometimes','nullable','string','max:100'],
            'instrument'  => ['sometimes','nullable','string','max:100'],
            'limit_value' => ['sometimes','nullable','numeric'],

            'is_compliant'=> ['sometimes','boolean'],
            'meta'        => ['sometimes','nullable','json'],

            // contoh relasi opsional:
            // 'linked_incident_id' => ['sometimes','nullable','uuid', Rule::exists('incidents','id')],
        ];
    }

    public function messages(): array
    {
        return [
            'site_id.exists'       => 'Site tidak ditemukan.',
            'sampled_at.date'      => 'Format tanggal & jam sampling tidak valid.',
            'type.in'              => 'Tipe harus salah satu dari: air, emission, noise.',
            'parameter.max'        => 'Parameter maksimal 120 karakter.',
            'value.numeric'        => 'Value harus berupa angka.',
            'limit_value.numeric'  => 'Limit value harus berupa angka.',
            'is_compliant.boolean' => 'Isian compliance tidak valid.',
            'meta.json'            => 'Meta harus berupa JSON yang valid.',
        ];
    }

    public function attributes(): array
    {
        return [
            'sampled_at'  => 'Sampled At',
            'type'        => 'Type',
            'location'    => 'Location',
            'parameter'   => 'Parameter',
            'value'       => 'Value',
            'unit'        => 'Unit',
            'method'      => 'Method',
            'instrument'  => 'Instrument',
            'limit_value' => 'Limit Value',
            'is_compliant'=> 'Compliant',
            'meta'        => 'Meta',
            'site_id'     => 'Site',
        ];
    }

    protected function passedValidation(): void
    {
        // Ubah meta JSON (string) menjadi array agar match casts di Model
        if ($this->has('meta') && is_string($this->meta)) {
            $decoded = json_decode($this->meta, true);
            $this->merge(['meta' => $decoded ?? null]);
        }
    }

    /* =========================
     | Helpers (sanitize/coerce)
     |=========================*/
    private function safeTrim($v): ?string
    {
        if ($v === null) return null;
        if (!is_string($v)) return (string) $v;
        return trim(strip_tags($v));
    }

    private function normalizeNumber($v): ?float
    {
        if ($v === '' || $v === null) return null;
        if (is_string($v)) $v = str_replace(',', '.', $v); // koma → titik
        return is_numeric($v) ? (float) $v : null;
    }

    private function normalizeBool($v): bool
    {
        // terima 1/0, "1"/"0", true/false, "true"/"false"
        return filter_var($v, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? false;
    }
}
