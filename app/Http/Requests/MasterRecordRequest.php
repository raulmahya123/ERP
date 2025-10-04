<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class MasterRecordRequest extends FormRequest
{
    /**
     * Tentukan apakah user boleh akses request ini.
     */
    public function authorize(): bool
    {
        // cek via Gate → hanya GM (gate: manage-master-data)
        return $this->user()?->can('manage-master-data') ?? false;
    }

    /**
     * Aturan validasi.
     */
    public function rules(): array
    {
        // ambil entity dari route parameter
        $entity = $this->route('entity');
        $config = config('masterdata');

        // kalau entity tidak dikenal → abort
        abort_unless(array_key_exists($entity, $config['entities'] ?? []), 404, 'Unknown entity');

        // default rules
        $rules = $config['rules']['*'] ?? [];

        // merge rules khusus entity kalau ada
        if (!empty($config['rules'][$entity])) {
            $rules = array_merge($rules, $config['rules'][$entity]);
        }

        return $rules;
    }

    /**
     * Preprocessing sebelum validasi.
     */
    protected function prepareForValidation(): void
    {
        // pastikan extra selalu berupa array
        $this->merge([
            'extra' => $this->input('extra', []) ?: [],
        ]);
    }
}
