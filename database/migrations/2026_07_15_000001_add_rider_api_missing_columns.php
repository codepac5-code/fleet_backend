<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Reconcile the rider-app DB schema with the public REST/Socket.IO contract
 * (docs/openapi.yaml → docs/API.md). Every column added here backs an
 * attribute the rider API returns or accepts that had no home in the schema.
 *
 * Idempotent: guarded by hasColumn / hasTable so it is safe to re-run.
 */
return new class extends Migration
{
    public function up(): void
    {
        // ── saved_places: map-pin icon + human-readable address ──────────────
        if (Schema::hasTable('saved_places')) {
            Schema::table('saved_places', function (Blueprint $table) {
                if (! Schema::hasColumn('saved_places', 'icon')) {
                    $table->string('icon', 16)->default('pin')->after('label'); // home|work|gem|heart|pin
                }
                if (! Schema::hasColumn('saved_places', 'address')) {
                    $table->string('address')->nullable()->after('title');
                }
            });
        }

        // ── safety_contacts: relation + primary flag ─────────────────────────
        if (Schema::hasTable('safety_contacts')) {
            Schema::table('safety_contacts', function (Blueprint $table) {
                if (! Schema::hasColumn('safety_contacts', 'relation')) {
                    $table->string('relation', 32)->nullable()->after('phone');
                }
                if (! Schema::hasColumn('safety_contacts', 'is_primary')) {
                    $table->boolean('is_primary')->default(false)->after('relation');
                }
            });
        }

        // ── rider_payment_methods: typed Stripe ids (pm_ / seti_) ────────────
        if (Schema::hasTable('rider_payment_methods')) {
            Schema::table('rider_payment_methods', function (Blueprint $table) {
                if (! Schema::hasColumn('rider_payment_methods', 'stripe_payment_method_id')) {
                    $table->string('stripe_payment_method_id')->nullable()->after('gateway_token'); // pm_...
                }
                if (! Schema::hasColumn('rider_payment_methods', 'stripe_setup_intent_id')) {
                    $table->string('stripe_setup_intent_id')->nullable()->after('stripe_payment_method_id'); // seti_...
                }
            });
        }

        // ── ride_bookings: Stripe intent, driver/vehicle snapshot, receipt ───
        if (Schema::hasTable('ride_bookings')) {
            Schema::table('ride_bookings', function (Blueprint $table) {
                if (! Schema::hasColumn('ride_bookings', 'driver_id')) {
                    $table->unsignedBigInteger('driver_id')->nullable()->after('office_id');
                }
                if (! Schema::hasColumn('ride_bookings', 'vehicle_id')) {
                    $table->unsignedBigInteger('vehicle_id')->nullable()->after('driver_id');
                }
                if (! Schema::hasColumn('ride_bookings', 'coupon_id')) {
                    $table->unsignedBigInteger('coupon_id')->nullable()->after('promo_code');
                }
                if (! Schema::hasColumn('ride_bookings', 'stripe_payment_intent_id')) {
                    $table->string('stripe_payment_intent_id')->nullable()->after('payment_method'); // pi_...
                }
                if (! Schema::hasColumn('ride_bookings', 'waiting_minor')) {
                    $table->integer('waiting_minor')->default(0)->after('discount_minor');
                }
                if (! Schema::hasColumn('ride_bookings', 'tip_minor')) {
                    $table->integer('tip_minor')->default(0)->after('waiting_minor');
                }
                if (! Schema::hasColumn('ride_bookings', 'rated_at')) {
                    $table->timestamp('rated_at')->nullable()->after('completed_at');
                }
            });
        }

        // ── ride_ratings: structured tags + office book-again / favorite ─────
        if (Schema::hasTable('ride_ratings')) {
            Schema::table('ride_ratings', function (Blueprint $table) {
                if (! Schema::hasColumn('ride_ratings', 'tags')) {
                    $table->json('tags')->nullable()->after('stars');
                }
                if (! Schema::hasColumn('ride_ratings', 'book_again')) {
                    $table->boolean('book_again')->nullable()->after('comment');
                }
                if (! Schema::hasColumn('ride_ratings', 'favorite')) {
                    $table->boolean('favorite')->nullable()->after('book_again');
                }
            });
        }

        // ── rider_support_tickets: contract topic enum ───────────────────────
        if (Schema::hasTable('rider_support_tickets')) {
            Schema::table('rider_support_tickets', function (Blueprint $table) {
                if (! Schema::hasColumn('rider_support_tickets', 'topic')) {
                    $table->string('topic', 16)->nullable()->after('category'); // lost_item|refund|payment|other
                }
            });
        }

        // ── help_suggestions: read-time for article cards ────────────────────
        if (Schema::hasTable('help_suggestions')) {
            Schema::table('help_suggestions', function (Blueprint $table) {
                if (! Schema::hasColumn('help_suggestions', 'read_minutes')) {
                    $table->unsignedInteger('read_minutes')->nullable()->after('category');
                }
            });
        }

        // ── offices: rider-facing card fields (verified/monitored/palette…) ──
        if (Schema::hasTable('offices')) {
            Schema::table('offices', function (Blueprint $table) {
                if (! Schema::hasColumn('offices', 'initials')) {
                    $table->string('initials', 4)->nullable()->after('officeName');
                }
                if (! Schema::hasColumn('offices', 'palette')) {
                    $table->string('palette', 1)->nullable()->after('initials'); // a|b|c
                }
                if (! Schema::hasColumn('offices', 'is_verified')) {
                    $table->boolean('is_verified')->default(false)->after('status');
                }
                if (! Schema::hasColumn('offices', 'is_monitored')) {
                    $table->boolean('is_monitored')->default(false)->after('is_verified');
                }
                if (! Schema::hasColumn('offices', 'on_time_percentage')) {
                    $table->decimal('on_time_percentage', 5, 2)->default(0)->after('rating');
                }
                if (! Schema::hasColumn('offices', 'avg_response_minutes')) {
                    $table->integer('avg_response_minutes')->nullable()->after('on_time_percentage');
                }
                if (! Schema::hasColumn('offices', 'ratings_count')) {
                    $table->integer('ratings_count')->default(0)->after('avg_response_minutes');
                }
                if (! Schema::hasColumn('offices', 'lat')) {
                    $table->decimal('lat', 10, 7)->nullable()->after('address');
                }
                if (! Schema::hasColumn('offices', 'lng')) {
                    $table->decimal('lng', 10, 7)->nullable()->after('lat');
                }
                if (! Schema::hasColumn('offices', 'working_hours')) {
                    $table->json('working_hours')->nullable()->after('lng');
                }
            });
        }

        // ── sub_services: catalog display fields (icon/badge/order/baseFare) ─
        if (Schema::hasTable('sub_services')) {
            Schema::table('sub_services', function (Blueprint $table) {
                if (! Schema::hasColumn('sub_services', 'icon')) {
                    $table->string('icon')->nullable()->after('image');
                }
                if (! Schema::hasColumn('sub_services', 'badge')) {
                    $table->string('badge')->nullable()->after('icon');
                }
                if (! Schema::hasColumn('sub_services', 'sort_order')) {
                    $table->integer('sort_order')->default(0)->after('badge');
                }
                if (! Schema::hasColumn('sub_services', 'base_fare')) {
                    $table->decimal('base_fare', 10, 2)->nullable()->after('minutePrice');
                }
            });
        }

        // ── services: catalog display fields (icon/badge/order) ──────────────
        if (Schema::hasTable('services')) {
            Schema::table('services', function (Blueprint $table) {
                if (! Schema::hasColumn('services', 'icon')) {
                    $table->string('icon')->nullable()->after('image');
                }
                if (! Schema::hasColumn('services', 'badge')) {
                    $table->string('badge')->nullable()->after('icon');
                }
                if (! Schema::hasColumn('services', 'sort_order')) {
                    $table->integer('sort_order')->default(0)->after('badge');
                }
            });
        }

        // ── family_members: B2B family/guardian riders ───────────────────────
        if (! Schema::hasTable('family_members')) {
            Schema::create('family_members', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('user_id')->index();       // owner (guardian)
                $table->string('name');
                $table->string('phone');
                $table->string('type', 8)->default('adult');          // minor|elder|adult
                $table->boolean('approval_required')->default(false);
                $table->boolean('auto_share')->default(false);
                $table->timestamps();
            });
        }

        // ── complaints: driver/office/safety complaints (routes to FleetOS) ──
        if (! Schema::hasTable('complaints')) {
            Schema::create('complaints', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('user_id')->index();
                $table->unsignedBigInteger('booking_id')->nullable();  // API tripId
                $table->string('about', 8);                            // driver|office|safety|other
                $table->text('description');
                $table->string('photo_url')->nullable();
                $table->string('routed_to', 8)->default('office');     // office|fleetos
                $table->string('priority', 8)->default('normal');      // normal|urgent
                $table->string('case_ref')->nullable();
                $table->string('status', 16)->default('open');
                $table->timestamps();
            });
        }

        // ── lost_items: item left in vehicle (POST /trips/{id}/lost-item) ────
        if (! Schema::hasTable('lost_items')) {
            Schema::create('lost_items', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('user_id')->index();
                $table->unsignedBigInteger('booking_id');              // API tripId
                $table->unsignedBigInteger('ticket_id')->nullable();
                $table->string('category', 16);                        // Phone|Wallet|Bag|Keys|Other
                $table->text('description')->nullable();
                $table->boolean('share_masked_number')->default(false);
                $table->string('status', 16)->default('open');         // open|awaiting_reply|resolved
                $table->timestamps();
            });
        }

        // ── corporate_invoices: B2B monthly billing (GET /corporate/invoices)
        if (! Schema::hasTable('corporate_invoices')) {
            Schema::create('corporate_invoices', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('user_id')->index();
                $table->string('month', 7);                            // YYYY-MM
                $table->unsignedInteger('trips')->default(0);
                $table->integer('amount_minor')->default(0);
                $table->string('currency_code', 3)->default('QAR');
                $table->string('status', 16)->default('unbilled');     // unbilled|due|paid
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('corporate_invoices');
        Schema::dropIfExists('lost_items');
        Schema::dropIfExists('complaints');
        Schema::dropIfExists('family_members');

        foreach ([
            'services' => ['icon', 'badge', 'sort_order'],
            'sub_services' => ['icon', 'badge', 'sort_order', 'base_fare'],
            'offices' => ['initials', 'palette', 'is_verified', 'is_monitored', 'on_time_percentage', 'avg_response_minutes', 'ratings_count', 'lat', 'lng', 'working_hours'],
            'help_suggestions' => ['read_minutes'],
            'rider_support_tickets' => ['topic'],
            'ride_ratings' => ['tags', 'book_again', 'favorite'],
            'ride_bookings' => ['driver_id', 'vehicle_id', 'coupon_id', 'stripe_payment_intent_id', 'waiting_minor', 'tip_minor', 'rated_at'],
            'rider_payment_methods' => ['stripe_payment_method_id', 'stripe_setup_intent_id'],
            'safety_contacts' => ['relation', 'is_primary'],
            'saved_places' => ['icon', 'address'],
        ] as $tbl => $cols) {
            if (Schema::hasTable($tbl)) {
                Schema::table($tbl, function (Blueprint $table) use ($tbl, $cols) {
                    foreach ($cols as $col) {
                        if (Schema::hasColumn($tbl, $col)) {
                            $table->dropColumn($col);
                        }
                    }
                });
            }
        }
    }
};
