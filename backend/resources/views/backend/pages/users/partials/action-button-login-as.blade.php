<a href="{{ route('admin.users.login-as', $user->id) }}" class="inline-flex items-center gap-1 rounded-md border border-gray-200 bg-white px-3 py-1.5 text-gray-700 shadow-sm hover:bg-gray-50">
    <iconify-icon icon="lucide:log-in" class="text-base"></iconify-icon>
    <span>{{ __('Login as') }}</span>
</a>
