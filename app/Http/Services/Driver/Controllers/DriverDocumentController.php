<?php

namespace App\Http\Services\Driver\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Core\Exceptions\DomainException;
use App\Http\Services\User\Support\Reply;
use App\Models\DriverDocument;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Driver documents (`driver_documents`): KYC / vehicle papers. Upload stores the
 * metadata + file reference; review/approval is handled by the office/FleetOS.
 */
class DriverDocumentController extends Controller
{
    /** Strip a `data:<mime>;base64,` prefix if the caller sent a data URI. */
    private function stripDataUri(string $value): string
    {
        $comma = strpos($value, ',');
        return ($comma !== false && str_starts_with($value, 'data:')) ? substr($value, $comma + 1) : $value;
    }

    private function present(DriverDocument $d): array
    {
        return [
            'id' => (int) $d->id,
            'document_id' => $d->document_id !== null ? (int) $d->document_id : null,
            'name' => $d->name,
            'file' => $d->file,
            'status' => $d->status,
            'note' => $d->note,
            'expires_at' => $d->expires_at !== null ? $d->expires_at->toIso8601ZuluString() : null,
        ];
    }

    public function index(Request $request): JsonResponse
    {
        $items = DriverDocument::query()
            ->where('driverId', $request->user()->id)
            ->orderByDesc('id')
            ->get()
            ->map(fn (DriverDocument $d) => $this->present($d))
            ->all();

        return Reply::ok(['items' => $items]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'document_id' => ['nullable', 'integer', 'min:1'],
            'name' => ['required', 'string', 'max:190'],
            'file' => ['nullable', 'string', 'max:512'],
            'file_base64' => ['nullable', 'string'],
            'ext' => ['nullable', 'string', 'max:8'],
            'note' => ['nullable', 'string', 'max:500'],
            'expires_at' => ['nullable', 'date'],
        ]);

        // Prefer a real uploaded blob (base64) → store on the public disk and
        // reference its URL; otherwise fall back to a caller-provided path.
        $file = $data['file'] ?? null;
        if (! empty($data['file_base64'])) {
            $binary = base64_decode($this->stripDataUri($data['file_base64']), true);
            if ($binary === false) {
                throw DomainException::make('invalid_file', 422);
            }
            $driverId = (int) $request->user()->id;
            $ext = preg_replace('/[^a-z0-9]/i', '', (string) ($data['ext'] ?? 'jpg')) ?: 'jpg';
            $path = "driver_documents/{$driverId}/" . Str::uuid() . ".{$ext}";
            Storage::disk('public')->put($path, $binary);
            $file = Storage::disk('public')->url($path);
        }

        if ($file === null) {
            throw DomainException::make('file_required', 422);
        }

        $doc = DriverDocument::query()->create([
            'driverId' => (int) $request->user()->id,
            'document_id' => $data['document_id'] ?? null,
            'name' => $data['name'],
            'file' => $file,
            'note' => $data['note'] ?? null,
            'status' => 'pending',
            'expires_at' => $data['expires_at'] ?? null,
        ]);

        return Reply::ok($this->present($doc), 201);
    }
}
