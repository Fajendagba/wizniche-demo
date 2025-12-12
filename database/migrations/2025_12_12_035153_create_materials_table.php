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
        Schema::create('materials', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('job_id')
                  ->constrained()
                  ->onDelete('cascade');
            $table->string('name');
            $table->decimal('cost', 8, 2);
            $table->timestamps();

            $table->index('job_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('materials');
    }
};
