<?php
// app/Http/Requests/Hse/UpdateEnvironmentalSampleStatusRequest.php
declare(strict_types=1);

namespace App\Http\Requests\Hse;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\EnvironmentalSample;

final class UpdateEnvironmentalSampleStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var \App\Models\EnvironmentalSample $sample */
        $sample = $this->route('sample');
        return (bool) $this->user()?->can('update', $sample ?? EnvironmentalSample::class);
    }

    public function rules(): array
    {
        return [
            'status' => ['required','in:draft,submitted,verified'],
        ];
    }

    public function messages(): array
    {
        return [
            'status.required' => 'Status wajib diisi.',
            'status.in'       => 'Status tidak valid.',
        ];
    }
}
