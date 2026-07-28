<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Lost & found GOVERNANCE. The table held two indistinguishable kinds of report
 * (a rider's LOST item and a driver's FOUND item) with a support-ticket status
 * and no way to link a matching pair. This adds:
 *
 *  - `reporter_type` (rider | driver) + `driver_id` — who filed it.
 *  - `office_id` — the office that arbitrates the hand-back (from the booking).
 *  - `matched_item_id` — the paired report on the same booking (lost ↔ found).
 *  - `resolution` + `matched_at` / `returned_at` — the lifecycle audit trail.
 *
 * `status` widens from open|awaiting_reply|resolved to the real lifecycle:
 * reported → acknowledged → matched → ready_for_handback → returned, with
 * `unresolved` / `cancelled` branches.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('lost_items')) {
            return;
        }

        Schema::table('lost_items', function (Blueprint $table) {
            if (! Schema::hasColumn('lost_items', 'reporter_type')) {
                $table->string('reporter_type', 8)->default('rider')->after('booking_id'); // rider | driver
            }
            if (! Schema::hasColumn('lost_items', 'driver_id')) {
                $table->unsignedBigInteger('driver_id')->nullable()->after('reporter_type');
            }
            if (! Schema::hasColumn('lost_items', 'office_id')) {
                $table->unsignedBigInteger('office_id')->nullable()->after('driver_id');
            }
            if (! Schema::hasColumn('lost_items', 'matched_item_id')) {
                $table->unsignedBigInteger('matched_item_id')->nullable()->after('status');
            }
            if (! Schema::hasColumn('lost_items', 'resolution')) {
                $table->string('resolution', 32)->nullable()->after('matched_item_id');
            }
            if (! Schema::hasColumn('lost_items', 'matched_at')) {
                $table->timestamp('matched_at')->nullable()->after('resolution');
            }
            if (! Schema::hasColumn('lost_items', 'returned_at')) {
                $table->timestamp('returned_at')->nullable()->after('matched_at');
            }
        });

        // Migrate legacy statuses onto the new vocabulary so old rows stay valid.
        // open → reported, awaiting_reply → acknowledged, resolved → returned.
        foreach ([
            'open' => 'reported',
            'awaiting_reply' => 'acknowledged',
            'resolved' => 'returned',
        ] as $from => $to) {
            \Illuminate\Support\Facades\DB::table('lost_items')->where('status', $from)->update(['status' => $to]);
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('lost_items')) {
            return;
        }

        Schema::table('lost_items', function (Blueprint $table) {
            foreach (['reporter_type', 'driver_id', 'office_id', 'matched_item_id', 'resolution', 'matched_at', 'returned_at'] as $col) {
                if (Schema::hasColumn('lost_items', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
