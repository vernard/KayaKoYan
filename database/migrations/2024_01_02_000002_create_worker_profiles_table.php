<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('worker_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('slug')->unique();
            $table->text('bio')->nullable();
            $table->string('phone', 50)->nullable();
            $table->string('location')->nullable();
            $table->string('avatar_path')->nullable();
            $table->string('gcash_number', 50)->nullable();
            $table->string('gcash_name')->nullable();
            $table->string('bank_name')->nullable();
            $table->string('bank_account_number', 100)->nullable();
            $table->string('bank_account_name')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('worker_profiles');
    }
};
