<div class="max-w-2xl mx-auto space-y-6">
    <div>
        <a href="{{ route('events.dashboard', $booking->event) }}" class="text-[13px] text-slate hover:text-ink">&larr; {{ $booking->event->name }}</a>

        <div class="flex items-start justify-between mt-2">
            <div>
                <h1 class="text-[24px] font-semibold text-ink mb-1">Booking with {{ $booking->provider->business_name }}</h1>
                <p class="text-[14px] text-slate">
                    Agreed amount: UGX {{ number_format($booking->agreed_total_ugx) }} ·
                    <span class="text-[12px] font-medium px-2 py-0.5 rounded-full {{ $booking->status instanceof \App\Domain\Bookings\States\BookingState\Cancelled ? 'bg-amber-100 text-amber-700' : 'bg-green-100 text-green-900' }}">
                        {{ ucfirst(str_replace('_', ' ', (string) $booking->status)) }}
                    </span>
                </p>
            </div>
        </div>
    </div>

    <div class="bg-surface-raised border border-line rounded-lg p-6">
        <p class="text-[13px] text-slate mb-4">
            This agreement is between you and {{ $booking->provider->business_name }}. Event Go does not hold funds or mediate the arrangement.
        </p>

        <div class="flex gap-3">
            @if (! $booking->status instanceof \App\Domain\Bookings\States\BookingState\Completed && ! $booking->status instanceof \App\Domain\Bookings\States\BookingState\Closed && ! $booking->status instanceof \App\Domain\Bookings\States\BookingState\Cancelled)
                @if (($viewerSide === 'organiser' && ! $booking->organiser_completed_at) || ($viewerSide === 'provider' && ! $booking->provider_completed_at))
                    <button type="button" wire:click="completeBooking" wire:loading.attr="disabled" wire:target="completeBooking"
                            class="bg-green-600 hover:bg-green-700 text-white rounded-sm px-4 py-2 text-[14px] font-medium transition disabled:opacity-60">
                        Mark my side complete
                    </button>
                @else
                    <p class="text-[13px] text-slate self-center">Waiting on the other side to mark complete.</p>
                @endif
            @endif
        </div>
    </div>

    <div class="bg-surface-raised border border-line rounded-lg p-6">
        <h2 class="text-[16px] font-semibold text-ink mb-4">Checklist</h2>

        @foreach ($tasks as $task)
            <label wire:key="task-{{ $task->id }}" class="flex items-center gap-3 border-t border-line py-3 first:border-t-0 cursor-pointer">
                <input type="checkbox" {{ $task->status === 'done' ? 'checked' : '' }}
                       wire:click="toggleTask({{ $task->id }})" class="rounded-sm border-line">
                <span class="text-[14px] {{ $task->status === 'done' ? 'text-slate line-through' : 'text-ink' }}">{{ $task->title }}</span>
                <span class="text-[12px] text-slate ml-auto">{{ ucfirst($task->owner_side) }}</span>
            </label>
        @endforeach

        <form wire:submit="addTask" class="flex gap-2 mt-4">
            <input type="text" wire:model="newTaskTitle" placeholder="Add a task…"
                   class="flex-1 border border-line rounded-sm px-3 py-2 text-[14px] text-ink focus:outline-none focus:ring-2 focus:ring-green-600">
            <select wire:model="newTaskOwnerSide" class="border border-line rounded-sm px-2 py-2 text-[14px] text-ink">
                <option value="both">Both</option>
                <option value="organiser">Organiser</option>
                <option value="provider">Provider</option>
            </select>
            <button type="submit" wire:loading.attr="disabled" wire:target="addTask"
                    class="bg-green-600 hover:bg-green-700 text-white rounded-sm px-3 py-2 text-[14px] transition disabled:opacity-60">
                Add
            </button>
        </form>
        @error('newTaskTitle') <p class="mt-1 text-[13px] text-amber-700">{{ $message }}</p> @enderror
    </div>

    <div class="bg-surface-raised border border-line rounded-lg p-6">
        <h2 class="text-[16px] font-semibold text-ink mb-4">Files</h2>

        @foreach ($files as $file)
            <div wire:key="file-{{ $file->id }}" class="flex items-center justify-between border-t border-line py-3 first:border-t-0">
                <a href="{{ \Illuminate\Support\Facades\Storage::disk(config('filesystems.default'))->url($file->path) }}" target="_blank" class="text-[14px] text-green-600 hover:underline">{{ $file->label }}</a>
                <button type="button" wire:click="deleteFile({{ $file->id }})" wire:loading.attr="disabled" wire:target="deleteFile({{ $file->id }})"
                        class="text-[13px] text-slate hover:text-ink underline">
                    Remove
                </button>
            </div>
        @endforeach

        <form wire:submit="uploadFile" class="flex gap-2 mt-4 items-start">
            <input type="text" wire:model="fileLabel" placeholder="Label (e.g. floor plan)"
                   class="flex-1 border border-line rounded-sm px-3 py-2 text-[14px] text-ink focus:outline-none focus:ring-2 focus:ring-green-600">
            <input type="file" wire:model="upload" class="text-[13px]">
            <button type="submit" wire:loading.attr="disabled" wire:target="uploadFile"
                    class="bg-green-600 hover:bg-green-700 text-white rounded-sm px-3 py-2 text-[14px] transition disabled:opacity-60">
                Upload
            </button>
        </form>
        @error('upload') <p class="mt-1 text-[13px] text-amber-700">{{ $message }}</p> @enderror
        @error('fileLabel') <p class="mt-1 text-[13px] text-amber-700">{{ $message }}</p> @enderror
    </div>

    <div class="bg-surface-raised border border-line rounded-lg p-6">
        <h2 class="text-[16px] font-semibold text-ink mb-4">Amendments</h2>
        <p class="text-[13px] text-slate mb-4">A record of what you and the other side say you've agreed. Event Go does not approve or enforce these changes.</p>

        @forelse ($amendments as $amendment)
            <div wire:key="amendment-{{ $amendment->id }}" class="border-t border-line py-3 first:border-t-0">
                <p class="text-[14px] text-ink">
                    UGX {{ number_format($amendment->previous_total_ugx) }} &rarr; UGX {{ number_format($amendment->new_total_ugx) }}
                </p>
                <p class="text-[13px] text-slate">{{ $amendment->note }} · {{ $amendment->created_at->diffForHumans() }}</p>
            </div>
        @empty
            <p class="text-[13px] text-slate">No amendments recorded yet.</p>
        @endforelse

        <form wire:submit="recordAmendment" class="flex gap-2 mt-4">
            <input type="number" wire:model="newTotalUgx" placeholder="New total (UGX)"
                   class="w-40 border border-line rounded-sm px-3 py-2 text-[14px] text-ink focus:outline-none focus:ring-2 focus:ring-green-600">
            <input type="text" wire:model="amendmentNote" placeholder="What changed and why…"
                   class="flex-1 border border-line rounded-sm px-3 py-2 text-[14px] text-ink focus:outline-none focus:ring-2 focus:ring-green-600">
            <button type="submit" wire:loading.attr="disabled" wire:target="recordAmendment"
                    class="bg-green-600 hover:bg-green-700 text-white rounded-sm px-3 py-2 text-[14px] transition disabled:opacity-60">
                Record
            </button>
        </form>
        @error('newTotalUgx') <p class="mt-1 text-[13px] text-amber-700">{{ $message }}</p> @enderror
        @error('amendmentNote') <p class="mt-1 text-[13px] text-amber-700">{{ $message }}</p> @enderror
    </div>

    <livewire:bookings.thread :booking-id="$bookingId" :key="'thread-'.$bookingId" />

    @if ($booking->status instanceof \App\Domain\Bookings\States\BookingState\Completed || $booking->status instanceof \App\Domain\Bookings\States\BookingState\Closed)
        <livewire:bookings.leave-review :booking-id="$bookingId" :key="'review-'.$bookingId" />
    @endif

    @unless ($booking->status instanceof \App\Domain\Bookings\States\BookingState\Cancelled || $booking->status instanceof \App\Domain\Bookings\States\BookingState\Closed)
        <div class="bg-surface-raised border border-line rounded-lg p-6">
            <h2 class="text-[16px] font-semibold text-ink mb-2">Cancel this booking</h2>
            <p class="text-[13px] text-slate mb-4">Cancelling records a fact and releases the date. No policy is applied and no refund is calculated — that's between you and the other side.</p>
            <form wire:submit="cancelBooking" class="flex gap-2">
                <input type="text" wire:model="cancellationNote" placeholder="Why is this being cancelled?"
                       class="flex-1 border border-line rounded-sm px-3 py-2 text-[14px] text-ink focus:outline-none focus:ring-2 focus:ring-green-600">
                <button type="submit" wire:loading.attr="disabled" wire:target="cancelBooking"
                        class="border border-line hover:bg-surface text-ink rounded-sm px-3 py-2 text-[14px] transition disabled:opacity-60">
                    Cancel booking
                </button>
            </form>
            @error('cancellationNote') <p class="mt-1 text-[13px] text-amber-700">{{ $message }}</p> @enderror
        </div>
    @endunless
</div>
