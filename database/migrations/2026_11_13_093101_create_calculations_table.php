<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('calculations', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('visitor_id')->index(); // anonymous owner, from an encrypted cookie
            $table->unsignedTinyInteger('household_size');
            $table->json('inputs');     // validated form answers
            $table->json('breakdown');  // kg CO2e per year, per category
            $table->decimal('total_kg', 12, 2);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('calculations');
    }
};
