<x-layouts.modules-layout :breadcrumbs="[
    ['label' => 'Dashboard', 'url' => route('admin.dashboard')],
    ['label' => 'Élèves', 'url' => route('admin.students.index')],
    ['label' => 'Détail élève', 'url' => '#'],
]">
    <div class="space-y-6">
        <!-- Header -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 bg-white dark:bg-gray-800 p-6 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700">
            <div>
                <h1 class="text-2xl font-black text-gray-800 dark:text-white flex items-center gap-2">
                    <iconify-icon icon="lucide:user" class="text-violet-500" width="32"></iconify-icon>
                    Fiche élève
                </h1>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                    Consultation et mise à jour rapide des informations de l'élève, avec un aperçu de sa présence et de ses fiches de cotation.
                </p>
            </div>
            <div class="flex flex-col items-end text-right text-xs text-gray-400 uppercase font-black">
                <span>ID #{{ $student->id }}</span>
                <span>MAT: {{ $student->matricule ?? 'SANS MATRICULE' }}</span>
                <span class="mt-1 text-[10px] text-gray-500">École : {{ $student->school->name ?? '—' }}</span>
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

        <!-- Mini menu / onglets locaux -->
        <div class="bg-white dark:bg-gray-800 p-3 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm flex flex-wrap gap-2 text-xs font-bold text-gray-500 dark:text-gray-300">
            <a href="#student-infos" class="inline-flex items-center gap-1 px-3 py-1.5 rounded-xl bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-white">
                <iconify-icon icon="lucide:user" width="14"></iconify-icon>
                Infos élève
            </a>
            <a href="#student-presences" class="inline-flex items-center gap-1 px-3 py-1.5 rounded-xl hover:bg-violet-50 dark:hover:bg-violet-900/30">
                <iconify-icon icon="lucide:calendar-check" width="14"></iconify-icon>
                Présences
            </a>
            <a href="#student-notes" class="inline-flex items-center gap-1 px-3 py-1.5 rounded-xl hover:bg-amber-50 dark:hover:bg-amber-900/30">
                <iconify-icon icon="lucide:clipboard-list" width="14"></iconify-icon>
                Fiches de cotation
            </a>
            <a href="#student-deliberations" class="inline-flex items-center gap-1 px-3 py-1.5 rounded-xl hover:bg-sky-50 dark:hover:bg-sky-900/30">
                <iconify-icon icon="lucide:scale" width="14"></iconify-icon>
                Délibérations
            </a>
            <a href="#student-exits" class="inline-flex items-center gap-1 px-3 py-1.5 rounded-xl hover:bg-rose-50 dark:hover:bg-rose-900/30">
                <iconify-icon icon="lucide:log-out" width="14"></iconify-icon>
                Sorties
            </a>
            <a href="#student-visits" class="inline-flex items-center gap-1 px-3 py-1.5 rounded-xl hover:bg-indigo-50 dark:hover:bg-indigo-900/30">
                <iconify-icon icon="lucide:eye" width="14"></iconify-icon>
                Visites de classe
            </a>
        </div>

        <div class="grid gap-6 lg:grid-cols-12">
            <!-- Colonne gauche : infos + édition -->
            <div class="lg:col-span-4 space-y-6">
                <!-- Carte identité -->
                <div id="student-infos" class="bg-white dark:bg-gray-800 p-6 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm">
                    @php
                        $fullname = trim(($student->firstname ?? '') . ' ' . ($student->lastname ?? '') . ' ' . ($student->name ?? ''));
                        if ($fullname === '') {
                            $fullname = 'Élève #'.$student->id;
                        }
                    @endphp
                    <div class="flex items-center gap-4 mb-4">
                        <div class="h-12 w-12 flex items-center justify-center rounded-2xl bg-gradient-to-br from-pink-500 to-rose-600 text-white font-black text-sm">
                            {{ mb_substr($fullname, 0, 2) }}
                        </div>
                        <div>
                            <p class="font-bold text-lg text-gray-800 dark:text-white">{{ $fullname }}</p>
                            <p class="text-xs text-gray-400 uppercase font-black">{{ $student->gender ?? 'N/A' }}</p>
                        </div>
                    </div>
                    <dl class="space-y-2 text-sm text-gray-600 dark:text-gray-300">
                        <div class="flex justify-between">
                            <dt class="font-semibold">École</dt>
                            <dd class="text-right">{{ $student->school->name ?? '—' }}</dd>
                        </div>
                        @if($student->registration)
                            <div class="flex justify-between">
                                <dt class="font-semibold">Classe</dt>
                                <dd class="text-right">{{ $student->registration->classroom->name ?? '—' }}</dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="font-semibold">Année scolaire</dt>
                                <dd class="text-right">{{ $student->registration->schoolYear->title ?? '—' }}</dd>
                            </div>
                        @endif
                    </dl>
                    <div class="mt-4 flex gap-2 flex-wrap">
                        <a href="{{ route('admin.students.transfers.index', $student) }}" class="inline-flex items-center gap-2 px-3 py-2 rounded-xl bg-gray-100 dark:bg-gray-700 text-xs font-bold text-gray-700 dark:text-gray-200 hover:bg-violet-600 hover:text-white transition-colors">
                            <iconify-icon icon="lucide:route" width="16"></iconify-icon>
                            Parcours / Transferts
                        </a>
                        <a href="{{ route('admin.students.index', ['focus' => $student->id]) }}" class="inline-flex items-center gap-2 px-3 py-2 rounded-xl bg-gray-50 dark:bg-gray-800 text-xs font-bold text-gray-600 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-700 transition-colors">
                            <iconify-icon icon="lucide:list" width="16"></iconify-icon>
                            Retour à la liste
                        </a>
                    </div>
                </div>

                <!-- Formulaire d'édition rapide -->
                <div class="bg-white dark:bg-gray-800 p-6 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm">
                    <h2 class="text-sm font-black text-gray-700 dark:text-gray-200 mb-4 uppercase tracking-widest">Informations de base</h2>
                    <form method="POST" action="{{ route('admin.students.update', $student) }}" class="space-y-4">
                        @csrf
                        @method('PUT')
                        <div class="grid gap-4 md:grid-cols-2">
                            <div>
                                <label class="text-[10px] font-black text-gray-400 uppercase mb-1 block">Prénom</label>
                                <input type="text" name="firstname" value="{{ old('firstname', $student->firstname) }}" class="w-full h-10 rounded-xl border-gray-200 dark:border-gray-700 dark:bg-gray-900 text-sm focus:ring-violet-500 focus:border-violet-500" />
                                @error('firstname')<p class="text-xs text-rose-600 mt-1">{{ $message }}</p>@enderror
                            </div>
                            <div>
                                <label class="text-[10px] font-black text-gray-400 uppercase mb-1 block">Post-nom</label>
                                <input type="text" name="lastname" value="{{ old('lastname', $student->lastname) }}" class="w-full h-10 rounded-xl border-gray-200 dark:border-gray-700 dark:bg-gray-900 text-sm focus:ring-violet-500 focus:border-violet-500" />
                                @error('lastname')<p class="text-xs text-rose-600 mt-1">{{ $message }}</p>@enderror
                            </div>
                        </div>
                        <div>
                            <label class="text-[10px] font-black text-gray-400 uppercase mb-1 block">Nom</label>
                            <input type="text" name="name" value="{{ old('name', $student->name) }}" class="w-full h-10 rounded-xl border-gray-200 dark:border-gray-700 dark:bg-gray-900 text-sm focus:ring-violet-500 focus:border-violet-500" />
                            @error('name')<p class="text-xs text-rose-600 mt-1">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="text-[10px] font-black text-gray-400 uppercase mb-1 block">Genre</label>
                            <select name="gender" class="w-full h-10 rounded-xl border-gray-200 dark:border-gray-700 dark:bg-gray-900 text-sm focus:ring-violet-500 focus:border-violet-500">
                                <option value="">—</option>
                                <option value="male" @selected(old('gender', $student->gender?->value ?? null) === 'male')>Masculin</option>
                                <option value="female" @selected(old('gender', $student->gender?->value ?? null) === 'female')>Féminin</option>
                            </select>
                            @error('gender')<p class="text-xs text-rose-600 mt-1">{{ $message }}</p>@enderror
                        </div>
                        <div class="pt-2 flex justify-end">
                            <button type="submit" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-violet-600 text-white text-xs font-bold hover:bg-violet-700 transition-colors">
                                <iconify-icon icon="lucide:save" width="16"></iconify-icon>
                                Enregistrer les modifications
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Colonne droite : présence, fiches de cotation, délibérations, sorties, visites -->
            <div class="lg:col-span-8 space-y-6">
                <!-- Présences récentes -->
                <div id="student-presences" class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm overflow-hidden">
                    <div class="p-6 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between">
                        <div>
                            <h2 class="text-sm font-black text-gray-700 dark:text-gray-200 uppercase tracking-widest">Présences récentes</h2>
                            <p class="text-xs text-gray-400 mt-1">Derniers enregistrements de présence connus pour cet élève.</p>
                        </div>
                        @php
                            $totalPresences = $presences->count();
                            $presentCount = $presences->where('presence', true)->count();
                            $absentCount = $totalPresences - $presentCount;
                        @endphp
                        <div class="text-right text-xs text-gray-500">
                            <p>Total: <span class="font-bold">{{ $totalPresences }}</span></p>
                            <p>Présent: <span class="font-bold text-emerald-600">{{ $presentCount }}</span> • Absent: <span class="font-bold text-rose-600">{{ $absentCount }}</span></p>
                        </div>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left">
                            <thead>
                                <tr class="bg-gray-50/50 dark:bg-gray-900/50 border-b border-gray-100 dark:border-gray-700 text-xs font-black text-gray-400 uppercase tracking-widest">
                                    <th class="px-6 py-3">Date</th>
                                    <th class="px-6 py-3">Classe</th>
                                    <th class="px-6 py-3">Statut</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 dark:divide-gray-700 text-sm">
                                @forelse($presences as $presence)
                                    <tr class="hover:bg-violet-50/40 dark:hover:bg-violet-900/10 transition-colors">
                                        <td class="px-6 py-3 font-medium text-gray-700 dark:text-gray-200">
                                            {{ optional($presence->created_at)?->format('d/m/Y') ?? '—' }}
                                        </td>
                                        <td class="px-6 py-3 text-gray-600 dark:text-gray-300">
                                            {{ $presence->classroom->name ?? '—' }}
                                        </td>
                                        <td class="px-6 py-3">
                                            @php
                                                $label = $presence->presence ? 'Présent' : 'Absent';
                                                $color = $presence->presence ? 'bg-emerald-50 text-emerald-700 border-emerald-100' : 'bg-rose-50 text-rose-700 border-rose-100';
                                                if (! $presence->presence && $presence->absent_justified) {
                                                    $label = 'Absent justifié';
                                                }
                                                if (! $presence->presence && $presence->sick) {
                                                    $label = 'Malade';
                                                }
                                            @endphp
                                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold border {{ $color }}">
                                                {{ $label }}
                                            </span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="px-6 py-8 text-center text-sm text-gray-500 dark:text-gray-400">
                                            Aucune présence enregistrée pour le moment.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Fiches de cotation récentes -->
                <div id="student-notes" class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm overflow-hidden">
                    <div class="p-6 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between">
                        <div>
                            <h2 class="text-sm font-black text-gray-700 dark:text-gray-200 uppercase tracking-widest">Fiches de cotation récentes</h2>
                            <p class="text-xs text-gray-400 mt-1">Dernières notes enregistrées pour cet élève.</p>
                        </div>
                        <div class="text-right text-xs text-gray-500">
                            <p>Total fiches: <span class="font-bold">{{ $notes->count() }}</span></p>
                        </div>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left">
                            <thead>
                                <tr class="bg-gray-50/50 dark:bg-gray-900/50 border-b border-gray-100 dark:border-gray-700 text-xs font-black text-gray-400 uppercase tracking-widest">
                                    <th class="px-6 py-3">Année</th>
                                    <th class="px-6 py-3">Semestre</th>
                                    <th class="px-6 py-3">Cours</th>
                                    <th class="px-6 py-3">Classe</th>
                                    <th class="px-6 py-3 text-right">Note / Max</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 dark:divide-gray-700 text-sm">
                                @forelse($notes as $fiche)
                                    <tr class="hover:bg-amber-50/40 dark:hover:bg-amber-900/10 transition-colors">
                                        <td class="px-6 py-3 text-gray-600 dark:text-gray-300">
                                            {{ $fiche->schoolYear->title ?? '—' }}
                                        </td>
                                        <td class="px-6 py-3 text-gray-600 dark:text-gray-300">
                                            {{ $fiche->semester->name ?? '—' }}
                                        </td>
                                        <td class="px-6 py-3 text-gray-700 dark:text-gray-200 font-medium">
                                            {{ $fiche->course->name ?? '—' }}
                                        </td>
                                        <td class="px-6 py-3 text-gray-600 dark:text-gray-300">
                                            {{ $fiche->classroom->name ?? '—' }}
                                        </td>
                                        <td class="px-6 py-3 text-right text-gray-800 dark:text-gray-100 font-bold">
                                            {{ $fiche->note ?? '—' }}
                                            @if(!is_null($fiche->Maxima))
                                                <span class="text-xs text-gray-400">/ {{ $fiche->Maxima }}</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="px-6 py-8 text-center text-sm text-gray-500 dark:text-gray-400">
                                            Aucune fiche de cotation trouvée pour cet élève.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Délibérations récentes -->
                <div id="student-deliberations" class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm overflow-hidden">
                    <div class="p-6 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between">
                        <div>
                            <h2 class="text-sm font-black text-gray-700 dark:text-gray-200 uppercase tracking-widest">Délibérations récentes</h2>
                            <p class="text-xs text-gray-400 mt-1">Dernières délibérations enregistrées pour cet élève.</p>
                        </div>
                        <div class="text-right text-xs text-gray-500">
                            <p>Total: <span class="font-bold">{{ $deliberations->count() }}</span></p>
                        </div>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left">
                            <thead>
                                <tr class="bg-gray-50/50 dark:bg-gray-900/50 border-b border-gray-100 dark:border-gray-700 text-xs font-black text-gray-400 uppercase tracking-widest">
                                    <th class="px-6 py-3">Année</th>
                                    <th class="px-6 py-3">Classe</th>
                                    <th class="px-6 py-3">Cours</th>
                                    <th class="px-6 py-3 text-right">Statut</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 dark:divide-gray-700 text-sm">
                                @forelse($deliberations as $delib)
                                    <tr class="hover:bg-sky-50/40 dark:hover:bg-sky-900/10 transition-colors">
                                        <td class="px-6 py-3 text-gray-600 dark:text-gray-300">
                                            {{ $delib->schoolYear->title ?? '—' }}
                                        </td>
                                        <td class="px-6 py-3 text-gray-600 dark:text-gray-300">
                                            {{ $delib->classroom->name ?? '—' }}
                                        </td>
                                        <td class="px-6 py-3 text-gray-700 dark:text-gray-200 font-medium">
                                            {{ $delib->course->name ?? '—' }}
                                        </td>
                                        <td class="px-6 py-3 text-right">
                                            @php
                                                $validated = (bool) $delib->is_validated;
                                                $label = $validated ? 'Validée' : 'Non validée';
                                                $color = $validated
                                                    ? 'bg-emerald-50 text-emerald-700 border-emerald-100'
                                                    : 'bg-amber-50 text-amber-700 border-amber-100';
                                            @endphp
                                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold border {{ $color }}">
                                                {{ $label }}
                                            </span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="px-6 py-8 text-center text-sm text-gray-500 dark:text-gray-400">
                                            Aucune délibération trouvée pour cet élève.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Sorties récentes -->
                <div id="student-exits" class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm overflow-hidden">
                    <div class="p-6 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between">
                        <div>
                            <h2 class="text-sm font-black text-gray-700 dark:text-gray-200 uppercase tracking-widest">Sorties récentes</h2>
                            <p class="text-xs text-gray-400 mt-1">Historique des sorties (permissions) de l'élève.</p>
                        </div>
                        <div class="text-right text-xs text-gray-500">
                            <p>Total: <span class="font-bold">{{ $exits->count() }}</span></p>
                        </div>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left">
                            <thead>
                                <tr class="bg-gray-50/50 dark:bg-gray-900/50 border-b border-gray-100 dark:border-gray-700 text-xs font-black text-gray-400 uppercase tracking-widest">
                                    <th class="px-6 py-3">Date</th>
                                    <th class="px-6 py-3">Heure</th>
                                    <th class="px-6 py-3">Filière</th>
                                    <th class="px-6 py-3">Année scolaire</th>
                                    <th class="px-6 py-3">Motif</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 dark:divide-gray-700 text-sm">
                                @forelse($exits as $exit)
                                    <tr class="hover:bg-rose-50/40 dark:hover:bg-rose-900/10 transition-colors">
                                        <td class="px-6 py-3 text-gray-700 dark:text-gray-200">
                                            {{ $exit->date ? \Carbon\Carbon::parse($exit->date)->format('d/m/Y') : '—' }}
                                        </td>
                                        <td class="px-6 py-3 text-gray-600 dark:text-gray-300">
                                            {{ $exit->exit_time ?? '—' }}
                                        </td>
                                        <td class="px-6 py-3 text-gray-600 dark:text-gray-300">
                                            {{ $exit->filiere->name ?? '—' }}
                                        </td>
                                        <td class="px-6 py-3 text-gray-600 dark:text-gray-300 text-xs">
                                            {{ $exit->schoolYear->title ?? '—' }}
                                        </td>
                                        <td class="px-6 py-3 text-gray-500 dark:text-gray-400 text-xs max-w-xs truncate" title="{{ $exit->motif }}">
                                            {{ $exit->motif ?? '—' }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="px-6 py-8 text-center text-sm text-gray-500 dark:text-gray-400">
                                            Aucune sortie enregistrée pour cet élève.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Visites de classe récentes (école) -->
                <div id="student-visits" class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm overflow-hidden">
                    <div class="p-6 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between">
                        <div>
                            <h2 class="text-sm font-black text-gray-700 dark:text-gray-200 uppercase tracking-widest">Visites de classe (école)</h2>
                            <p class="text-xs text-gray-400 mt-1">Dernières visites pédagogiques réalisées dans cette école.</p>
                        </div>
                        <div class="text-right text-xs text-gray-500">
                            <p>Total: <span class="font-bold">{{ $visits->count() }}</span></p>
                        </div>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left">
                            <thead>
                                <tr class="bg-gray-50/50 dark:bg-gray-900/50 border-b border-gray-100 dark:border-gray-700 text-xs font-black text-gray-400 uppercase tracking-widest">
                                    <th class="px-6 py-3">Date / Heure</th>
                                    <th class="px-6 py-3">Classe</th>
                                    <th class="px-6 py-3">Visiteur</th>
                                    <th class="px-6 py-3">Objet</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 dark:divide-gray-700 text-sm">
                                @forelse($visits as $visit)
                                    <tr class="hover:bg-indigo-50/40 dark:hover:bg-indigo-900/10 transition-colors">
                                        <td class="px-6 py-3 text-gray-700 dark:text-gray-200">
                                            @php
                                                $vh = $visit->visit_hour;
                                            @endphp
                                            {{ $vh ? \Carbon\Carbon::parse($vh)->format('d/m/Y H:i') : '—' }}
                                        </td>
                                        <td class="px-6 py-3 text-gray-600 dark:text-gray-300">
                                            {{ $visit->classroom->name ?? '—' }}
                                        </td>
                                        <td class="px-6 py-3 text-gray-600 dark:text-gray-300">
                                            {{ $visit->visiteur ?? '—' }}
                                        </td>
                                        <td class="px-6 py-3 text-gray-500 dark:text-gray-400 text-xs max-w-xs truncate" title="{{ $visit->subject }}">
                                            {{ $visit->subject ?? '—' }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="px-6 py-8 text-center text-sm text-gray-500 dark:text-gray-400">
                                            Aucune visite enregistrée pour cette école.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-layouts.modules-layout>
