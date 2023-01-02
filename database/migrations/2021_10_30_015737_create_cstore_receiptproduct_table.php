<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCstoreReceiptproductTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cstore_receiptproduct', function (Blueprint $table) {
            $table->bigIncrements('id');

			// FK to receipt.id
            $table->integer('receipt_id')->unsigned();
            // FK to product.id
            $table->integer('product_id')->unsigned();

            /* This is a local copy of the product */
            $table->string('name'); // Name of product
            $table->decimal('quantity',8,4)->unsigned();
            $table->integer('price')->unsigned();
            $table->integer('discount_pct')->unsigned()->
                default(0)->nullable();
            $table->integer('discount')->unsigned()->
                default(0)->nullable();

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
        Schema::dropIfExists('cstore_receiptproduct');
    }
}
