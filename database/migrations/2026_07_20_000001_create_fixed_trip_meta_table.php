<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Companion table for office-mediated FIXED trips — one row per fixed
 * ride_booking. Kept separate from ride_bookings so the office-acceptance,
 * locked-fare, context and escalation bookkeeping does not bloat the shared
 * booking row the meter flow also uses. Per-shard (same connection as
 * ride_bookings).
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('fixed_trip_meta')) {
            return;
        }

        Schema::create('fixed_trip_meta', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('booking_id')->unique();

            // personal | corporate | family — the ONE axis that changes office
            // selection, billing and sharing. Everything else is shared.
            $table->string('context', 16)->default('personal');
            $table->unsignedBigInteger('company_id')->nullable();   // corporate billing target
            $table->string('on_behalf_of', 32)->nullable();          // family: self|parent|kids|<member id>

            // The fare the rider agreed to. It MUST survive to settlement even
            // though a fixed trip produces no meter ticks — this is the exact
            // path that previously nulled the final fare.
            $table->unsignedBigInteger('locked_fare_minor');
            $table->string('currency_code', 8);
            $table->timestamp('offer_expires_at')->nullable();

            // Office acceptance lifecycle.
            $table->timestamp('accepted_at')->nullable();
            $table->timestamp('declined_at')->nullable();
            $table->string('decline_reason', 255)->nullable();

            // Next-office fallback: offices already tried (so we never re-offer
            // the same one) and the deadline by which a driver must be assigned
            // before we escalate to a backup office.
            $table->json('offered_office_ids')->nullable();
            $table->timestamp('sla_assign_by')->nullable();
            $table->unsignedBigInteger('escalated_from_office_id')->nullable();

            $table->timestamps();

            $table->index('sla_assign_by');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fixed_trip_meta');
    }
};
