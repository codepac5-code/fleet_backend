<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMtnInvoicesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('mtn_invoices', function (Blueprint $table) {
            $table->id();
            $table->string('guid')->default('none');
            $table->string('amount');
            $table->string('TTL');
            $table->string('phoneNumber');
            $table->string('operationNumber')->nullable();
            $table->string('code')->nullable();
            $table->morphs('actor');
            $table->foreignId('orderId')->references('id')->on('bookings')->onUpdate('cascade')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('mtn_invoices');
    }
}
