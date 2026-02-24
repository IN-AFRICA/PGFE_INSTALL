<x-layouts.modules-layout :breadcrumbs="[['label' => 'Dashboard', 'url' => route('admin.dashboard')], ['label' => 'Planification des travaux', 'url' => route('admin.planning.index')], ['label' => 'Nouvelle planification', 'url' => '#']]">
    <div class="max-w-3xl mx-auto space-y-8">
        <div class="flex items-center justify-between bg-white dark:bg-gray-900 p-8 rounded-[2.5rem] shadow-xl border border-gray-100 dark:border-gray-800">
            <div class="flex items-center gap-4">
                <div class="h-14 w-14 rounded-2xl bg-gradient-to-br from-indigo-500 to-purple-500 flex items-center justify-center text-white shadow-lg shadow-indigo-500/20">
                    <iconify-icon icon="lucide:calendar-plus" width="32"></iconify-icon>
                </div>
                <div>
                    <h1 class="text-2xl font-black text-gray-800 dark:text-white">Nouvelle planification</h1>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Définissez un travail scolaire pour une classe donnée.</p>
                </div>
            </div>
            <a href="{{ route('admin.planning.index') }}" class="h-12 px-6 flex items-center gap-2 rounded-xl bg-gray-50 dark:bg-gray-800 text-gray-500 hover:bg-gray-100 dark:hover:bg-gray-700 transition-all font-black text-xs uppercase tracking-widest">
                <iconify-icon icon="lucide:arrow-left" width="18"></iconify-icon>
                RETOUR
            </a>
        </div>

        <div class="bg-white dark:bg-gray-900 rounded-[2.5rem] shadow-2xl border border-gray-100 dark:border-gray-800 overflow-hidden">
            <form action="{{ route('admin.planning.store') }}" method="POST" class="p-10 space-y-8">
                @csrf

                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <div class="space-y-2 md:col-span-2">
                        <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest px-1">Titre</label>
                        <input type="text" name="label" value="{{ old('label') }}" placeholder="Ex: Devoir de mathématiques chapitre 3" class="w-full h-12 rounded-2xl bg-gray-50 dark:bg-gray-800 border-none font-bold text-gray-800 dark:text-white px-4 focus:ring-2 focus:ring-indigo-600" />
                        @error('label') <p class="text-xs text-rose-500 font-bold px-1 italic">{{ $message }}</p> @enderror
                    </div>

                    <div class="space-y-2">
                        <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest px-1">Classe</label>
                        <select name="classroom_id" class="w-full h-12 rounded-2xl bg-gray-50 dark:bg-gray-800 border-none font-bold text-gray-800 dark:text-white focus:ring-2 focus:ring-indigo-600 appearance-none transition-all">
                            <option value="">-- Sélectionner --</option>
                            @foreach($classrooms as $classroom)
                                <option value="{{ $classroom->id }}" @selected(old('classroom_id') == $classroom->id)>{{ $classroom->name }}</option>
                            @endforeach
                        </select>
                        @error('classroom_id') <p class="text-xs text-rose-500 font-bold px-1 italic">{{ $message }}</p> @enderror
                    </div>

                    <div class="space-y-2">
                        <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest px-1">Cours</label>
                        <select name="course_id" class="w-full h-12 rounded-2xl bg-gray-50 dark:bg-gray-800 border-none font-bold text-gray-800 dark:text-white focus:ring-2 focus:ring-indigo-600 appearance-none transition-all">
                            <option value="">-- Sélectionner --</option>
                            @foreach($courses as $course)
                                <option value="{{ $course->id }}" @selected(old('course_id') == $course->id)>{{ $course->label }}</option>
                            @endforeach
                        </select>
                        @error('course_id') <p class="text-xs text-rose-500 font-bold px-1 italic">{{ $message }}</p> @enderror
                    </div>

                    <div class="space-y-2">
                        <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest px-1">Date de début</label>
                        <input type="date" name="start_date" value="{{ old('start_date') }}" class="w-full h-12 rounded-2xl bg-gray-50 dark:bg-gray-800 border-none font-bold text-gray-800 dark:text-white px-4 focus:ring-2 focus:ring-indigo-600" />
                        @error('start_date') <p class="text-xs text-rose-500 font-bold px-1 italic">{{ $message }}</p> @enderror
                    </div>

                    <div class="space-y-2">
                        <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest px-1">Date de fin</label>
                        <input type="date" name="end_date" value="{{ old('end_date') }}" class="w-full h-12 rounded-2xl bg-gray-50 dark:bg-gray-800 border-none font-bold text-gray-800 dark:text-white px-4 focus:ring-2 focus:ring-indigo-600" />
                        @error('end_date') <p class="text-xs text-rose-500 font-bold px-1 italic">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="pt-6 border-t border-gray-50 dark:border-gray-800 flex justify-end">
                    <button type="submit" class="inline-flex items-center justify-center gap-3 rounded-2xl bg-indigo-600 px-10 py-4 text-sm font-black text-white hover:bg-indigo-700 shadow-xl shadow-indigo-600/20 transition-all hover:-translate-y-0.5">
                        <iconify-icon icon="lucide:save" width="20"></iconify-icon>
                        ENREGISTRER
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-layouts.modules-layout>
