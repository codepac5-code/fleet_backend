<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * An office runs services — plural.
 *
 * It first shipped as a single `offices.service_id`, which does not survive
 * contact with a real office: the same company runs the city meter service AND
 * sells airport corridors. One column forced a choice between them, so whichever
 * half it could not name became unpriceable.
 *
 * The many-to-many table `office_services` was already in the schema and empty —
 * `Office::services()` has always pointed at it. So this moves the single
 * assignment into that pivot and drops the column, leaving exactly ONE answer to
 * "what does this office sell" instead of a column and a table free to disagree.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('offices') || ! Schema::hasColumn('offices', 'service_id')) {
            return;
        }

        foreach (DB::table('offices')->whereNotNull('service_id')->get(['id', 'service_id']) as $office) {
            $exists = DB::table('office_services')
                ->where('officeId', $office->id)
                ->where('serviceId', $office->service_id)
                ->exists();

            if (! $exists) {
                DB::table('office_services')->insert([
                    'officeId' => $office->id,
                    'serviceId' => $office->service_id,
                    'status' => 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        Schema::table('offices', function (Blueprint $table) {
            $table->dropColumn('service_id');
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('offices') || Schema::hasColumn('offices', 'service_id')) {
            return;
        }

        Schema::table('offices', function (Blueprint $table) {
            $table->unsignedBigInteger('service_id')->nullable()->index();
        });

        // Only the first assignment can survive the narrowing.
        foreach (DB::table('office_services')->whereNull('deleted_at')->orderBy('id')->get() as $row) {
            DB::table('offices')->where('id', $row->officeId)->whereNull('service_id')
                ->update(['service_id' => $row->serviceId]);
        }
    }
};
