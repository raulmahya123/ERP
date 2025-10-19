<?php

namespace App\Http\Requests\Hse;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Models\EnvironmentalSample;

class StoreEnvironmentalSampleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->can('create', EnvironmentalSample::class);
    }

    public function prepareForValidation(): void
    {
        // fallback site_id ke session + trimming & normalisasi ringan
        $this->merge([
            'site_id'      => $this->input('site_id') ?: session('site_id'),
            'type'         => $this->input('type') ? strtolower(trim($this->input('type'))) : null,
            'location'     => $this->safeTrim($this->input('location')),
            'parameter'    => $this->safeTrim($this->input('parameter')),
            'unit'         => $this->safeTrim($this->input('unit')),
            'method'       => $this->safeTrim($this->input('method')),
            'instrument'   => $this->safeTrim($this->input('instrument')),
            'value'        => $this->normalizeNumber($this->input('value')),
            'limit_value'  => $this->normalizeNumber($this->input('limit_value')),
            // checkbox akan ada (hidden 0 + checkbox 1) → aman untuk required|boolean
            'is_compliant' => $this->normalizeBool($this->input('is_compliant')),
        ]);
    }

    public function rules(): array
    {
        return [
            // pakai 'bail' agar berhenti di error pertama → UX lebih jelas
            'site_id'      => ['bail','nullable','uuid', Rule::exists('sites','id')],
            'sampled_at'   => ['bail','required','date'],
            'type'         => ['bail','required', Rule::in(['air','emission','noise'])],
            'location'     => ['nullable','string','max:255'],

            'parameter'    => ['bail','required','string','max:120'],
            'value'        => ['nullable','numeric'],
            'unit'         => ['nullable','string','max:20'],
            'method'       => ['nullable','string','max:100'],
            'instrument'   => ['nullable','string','max:100'],
            'limit_value'  => ['nullable','numeric'],

            'is_compliant' => ['bail','required','boolean'],
            'meta'         => ['nullable','json'],

            // contoh relasi lain jika dipakai nanti:
            // 'linked_incident_id' => ['nullable','uuid', Rule::exists('incidents','id')],
        ];
    }

    public function messages(): array
    {
        return [
            // disederhanakan karena kita tidak pakai soft-deletes di sites
            'site_id.exists'         => 'Site tidak ditemukan.',
            'sampled_at.required'    => 'Tanggal & jam sampling wajib diisi.',
            'sampled_at.date'        => 'Format tanggal & jam sampling tidak valid.',
            'type.required'          => 'Tipe sampling wajib diisi.',
            'type.in'                => 'Tipe harus salah satu dari: air, emission, noise.',
            'parameter.required'     => 'Parameter wajib diisi.',
            'parameter.max'          => 'Parameter maksimal 120 karakter.',
            'value.numeric'          => 'Value harus berupa angka.',
            'limit_value.numeric'    => 'Limit value harus berupa angka.',
            'is_compliant.required'  => 'Isian compliance wajib diisi.',
            'is_compliant.boolean'   => 'Isian compliance tidak valid.',
            'meta.json'              => 'Meta harus berupa JSON yang valid.',
        ];
    }

    public function attributes(): array
    {
        return [
            'sampled_at'   => 'Sampled At',
            'type'         => 'Type',
            'location'     => 'Location',
            'parameter'    => 'Parameter',
            'value'        => 'Value',
            'unit'         => 'Unit',
            'method'       => 'Method',
            'instrument'   => 'Instrument',
            'limit_value'  => 'Limit Value',
            'is_compliant' => 'Compliant',
            'meta'         => 'Meta',
            'site_id'      => 'Site',
        ];
    }

    /**
     * Setelah valid, ubah meta JSON (string) menjadi array agar match casts di Model.
     */
    protected function passedValidation(): void
    {
        if (is_string($this->meta)) {
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
