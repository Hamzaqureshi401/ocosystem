<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateGtUptimeTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('gt_uptime', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('opreator_partner');
            $table->string('charging_station');
            $table->string('manufacturer_name');
            $table->string('model');
            $table->string('charger_code')->nullable();
            
            $table->decimal('down_time', 8, 4)->unsigned()->nullable();
            $table->decimal('up_time', 8, 4)->unsigned()->nullable();
            $table->decimal('total_time', 8, 4)->unsigned()->nullable();

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
        Schema::dropIfExists('gt_uptime');
    }
}
