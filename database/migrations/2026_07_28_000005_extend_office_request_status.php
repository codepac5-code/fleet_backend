<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * `status` was an enum of new|reviewed, written when reviewing a request only
 * flipped a label. Approval now provisions a real office account, so the column
 * has to hold approved|rejected too — widened to a plain string rather than a
 * bigger enum, so the next state costs no migration.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('office_requests')) {
            return;
        }

        Schema::table('office_requests', function (Blueprint $table) {
            $table->string('status', 16)->default('new')->change();
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('office_requests')) {
            return;
        }

        Schema::table('office_requests', function (Blueprint $table) {
            $table->enum('status', ['new', 'reviewed'])->default('new')->change();
        });
    }
};
