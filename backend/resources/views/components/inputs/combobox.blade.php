@props([
    'name',
    'label' => null,
    'options' => [],
    'selected' => null,
    'multiple' => false,
    'searchable' => false,
    'placeholder' => null,
])

<div class="space-y-1">
    @if($label)
        <label for="{{ $name }}" class="block text-sm font-semibold text-gray-700">{{ $label }}</label>
    @endif
    <select name="{{ $multiple ? $name . '[]' : $name }}" id="{{ $name }}" @if($multiple) multiple @endif class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
        @if($placeholder)
            <option value="">{{ $placeholder }}</option>
        @endif
        @foreach($options as $opt)
            @php
                $value = $opt['value'] ?? $opt['id'] ?? '';
                $label = $opt['label'] ?? $opt['name'] ?? $value;
                $isSelected = $multiple
                    ? (is_array(old($name, $selected ?? [])) && in_array($value, old($name, $selected ?? [])))
                    : (old($name, $selected) == $value);
            @endphp
            <option value="{{ $value }}" @if($isSelected) selected @endif>{{ $label }}</option>
        @endforeach
    </select>
</div>

