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
        Schema::create('trash_transactions', function (Blueprint $table) {
            $table->id();
            $table->uuid('trash_transaction_id')->unique();
            $table->string('trash_type');
            $table->decimal('trash_weight',8,2);
            $table->decimal('points',10,2);
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('petugas_id')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('trash_transactions');
    }
};
