<div class="max-w-4xl mx-auto">
    <a href="{{ route('admin.dashboard') }}" class="text-[13px] text-slate hover:text-ink">&larr; Admin</a>

    <h1 class="text-[24px] font-semibold text-ink mt-2 mb-6">Users</h1>

    @if ($actionError)
        <div class="bg-amber-100 text-amber-700 text-[14px] rounded-lg px-4 py-3 mb-4">{{ $actionError }}</div>
    @endif

    <input type="text" wire:model.live.debounce.500ms="search" placeholder="Search by name or email…"
           class="w-full border border-line rounded-sm px-3 py-2 text-[16px] text-ink mb-6 focus:outline-none focus:ring-2 focus:ring-green-600">

    <div class="bg-surface-raised border border-line rounded-lg divide-y divide-line">
        @forelse ($users as $user)
            <div wire:key="user-{{ $user->id }}" class="p-4 flex items-center justify-between gap-4">
                <div class="min-w-0">
                    <p class="text-[14px] font-medium text-ink truncate">{{ $user->full_name }}</p>
                    <p class="text-[13px] text-slate truncate">{{ $user->email }}</p>
                </div>
                <div class="flex items-center gap-3 shrink-0">
                    @if ($user->status === 'suspended')
                        <span class="text-[12px] font-medium px-2 py-0.5 rounded-full bg-amber-100 text-amber-700">Suspended</span>
                    @endif
                    @if ($user->hasRole('admin'))
                        <span class="text-[12px] font-medium px-2 py-0.5 rounded-full bg-green-100 text-green-900">Admin</span>
                    @endif

                    <button type="button" wire:click="toggleAdmin({{ $user->id }})"
                            wire:confirm="{{ $user->hasRole('admin') ? 'Remove admin access for this user?' : 'Grant admin access to this user?' }}"
                            class="text-[13px] text-green-600 hover:underline">
                        {{ $user->hasRole('admin') ? 'Revoke admin' : 'Make admin' }}
                    </button>

                    @if ($user->status === 'suspended')
                        <button type="button" wire:click="reactivate({{ $user->id }})" class="text-[13px] text-slate hover:text-ink underline">
                            Reactivate
                        </button>
                    @else
                        <button type="button" wire:click="suspend({{ $user->id }})" wire:confirm="Suspend this account? They will not be able to log in."
                                class="text-[13px] text-amber-700 hover:underline">
                            Suspend
                        </button>
                    @endif
                </div>
            </div>
        @empty
            <p class="p-4 text-[14px] text-slate">No users found.</p>
        @endforelse
    </div>

    <div class="mt-4">
        {{ $users->links() }}
    </div>
</div>
