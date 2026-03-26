@props([
    'name',
    'label' => null,
    'multiple' => false,
    'allowedTypes' => null,
    'existingMedia' => null,
    'existingAltText' => null,
    'removeCheckboxName' => null,
    'removeCheckboxLabel' => null,
    'showPreview' => false,
])

<div class="space-y-2">
    @if($label)
        <label class="block text-sm font-semibold text-gray-700">{{ $label }}</label>
    @endif
    @if($existingMedia && $showPreview)
        <div class="rounded border border-gray-200 p-2 bg-gray-50">
            <img src="{{ $existingMedia }}" alt="{{ $existingAltText }}" class="max-h-32 object-contain mx-auto">
        </div>
    @endif
    <input type="file" name="{{ $multiple ? $name . '[]' : $name }}" @if($multiple) multiple @endif @if($allowedTypes === 'images') accept="image/*" @endif class="block w-full text-sm text-gray-700" />
    @if($removeCheckboxName)
        <label class="inline-flex items-center gap-2 text-sm text-gray-600">
            <input type="checkbox" name="{{ $removeCheckboxName }}" value="1" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
            {{ $removeCheckboxLabel ?? __('Remove') }}
        </label>
    @endif
</div>

