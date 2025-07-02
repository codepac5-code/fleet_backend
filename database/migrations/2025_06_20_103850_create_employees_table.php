<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->string('firstName',30);
            $table->string('lastName',30);
            $table->string('email',50)->unique();
            $table->string('phoneNumber',25);
            $table->string('employeeJobName_en',60);
            $table->string('employeeJobName_ar',60);
            $table->string('job_description_en',60);
            $table->string('job_description_ar',60);
            $table->foreignId('officeId')->references('id')->on('offices')->cascadeOnDelete();
            $table->text('address');
            $table->string('country');
            $table->string('city');
            $table->string('region');
            $table->boolean('isActive')->default(1);
            $table->boolean('isOnline')->default(0);
            $table->string('gender',10)->enum('male','female');
            $table->string('password');
            // $table->boolean('is_registered')->default(false);
            $table->text('photo')->nullable();
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
        Schema::dropIfExists('employees');
    }
};
