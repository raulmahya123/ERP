<?php

namespace App\Http\Requests\Scm;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreDailyPlanRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    protected function prepareForValidation(): void
    {
        $items = collect($this->input('items', []))
            ->map(fn($r) => [
                'pit_id'        => $r['pit_id']        ?? null,
                'target_ton'    => $r['target_ton']    ?? null,
                'target_ritase' => $r['target_ritase'] ?? null,
                'notes'         => $r['notes']         ?? null,
            ])
            ->filter(fn($r) =>
                ($r['pit_id'] ?? null) ||
                ($r['target_ton'] !== null && $r['target_ton'] !== '') ||
                ($r['target_ritase'] !== null && $r['target_ritase'] !== '')
            )
            ->values()->all();

        $this->merge(['items' => $items]);
    }

    public function rules(): array
    {
        $siteId = (string) session('site_id');

        return [
            'plan_date' => ['required','date'],
            'shift_id'  => ['required','uuid', Rule::exists('shifts','id')],
            'remarks'   => ['nullable','string','max:2000'],

            'items'     => ['required','array','min:1'],
            'items.*.pit_id'        => ['required','uuid', Rule::exists('pits','id')->where(fn($q) => $q->where('site_id',$siteId))],
            'items.*.target_ton'    => ['required','numeric','min:0'],
            'items.*.target_ritase' => ['required','integer','min:0'],
            'items.*.notes'         => ['nullable','string','max:2000'],
        ];
    }

    public function attributes(): array
    {
        return [
            'plan_date' => 'Tanggal',
            'shift_id'  => 'Shift',
            'remarks'   => 'Catatan',
            'items'     => 'Items',
            'items.*.pit_id'        => 'PIT',
            'items.*.target_ton'    => 'Target ton',
            'items.*.target_ritase' => 'Target ritase',
            'items.*.notes'         => 'Catatan item',
        ];
    }

    public function messages(): array
    {
        return [
            'required'   => ':attribute wajib diisi.',
            'date'       => ':attribute tidak valid.',
            'uuid'       => ':attribute tidak valid.',
            'numeric'    => ':attribute harus berupa angka.',
            'integer'    => ':attribute harus bilangan bulat.',
            'min'        => ':attribute minimal :min.',
            'items.min'  => 'Minimal 1 baris item (PIT & target).',
            'shift_id.exists'       => 'Shift tidak ditemukan.',
            'items.*.pit_id.exists' => 'PIT tidak ditemukan pada site aktif.',
        ];
    }
}
