<?php

namespace App\Console\Commands;

use App\Http\Core\Const\Options\OrderStatus;
use App\Http\Core\GeoServices\ShardContext;
use App\Http\Core\SubSystems\RedisDatabase\RedisModels\Order\OrderRedisModel;
use App\Models\Booking;
use App\Models\Driver;
use App\Models\InfrastructureNode;
use App\Models\Office;
use App\Models\SubService;
use App\Models\User;
use Illuminate\Console\Command;

class PanelDemoLive extends Command
{
    protected $signature = 'panel:demo-live {--clear : Remove all demo live trips}';

    protected $description = 'Seed (or clear) demo immediate trips in Redis for previewing the Panel live board';

    private const MARK = 'PANEL_DEMO_LIVE';

    public function handle(): int
    {
        $this->bootShard();

        if ($this->option('clear')) {
            return $this->clear();
        }

        $this->clear(false);

        $offices = Office::query()->orderBy('id')->take(5)->pluck('id')->all();
        $userId  = User::query()->value('id');
        $subId   = SubService::query()->value('id');

        if (empty($offices) || ! $userId || ! $subId) {
            $this->error('Missing offices/users/sub-services to attach demo trips to.');

            return self::FAILURE;
        }

        $routes = [
            ['حي الملقا - الرياض', 'مطار الملك خالد الدولي'],
            ['حي العليا - الرياض', 'بوليفارد رياض سيتي'],
            ['حي النخيل - الرياض', 'جامعة الملك سعود'],
            ['حي الياسمين - الرياض', 'الواجهة البحرية'],
            ['حي القيروان - الرياض', 'مركز المملكة'],
        ];

        $plan = [
            ['status' => OrderStatus::$Pending,        'assign' => false, 'fleet' => false],
            ['status' => OrderStatus::$SearchOnDriver, 'assign' => false, 'fleet' => false],
            ['status' => OrderStatus::$OnGoing,        'assign' => true,  'fleet' => false],
            ['status' => OrderStatus::$InProgress,     'assign' => true,  'fleet' => false],
            ['status' => OrderStatus::$Hold,           'assign' => true,  'fleet' => false],
            ['status' => OrderStatus::$Completed,      'assign' => true,  'fleet' => false],
            ['status' => OrderStatus::$Cancelled,      'assign' => true,  'fleet' => false],
            ['status' => OrderStatus::$SearchOnDriver, 'assign' => false, 'fleet' => true],
            ['status' => OrderStatus::$InProgress,     'assign' => true,  'fleet' => true],
        ];

        $count = 0;
        foreach ($plan as $i => $row) {
            $officeId = $row['fleet'] ? null : $offices[$i % count($offices)];
            $route    = $routes[$i % count($routes)];
            $driverId = $row['assign']
                ? ($officeId ? Driver::query()->where('officeId', $officeId)->value('id') : Driver::query()->value('id'))
                : null;

            $b = new Booking();
            $b->userId        = $userId;
            $b->officeId      = $officeId;
            $b->subServiceId  = $subId;
            $b->driverId      = $driverId;
            $b->status        = $row['status'];
            $b->is_scheduled  = 0;
            $b->startAddress  = $route[0];
            $b->endAddress    = $route[1];
            $b->distance      = (string) (4 + $i * 3.2);
            $b->amount        = 25 + $i * 9;
            $b->totalAmount   = 25 + $i * 9;
            $b->paymentType   = $i % 2 === 0 ? 'cash' : 'electronic';
            $b->paymentStatus = $row['status'] === OrderStatus::$Completed ? 'paid' : 'pending';
            $b->description   = self::MARK;

            if ($driverId) {
                $b->assignedAt = now();
            }
            if ($row['status'] === OrderStatus::$Cancelled) {
                $b->reason      = 'إلغاء تجريبي للمعاينة';
                $b->cancelledAt = now();
            }

            $b->save();
            OrderRedisModel::storeWithPagenationService($b);
            $count++;
        }

        $this->info("Seeded {$count} demo live trips into Redis (marker: " . self::MARK . ").");
        $this->line('Remove them anytime with: php artisan panel:demo-live --clear');

        return self::SUCCESS;
    }

    private function clear(bool $report = true): int
    {
        $rows = Booking::withTrashed()->where('description', self::MARK)->get(['id', 'status']);

        foreach ($rows as $row) {
            OrderRedisModel::delete($row->id, $row->status);
        }

        $removed = Booking::withTrashed()->where('description', self::MARK)->forceDelete();

        if ($report) {
            $this->info("Removed {$removed} demo live trips (Redis + DB).");
        }

        return self::SUCCESS;
    }

    private function bootShard(): void
    {
        $node = InfrastructureNode::query()->where('type', 'country')->where('is_active', true)->first();

        if (! $node) {
            return;
        }

        ShardContext::set($node);
        config([
            'database.connections.dynamic.host'     => $node->db_host,
            'database.connections.dynamic.port'     => $node->db_port,
            'database.connections.dynamic.database' => $node->db_name,
            'database.connections.dynamic.username' => $node->db_user,
            'database.connections.dynamic.password' => $node->db_pass,
        ]);
    }
}
