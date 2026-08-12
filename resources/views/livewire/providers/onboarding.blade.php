<div class="max-w-2xl mx-auto">
    <h1 class="text-[24px] font-semibold text-ink mb-1">Set up your provider profile</h1>
    <p class="text-[14px] text-slate mb-6">Step {{ $step }} of 3</p>

    <div class="flex items-center gap-2 mb-8">
        @foreach ([1 => 'Business info', 2 => 'Services & pricing', 3 => 'Areas served'] as $n => $label)
            <div class="flex-1">
                <div class="h-1 rounded-full {{ $step >= $n ? 'bg-green-600' : 'bg-line' }}"></div>
                <p class="mt-1 text-[13px] {{ $step === $n ? 'text-ink font-medium' : 'text-slate' }}">{{ $label }}</p>
            </div>
        @endforeach
    </div>

    <div class="bg-white border border-line rounded-lg p-6">
        @if ($step === 1)
            <form wire:submit="saveInfo" class="space-y-4">
                <div>
                    <label class="block text-[14px] font-medium text-ink mb-1">Business name <span class="text-amber-700">*</span></label>
                    <input type="text" wire:model.blur="business_name"
                           class="w-full border border-line rounded-sm px-3 py-2 text-[16px] text-ink focus:outline-none focus:ring-2 focus:ring-green-600">
                    @error('business_name') <p class="mt-1 text-[13px] text-amber-700">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-[14px] font-medium text-ink mb-1">About your business</label>
                    <textarea wire:model.blur="about" rows="4"
                              class="w-full border border-line rounded-sm px-3 py-2 text-[16px] text-ink focus:outline-none focus:ring-2 focus:ring-green-600"></textarea>
                    @error('about') <p class="mt-1 text-[13px] text-amber-700">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-[14px] font-medium text-ink mb-1">Phone number</label>
                    <input type="text" wire:model.blur="primary_phone_e164" placeholder="+2567XXXXXXXX"
                           class="w-full border border-line rounded-sm px-3 py-2 text-[16px] text-ink focus:outline-none focus:ring-2 focus:ring-green-600">
                    @error('primary_phone_e164') <p class="mt-1 text-[13px] text-amber-700">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-[14px] font-medium text-ink mb-1">Base district <span class="text-amber-700">*</span></label>
                    <select wire:model.blur="base_district_id"
                            class="w-full border border-line rounded-sm px-3 py-2 text-[16px] text-ink focus:outline-none focus:ring-2 focus:ring-green-600">
                        <option value="">Select a district</option>
                        @foreach ($districts as $district)
                            <option value="{{ $district->id }}">{{ $district->name }}</option>
                        @endforeach
                    </select>
                    @error('base_district_id') <p class="mt-1 text-[13px] text-amber-700">{{ $message }}</p> @enderror
                </div>

                <button type="submit" wire:loading.attr="disabled" wire:target="saveInfo"
                        class="w-full bg-green-600 hover:bg-green-700 text-white rounded-sm px-4 py-2 text-[14px] font-medium transition disabled:opacity-60">
                    <span wire:loading.remove wire:target="saveInfo">Continue</span>
                    <span wire:loading wire:target="saveInfo">Saving…</span>
                </button>
            </form>
        @elseif ($step === 2)
            <form wire:submit="saveServices" class="space-y-6">
                @foreach ($services as $index => $row)
                    <div wire:key="service-{{ $index }}" class="border border-line rounded-sm p-4 space-y-3">
                        <div class="flex items-start justify-between gap-3">
                            <div class="flex-1">
                                <label class="block text-[14px] font-medium text-ink mb-1">Service category <span class="text-amber-700">*</span></label>
                                <select wire:model.blur="services.{{ $index }}.service_category_id"
                                        class="w-full border border-line rounded-sm px-3 py-2 text-[16px] text-ink focus:outline-none focus:ring-2 focus:ring-green-600">
                                    <option value="">Select a category</option>
                                    @foreach ($categories as $category)
                                        <option value="{{ $category->id }}">{{ $category->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            @if (count($services) > 1)
                                <button type="button" wire:click="removeServiceRow({{ $index }})"
                                        class="mt-6 text-[13px] text-slate hover:text-amber-700">Remove</button>
                            @endif
                        </div>

                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-[14px] font-medium text-ink mb-1">Price from (UGX)</label>
                                <input type="number" wire:model.blur="services.{{ $index }}.price_min_ugx"
                                       class="w-full border border-line rounded-sm px-3 py-2 text-[16px] text-ink focus:outline-none focus:ring-2 focus:ring-green-600">
                            </div>
                            <div>
                                <label class="block text-[14px] font-medium text-ink mb-1">Price to (UGX)</label>
                                <input type="number" wire:model.blur="services.{{ $index }}.price_max_ugx"
                                       class="w-full border border-line rounded-sm px-3 py-2 text-[16px] text-ink focus:outline-none focus:ring-2 focus:ring-green-600">
                            </div>
                        </div>

                        <div>
                            <label class="block text-[14px] font-medium text-ink mb-1">Price unit</label>
                            <input type="text" wire:model.blur="services.{{ $index }}.price_unit" placeholder="per event, per plate…"
                                   class="w-full border border-line rounded-sm px-3 py-2 text-[16px] text-ink focus:outline-none focus:ring-2 focus:ring-green-600">
                        </div>
                    </div>
                @endforeach

                <button type="button" wire:click="addServiceRow" class="text-[13px] text-green-600 hover:underline">
                    + Add another service
                </button>

                <div class="flex items-center gap-3">
                    <button type="button" wire:click="backTo(1)" class="text-[14px] text-slate hover:text-ink">Back</button>
                    <button type="submit" wire:loading.attr="disabled" wire:target="saveServices"
                            class="flex-1 bg-green-600 hover:bg-green-700 text-white rounded-sm px-4 py-2 text-[14px] font-medium transition disabled:opacity-60">
                        <span wire:loading.remove wire:target="saveServices">Continue</span>
                        <span wire:loading wire:target="saveServices">Saving…</span>
                    </button>
                </div>
            </form>
        @elseif ($step === 3)
            <form wire:submit="saveAreas" class="space-y-4">
                <p class="text-[14px] text-ink mb-2">Which districts do you serve?</p>

                <input type="text" wire:model.live.debounce.500ms="districtSearch" placeholder="Search districts…"
                       class="w-full border border-line rounded-sm px-3 py-2 text-[16px] text-ink mb-2 focus:outline-none focus:ring-2 focus:ring-green-600">

                <div class="grid grid-cols-2 gap-2 max-h-80 overflow-y-auto border border-line rounded-sm p-3">
                    @forelse ($areaDistricts as $district)
                        <label class="flex items-center gap-2 text-[14px] text-ink">
                            <input type="checkbox" wire:model="selectedDistricts" value="{{ $district->id }}"
                                   class="rounded-sm border-line">
                            {{ $district->name }}
                        </label>
                    @empty
                        <p class="text-[13px] text-slate col-span-2">No districts match "{{ $districtSearch }}".</p>
                    @endforelse
                </div>

                <div class="flex items-center gap-3">
                    <button type="button" wire:click="backTo(2)" class="text-[14px] text-slate hover:text-ink">Back</button>
                    <button type="submit" wire:loading.attr="disabled" wire:target="saveAreas"
                            class="flex-1 bg-green-600 hover:bg-green-700 text-white rounded-sm px-4 py-2 text-[14px] font-medium transition disabled:opacity-60">
                        <span wire:loading.remove wire:target="saveAreas">Finish</span>
                        <span wire:loading wire:target="saveAreas">Saving…</span>
                    </button>
                </div>
            </form>
        @endif
    </div>
</div>
