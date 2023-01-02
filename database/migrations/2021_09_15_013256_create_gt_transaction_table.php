<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateGtTransactionTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('gt_transaction', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->Integer('booking_id');
            $table->string('city');
            $table->string('charge_code');
            $table->string('charging_station_name');
            $table->string('operator_partner');
            $table->Integer('connector_id');
            $table->string('ocpp_id_tag');
            $table->timestamp('start_date_time')->nullable();
            $table->string('start_value')->nullable();
            $table->timestamp('stop_date_time')->nullable();
            $table->string('stop_value')->nullable();
            $table->string('booking_mode');
            $table->string('meter_reading');
            $table->string('energy_consumed');
            $table->string('initial_soc');
            $table->string('final_soc');
            $table->string('vin_number');
            $table->string('duration');
            $table->string('reason');
            $table->string('auth_status');

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
        Schema::dropIfExists('gt_transaction');
    }
}
