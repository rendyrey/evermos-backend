<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('product_type_id')->unsigned();
            $table->string('name');
            $table->integer('quantity')->unsigned();
            $table->float('price',15,2);
            $table->timestamps();
        });

        Schema::table('products', function(Blueprint $table){
            $table->foreign('product_type_id')->references('id')->on('product_types')->onDelete('cascade')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('products');
    }
}
