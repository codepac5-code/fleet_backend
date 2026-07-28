<?php

namespace App\Http\Services\Panel\Shared\Notifications;

use App\Http\Services\Panel\Bookings\Logic\BookingStatus;
use App\Http\Services\Panel\Shared\Scoping\EntityScope;
use App\Http\Services\Panel\Shared\Tenant\TenantConnection;
use App\Models\Booking;
use App\Models\Driver;
use App\Models\Office;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Route;

class NotificationFeed
{
    public function __construct(private EntityScope $scope) {}

    private function connection(): ?string
    {
        return TenantConnection::current();
    }

    private function seenKey(): string
    {
        return 'panel_notifs_seen_' . ($this->scope->guard() ?? 'guest');
    }

    public function seenAt(): int
    {
        return (int) session($this->seenKey(), 0);
    }

    public function markAllRead(): void
    {
        session([$this->seenKey() => now()->timestamp]);
    }

    public function build(int $limit = 8): array
    {
        if (! $this->scope->user()) {
            return [];
        }

        $conn = $this->connection();
        $officeId = $this->scope->officeId();
        $isAdmin = $this->scope->isAdmin();
        $entity = $this->scope->guard();
        $items = [];

        $bookingQuery = Booking::on($conn)->latest('created_at')->limit($limit);
        if (! $isAdmin) {
            $bookingQuery->where('officeId', $officeId);
        }
        foreach ($bookingQuery->get(['id', 'status', 'totalAmount', 'created_at']) as $b) {
            $items[] = [
                'icon'  => 'bi-card-checklist',
                'tone'  => 'primary',
                'title' => textByLanguage('طلب جديد', 'New order') . ' #' . $b->id,
                'body'  => BookingStatus::label($b->status) . ' · ' . getPriceFormat($b->totalAmount),
                'time'  => $b->created_at,
                'ts'    => $b->created_at ? $b->created_at->timestamp : 0,
                'link'  => $this->routeOrNull("panel.{$entity}.booking.show", $b->id),
            ];
        }

        $driverQuery = Driver::on($conn)->latest('created_at')->limit($limit);
        if (! $isAdmin) {
            $driverQuery->where('officeId', $officeId);
        }
        foreach ($driverQuery->get(['id', 'firstName', 'lastName', 'created_at']) as $d) {
            $items[] = [
                'icon'  => 'bi-taxi-front',
                'tone'  => 'success',
                'title' => textByLanguage('سائق جديد', 'New driver'),
                'body'  => trim($d->firstName . ' ' . $d->lastName),
                'time'  => $d->created_at,
                'ts'    => $d->created_at ? $d->created_at->timestamp : 0,
                'link'  => $this->routeOrNull("panel.{$entity}.driver.show", $d->id),
            ];
        }

        if ($isAdmin) {
            foreach (Office::on($conn)->latest('created_at')->limit($limit)->get(['id', 'officeName', 'created_at']) as $o) {
                $items[] = [
                    'icon'  => 'bi-building',
                    'tone'  => 'warning',
                    'title' => textByLanguage('مكتب جديد', 'New office'),
                    'body'  => $o->officeName,
                    'time'  => $o->created_at,
                    'ts'    => $o->created_at ? $o->created_at->timestamp : 0,
                    'link'  => $this->routeOrNull('panel.admin.office.show', $o->id),
                ];
            }
            foreach (User::query()->latest('created_at')->limit($limit)->get(['id', 'firstName', 'lastName', 'created_at']) as $u) {
                $items[] = [
                    'icon'  => 'bi-person-plus',
                    'tone'  => 'primary',
                    'title' => textByLanguage('مستخدم جديد', 'New user'),
                    'body'  => trim($u->firstName . ' ' . $u->lastName),
                    'time'  => $u->created_at,
                    'ts'    => $u->created_at ? $u->created_at->timestamp : 0,
                    'link'  => $this->routeOrNull('panel.admin.user.index', null),
                ];
            }
        }

        usort($items, fn ($a, $b) => $b['ts'] <=> $a['ts']);
        $items = array_slice($items, 0, $limit);

        $seenAt = $this->seenAt();
        foreach ($items as &$item) {
            $item['unread'] = $item['ts'] > $seenAt;
            $item['ago'] = $item['time'] ? Carbon::parse($item['time'])->diffForHumans() : '';
        }

        return $items;
    }

    public function unreadCount(array $items): int
    {
        return count(array_filter($items, fn ($i) => $i['unread'] ?? false));
    }

    private function routeOrNull(string $name, $param)
    {
        if (! Route::has($name)) {
            return null;
        }

        return $param !== null ? route($name, $param) : route($name);
    }
}
