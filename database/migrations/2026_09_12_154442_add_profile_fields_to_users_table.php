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
        Schema::table('users', function (Blueprint $table) {
            $table->string('last_name', 100)->nullable();
            $table->string('dni', 8)->nullable()->unique();
            $table->boolean('is_active')->default(false);
            $table->foreignId('role_id')
                ->nullable()
                ->constrained('roles')
                ->restrictOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('role_id');
            $table->dropUnique(['dni']);
            $table->dropColumn([
                'last_name',
                'dni',
                'is_active',
            ]);
        });
    }
};
