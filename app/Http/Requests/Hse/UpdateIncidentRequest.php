<?php

declare(strict_types=1);

namespace App\Http\Requests\Hse;

use App\Models\Incident;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class UpdateIncidentRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var \App\Models\Incident|null $incident */
        $incident = $this->route('incident'); // model binding
        return $incident && ($this->user()?->can('update', $incident) ?? false);
    }

    public function rules(): array
    {
        return [
            // ❗️Tanpa exists supaya tidak memunculkan "validation.exists"
            'site_id'     => ['nullable', 'uuid'],
            'occurred_at' => ['required', 'date'],

            'location'    => ['nullable', 'string', 'max:120'],
            'category'    => ['nullable', 'string', 'max:80'],
            'severity'    => ['nullable', 'string', 'max:40'],
            'description' => ['nullable', 'string', 'max:2000'],

            'status'      => ['nullable', Rule::in(['reported','under_investigation','action_in_progress','closed'])],

            // CODE TIDAK BOLEH DIUBAH SAAT EDIT
            'code'        => ['prohibited'],

            // koleksi opsional
            'tags'        => ['nullable', 'array', 'max:50'],
            'tags.*'      => ['nullable', 'string', 'max:40'],
            'meta'        => ['nullable', 'array'],
            // 'meta.*'    => ['nullable','string','max:255'],
        ];
    }

    public function attributes(): array
    {
        return [
            'occurred_at' => 'waktu kejadian',
            'status'      => 'status',
            'location'    => 'lokasi',
            'category'    => 'kategori',
            'severity'    => 'tingkat keparahan',
            'description' => 'deskripsi',
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
            'site_id.uuid'         => 'Site tidak valid.',
            'status.in'            => 'Status tidak valid.',
            'code.prohibited'      => 'Kode tidak boleh diubah.',
        ];
    }

    protected function prepareForValidation(): void
    {
        // Trim string & ubah string kosong menjadi null agar 'nullable' bekerja benar
        $castNullIfEmpty = static function ($v) {
            if (is_string($v)) {
                $v = trim($v);
                return $v === '' ? null : $v;
            }
            return $v;
        };

        $data = $this->all();

        foreach (['site_id','location','category','severity','description','status'] as $key) {
            if ($this->has($key)) {
                $data[$key] = $castNullIfEmpty($data[$key]);
            }
        }

        // abaikan tags/meta yang bukan array
        if ($this->has('tags') && !is_array($this->input('tags'))) {
            $data['tags'] = null;
        }
        if ($this->has('meta') && !is_array($this->input('meta'))) {
            $data['meta'] = null;
        }

        // 'code' dibiarkan; rule 'prohibited' akan menangani
        $this->replace($data);
    }
}
