{!! Hook::applyFilters(AdminFilterHook::HEADER_RIGHT_MENU_BEFORE, '') !!}

@php
    $authUser = auth()->user();
    $rawNameHeader = trim($authUser->name ?? ($authUser->email ?? 'Utilisateur'));
    $firstTokenHeader = $rawNameHeader !== '' ? preg_split('/\s+/', $rawNameHeader)[0] : 'Utilisateur';
    $initialsHeader = strtoupper(mb_substr($firstTokenHeader, 0, 2));
@endphp
<div class="flex items-center gap-1">
    <div class="hidden md:block">
        @include('backend.layouts.partials.demo-mode-notice')
    </div>
    {!! Hook::applyFilters(AdminFilterHook::DARK_MODE_TOGGLER_BEFORE_BUTTON, '') !!}
    <x-tooltip title="{{ __('Basculer le thème') }}" position="bottom">
        <button id="darkModeToggle" class="hover:text-dark-900 relative flex items-center justify-center rounded-full text-gray-700 transition-colors hover:bg-gray-100 hover:text-gray-800 dark:text-gray-300 dark:hover:bg-gray-700 dark:hover:text-white p-2 dark-mode-toggle" @click.prevent="darkMode = !darkMode" @click="menuToggle = true">
            <iconify-icon icon="lucide:moon" width="24" height="24" class="hidden dark:block"></iconify-icon>
            <iconify-icon icon="lucide:sun" width="24" height="24" class="dark:hidden"></iconify-icon>
        </button>
    </x-tooltip>
    {!! Hook::applyFilters(AdminFilterHook::DARK_MODE_TOGGLER_AFTER_BUTTON, '') !!}
    @if (config('app.show_demo_component_preview', false))
        <x-tooltip title="{{ __('Prévisualiser composants démo') }}" position="bottom">
            <a href="{{ route('demo.preview') }}" class="hover:text-dark-900 relative flex p-2 items-center justify-center rounded-full text-gray-700 transition-colors hover:bg-gray-100 hover:text-gray-800 dark:text-gray-300 dark:hover:bg-gray-700 dark:hover:text-white">
                <iconify-icon icon="lucide:view" width="22" height="22"></iconify-icon>
            </a>
        </x-tooltip>
    @endif
    @if (env('GITHUB_LINK'))
        <x-tooltip title="Github" position="bottom">
            <a href="{{ env('GITHUB_LINK') }}" target="_blank" class="hover:text-dark-900 relative flex p-2 items-center justify-center rounded-full text-gray-700 transition-colors hover:bg-gray-100 hover:text-gray-800 dark:text-gray-300 dark:hover:bg-gray-700 dark:hover:text-white">
                <iconify-icon icon="lucide:github" width="22" height="22"></iconify-icon>
            </a>
        </x-tooltip>
    @endif
    {!! Hook::applyFilters(AdminFilterHook::HEADER_AFTER_ACTIONS, '') !!}
</div>
{!! Hook::applyFilters(AdminFilterHook::USER_DROPDOWN_BEFORE, '') !!}
<div class="relative" x-data="{ dropdownOpen: false }" @click.outside="dropdownOpen = false">
    <a class="flex items-center text-gray-700 dark:text-gray-300" href="#" @click.prevent="dropdownOpen = ! dropdownOpen">
        <div class="mr-3 h-8 w-8 overflow-hidden rounded-full bg-violet-600 text-white flex items-center justify-center text-xs font-semibold">
            {{ $initialsHeader }}
        </div>
    </a>
    <div x-show="dropdownOpen" class="absolute right-0 mt-[17px] flex w-[220px] flex-col rounded-md border bg-white dark:bg-gray-700 border-gray-200  p-3 shadow-theme-lg dark:border-gray-800 z-100" style="display: none">
        <div class="border-b border-gray-200 pb-2 dark:border-gray-800 mb-2">
            <span class="block font-medium text-gray-700 dark:text-gray-300">{{ $firstTokenHeader }}</span>
            <span class="mt-0.5 block text-theme-sm text-gray-700 dark:text-gray-300">{{ $authUser->email }}</span>
        </div>
        {!! Hook::applyFilters(AdminFilterHook::USER_DROPDOWN_AFTER_USER_INFO, '') !!}
        <ul class="flex flex-col gap-1 border-b border-gray-200 pb-2 dark:border-gray-800">
            <li>
                <a href="{{ route('profile.edit') }}" class="group flex items-center gap-3 rounded-md px-3 py-2 text-theme-sm font-medium text-gray-700 hover:bg-gray-100 hover:text-gray-800 dark:text-gray-300 dark:hover:bg-white/5 dark:hover:text-gray-300">
                    <iconify-icon icon="lucide:user" width="20" height="20" class="fill-gray-500 group-hover:fill-gray-700 dark:fill-gray-400 dark:group-hover:fill-gray-300"></iconify-icon>
                    {{ __('Modifier le profil') }}
                </a>
            </li>
        </ul>
        {!! Hook::applyFilters(AdminFilterHook::USER_DROPDOWN_AFTER_PROFILE_LINKS, '') !!}
        <form method="POST" action="{{ route('web.auth.logout') }}" class="inline">
            @csrf
            <button type="submit" class="group flex items-center gap-3 rounded-md px-3 py-2 text-theme-sm font-medium text-gray-700 hover:bg-gray-100 hover:text-gray-800 dark:text-gray-300 dark:hover:bg-white/5 dark:hover:text-gray-300 mt-2 w-full">
                <iconify-icon icon="lucide:log-out" width="20" height="20" class="fill-gray-500 group-hover:fill-gray-700 dark:group-hover:fill-gray-300"></iconify-icon>
                {{ __('Déconnexion') }}
            </button>
        </form>
        {!! Hook::applyFilters(AdminFilterHook::USER_DROPDOWN_AFTER_LOGOUT, '') !!}
    </div>
</div>
{!! Hook::applyFilters(AdminFilterHook::HEADER_RIGHT_MENU_AFTER, '') !!}
