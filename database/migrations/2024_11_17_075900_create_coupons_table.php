<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCouponsTable extends Migration
{
    /**
     * Schema table name to migrate
     * @var string
     */
    public $tableName = 'coupons';
    /**
     * Run the migrations.
     * @table coupons
     *
     * @return void
     */
    public function up()
    {
        Schema::create($this->tableName, function (Blueprint $table) {
            $table->id();
            $table->string('code')->nullable();
            $table->string('discountType')->nullable()->comment('percentage,fixed');
            $table->double('discount')->nullable();
            $table->tinyInteger('status')->nullable();
            $table->dateTime('expireDate')->nullable();
            $table->boolean('isActive')->default(true);
            $table->boolean('isPercentage')->default(true);
            $table->integer('limit');
            $table->softDeletes();
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
        Schema::dropIfExists($this->tableName);
    }
}
