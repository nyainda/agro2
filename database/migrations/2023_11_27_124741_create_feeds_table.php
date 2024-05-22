<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('feeds', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('animal_id');
            $table->unsignedBigInteger('user_id')->nullable();
            $table->decimal('amount')->nullable();
            $table->string('unit')->nullable();
            $table->string('feed_details')->nullable();
            $table->decimal('feed_weight')->nullable();
            $table->string('weight_unit')->nullable();
            $table->string('feeding_currency')->nullable();
            $table->decimal('estimated_cost')->nullable();
            $table->text('feeding_description')->nullable();
            $table->date('feeding_date')->nullable();
            $table->integer('repeat_days')->nullable();
            $table->string('feeding_method')->nullable();
            $table->string('food_type')->nullable();
            $table->string('feeder_name')->nullable();
            $table->time('feeding_time')->nullable();

            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users');
            $table->foreign('animal_id')->references('id')->on('animals')->onDelete('cascade');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('feeds');

    }
};
