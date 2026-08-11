<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

// REFERENCE — db/01-schema.sql
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('districts', function (Blueprint $table) {
            $table->id();
            $table->string('name', 80);
            $table->string('region', 20);
            $table->decimal('centroid_lat', 9, 6)->nullable();
            $table->decimal('centroid_lng', 9, 6)->nullable();
            $table->date('effective_from')->nullable();
            $table->boolean('is_active')->default(true);

            $table->unique('name', 'uq_districts_name');
            $table->index('region', 'idx_districts_region');
        });

        DB::statement("ALTER TABLE districts ADD CONSTRAINT chk_districts_region
            CHECK (region IN ('central','eastern','northern','western'))");
    }

    public function down(): void
    {
        Schema::dropIfExists('districts');
    }
};
