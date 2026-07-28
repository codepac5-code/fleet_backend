<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('rider_support_tickets')) {
            Schema::create('rider_support_tickets', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('user_id')->index();
                $table->unsignedBigInteger('booking_id')->nullable();
                $table->unsignedBigInteger('office_id')->nullable();
                $table->string('category', 32);
                $table->string('layer', 16);
                $table->string('subject');
                $table->string('status', 16)->default('open');
                $table->timestamp('last_message_at')->nullable();
                $table->timestamps();

                $table->index(['user_id', 'status']);
            });
        }

        if (! Schema::hasTable('rider_support_messages')) {
            Schema::create('rider_support_messages', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('ticket_id')->index();
                $table->string('sender_type', 8);
                $table->unsignedBigInteger('sender_id');
                $table->text('body');
                $table->timestamp('created_at')->nullable();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('rider_support_messages');
        Schema::dropIfExists('rider_support_tickets');
    }
};
