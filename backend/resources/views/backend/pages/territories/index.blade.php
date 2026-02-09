<x-layouts.backend-layout :breadcrumbs="[['title'=>'Territoires']]">
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-lg font-semibold">Territoires</h1>
        <a href="{{ route('admin.territories.create') }}" class="inline-flex items-center gap-1 rounded-md bg-violet-600 px-4 py-2 text-sm font-medium text-white hover:bg-violet-700">
            <iconify-icon icon="lucide:plus" width="16" height="16"></iconify-icon>Créer
        </a>
    </div>
    <div class="overflow-x-auto bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg">
        <table class="min-w-full text-sm">
            <thead class="bg-gray-50 dark:bg-gray-700/50 text-gray-600 dark:text-gray-300">
                <tr>
                    <th class="px-3 py-2 text-left">ID</th>
                    <th class="px-3 py-2 text-left">Nom</th>
                    <th class="px-3 py-2 text-left">Province</th>
                    <th class="px-3 py-2 text-left w-px">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                @forelse($territories as $t)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/40">
                        <td class="px-3 py-2">{{ $t->id }}</td>
                        <td class="px-3 py-2 font-medium">{{ $t->name }}</td>
                        <td class="px-3 py-2">{{ $t->province->name ?? '—' }}</td>
                        <td class="px-3 py-2"><x-ui.action-buttons :edit-url="route('admin.territories.edit',$t)" :delete-url="route('admin.territories.destroy',$t)" /></td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="px-3 py-6 text-center text-gray-500">Aucun territoire.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $territories->links() }}</div>
</x-layouts.backend-layout>
