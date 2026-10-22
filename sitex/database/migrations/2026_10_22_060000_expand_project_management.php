<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->foreignId('owner_id')
                ->nullable()
                ->after('company_id')
                ->constrained('users')
                ->restrictOnDelete();
            $table->foreignId('project_manager_id')
                ->nullable()
                ->after('owner_id')
                ->constrained('users')
                ->restrictOnDelete();
        });

        Schema::create('construction_activities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->string('code', 50);
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('unit', 50)->nullable();
            $table->decimal('planned_quantity', 18, 4)->nullable();
            $table->decimal('completed_quantity', 18, 4)->default(0);
            $table->date('planned_start_date')->nullable();
            $table->date('planned_end_date')->nullable();
            $table->date('actual_start_date')->nullable();
            $table->date('actual_end_date')->nullable();
            $table->string('status', 30)->default('not_started');
            $table->timestamps();

            $table->unique(['project_id', 'code']);
            $table->index(['project_id', 'status']);
        });

        Schema::create('project_milestones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->date('due_date')->nullable();
            $table->date('completed_at')->nullable();
            $table->string('status', 30)->default('pending');
            $table->timestamps();

            $table->index(['project_id', 'status', 'due_date']);
        });

        Schema::create('warehouses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('code', 50);
            $table->string('location')->nullable();
            $table->string('type', 30)->default('site_store');
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['project_id', 'code']);
            $table->index(['company_id', 'project_id', 'is_active']);
        });

        Schema::create('project_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->foreignId('uploaded_by')->constrained('users')->restrictOnDelete();
            $table->string('name');
            $table->string('category', 50);
            $table->string('visibility', 20)->default('project');
            $table->string('disk', 30)->default('local');
            $table->string('path');
            $table->string('original_name');
            $table->string('mime_type', 150);
            $table->unsignedBigInteger('size');
            $table->timestamps();

            $table->index(['project_id', 'category']);
            $table->index(['project_id', 'visibility']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('project_documents');
        Schema::dropIfExists('warehouses');
        Schema::dropIfExists('project_milestones');
        Schema::dropIfExists('construction_activities');

        Schema::table('projects', function (Blueprint $table) {
            $table->dropConstrainedForeignId('project_manager_id');
            $table->dropConstrainedForeignId('owner_id');
        });
    }
};
