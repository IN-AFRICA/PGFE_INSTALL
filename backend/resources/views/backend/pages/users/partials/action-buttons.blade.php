@if (auth()->user()->canBeModified($user) || auth()->user()->can('user.login_as') || auth()->user()->canBeModified($user, 'user.delete'))
    <div class="flex items-center justify-end gap-2 text-sm">
        @if (auth()->user()->canBeModified($user))
            <a href="{{ route('admin.users.edit', $user->id) }}" class="inline-flex items-center gap-1 rounded-md border border-gray-200 bg-white px-3 py-1.5 text-gray-700 shadow-sm hover:bg-gray-50">
                <iconify-icon icon="lucide:pencil" class="text-base"></iconify-icon>
                <span>{{ __('Edit') }}</span>
            </a>
        @endif

        @if (auth()->user()->can('user.login_as') && $user->id != auth()->user()->id)
            <a href="{{ route('admin.users.login-as', $user->id) }}" class="inline-flex items-center gap-1 rounded-md border border-gray-200 bg-white px-3 py-1.5 text-gray-700 shadow-sm hover:bg-gray-50">
                <iconify-icon icon="lucide:log-in" class="text-base"></iconify-icon>
                <span>{{ __('Login as') }}</span>
            </a>
        @endif

        @if (auth()->user()->canBeModified($user, 'user.delete'))
            <form action="{{ route('admin.users.destroy', $user->id) }}" method="POST" onsubmit="return confirm('{{ __('Are you sure you want to delete this user?') }}')">
                @csrf
                @method('DELETE')
                <button type="submit" class="inline-flex items-center gap-1 rounded-md border border-red-200 bg-red-50 px-3 py-1.5 text-red-700 shadow-sm hover:bg-red-100">
                    <iconify-icon icon="lucide:trash" class="text-base"></iconify-icon>
                    <span>{{ __('Delete') }}</span>
                </button>
            </form>
        @endif
    </div>
@endif
