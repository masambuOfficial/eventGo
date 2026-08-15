<div class="max-w-2xl mx-auto">
    <h1 class="text-[24px] font-semibold text-ink mb-1">Plan your event</h1>
    <p class="text-[14px] text-slate mb-6">Step {{ $step }} of 3</p>

    <div class="flex items-center gap-2 mb-8">
        @foreach ([1 => 'Event basics', 2 => 'Tell us more', 3 => 'Requirements & budget'] as $n => $label)
            <div class="flex-1">
                <div class="h-1 rounded-full {{ $step >= $n ? 'bg-green-600' : 'bg-line' }}"></div>
                <p class="mt-1 text-[13px] {{ $step === $n ? 'text-ink font-medium' : 'text-slate' }}">{{ $label }}</p>
            </div>
        @endforeach
    </div>

    <div class="bg-white border border-line rounded-lg p-6">
        @if ($step === 1)
            <form wire:submit="saveBasics" class="space-y-4">
                <div>
                    <label class="block text-[14px] font-medium text-ink mb-1">Event name <span class="text-amber-700">*</span></label>
                    <input type="text" wire:model.blur="name" placeholder="e.g. Sarah & David's Wedding"
                           class="w-full border border-line rounded-sm px-3 py-2 text-[16px] text-ink focus:outline-none focus:ring-2 focus:ring-green-600">
                    @error('name') <p class="mt-1 text-[13px] text-amber-700">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-[14px] font-medium text-ink mb-1">Event type <span class="text-amber-700">*</span></label>
                    <select wire:model.blur="event_type_id"
                            class="w-full border border-line rounded-sm px-3 py-2 text-[16px] text-ink focus:outline-none focus:ring-2 focus:ring-green-600">
                        <option value="">Select a type</option>
                        @foreach ($eventTypes as $type)
                            <option value="{{ $type->id }}">{{ $type->name }}</option>
                        @endforeach
                    </select>
                    @error('event_type_id') <p class="mt-1 text-[13px] text-amber-700">{{ $message }}</p> @enderror
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-[14px] font-medium text-ink mb-1">Date <span class="text-amber-700">*</span></label>
                        <input type="date" wire:model.blur="starts_at"
                               class="w-full border border-line rounded-sm px-3 py-2 text-[16px] text-ink focus:outline-none focus:ring-2 focus:ring-green-600">
                        @error('starts_at') <p class="mt-1 text-[13px] text-amber-700">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-[14px] font-medium text-ink mb-1">Rough guest count</label>
                        <input type="number" min="1" wire:model.blur="guest_count_expected"
                               class="w-full border border-line rounded-sm px-3 py-2 text-[16px] text-ink focus:outline-none focus:ring-2 focus:ring-green-600">
                    </div>
                </div>

                <div>
                    <label class="block text-[14px] font-medium text-ink mb-1">District</label>
                    <select wire:model.blur="district_id"
                            class="w-full border border-line rounded-sm px-3 py-2 text-[16px] text-ink focus:outline-none focus:ring-2 focus:ring-green-600">
                        <option value="">Select a district</option>
                        @foreach ($districts as $district)
                            <option value="{{ $district->id }}">{{ $district->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-[14px] font-medium text-ink mb-1">Venue (if you know it)</label>
                    <input type="text" wire:model.blur="venue_name"
                           class="w-full border border-line rounded-sm px-3 py-2 text-[16px] text-ink focus:outline-none focus:ring-2 focus:ring-green-600">
                </div>

                <button type="submit" wire:loading.attr="disabled" wire:target="saveBasics"
                        class="w-full bg-green-600 hover:bg-green-700 text-white rounded-sm px-4 py-2 text-[14px] font-medium transition disabled:opacity-60">
                    <span wire:loading.remove wire:target="saveBasics">Continue</span>
                    <span wire:loading wire:target="saveBasics">Saving…</span>
                </button>
            </form>
        @elseif ($step === 2)
            <form wire:submit="saveAnswers" class="space-y-4">
                @forelse ($questions as $question)
                    <div>
                        <label class="block text-[14px] font-medium text-ink mb-1">
                            {{ $question->label }}
                            @if ($question->is_required) <span class="text-amber-700">*</span> @endif
                        </label>
                        @if ($question->help_text)
                            <p class="text-[13px] text-slate mb-1">{{ $question->help_text }}</p>
                        @endif

                        @if ($question->input_type === 'number')
                            <input type="number" wire:model.blur="answers.{{ $question->id }}"
                                   class="w-full border border-line rounded-sm px-3 py-2 text-[16px] text-ink focus:outline-none focus:ring-2 focus:ring-green-600">
                        @elseif ($question->input_type === 'bool')
                            <select wire:model.blur="answers.{{ $question->id }}"
                                    class="w-full border border-line rounded-sm px-3 py-2 text-[16px] text-ink focus:outline-none focus:ring-2 focus:ring-green-600">
                                <option value="">Select…</option>
                                <option value="1">Yes</option>
                                <option value="0">No</option>
                            </select>
                        @elseif ($question->input_type === 'select')
                            <select wire:model.blur="answers.{{ $question->id }}"
                                    class="w-full border border-line rounded-sm px-3 py-2 text-[16px] text-ink focus:outline-none focus:ring-2 focus:ring-green-600">
                                <option value="">Select…</option>
                                @foreach ($question->options ?? [] as $option)
                                    <option value="{{ $option }}">{{ $option }}</option>
                                @endforeach
                            </select>
                        @elseif ($question->input_type === 'multiselect')
                            <div class="space-y-1">
                                @foreach ($question->options ?? [] as $option)
                                    <label wire:key="q{{ $question->id }}-opt-{{ $loop->index }}" class="flex items-center gap-2 text-[14px] text-ink">
                                        <input type="checkbox" wire:model="answers.{{ $question->id }}" value="{{ $option }}"
                                               class="rounded-sm border-line">
                                        {{ $option }}
                                    </label>
                                @endforeach
                            </div>
                        @elseif ($question->input_type === 'date')
                            <input type="date" wire:model.blur="answers.{{ $question->id }}"
                                   class="w-full border border-line rounded-sm px-3 py-2 text-[16px] text-ink focus:outline-none focus:ring-2 focus:ring-green-600">
                        @else
                            <input type="text" wire:model.blur="answers.{{ $question->id }}"
                                   class="w-full border border-line rounded-sm px-3 py-2 text-[16px] text-ink focus:outline-none focus:ring-2 focus:ring-green-600">
                        @endif

                        @error('answers.' . $question->id) <p class="mt-1 text-[13px] text-amber-700">{{ $message }}</p> @enderror
                    </div>
                @empty
                    <p class="text-[14px] text-slate">No extra questions for this event type yet — continue to see suggested requirements.</p>
                @endforelse

                <div class="flex items-center gap-3 pt-2">
                    <button type="button" wire:click="backTo(1)" class="text-[14px] text-slate hover:text-ink">Back</button>
                    <button type="submit" wire:loading.attr="disabled" wire:target="saveAnswers"
                            class="flex-1 bg-green-600 hover:bg-green-700 text-white rounded-sm px-4 py-2 text-[14px] font-medium transition disabled:opacity-60">
                        <span wire:loading.remove wire:target="saveAnswers">See my requirements</span>
                        <span wire:loading wire:target="saveAnswers">Working it out…</span>
                    </button>
                </div>
            </form>
        @elseif ($step === 3)
            <div class="space-y-4">
                <p class="text-[14px] text-slate">
                    A starting point based on your answers — remove anything you don't need, adjust quantities, or add your own.
                </p>

                <div class="space-y-3">
                    @foreach ($lines as $index => $line)
                        <div wire:key="line-{{ $index }}" class="border border-line rounded-sm p-4 {{ ($line['include'] ?? true) ? '' : 'opacity-50' }}">
                            <div class="flex items-start gap-3">
                                <input type="checkbox" wire:model="lines.{{ $index }}.include" class="mt-1.5 rounded-sm border-line">

                                <div class="flex-1 space-y-2">
                                    @if (($line['source'] ?? null) === 'manual')
                                        <select wire:model="lines.{{ $index }}.service_category_id"
                                                class="w-full border border-line rounded-sm px-3 py-2 text-[14px] text-ink focus:outline-none focus:ring-2 focus:ring-green-600">
                                            <option value="">Select a category</option>
                                            @foreach ($categories as $category)
                                                <option value="{{ $category->id }}">{{ $category->name }}</option>
                                            @endforeach
                                        </select>
                                    @endif

                                    <input type="text" wire:model="lines.{{ $index }}.title" placeholder="Title"
                                           class="w-full border border-line rounded-sm px-3 py-2 text-[14px] font-medium text-ink focus:outline-none focus:ring-2 focus:ring-green-600">

                                    <div class="grid grid-cols-3 gap-2">
                                        <div>
                                            <label class="block text-[13px] text-slate mb-1">Quantity</label>
                                            <input type="number" step="0.01" wire:model="lines.{{ $index }}.quantity"
                                                   class="w-full border border-line rounded-sm px-2 py-1.5 text-[14px] text-ink focus:outline-none focus:ring-2 focus:ring-green-600">
                                        </div>
                                        <div>
                                            <label class="block text-[13px] text-slate mb-1">Budget (UGX)</label>
                                            <input type="number" wire:model="lines.{{ $index }}.budget_estimate_ugx"
                                                   class="w-full border border-line rounded-sm px-2 py-1.5 text-[14px] text-ink focus:outline-none focus:ring-2 focus:ring-green-600">
                                        </div>
                                        <div>
                                            <label class="block text-[13px] text-slate mb-1">Priority</label>
                                            <select wire:model="lines.{{ $index }}.priority"
                                                    class="w-full border border-line rounded-sm px-2 py-1.5 text-[14px] text-ink focus:outline-none focus:ring-2 focus:ring-green-600">
                                                <option value="essential">Essential</option>
                                                <option value="important">Important</option>
                                                <option value="optional">Optional</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <button type="button" wire:click="removeLine({{ $index }})" class="text-[13px] text-slate hover:text-amber-700">
                                    Remove
                                </button>
                            </div>
                        </div>
                    @endforeach
                </div>

                <button type="button" wire:click="addCustomLine" class="text-[13px] text-green-600 hover:underline">
                    + Add something not listed
                </button>

                <div class="flex items-center justify-between border-t border-line pt-4">
                    <span class="text-[16px] font-semibold text-ink">Estimated total</span>
                    <span class="text-[18px] font-semibold text-ink">UGX {{ number_format($this->budgetTotal()) }}</span>
                </div>
                <p class="text-[13px] text-slate -mt-2">
                    A starting estimate, not a quote — real prices come from providers once you source them.
                </p>

                <div class="flex items-center gap-3">
                    <button type="button" wire:click="backTo(2)" class="text-[14px] text-slate hover:text-ink">Back</button>
                    <button type="button" wire:click="saveRequirements" wire:loading.attr="disabled" wire:target="saveRequirements"
                            class="flex-1 bg-green-600 hover:bg-green-700 text-white rounded-sm px-4 py-2 text-[14px] font-medium transition disabled:opacity-60">
                        <span wire:loading.remove wire:target="saveRequirements">Save my requirements</span>
                        <span wire:loading wire:target="saveRequirements">Saving…</span>
                    </button>
                </div>
            </div>
        @endif
    </div>
</div>
