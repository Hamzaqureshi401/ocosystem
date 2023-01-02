<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateIpconfTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ipconf', function (Blueprint $table) {
            $table->bigIncrements('id');
			$table->string('public_ip');
            $table->string('local_ip');
            $table->integer('public_port')->unsigned()->nullable();
            $table->integer('local_port')->unsigned()->nullable();
            $table->string('location_systemid')->nullable();
			// Record who pressed Configure at Ocosystem
            $table->string('hq_staff_systemid')->nullable();
            $table->string('hq_staff_email')->nullable();
            $table->string('hq_staff_name')->nullable();
            $table->softDeletes();
            $table->timestamps();
            $table->engine = "ARIA";
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('ipconf');
    }
}
