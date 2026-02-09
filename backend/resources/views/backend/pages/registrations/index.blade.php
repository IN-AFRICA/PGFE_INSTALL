<x-layouts.backend-layout :breadcrumbs="[['title'=>'Inscriptions']]">
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-lg font-semibold">Inscriptions</h1>
        <a href="{{ route('admin.registrations.create') }}" class="inline-flex items-center gap-1 rounded-md bg-violet-600 px-4 py-2 text-sm font-medium text-white hover:bg-violet-700">
            <iconify-icon icon="lucide:plus" width="16" height="16" />Nouvelle
        </a>
    </div>
    <div class="overflow-x-auto bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg">
        <table class="min-w-full text-sm">
            <thead class="bg-gray-50 dark:bg-gray-700/50 text-gray-600 dark:text-gray-300">
                <tr>
                    <th class="px-3 py-2 text-left">ID</th>
                    <th class="px-3 py-2 text-left">Élève</th>
                    <th class="px-3 py-2 text-left">Classe</th>
                    <th class="px-3 py-2 text-left">École</th>
                    <th class="px-3 py-2 text-left">Date</th>
                    <th class="px-3 py-2 text-left">Statut</th>
                    <th class="px-3 py-2 text-left w-px">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                @forelse($registrations as $r)
                    @php
                        $studentParts = array_filter([$r->student->firstname ?? null, $r->student->lastname ?? null, $r->student->name ?? null]);
                        $studentName = $studentParts ? implode(' ', $studentParts) : 'Élève #'.$r->student_id;
                    @endphp
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/40">
                        <td class="px-3 py-2">{{ $r->id }}</td>
                        <td class="px-3 py-2 font-medium">{{ $studentName }}</td>
                        <td class="px-3 py-2">{{ $r->classroom->name ?? '—' }}</td>
                        <td class="px-3 py-2">{{ $r->school->name ?? '—' }}</td>
                        <td class="px-3 py-2">{{ $r->registration_date?->format('Y-m-d') }}</td>
                        <td class="px-3 py-2">
                            @if($r->registration_status)
                                <span class="px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-700 text-xs">Active</span>
                            @else
                                <span class="px-2 py-0.5 rounded-full bg-gray-200 text-gray-600 text-xs">Inactive</span>
                            @endif
                        </td>
                        <td class="px-3 py-2"><x-ui.action-buttons :edit-url="route('admin.registrations.edit',$r)" :delete-url="route('admin.registrations.destroy',$r)" /></td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="px-3 py-6 text-center text-gray-500">Aucune inscription.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $registrations->links() }}</div>
</x-layouts.backend-layout>
