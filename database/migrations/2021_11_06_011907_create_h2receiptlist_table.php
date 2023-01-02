<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateH2receiptlistTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('h2receiptlist', function (Blueprint $table) {
            $table->bigIncrements('id');
			$table->timestamp('h2receipt_tstamp');
			// FK to h2receipt.systemid
			$table->integer('h2receipt_id')->unsigned();
			$table->string('h2receipt_systemid');
			$table->integer('pump_no')->unsigned();
			$table->integer('total')->unsigned();
			// Amount that had been authorized
			$table->integer('fuel')->unsigned();
			// Amount that had been actually filled
			$table->integer('filled')->unsigned();
			// Difference between fuel and filled
			// Should be refund_amt instead
			$table->integer('refund')->unsigned();
			// Refunded qty in litres
			$table->float('refund_qty')->unsigned();

			// Store user_id who had pressed the bluecrab button
			$table->integer('refund_staff_user_id')->unsigned()->nullable();
			// Store timestamp when user pressed the bluecrab button
			$table->timestamp('refund_tstamp')->nullable();

			// Whether user had clicked on blue crab button
			$table->enum('status', ['active','completed','refunded','voided'])
                ->default('active');
			$table->softDeletes();
            $table->timestamps();
            $table->engine = "Aria";
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('h2receiptlist');
    }
}
