<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ats_answers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shift_record_id')->constrained()->cascadeOnDelete();
            $table->foreignId('ats_question_id')->constrained()->cascadeOnDelete();
            $table->boolean('answer');
            $table->timestamps();

            $table->unique(['shift_record_id', 'ats_question_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ats_answers');
    }
};
