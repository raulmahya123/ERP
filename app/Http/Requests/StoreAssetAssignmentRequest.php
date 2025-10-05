<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreAssetAssignmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Sesuaikan logic permission kamu
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'to_site_id'  => ['nullable', 'uuid', 'exists:sites,id'],
            'to_user_id'  => ['nullable', 'uuid', 'exists:users,id'],
            'assigned_at' => ['nullable', 'date'], // format bebas, akan di-parse Carbon di helper
            'note'        => ['nullable', 'string', 'max:2000'],
        ];
    }

    public function messages(): array
    {
        return [
            'to_site_id.exists' => 'Site tujuan tidak ditemukan.',
            'to_user_id.exists' => 'User penerima tidak ditemukan.',
            'assigned_at.date'  => 'Tanggal efektif tidak valid.',
        ];
    }
}
