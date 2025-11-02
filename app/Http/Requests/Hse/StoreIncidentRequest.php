<?php

declare(strict_types=1);

namespace App\Http\Requests\Hse;

use App\Models\Incident;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class StoreIncidentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', Incident::class) ?? false;
    }

    public function rules(): array
    {
        return [
            // ❗️Tidak pakai exists/required supaya controller bisa kasih flash error sendiri
            'site_id'     => ['nullable', 'uuid'],

            'occurred_at' => ['required', 'date'],

            // Selaraskan batasan panjang dengan UI
            'location'    => ['nullable', 'string', 'max:120'],
            'category'    => ['nullable', 'string', 'max:80'],
            'severity'    => ['nullable', 'string', 'max:40'],
            'description' => ['nullable', 'string', 'max:2000'],

            // Status opsional (controller default -> 'reported')
            'status'      => ['nullable', Rule::in(['reported','under_investigation','action_in_progress','closed'])],

            // Code opsional; unik jika diisi
            'code'        => ['nullable', 'string', 'max:50', Rule::unique('incidents', 'code')],

            // Koleksi opsional
            'tags'        => ['nullable', 'array', 'max:50'],
            'tags.*'      => ['nullable', 'string', 'max:40'],

            'meta'        => ['nullable', 'array'],
            'meta.*'      => ['nullable'],
        ];
    }

    public function attributes(): array
    {
        return [
            'occurred_at' => 'waktu kejadian',
            'location'    => 'lokasi',
            'category'    => 'kategori',
            'severity'    => 'tingkat keparahan',
            'description' => 'deskripsi',
            'status'      => 'status',
            'site_id'     => 'site',
            'code'        => 'kode',
            'tags'        => 'tag',
            'meta'        => 'meta',
        ];
    }

    public function messages(): array
    {
        return [
            'occurred_at.required' => 'Tanggal & jam kejadian wajib diisi.',
            'occurred_at.date'     => 'Tanggal & jam kejadian tidak valid.',
            'site_id.uuid'         => 'Site tidak valid.', // tetap rapi kalau user selundupkan nilai aneh
        ];
    }

    protected function prepareForValidation(): void
    {
        // Inject site dari session jika tidak dikirim, tapi tetap nullable
        if (!$this->has('site_id') && session()->has('site_id')) {
            $this->merge(['site_id' => session('site_id')]);
        }

        // Trim string & kosong -> null supaya nullable bekerja
        $castNullIfEmpty = fn ($v) => is_string($v) ? (trim($v) === '' ? null : trim($v)) : $v;

        $data = $this->all();

        foreach (['site_id','location','category','severity','description','status','code'] as $key) {
            if ($this->has($key)) {
                $data[$key] = $castNullIfEmpty($data[$key]);
            }
        }

        if ($this->has('tags') && !is_array($this->input('tags'))) {
            $data['tags'] = null;
        }
        if ($this->has('meta') && !is_array($this->input('meta'))) {
            $data['meta'] = null;
        }

        $this->replace($data);
    }
}
