<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('channels', function (Blueprint $table) {
            $table->id();
            $table->foreignId('owner_id')->constrained('users')->restrictOnDelete();
            $table->string('name', 50);
            $table->text('description')->nullable();
            $table->boolean('is_private')->default(false);
            $table->timestamps();
            $table->unique(['name', 'is_private']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('channels');
    }
};
