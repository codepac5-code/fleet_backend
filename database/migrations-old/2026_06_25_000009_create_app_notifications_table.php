<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('app_notifications')) {
            return;
        }

        Schema::create('app_notifications', function (Blueprint $table) {
            $table->id();
            $table->uuid('event_uuid')->nullable();
            $table->string('notifiable_type', 32);
            $table->unsignedBigInteger('notifiable_id');
            $table->string('template_key', 64)->nullable();
            $table->string('type', 64);
            $table->string('locale', 8)->default('en');
            $table->string('title', 191)->nullable();
            $table->text('body')->nullable();
            $table->json('data')->nullable();
            $table->timestamp('read_at')->nullable();
            $table->timestamps();

            $table->unique(['event_uuid', 'notifiable_type', 'notifiable_id'], 'app_notifications_event_recipient_unique');
            $table->index(['notifiable_type', 'notifiable_id', 'read_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('app_notifications');
    }
};
