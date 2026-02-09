@php
    // Extraction du premier mot du name comme affichage principal
    $rawName = trim($user->name ?? ($user->email ?? 'Utilisateur'));
    $firstToken = $rawName !== '' ? preg_split('/\s+/', $rawName)[0] : 'Utilisateur';
    $initials = strtoupper(mb_substr($firstToken, 0, 2));
@endphp
<x-tooltip title="{{ auth()->user()->canBeModified($user) ? __('Edit User') : __('No permission to edit') }}" position="top">
    <a
        data-tooltip-target="tooltip-user-{{ $user->id }}"
        href="{{ auth()->user()->canBeModified($user) ? route('admin.users.edit', $user->id) : '#' }}"
        class="flex items-center"
    >
        <div class="w-10 h-10 rounded-full mr-3 bg-violet-600 text-white flex items-center justify-center text-xs font-semibold">
            {{ $initials }}
        </div>
        <div class="flex flex-col gap-2 flex-1 min-w-0">
            <span>{{ $firstToken }}</span>
            <span class="text-xs text-gray-500 dark:text-gray-300">{{ $user->username }}</span>
        </div>
    </a>

    @if (auth()->user()->canBeModified($user))
    <div
        id="tooltip-user-{{ $user->id }}"
        href="{{ route('admin.users.edit', $user->id) }}"
        class="absolute z-10 invisible inline-block px-3 py-2 text-sm font-medium text-white transition-opacity duration-300 bg-gray-900 rounded-md shadow-xs opacity-0 tooltip dark:bg-gray-700"
    >
        {{ __("Edit User") }}
        <div class="tooltip-arrow" data-popper-arrow></div>
    </div>
    @endif
</x-tooltip>
