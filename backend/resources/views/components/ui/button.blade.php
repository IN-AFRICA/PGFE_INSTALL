@props([
    'variant' => 'default', // default, outline, ghost, destructive, secondary
    'size' => 'default', // default, sm, lg, icon
    'as' => 'button', // button | a
    'href' => null,
    'type' => 'button',
    'disabled' => false,
])
@php
    $base = 'inline-flex items-center justify-center whitespace-nowrap rounded-md text-sm font-medium transition-colors focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring disabled:pointer-events-none disabled:opacity-50';
    $variants = [
        'default' => 'bg-primary text-primary-foreground shadow hover:bg-primary/90',
        'secondary' => 'bg-secondary text-secondary-foreground shadow-sm hover:bg-secondary/80',
        'outline' => 'border border-input bg-background shadow-sm hover:bg-accent hover:text-accent-foreground',
        'ghost' => 'hover:bg-accent hover:text-accent-foreground',
        'destructive' => 'bg-destructive text-destructive-foreground shadow-sm hover:bg-destructive/90',
    ];
    $sizes = [
        'default' => 'h-9 px-4 py-2',
        'sm' => 'h-8 rounded-md px-3 text-xs',
        'lg' => 'h-10 rounded-md px-8',
        'icon' => 'h-9 w-9',
    ];
    $classes = $base.' '.($variants[$variant] ?? $variants['default']).' '.($sizes[$size] ?? $sizes['default']);
@endphp
@if($as === 'a')
    <a {{ $attributes->class($classes) }} href="{{ $href }}">{{ $slot }}</a>
@else
    <button {{ $attributes->class($classes) }} type="{{ $type }}" @disabled($disabled)>{{ $slot }}</button>
@endif

