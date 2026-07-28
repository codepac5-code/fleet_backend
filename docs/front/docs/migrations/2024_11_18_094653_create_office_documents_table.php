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
        Schema::create('office_documents', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('officeId');
            $table->unsignedBigInteger('documentId');
            $table->tinyInteger('isVerified')->nullable()->default('0');
            $table->foreign('officeId')->references('id')->on('offices')->onDelete('cascade');
            $table->foreign('documentId')->references('id')->on('documents')->onDelete('cascade');
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('office_documents');
    }
};
