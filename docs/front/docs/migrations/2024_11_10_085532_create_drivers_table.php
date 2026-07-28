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
        Schema::create('drivers', function (Blueprint $table) {
            $table->id();
            $table->string('firstName',30);
            $table->string('lastName',30);
            $table->string('phoneNumber',10)->unique();
            $table->boolean('car_owner')->default(false);
            $table->string('dialCode');
            // $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->unsignedBigInteger('officeId')->nullable();
            $table->foreign('officeId')->references('id')->on('offices')->onDelete('set null');
            $table->foreignId('vehicleId')
            ->nullable()
            ->constrained('vehicles')
            ->cascadeOnDelete();
            $table->text('address');
            $table->string('country');
            $table->string('city');
            $table->string('region');
            $table->boolean('isActive')->default(1);
            $table->boolean('isConected')->default(0);
            $table->string('gender',10)->nullable()->enum('male','female');
            // $table->timestamp('email_verified_at')->nullable();
            $table->decimal('fleetCommissionCustomValue', 10, 2)->default(0);
            $table->decimal('driverCommissionCustomValue', 10, 2)->default(0);
            $table->boolean('isOfficeCommissionCustom')->default(false);
            $table->boolean('isFleetCommissionCustom')->default(false);
            $table->boolean('is_registered')->default(false);
            $table->boolean('free_driver')->default(false);
            $table->double('walletBalance')->default(0);
            $table->double('officeDues')->default(0);
            $table->double('fleetDues')->default(0);
            $table->integer('kmCount')->default(0);
            $table->integer('rideCount')->default(0);
            $table->float('rating')->default(0);
            $table->text('photo')->nullable();
            $table->integer('ratingExcellent')->default(0);
            $table->integer('ratingGood')->default(0);
            $table->integer('ratingAverage')->default(0);
            $table->integer('ratingBelowAverage')->default(0);
            $table->integer('ratingPoor')->default(0);
            $table->softDeletes();
            $table->rememberToken();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('drivers');
    }
};
