@props([
    'cancelUrl' => null,
    'submitLabel' => 'Save',
    'cancelLabel' => 'Cancel',
])

<div class="flex items-center gap-3">
    <button type="submit" class="inline-flex items-center gap-2 rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-700">
        <iconify-icon icon="lucide:save"></iconify-icon>
        <span>{{ __($submitLabel) }}</span>
    </button>
    @if($cancelUrl)
        <a href="{{ $cancelUrl }}" class="inline-flex items-center gap-2 rounded-md border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">
            <iconify-icon icon="lucide:x"></iconify-icon>
            <span>{{ __($cancelLabel) }}</span>
        </a>
    @endif
</div>

