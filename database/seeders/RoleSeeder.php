<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

/**
 * Platform staff role only. "Organiser" and "provider" are not RBAC roles —
 * any user can create events, and becomes a provider by registering a
 * providers row (architecture §2, §11.1). This seeder exists to gate the
 * admin console, nothing more.
 */
class RoleSeeder extends Seeder
{
    public function run(): void
    {
        Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
    }
}
