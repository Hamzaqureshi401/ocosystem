<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCstoreReceiptrefundTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cstore_receiptrefund', function (Blueprint $table) {
            $table->bigIncrements('id');
			// FK to cstore_receipt.id
            $table->integer('cstore_receipt_id')->unsigned();
			// Amount to be refunded in cents
            $table->integer('refund_amount')->unsigned();
			// FK to users.id
            $table->integer('staff_user_id')->unsigned();
            $table->string('description')->nullable();

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
        Schema::dropIfExists('cstore_receiptrefund');
    }
}
