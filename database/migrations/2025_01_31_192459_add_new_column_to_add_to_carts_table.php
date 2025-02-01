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
        Schema::table('add_to_carts', function (Blueprint $table) {
            $table->string('category')->nullable();
            $table->string('brand')->nullable();
            $table->string('price');
            $table->string('title');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('add_to_carts', function (Blueprint $table) {
            //
        });
    }
};
