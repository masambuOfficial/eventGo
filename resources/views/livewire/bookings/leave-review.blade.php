<div class="bg-surface-raised border border-line rounded-lg p-6">
    <h2 class="text-[16px] font-semibold text-ink mb-4">Review</h2>

    @if (! $unlocked)
        <p class="text-[13px] text-slate">Reviews unlock once both sides mark this booking complete.</p>
    @elseif ($existing)
        <p class="text-[14px] text-ink">You rated this {{ $existing->rating }}/5.</p>
        @if ($existing->comment)
            <p class="text-[13px] text-slate mt-1">{{ $existing->comment }}</p>
        @endif
    @else
        <form wire:submit="submit" class="space-y-3">
            <div>
                <label class="block text-[14px] font-medium text-ink mb-1">Rating</label>
                <select wire:model="rating" class="border border-line rounded-sm px-3 py-2 text-[14px] text-ink">
                    @foreach ([5, 4, 3, 2, 1] as $value)
                        <option value="{{ $value }}">{{ $value }} / 5</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-[14px] font-medium text-ink mb-1">Comment (optional)</label>
                <textarea wire:model="comment" rows="3"
                          class="w-full border border-line rounded-sm px-3 py-2 text-[14px] text-ink focus:outline-none focus:ring-2 focus:ring-green-600"></textarea>
                @error('comment') <p class="mt-1 text-[13px] text-amber-700">{{ $message }}</p> @enderror
            </div>
            <button type="submit" wire:loading.attr="disabled" wire:target="submit"
                    class="bg-green-600 hover:bg-green-700 text-white rounded-sm px-4 py-2 text-[14px] font-medium transition disabled:opacity-60">
                Submit review
            </button>
        </form>
    @endif
</div>
