<?php

namespace App\Http\Controllers\Admin\Hse;

use App\Http\Controllers\Controller;
use App\Http\Requests\Hse\StoreMediaAttachmentRequest;
use App\Models\Incident;
use App\Models\IncidentInvestigation;
use App\Models\Pica;
use App\Models\HazardReport;
use App\Models\EnvironmentalSample;
use App\Models\MediaAttachment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class MediaAttachmentController extends Controller
{
    public function __construct()
{
    $this->authorizeResource(\App\Models\HazardReport::class, 'hazard');

    $this->middleware('can:assign,hazard')->only('assign');
    $this->middleware('can:mitigate,hazard')->only('mitigate');
    $this->middleware('can:verify,hazard')->only('verify');
    $this->middleware('can:close,hazard')->only('close');
}
    /** POST /admin/hse/media/{type}/{id} */
    public function store(StoreMediaAttachmentRequest $request, string $type, string $id)
    {
        $attachable = $this->resolveAttachable($type, $id);

        $file = $request->file('file');
        $disk = $request->input('disk', 'public');
        $dir  = "hse/{$type}/{$attachable->getKey()}";

        $path = $file->store($dir, $disk);

        $meta = $request->input('meta', []);
        if (!is_array($meta)) $meta = [];

        $attachment = $attachable->media()->create([
            'uploaded_by' => auth()->id(),
            'path'        => $path,
            'disk'        => $disk,
            'mime'        => $file->getClientMimeType(),
            'size_bytes'  => $file->getSize(),
            'taken_at'    => $request->date('taken_at'),
            'caption'     => $request->string('caption')->toString(),
            'meta'        => $meta,
        ]);

        return response()->json([
            'ok' => true,
            'id' => $attachment->id,
            'url'=> Storage::disk($disk)->url($path),
        ]);
    }

    /** DELETE /admin/hse/media/{attachment} */
    public function destroy(MediaAttachment $attachment)
    {
        try {
            Storage::disk($attachment->disk)->delete($attachment->path);
        } catch (\Throwable $e) {
            // ignore delete file error
        }
        $attachment->delete();

        return response()->json(['ok' => true]);
    }

    /** Helpers */
    protected function resolveAttachable(string $type, string $id)
    {
        $type = strtolower($type);
        $map = [
            'incidents'              => Incident::class,
            'investigations'         => IncidentInvestigation::class,
            'picas'                  => Pica::class,
            'hazards'                => HazardReport::class,
            'environmental-samples'  => EnvironmentalSample::class,
        ];
        if (!array_key_exists($type, $map)) {
            abort(422, 'Unsupported media attachable type.');
        }
        return $map[$type]::query()->findOrFail($id);
    }
}
