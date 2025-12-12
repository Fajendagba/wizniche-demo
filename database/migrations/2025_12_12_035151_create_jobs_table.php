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
        Schema::create('jobs', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->string('job_type');
            $table->string('client_name');
            $table->decimal('invoice_amount', 10, 2);
            $table->integer('labor_hours');
            $table->decimal('labor_rate', 8, 2);
            $table->enum('status', ['completed', 'in_progress', 'pending'])
                  ->default('completed');
            $table->timestamps();

            $table->index('status');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('jobs');
    }
};
