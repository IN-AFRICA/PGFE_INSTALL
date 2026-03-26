@props([
    'user' => null,
    'roles' => [],
    'timezones' => [],
    'locales' => [],
    'userMeta' => [],
    'mode' => 'create', // 'create', 'edit', 'profile'
    'showUsername' => true,
    'showRoles' => true,
    'showDisplayName' => true,
    'showAdditional' => false,
    'firstNameLabel' => null,
    'lastNameLabel' => null,
    'emailLabel' => null,
    'usernameLabel' => null,
    'passwordLabel' => null,
    'confirmPasswordLabel' => null,
    'rolesLabel' => null,
    'avatarLabel' => null,
    'cancelUrl' => route('admin.users.index'),
    'showImage' => true
])

@php
    $isCreate = $mode === 'create';
    $isEdit = $mode === 'edit';
    $isProfile = $mode === 'profile';

    // Default labels
    $firstNameLabel = $firstNameLabel ?? __('First Name');
    $lastNameLabel = $lastNameLabel ?? __('Last Name');
    $emailLabel = $emailLabel ?? ($isProfile ? __('Email') : __('User Email'));
    $usernameLabel = $usernameLabel ?? __('Username');
    $passwordLabel = $passwordLabel ?? ($isCreate ? __('Password') : __('Password (Optional)'));
    $confirmPasswordLabel = $confirmPasswordLabel ?? ($isCreate ? __('Confirm Password') : __('Confirm Password (Optional)'));
    $rolesLabel = $rolesLabel ?? __('Assign Roles');
    $avatarLabel = $avatarLabel ?? __('Avatar');

    // Get avatar URL for display.
    $avatarUrl = null;
    if ($user?->avatar_id && $user->avatar) {
        $avatarUrl = $user->avatar_url ?? $user->avatar->getUrl();
    }

    $emptyText = isset($user) && $user?->full_name
        ? strtoupper(mb_substr($user->full_name, 0, 2))
        : __('No Profile Selected');
@endphp

<div class="flex flex-col md:flex-row gap-8 md:gap-12 items-start" x-data="{ avatarSelected: false }" @avatar-selected.window="avatarSelected = $event.detail">
    {{-- Profile Picture Section --}}
    @if($showImage)
    <div class="w-full md:w-1/5 flex-shrink-0 flex flex-col items-center gap-4">
        <div class="w-full flex flex-col items-center">
            <div class="mt-2 w-full">
                <!-- Media selector removed -->
            </div>
            {!! Hook::applyFilters(UserFilterHook::USER_FORM_AFTER_AVATAR, '') !!}
            @if($user)
                <p class="mt-2 text-sm text-gray-500 dark:text-gray-400 text-center">
                    {{ __('Account created on') }} {{ $user->created_at->format('M d, Y') }}
                </p>
            @endif
        </div>

        {{-- Social Links Section - Only show in edit and profile modes --}}
        {{-- Composant x-users.social-links supprimé car fichier manquant --}}
    </div>
    @endif

    {{-- Form Fields Section --}}
    <div class="w-full {{ $showImage ? 'md:w-4/5' : 'md:w-full' }}">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            {{-- Personal Information --}}
            <div class="col-span-2">
                <h3 class="text-lg font-medium text-gray-900 dark:text-white pb-1 border-b border-gray-200 dark:border-gray-700">
                    {{ __('Personal Information') }}
                </h3>
            </div>
            <div>
                <label for="first_name" class="form-label">{{ $firstNameLabel }}</label>
                <input type="text" name="first_name" id="first_name" required
                    value="{{ old('first_name', $user?->first_name) }}"
                    placeholder="{{ __('Enter First Name') }}"
                    class="form-control"
                    autofocus
                />
            </div>
            {!! Hook::applyFilters(UserFilterHook::USER_FORM_AFTER_FIRST_NAME, '', $user) !!}
            <div>
                <label for="last_name" class="form-label">{{ $lastNameLabel }}</label>
                <input type="text" name="last_name" id="last_name"
                    value="{{ old('last_name', $user?->last_name) }}"
                    placeholder="{{ __('Enter Last Name') }}"
                    class="form-control"
                />
            </div>
            {!! Hook::applyFilters(UserFilterHook::USER_FORM_AFTER_LAST_NAME, '', $user) !!}
            @if($showUsername)
                <div>
                    <label for="username" class="form-label">{{ $usernameLabel }}</label>
                    <input type="text" name="username" id="username" required
                        value="{{ old('username', $user?->username) }}"
                        placeholder="{{ __('Enter Username') }}"
                        class="form-control"
                        autocomplete="username"
                    >

                </div>
                {!! Hook::applyFilters(UserFilterHook::USER_FORM_AFTER_USERNAME, '', $user) !!}
            @endif
            <div>
                <label for="email" class="form-label">{{ $emailLabel }}</label>
                <input type="email" name="email" id="email" required
                    value="{{ old('email', $user?->email) }}"
                    placeholder="{{ __('Enter Email') }}"
                    class="form-control">
            </div>
            {!! Hook::applyFilters(UserFilterHook::USER_FORM_AFTER_EMAIL, '', $user) !!}

            {{-- Password Fields --}}
            <div>
                <!-- Password input removed -->
            </div>
            {!! Hook::applyFilters(UserFilterHook::USER_FORM_AFTER_PASSWORD, '', $user) !!}
            <div>
                <!-- Password confirmation input removed -->
            </div>
            {!! Hook::applyFilters(UserFilterHook::USER_FORM_AFTER_CONFIRM_PASSWORD, '', $user) !!}

            {{-- Roles & Display Name --}}

            @if($showRoles || $showDisplayName)
                <div class="col-span-2">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white pb-1 border-b border-gray-200 dark:border-gray-700">
                        {{ __('Permissions & Display') }}
                    </h3>
                </div>
                @if($showRoles)
                <div>
                    <!-- Roles combobox removed -->
                </div>
                {!! Hook::applyFilters(UserFilterHook::USER_FORM_AFTER_ROLES, '', $user) !!}
                @endif

                @if($showDisplayName)
                <div>
                    <label for="display_name" class="form-label">{{ __('Display Name') }}</label>
                    <input type="text" name="display_name" id="display_name"
                        value="{{ old('display_name', $userMeta['display_name'] ?? '') }}"
                        placeholder="{{ __('Enter Display Name') }}"
                        class="form-control">
                </div>
                {!! Hook::applyFilters(UserFilterHook::USER_FORM_AFTER_DISPLAY_NAME, '', $user) !!}
                @endif
            @endif

            {{-- Additional Information --}}
            @if($showAdditional)
                <div class="col-span-2">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white pb-1 border-b border-gray-200 dark:border-gray-700">
                        {{ __('Additional Information') }}
                    </h3>
                </div>
                <div class="col-span-2">
                    <label for="bio" class="form-label">{{ __('Bio') }}</label>
                    <textarea name="bio" id="bio" rows="3"
                        placeholder="{{ __('Tell us about yourself...') }}"
                        class="form-control h-16">{{ old('bio', $userMeta['bio'] ?? '') }}</textarea>
                </div>
                {!! Hook::applyFilters(UserFilterHook::USER_FORM_AFTER_BIO, '', $user) !!}

                @if(!empty($timezones))
                    {{-- Composant x-searchable-select supprimé car fichier manquant --}}
                    {!! Hook::applyFilters(UserFilterHook::USER_FORM_AFTER_TIMEZONE, '', $user) !!}
                @endif

                @if(!empty($locales))
                    {{-- Composant x-searchable-select supprimé car fichier manquant --}}
                    {!! Hook::applyFilters(UserFilterHook::USER_FORM_AFTER_LOCALE, '', $user) !!}
                @endif
            @endif

            <div class="col-span-2 flex mt-4">
                <!-- Submit buttons removed -->
            </div>
        </div>
    </div>
</div>