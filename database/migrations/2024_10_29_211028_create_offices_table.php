<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('offices', function (Blueprint $table) {
            $table->id();
            $table->string('officeName');
            $table->string('logo')->nullable();
            $table->string('email')->unique();
            $table->string('password');
            $table->string('contactNumber', 20)->nullable();
            $table->string('country')->nullable();
            $table->string('city')->nullable();
            $table->string('region')->nullable();
            $table->text('address')->nullable();
            $table->double('walletBalance')->default(0);
            $table->tinyInteger('status')->default('1');
            $table->decimal('commission_with_office_car', 10, 2)->default(0);
            $table->decimal('commission_with_driver_car', 10, 2)->default(0);
            $table->decimal('driver_commission_precentage', 10, 2)->default(70);
            $table->decimal('driver_car_commission_precentage', 10, 2)->default(15);
            $table->unsignedBigInteger('officeTypeId')->nullable();
            $table->string('timeZone')->default('UTC');
            $table->timestamp('last_notification_seen')->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->integer('limitOrders')->default(0);
            $table->float('rating')->default(0);
            $table->integer('ratingExcellent')->default(0);
            $table->integer('ratingGood')->default(0);
            $table->integer('ratingAverage')->default(0);
            $table->integer('ratingBelowAverage')->default(0);
            $table->integer('ratingPoor')->default(0);
            //$table->integer('limitMoney');
            $table->rememberToken();
            $table->softDeletes();
            $table->timestamps();
            // $table->foreignId("user_id")->references("id")->on("users")->onDelete("cascade")->onUpdate("cascade");
            // $table->string('name');
            // $table->text('logo');
            // $table->text('address');
            // $table->double('earnings');
            // $table->integer('type');
            // $table->integer('kmPrice');
            // $table->double('commissionValue');
            // $table->boolean('inQueue');

            // $table->boolean('isDeleted');
            // $table->boolean('hasTravelMode');
            // $table->integer('openPrice');
            // $table->boolean('isEnabled');
            // $table->string('phone1');
            // $table->string('phone2');
            // $table->string('phone3');
            // $table->integer('perMin');
            // $table->integer('radius')->default(3000);
            // $table->integer('currentOrders')->default(0);
            // $table->integer('ordersCount')->default(0);
            // $table->integer('currentMoney')->default(0);
            // $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('offices');
    }
};
