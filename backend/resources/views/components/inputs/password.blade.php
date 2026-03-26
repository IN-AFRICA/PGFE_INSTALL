@props([
    'name' => 'password',
    'label' => null,
    'placeholder' => null,
    'required' => false,
])

<div class="space-y-2" x-data="{ show: false }">
    @if($label)
        <label for="{{ $name }}" class="block text-sm font-semibold text-gray-700">{{ $label }} @if($required)<span class="text-red-500">*</span>@endif</label>
    @endif
    <div class="relative">
        <input
            id="{{ $name }}"
            name="{{ $name }}"
            :type="show ? 'text' : 'password'"
            @class([
                'w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500',
            ])
            placeholder="{{ $placeholder }}"
            {{ $required ? 'required' : '' }}
            {{ $attributes->except(['class']) }}
        />
        <button type="button" @click="show = !show" class="absolute inset-y-0 right-3 flex items-center text-gray-500 hover:text-gray-700">
            <iconify-icon :icon="show ? 'lucide:eye-off' : 'lucide:eye'" class="text-lg"></iconify-icon>
        </button>
    </div>
</div>

