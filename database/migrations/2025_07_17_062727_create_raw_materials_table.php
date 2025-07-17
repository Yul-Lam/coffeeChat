<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
{
    Schema::create('raw_materials', function (Blueprint $table) {
        $table->id('materialID');
        $table->string('name');
        $table->integer('quantity');
        $table->unsignedBigInteger('farmerID');
        $table->string('qualitygrade');
        $table->timestamps();

        // Add this only if there's a farmers table
        // $table->foreign('farmerID')->references('id')->on('farmers');
    });
}
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('raw_materials');
    }
};
