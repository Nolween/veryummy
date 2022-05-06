<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ingredients', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->references('id')->on('users')->onUpdate('cascade')->onDelete('cascade');
            $table->string('name');
            $table->string('icon')->nullable();
            $table->boolean('is_accepted')->nullable();
            $table->boolean('vegan_compatible')->nullable();
            $table->boolean('vegetarian_compatible')->nullable();
            $table->boolean('gluten_free_compatible')->nullable();
            $table->boolean('halal_compatible')->nullable();
            $table->boolean('kosher_compatible')->nullable();
            $table->timestamps();
            $table->index(['user_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('ingredients');
    }
};
