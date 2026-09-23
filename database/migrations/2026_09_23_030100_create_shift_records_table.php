<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shift_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('machine_id')->constrained()->cascadeOnDelete();

            $table->string('photo_path')->nullable();
            $table->string('signature_path')->nullable();
            $table->string('status', 20)->default('pending'); // pending | completed
            $table->timestamp('started_at');
            $table->timestamp('finished_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'status']);
            $table->index('machine_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shift_records');
    }
};
