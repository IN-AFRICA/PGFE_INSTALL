<x-layouts.modules-layout :breadcrumbs="[
    ['label' => 'Dashboard', 'url' => route('admin.dashboard')],
    ['label' => 'Élèves', 'url' => route('admin.students.index')],
    ['label' => 'Parcours élève', 'url' => '#'],
]">
    <div class="space-y-6">
        <!-- Header -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 bg-white dark:bg-gray-800 p-6 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700">
            <div>
                <h1 class="text-2xl font-black text-gray-800 dark:text-white flex items-center gap-2">
                    <iconify-icon icon="lucide:route" class="text-blue-500" width="32"></iconify-icon>
                    Parcours de l'élève
                </h1>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                    Historique des transferts et changement d'école pour cet élève.
                </p>
            </div>
            <div class="flex items-center gap-3">
                <div class="flex flex-col items-end">
                    <span class="text-xs font-bold text-gray-400 uppercase">Élève</span>
                    <span class="text-sm font-black text-gray-800 dark:text-white">
                        {{ trim(($student->firstname.' '.$student->lastname.' '.$student->name)) ?: 'Élève #'.$student->id }}
                    </span>
                    <span class="text-[10px] text-gray-400 font-bold uppercase">MAT: {{ $student->matricule ?? 'SANS MATRICULE' }}</span>
                </div>
            </div>
        </div>

        @if(session('success'))
            <div class="p-4 rounded-xl bg-emerald-50 text-emerald-700 border border-emerald-100 text-sm font-medium">
                {{ session('success') }}
            </div>
        @elseif(session('error'))
            <div class="p-4 rounded-xl bg-rose-50 text-rose-700 border border-rose-100 text-sm font-medium">
                {{ session('error') }}
            </div>
        @endif

        <!-- Formulaire de transfert -->
        <div class="bg-white dark:bg-gray-800 p-6 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm">
            <h2 class="text-sm font-black text-gray-700 dark:text-gray-200 mb-4 uppercase tracking-widest">Nouveau transfert</h2>
            <form method="POST" action="{{ route('admin.students.transfers.store', $student) }}" class="grid gap-4 md:grid-cols-12 items-end">
                @csrf
                <div class="md:col-span-4">
                    <label class="text-[10px] font-black text-gray-400 uppercase mb-1 block">Nouvelle école</label>
                    <select name="to_school_id" class="w-full h-11 rounded-xl border-gray-200 dark:border-gray-700 dark:bg-gray-900 text-sm focus:ring-blue-500 focus:border-blue-500" required>
                        <option value="">Sélectionner une école</option>
                        @foreach($schools as $sch)
                            <option value="{{ $sch->id }}" @selected(old('to_school_id') == $sch->id)>{{ $sch->name }}</option>
                        @endforeach
                    </select>
                    @error('to_school_id')<p class="text-xs text-rose-600 mt-1">{{ $message }}</p>@enderror
                </div>
                <div class="md:col-span-3">
                    <label class="text-[10px] font-black text-gray-400 uppercase mb-1 block">Année scolaire (optionnel)</label>
                    <input type="number" name="school_year_id" value="{{ old('school_year_id') }}" class="w-full h-11 rounded-xl border-gray-200 dark:border-gray-700 dark:bg-gray-900 text-sm focus:ring-blue-500 focus:border-blue-500" placeholder="ID année scolaire">
                    @error('school_year_id')<p class="text-xs text-rose-600 mt-1">{{ $message }}</p>@enderror
                </div>
                <div class="md:col-span-3">
                    <label class="text-[10px] font-black text-gray-400 uppercase mb-1 block">Date de transfert</label>
                    <input type="date" name="transfer_date" value="{{ old('transfer_date', now()->toDateString()) }}" class="w-full h-11 rounded-xl border-gray-200 dark:border-gray-700 dark:bg-gray-900 text-sm focus:ring-blue-500 focus:border-blue-500">
                    @error('transfer_date')<p class="text-xs text-rose-600 mt-1">{{ $message }}</p>@enderror
                </div>
                <div class="md:col-span-12">
                    <label class="text-[10px] font-black text-gray-400 uppercase mb-1 block">Motif (optionnel)</label>
                    <textarea name="reason" rows="2" class="w-full rounded-xl border-gray-200 dark:border-gray-700 dark:bg-gray-900 text-sm focus:ring-blue-500 focus:border-blue-500" placeholder="Ex: Mutation, rapprochement familial, etc.">{{ old('reason') }}</textarea>
                    @error('reason')<p class="text-xs text-rose-600 mt-1">{{ $message }}</p>@enderror
                </div>
                <div class="md:col-span-12 flex justify-end gap-2">
                    <a href="{{ route('admin.students.index', ['focus' => $student->id]) }}" class="h-11 px-5 rounded-xl bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-200 text-sm font-bold flex items-center gap-2">
                        <iconify-icon icon="lucide:arrow-left" width="16"></iconify-icon>
                        Retour à la liste
                    </a>
                    <button type="submit" class="h-11 px-6 rounded-xl bg-blue-600 text-white text-sm font-bold hover:bg-blue-700 transition-colors flex items-center gap-2">
                        <iconify-icon icon="lucide:send" width="16"></iconify-icon>
                        Enregistrer le transfert
                    </button>
                </div>
            </form>
        </div>

        <!-- Historique des transferts -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm overflow-hidden">
            <div class="p-6 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between">
                <h2 class="text-sm font-black text-gray-700 dark:text-gray-200 uppercase tracking-widest">Historique des transferts</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead>
                        <tr class="bg-gray-50/50 dark:bg-gray-900/50 border-b border-gray-100 dark:border-gray-700 text-xs font-black text-gray-400 uppercase tracking-widest">
                            <th class="px-6 py-3">Date</th>
                            <th class="px-6 py-3">De</th>
                            <th class="px-6 py-3">Vers</th>
                            <th class="px-6 py-3">Année scolaire</th>
                            <th class="px-6 py-3">Motif</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700 text-sm">
                        @forelse($transfers as $tr)
                            <tr class="hover:bg-blue-50/40 dark:hover:bg-blue-900/10 transition-colors">
                                <td class="px-6 py-3 font-medium text-gray-700 dark:text-gray-200">
                                    {{ optional($tr->transfer_date ? \Carbon\Carbon::parse($tr->transfer_date) : null)?->format('d/m/Y') ?? '—' }}
                                </td>
                                <td class="px-6 py-3 text-gray-600 dark:text-gray-300">
                                    {{ $tr->fromSchool->name ?? '—' }}
                                </td>
                                <td class="px-6 py-3 text-gray-600 dark:text-gray-300">
                                    {{ $tr->toSchool->name ?? '—' }}
                                </td>
                                <td class="px-6 py-3 text-gray-500 dark:text-gray-400 text-xs">
                                    {{ $tr->schoolYear->name ?? ($tr->school_year_id ? 'ID #'.$tr->school_year_id : '—') }}
                                </td>
                                <td class="px-6 py-3 text-gray-500 dark:text-gray-400 text-xs">
                                    {{ $tr->reason ?? '—' }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-10 text-center text-sm text-gray-500 dark:text-gray-400">
                                    Aucun transfert enregistré pour cet élève.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-layouts.modules-layout>
