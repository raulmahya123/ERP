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
        // Policy create
        return $this->user()?->can('create', Incident::class) ?? false;
    }

    public function rules(): array
    {
        return [
            'site_id'     => ['nullable', 'uuid', 'exists:sites,id'],
            'occurred_at' => ['required', 'date'],

            // Selaraskan batasan panjang dengan UI/Controller
            'location'    => ['nullable', 'string', 'max:120'],
            'category'    => ['nullable', 'string', 'max:80'],
            'severity'    => ['nullable', 'string', 'max:40'],
            'description' => ['nullable', 'string', 'max:2000'],

            // Status boleh kosong (controller default -> 'reported')
            'status'      => ['nullable', Rule::in(['reported','under_investigation','action_in_progress','closed'])],

            // Code opsional; unik jika diisi
            'code'        => ['nullable', 'string', 'max:50', Rule::unique('incidents', 'code')],

            // Koleksi opsional – validasi itemnya juga
            'tags'        => ['nullable', 'array', 'max:50'],
            'tags.*'      => ['nullable', 'string', 'max:40'],

            // Meta bebas kunci, batasi tipe dasar
            'meta'        => ['nullable', 'array'],
            'meta.*'      => ['nullable'], // bisa dipersempit: string|numeric|boolean
        ];
    }

    public function attributes(): array
    {
        // Jika pakai resources/lang/id/validation.php, ini opsional.
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
        // Gunakan default dari file lang; tambahkan custom jika perlu
        return [
            'occurred_at.required' => 'Tanggal & jam kejadian wajib diisi.',
        ];
    }

    /**
     * Normalisasi input: trim string & ubah string kosong menjadi null,
     * agar lulus "nullable" tanpa false-positive (mis. "" pada unique).
     */
    protected function prepareForValidation(): void
    {
        $castNullIfEmpty = fn ($v) => is_string($v) ? (trim($v) === '' ? null : trim($v)) : $v;

        $data = $this->all();

        foreach (['site_id','location','category','severity','description','status','code'] as $key) {
            if ($this->has($key)) {
                $data[$key] = $castNullIfEmpty($data[$key]);
            }
        }

        // Pastikan array valid
        if ($this->has('tags') && !is_array($this->input('tags'))) {
            $data['tags'] = null;
        }
        if ($this->has('meta') && !is_array($this->input('meta'))) {
            $data['meta'] = null;
        }

        $this->replace($data);
    }
}
