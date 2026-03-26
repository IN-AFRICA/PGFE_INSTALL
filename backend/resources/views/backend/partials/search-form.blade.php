<form class="w-full" method="GET">
    <label class="sr-only" for="search">{{ __('Search') }}</label>
    <div class="relative">
        <input id="search" name="search" type="search" value="{{ request('search') }}" placeholder="{{ $searchbarPlaceholder ?? __('Search...') }}" class="block w-full rounded-lg border border-gray-300 bg-white py-2.5 pl-3 pr-10 text-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500" />
        <span class="absolute inset-y-0 right-3 flex items-center text-gray-400">
            <iconify-icon icon="lucide:search"></iconify-icon>
        </span>
    </div>
</form>
