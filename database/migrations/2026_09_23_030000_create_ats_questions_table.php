<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ats_questions', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('stage', 20); // start | finish
            $table->unsignedSmallInteger('position')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['stage', 'is_active', 'position']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ats_questions');
    }
};
