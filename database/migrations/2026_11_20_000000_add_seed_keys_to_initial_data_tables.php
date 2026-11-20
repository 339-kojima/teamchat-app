<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('seed_key', 100)->nullable()->unique();
        });

        Schema::table('channels', function (Blueprint $table) {
            $table->string('seed_key', 100)->nullable()->unique();
        });

        Schema::table('messages', function (Blueprint $table) {
            $table->string('seed_key', 100)->nullable()->unique();
        });
    }

    public function down(): void
    {
        Schema::table('messages', function (Blueprint $table) {
            $table->dropUnique('messages_seed_key_unique');
            $table->dropColumn('seed_key');
        });

        Schema::table('channels', function (Blueprint $table) {
            $table->dropUnique('channels_seed_key_unique');
            $table->dropColumn('seed_key');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique('users_seed_key_unique');
            $table->dropColumn('seed_key');
        });
    }
};
