{!! Hook::applyFilters(SettingFilterHook::SETTINGS_GENERAL_TAB_BEFORE_SECTION_START, '') !!}
<div class="rounded-md border border-gray-200 dark:border-gray-800 dark:bg-white/[0.03]">
    <div class="px-5 py-4 sm:px-6 sm:py-5">
        <h3 class="text-base font-medium text-gray-700 dark:text-white/90">
            {{ __('General Settings') }}
        </h3>
    </div>
    <div class="space-y-6 border-t border-gray-100 p-5 sm:p-6 dark:border-gray-800">
        <div class="flex">
            <div class="md:basis-1/2 relative">
                <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">
                    {{ __('Site Name') }}
                </label>
                <input type="text" name="app_name" placeholder="{{ __('Enter site name') }}"
                    value="{{ config('settings.app_name') ?? '' }}" @if (config('app.demo_mode', false)) disabled @endif
                    class="form-control" data-tooltip-target="tooltip-app-name">
                <div id="tooltip-app-name" role="tooltip"
                    class="absolute z-10 invisible inline-block px-3 py-2 text-sm font-medium text-white transition-opacity duration-300 bg-gray-900 rounded-md shadow-xs opacity-0 tooltip dark:bg-gray-700">
                    {{ __('Editing site name is disabled in demo mode.') }}
                    <div class="tooltip-arrow" data-popper-arrow></div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
            <!-- File input components removed: missing x-inputs.file-input -->
        </div>
    </div>
    {!! Hook::applyFilters(SettingFilterHook::SETTINGS_GENERAL_TAB_BEFORE_SECTION_END, '') !!}
</div>

