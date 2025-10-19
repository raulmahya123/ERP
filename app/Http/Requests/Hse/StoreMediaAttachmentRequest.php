<?php

namespace App\Http\Requests\Hse;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\MediaAttachment;

class StoreMediaAttachmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Simple: izin create lampiran
        return $this->user()?->can('create', MediaAttachment::class) ?? true; // true jika belum ada policy
    }

    public function rules(): array
    {
        return [
            // polymorphic target (controller biasanya ambil dari route)
            'attachable_type'   => ['nullable','string','max:191'],
            'attachable_id'     => ['nullable','uuid'],

            'uploaded_by'       => ['nullable','uuid','exists:users,id'],

            'path'              => ['required','string','max:255'],
            'disk'              => ['nullable','string','max:50'],
            'mime'              => ['nullable','string','max:100'],
            'size_bytes'        => ['nullable','integer','min:0'],
            'taken_at'          => ['nullable','date'],
            'caption'           => ['nullable','string','max:255'],

            'meta'              => ['nullable','array'],
        ];
    }
}
