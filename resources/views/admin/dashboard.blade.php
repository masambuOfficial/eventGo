<x-layouts.app title="Admin">
    <h1 class="text-[24px] font-semibold text-ink mb-6">Admin</h1>

    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4 mb-8">
        <x-admin.stat-tile
            label="Pending verifications"
            :value="$pendingVerifications"
            :href="route('admin.verifications.index')"
        />
        <x-admin.stat-tile label="New users today" :value="$newUsersToday" :href="route('admin.users.index')" />
        <x-admin.stat-tile label="New users this week" :value="$newUsersThisWeek" :href="route('admin.users.index')" />
        <x-admin.stat-tile
            label="Active event types"
            :value="$activeEventTypes"
            :href="route('admin.taxonomy.event-types')"
        />
        <x-admin.stat-tile
            label="Active service categories"
            :value="$activeServiceCategories"
            :href="route('admin.taxonomy.service-categories')"
        />
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <a href="{{ route('admin.verifications.index') }}" class="block bg-surface-raised border border-line rounded-lg p-6 hover:border-green-600 transition-colors duration-200">
            <h2 class="text-[16px] font-semibold text-ink mb-1">Verification queue</h2>
            <p class="text-[14px] text-slate">Review provider verification requests.</p>
        </a>
        <a href="{{ route('admin.billing.index') }}" class="block bg-surface-raised border border-line rounded-lg p-6 hover:border-green-600 transition-colors duration-200">
            <h2 class="text-[16px] font-semibold text-ink mb-1">Billing activation</h2>
            <p class="text-[14px] text-slate">Activate a provider's plan or featured placement against a mobile money reference.</p>
        </a>
        <a href="{{ route('admin.taxonomy.index') }}" class="block bg-surface-raised border border-line rounded-lg p-6 hover:border-green-600 transition-colors duration-200">
            <h2 class="text-[16px] font-semibold text-ink mb-1">Taxonomy</h2>
            <p class="text-[14px] text-slate">Event types, service categories, districts, scope questions, and requirement templates.</p>
        </a>
        <a href="{{ route('admin.users.index') }}" class="block bg-surface-raised border border-line rounded-lg p-6 hover:border-green-600 transition-colors duration-200">
            <h2 class="text-[16px] font-semibold text-ink mb-1">Users</h2>
            <p class="text-[14px] text-slate">Search registered users, grant or revoke admin access, suspend accounts.</p>
        </a>
        <a href="{{ route('admin.reports.index') }}" class="block bg-surface-raised border border-line rounded-lg p-6 hover:border-green-600 transition-colors duration-200">
            <h2 class="text-[16px] font-semibold text-ink mb-1">Reports</h2>
            <p class="text-[14px] text-slate">Liquidity, operational health, funnels, and revenue.</p>
        </a>
    </div>
</x-layouts.app>
