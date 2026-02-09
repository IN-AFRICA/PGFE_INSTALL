@props(['muted'=>false,'small'=>false,'align'=>'left'])
@php
    $alignClass = match($align){
        'center' => 'text-center',
        'right' => 'text-right',
        default => 'text-left'
    };
    $classes = 'p-3 align-middle';
    if($muted) $classes .= ' text-xs text-muted-foreground';
    elseif($small) $classes .= ' text-sm';
@endphp
<td {{ $attributes->class($classes.' '.$alignClass) }}>
    {{ $slot }}
</td>
@php
    $alignClass = match($align){
        'center' => 'text-center',
        'right' => 'text-right',
        default => 'text-left'
    };
@endphp
<th {{ $attributes->class("h-10 px-3 align-middle font-medium text-muted-foreground $alignClass") }}>
    {{ $slot }}
</th>

