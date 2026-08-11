<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

// CATALOG (admin-editable taxonomy — no deploys to add an event type) — db/01-schema.sql
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('event_types', function (Blueprint $table) {
            $table->id();
            $table->string('slug', 60);
            $table->string('name', 80);
            $table->string('icon', 40)->nullable();
            $table->boolean('is_active')->default(true);
            $table->smallInteger('sort_order')->default(0);
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();

            $table->unique('slug', 'uq_event_types_slug');
        });

        Schema::create('scope_questions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('event_type_id');
            $table->string('key', 50);
            $table->string('label', 200);
            $table->string('help_text', 255)->nullable();
            $table->string('input_type', 20);
            $table->json('options')->nullable();
            $table->string('default_value', 120)->nullable();
            $table->boolean('is_required')->default(false);
            $table->smallInteger('sort_order')->default(0);
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();

            $table->unique(['event_type_id', 'key'], 'uq_scope_q');

            $table->foreign('event_type_id', 'fk_scope_q_type')
                ->references('id')->on('event_types')->onDelete('cascade');
        });

        DB::statement("ALTER TABLE scope_questions ADD CONSTRAINT chk_scope_q_input
            CHECK (input_type IN ('number','bool','select','multiselect','text','date'))");

        Schema::create('service_categories', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('parent_id')->nullable();
            $table->string('slug', 80);
            $table->string('name', 100);
            $table->string('icon', 40)->nullable();
            $table->string('unit_label', 40)->nullable();
            $table->boolean('requires_capacity')->default(false);
            $table->boolean('is_active')->default(true);
            $table->smallInteger('sort_order')->default(0);
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();

            $table->unique('slug', 'uq_svc_cat_slug');
            $table->index('parent_id', 'idx_svc_cat_parent');

            $table->foreign('parent_id', 'fk_svc_cat_parent')
                ->references('id')->on('service_categories')->onDelete('cascade');
        });

        // Rules that turn scope answers into a requirements matrix. See architecture §6.
        Schema::create('requirement_templates', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('event_type_id');
            $table->unsignedBigInteger('service_category_id');
            $table->string('condition_expr', 255)->nullable();
            $table->string('quantity_expression', 255)->default('1');
            $table->bigInteger('benchmark_unit_cost_ugx')->nullable();
            $table->string('default_title', 150)->nullable();
            $table->string('default_notes', 500)->nullable();
            $table->string('priority', 20)->default('important');
            $table->smallInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();

            $table->unique(['event_type_id', 'service_category_id'], 'uq_req_tpl');

            $table->foreign('event_type_id', 'fk_req_tpl_type')
                ->references('id')->on('event_types')->onDelete('cascade');
            $table->foreign('service_category_id', 'fk_req_tpl_cat')
                ->references('id')->on('service_categories')->onDelete('cascade');
        });

        DB::statement("ALTER TABLE requirement_templates ADD CONSTRAINT chk_req_tpl_priority
            CHECK (priority IN ('essential','important','optional'))");
    }

    public function down(): void
    {
        Schema::dropIfExists('requirement_templates');
        Schema::dropIfExists('service_categories');
        Schema::dropIfExists('scope_questions');
        Schema::dropIfExists('event_types');
    }
};
