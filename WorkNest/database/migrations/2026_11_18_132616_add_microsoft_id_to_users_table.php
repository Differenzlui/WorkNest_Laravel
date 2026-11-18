<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Microsoft users have no password, so it is allowed to be empty
        Schema::table('users', function (Blueprint $table) {
            $table->string('password')->nullable()->change();
        });

        // Remember each person's Microsoft ID
        Schema::table('users', function (Blueprint $table) {
            $table->string('microsoft_id')->nullable()->unique();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique(['microsoft_id']);
            $table->dropColumn('microsoft_id');
        });
    }
};