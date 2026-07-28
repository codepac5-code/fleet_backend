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
        Schema::create('issues', function (Blueprint $table) {
            $table->id();
            $table->morphs('owner'); 
            $table->string('subject');
            $table->nullableMorphs('assigned_to');
            $table->longText('description');
            $table->string('mode')->nullable();
            $table->string('photo')->nullable();
            $table->boolean('isClosed')->default(false);
            $table->timestamp('closedAt')->nullable();
            // $table->string('status')->nullable()->default('0');
            $table->enum('priority', ['low', 'medium', 'high'])->default('medium');
            // $table->foreignId('assigned_to')->nullable()->constrained('employees');
            $table->foreignId('department_id')->nullable()->constrained('departments');
            $table->enum('status', ['open', 'processing', 'closed'])->default('open');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('issues');
    }
};
