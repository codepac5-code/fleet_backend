<?php

namespace App\Http\Services\Driver\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Core\Exceptions\DomainException;
use App\Http\Services\User\Support\Reply;
use App\Models\LostItem;
use App\Models\RideBooking;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Driver reports an item a rider left in the vehicle
 * (`POST /driver/trips/{id}/found-items`). Writes the rider-owned `lost_items`
 * table with `user_id` resolved from the booking (handoff §A), so the office
 * can arrange hand-back with the rider.
 */
class DriverFoundItemController extends Controller
{
    /** The driver's own found-item reports with their governed status. */
    public function index(Request $request): JsonResponse
    {
        $items = app(\App\Http\Core\Classes\Support\LostFoundService::class)
            ->forDriver((int) $request->user()->id);

        return Reply::ok(['items' => $items]);
    }

    public function store(Request $request, int $id): JsonResponse
    {
        $data = $request->validate([
            'kind' => ['required', 'string', 'max:32'],
            'note' => ['nullable', 'string', 'max:500'],
            'share_masked_number' => ['nullable', 'boolean'],
            'photo_base64' => ['nullable', 'string'],
            'ext' => ['nullable', 'string', 'max:8'],
        ]);

        $booking = RideBooking::query()
            ->where('id', $id)
            ->where('driver_id', (int) $request->user()->id)
            ->first();

        if ($booking === null) {
            throw DomainException::notFound();
        }

        // Optional photo of the found item → stored on the public disk.
        $photo = null;
        if (! empty($data['photo_base64'])) {
            $raw = $data['photo_base64'];
            $comma = strpos($raw, ',');
            if ($comma !== false && str_starts_with($raw, 'data:')) {
                $raw = substr($raw, $comma + 1);
            }
            $binary = base64_decode($raw, true);
            if ($binary === false) {
                throw DomainException::make('invalid_photo', 422);
            }
            $ext = preg_replace('/[^a-z0-9]/i', '', (string) ($data['ext'] ?? 'jpg')) ?: 'jpg';
            $path = 'found_items/' . (int) $booking->id . '/' . Str::uuid() . ".{$ext}";
            Storage::disk('public')->put($path, $binary);
            $photo = Storage::disk('public')->url($path);
        }

        // Governed lost & found: files a DRIVER found-item report on the trip's
        // office, which can then match it to the rider's lost-item report.
        $item = app(\App\Http\Core\Classes\Support\LostFoundService::class)->reportFound(
            (int) $request->user()->id,
            (int) $booking->user_id,
            (int) $booking->id,
            (int) $booking->office_id,
            [
                'category' => $data['kind'],
                'description' => $data['note'] ?? null,
                'photo' => $photo,
                'share_masked_number' => (bool) ($data['share_masked_number'] ?? true),
            ]
        );

        return Reply::ok([
            'id' => (int) $item->id,
            'status' => $item->status,
            'booking_id' => (int) $booking->id,
        ], 201);
    }
}
