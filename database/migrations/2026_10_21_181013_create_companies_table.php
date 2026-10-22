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
    Schema::create('companies', function (Blueprint $table) {
        $table->id();
        $table->string('name');
        $table->string('slug')->unique();
        $table->string('email')->nullable();
        $table->string('phone', 30)->nullable();
        $table->string('address')->nullable();
        $table->string('country', 100)->default('Rwanda');
        $table->string('currency', 3)->default('RWF');
        $table->foreignId('created_by')
            ->constrained('users')
            ->restrictOnDelete();
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('companies');
    }
};
