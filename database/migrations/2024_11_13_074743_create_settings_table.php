<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSettingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('settings', function (Blueprint $table) {
            $table->id('id');
            $table->string('type')->nullable()->default(null);
            $table->string('key');
            $table->string('name');
            $table->json('value_ar')->nullable();
            $table->json('value_en')->nullable();
            $table->foreignId('officeId')->references('id')->on('offices')->cascadeOnDelete();
            $table->index(["key"], 'settings_key_index');
        });
    }

    // keis :
    //[
    //  ride-commission
    //]

    

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('settings');
    }
}
