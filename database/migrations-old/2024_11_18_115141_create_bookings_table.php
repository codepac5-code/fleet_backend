<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBookingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            // $table->dateTime('date')->nullable();
            $table->dateTime('startAt')->nullable();
            $table->dateTime('endAt')->nullable();
            // $table->integer('quantity')->nullable()->default('0');
            $table->double('amount')->nullable()->default('0');
            $table->double('discount')->nullable();
            $table->string('time')->nullable();
            // $table->decimal('time')->nullable();
            $table->boolean('isPercentage')->default(1);
            $table->double('totalAmount')->nullable()->default('0');
            $table->text('description')->nullable();
            $table->double('rating')->nullable();
            $table->string('reason' , 350)->nullable();
            $table->bigInteger('couponId')->nullable();
            $table->string('status')->nullable();
            $table->text('startAddress')->nullable();
            $table->text('endAddress')->nullable();
            $table->double('startLatitude')->nullable();
            $table->double('startLongitude')->nullable();
            $table->double('endLatitude')->nullable();
            $table->double('endLongitude')->nullable();
            $table->string('distance')->nullable()->default('0');
            $table->bigInteger('paymentId')->nullable();
            $table->enum('paymentType', ['cash', 'electronic', 'fleet_wallet'])
            ->default('cash');
            $table->string('stripe_payment_intent_id')->nullable();
            $table->string('durationDiff')->nullable()->default('0');
            $table->unsignedBigInteger('officeId')->nullable();
            $table->unsignedBigInteger('driverId')->nullable();
            $table->foreign('officeId')->references('id')->on('offices')->onDelete('set null');
            $table->foreign('driverId')->references('id')->on('drivers')->onDelete('cascade');
            // $table->foreignId('userId');->references('id')->on('users')->onDelete('cascade');
            $table->unsignedBigInteger('userId')->index();
            $table->foreignId('subServiceId')->references('id')->on('sub_services')->onDelete('cascade');
            $table->json('multiDestnationArray')->nullable();
            // $table->integer('rideCommission')->min(0)->max(100)->default(0);
            $table->integer('officeCommissionValue')->min(0)->max(100)->default(0);
            $table->integer('driverCommissionValue')->min(0)->max(100)->default(0);
            $table->integer('fleetCommissionValue')->min(0)->max(100)->default(0);
            $table->integer('driverCommissionPercentage')->min(0)->max(100)->default(0);
            $table->integer('officeCommissionPercentage')->min(0)->max(100)->default(0);
            $table->integer('fleetCommissionPercentage')->min(0)->max(100)->default(0);


            $table->string('paymentStatus', 20)->nullable()->default('pending')->comment('pending, paid , failed');
            $table->dateTime('PaymentDatetime')->nullable()->default(null);
            $table->text('otherPaymentTransactionDetail')->nullable()->default(null);
////////////////
            $table->boolean('is_scheduled')->default(false);
            $table->dateTime('scheduled_time')->nullable();
            $table->boolean('isReminderSent')->default(0);
            $table->dateTime('reminderSentAt')->nullable();

            $table->index(['userId','status'], 'bookings_user_status_index');
            $table->index(['userId','is_scheduled', 'scheduled_time','status'], 'bookings_user_scheduled_index');
            $table->index(['driverId', 'is_scheduled', 'scheduled_time'], 'bookings_driver_scheduled_index');
            $table->index(['driverId','status'], 'bookings_driver_status_index');

////////////////
            $table->softDeletes();
            $table->timestamps();

        //-------------  Status -----
        // pending
        // PendingApproval

        // inProgress
        // paid
        // Hold
        // Ongoing
        // Cancelled
        // Completed
        // research
        // Failed



        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('bookings');
    }
}
