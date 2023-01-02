<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGtRevenueTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('gt_revenue', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->Integer('bookingid');

            $table->string("charging_station");
            $table->string("zone");
            $table->string("charger_code");
            $table->string("operator_partner");
            $table->string("city_name");
            $table->string("state_name");
            $table->timestamp("date");
            $table->string("user_type");
            $table->string("corporate");
            $table->string("vehicle_number");
            $table->timestamp("start_time")->nullable();
            $table->timestamp("end_time")->nullable();
            $table->string("duration");
            $table->string("energy_consumed");
            $table->string("revenue");
            $table->decimal('charging_amount', 8, 4)->unsigned()->nullable();
            $table->decimal('tax', 8, 4)->unsigned()->nullable();
            
            $table->string("vin");
            $table->string("partner_name");
            
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
        Schema::dropIfExists('gt_revenue');
    }
}
