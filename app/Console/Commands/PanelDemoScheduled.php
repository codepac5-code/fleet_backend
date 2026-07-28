<?php

namespace App\Console\Commands;

use App\Http\Core\Const\Options\OrderStatus;
use App\Http\Core\GeoServices\ShardContext;
use App\Models\Booking;
use App\Models\Driver;
use App\Models\InfrastructureNode;
use App\Models\Office;
use App\Models\SubService;
use App\Models\User;
use Illuminate\Console\Command;

class PanelDemoScheduled extends Command
{
    protected $signature = 'panel:demo-scheduled {--clear : Remove all demo scheduled trips}';

    protected $description = 'Seed (or clear) demo scheduled trips for previewing the Panel scheduled board';

    private const MARK = 'PANEL_DEMO';

    public function handle(): int
    {
        $this->bootShard();

        if ($this->option('clear')) {
            $removed = Booking::withTrashed()->where('description', self::MARK)->forceDelete();
            $this->info("Removed {$removed} demo scheduled trips.");

            return self::SUCCESS;
        }

        Booking::withTrashed()->where('description', self::MARK)->forceDelete();

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
            ['status' => OrderStatus::$Pending,    'when' => '+3 hours',  'assign' => false],
            ['status' => OrderStatus::$Scheduled,  'when' => '+1 day',    'assign' => true],
            ['status' => OrderStatus::$Pending,    'when' => '+2 days',   'assign' => true],
            ['status' => OrderStatus::$OnGoing,    'when' => '-20 minutes', 'assign' => true],
            ['status' => OrderStatus::$InProgress, 'when' => '-40 minutes', 'assign' => true],
            ['status' => OrderStatus::$Hold,       'when' => '+6 hours',  'assign' => true],
            ['status' => OrderStatus::$Completed,  'when' => '-1 day',    'assign' => true],
            ['status' => OrderStatus::$Completed,  'when' => '-2 days',   'assign' => true],
            ['status' => OrderStatus::$Cancelled,  'when' => '-5 hours',  'assign' => true],
            ['status' => OrderStatus::$Pending,    'when' => '+5 hours',  'assign' => false],
        ];

        $count = 0;
        foreach ($plan as $i => $row) {
            $officeId = $offices[$i % count($offices)];
            $route    = $routes[$i % count($routes)];
            $driverId = $row['assign'] ? Driver::query()->where('officeId', $officeId)->value('id') : null;

            $b = new Booking();
            $b->userId         = $userId;
            $b->officeId       = $officeId;
            $b->subServiceId   = $subId;
            $b->driverId       = $driverId;
            $b->status         = $row['status'];
            $b->is_scheduled   = 1;
            $b->scheduled_time = date('Y-m-d H:i:s', strtotime($row['when']));
            $b->startAddress   = $route[0];
            $b->endAddress     = $route[1];
            $b->distance       = (string) (5 + $i * 2.5);
            $b->amount         = 30 + $i * 7;
            $b->totalAmount    = 30 + $i * 7;
            $b->paymentType    = $i % 2 === 0 ? 'cash' : 'electronic';
            $b->paymentStatus  = $row['status'] === OrderStatus::$Completed ? 'paid' : 'pending';
            $b->description    = self::MARK;

            if ($driverId) {
                $b->assignedAt = now();
            }
            if ($row['status'] === OrderStatus::$Cancelled) {
                $b->reason      = 'إلغاء تجريبي للمعاينة';
                $b->cancelledAt = now();
            }

            $b->save();
            $count++;
        }

        $this->info("Seeded {$count} demo scheduled trips (marker: " . self::MARK . ").");
        $this->line('Remove them anytime with: php artisan panel:demo-scheduled --clear');

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
