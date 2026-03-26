@props([
    'name',
    'id' => null,
    'label' => null,
    'value' => null,
    'minDate' => null,
    'helpText' => null,
])
@php($inputId = $id ?? $name)
<div class="space-y-1">
    @if($label)
        <label for="{{ $inputId }}" class="block text-sm font-semibold text-gray-700">{{ $label }}</label>
    @endif
    <input type="datetime-local"
           name="{{ $name }}"
           id="{{ $inputId }}"
           value="{{ old($name, $value) }}"
           @if($minDate) min="{{ $minDate }}" @endif
           class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm" />
    @if($helpText)
        <p class="text-xs text-gray-500">{{ $helpText }}</p>
    @endif
</div>

