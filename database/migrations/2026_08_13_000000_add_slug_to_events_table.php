<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

// Adds a human-readable, unique slug for public URLs (/events/{slug}/wizard)
// so organisers don't see raw ULIDs or numeric ids. Nullable at the DB level
// because adding it to a populated table can't carry a NOT NULL default here
// without doctrine/dbal — the app always sets it via Event::generateSlug().
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->string('slug', 180)->nullable()->after('name');
        });

        foreach (DB::table('events')->orderBy('id')->get(['id', 'name']) as $event) {
            $base = Str::slug($event->name) ?: 'event';
            $slug = $base;
            $suffix = 2;

            while (DB::table('events')->where('slug', $slug)->exists()) {
                $slug = "{$base}-{$suffix}";
                $suffix++;
            }

            DB::table('events')->where('id', $event->id)->update(['slug' => $slug]);
        }

        Schema::table('events', function (Blueprint $table) {
            $table->unique('slug', 'uq_events_slug');
        });
    }

    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->dropUnique('uq_events_slug');
            $table->dropColumn('slug');
        });
    }
};
