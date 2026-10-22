<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('code', 50);
            $table->string('client_name')->nullable();
            $table->string('location')->nullable();
            $table->text('description')->nullable();
            $table->string('construction_type')->nullable();
            $table->date('start_date')->nullable();
            $table->date('expected_completion_date')->nullable();
            $table->decimal('original_budget', 18, 2)->default(0);
            $table->decimal('approved_revised_budget', 18, 2)->default(0);
            $table->decimal('contract_value', 18, 2)->nullable();
            $table->text('payment_terms')->nullable();
            $table->string('status', 30)->default('planning');
            $table->timestamps();

            $table->unique(['company_id', 'code']);
            $table->index(['company_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('projects');
    }
};
