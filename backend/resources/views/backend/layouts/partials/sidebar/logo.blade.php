<div class="flex items-center gap-2 py-3">
    <a href="{{ route('admin.dashboard') }}" class="flex flex-col leading-tight">
        <span class="text-sm font-semibold text-violet-700 dark:text-violet-300">
            {{ config('app.name', 'PGFE') }}
        </span>
        <span class="text-[11px] text-gray-400 dark:text-gray-500">
            {{ __('Administration') }}
        </span>
    </a>
</div>
