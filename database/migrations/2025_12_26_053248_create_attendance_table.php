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
        Schema::create('attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->date('date');
            $table->time('clock_in_time')->nullable();
            $table->time('clock_out_time')->nullable();
            $table->integer('late_minutes')->default(0);
            $table->string('status')->default('PRESENT');
            $table->string('ip_address', 45)->nullable();
            $table->text('notes')->nullable();
            $table->boolean('is_forgiven')->default(false);
            $table->foreignId('forgiven_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('forgive_reason')->nullable();
            $table->timestamps();
            $table->unique(['user_id', 'date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendances');
    }
};