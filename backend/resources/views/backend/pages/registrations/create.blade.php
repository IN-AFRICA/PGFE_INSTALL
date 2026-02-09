<x-layouts.backend-layout :breadcrumbs="[['title'=>'Inscriptions','url'=>route('admin.registrations.index')],['title'=>'Nouvelle']]">
    <h1 class="text-lg font-semibold mb-6">Nouvelle inscription</h1>
    <form method="POST" action="{{ route('admin.registrations.store') }}" class="space-y-8">
        @csrf
        <div class="grid gap-6 md:grid-cols-2">
            <div>
                <label class="block text-sm font-medium mb-1" for="student_id">Élève <span class="text-red-600">*</span></label>
                <select name="student_id" id="student_id" required class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-800 text-sm">
                    <option value="">-- Sélectionner --</option>
                    @foreach($students as $s)
                        @php $n = trim(($s->firstname.' '.$s->lastname.' '.$s->name)); @endphp
                        <option value="{{ $s->id }}" @selected(old('student_id')==$s->id)>{{ $n ?: 'Élève #'.$s->id }}</option>
                    @endforeach
                </select>
                @error('student_id')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-sm font-medium mb-1" for="classroom_id">Classe <span class="text-red-600">*</span></label>
                <select name="classroom_id" id="classroom_id" required class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-800 text-sm">
                    <option value="">-- Sélectionner --</option>
                    @foreach($classrooms as $c)
                        <option value="{{ $c->id }}" @selected(old('classroom_id')==$c->id)>{{ $c->name }}</option>
                    @endforeach
                </select>
                @error('classroom_id')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-sm font-medium mb-1" for="school_year_id">Année scolaire <span class="text-red-600">*</span></label>
                <select name="school_year_id" id="school_year_id" required class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-800 text-sm">
                    <option value="">-- Sélectionner --</option>
                    @foreach($schoolYears as $y)
                        <option value="{{ $y->id }}" @selected(old('school_year_id')==$y->id)>{{ $y->title ?? ('Année #'.$y->id) }}</option>
                    @endforeach
                </select>
                @error('school_year_id')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-sm font-medium mb-1" for="academic_personal_id">Personnel référent <span class="text-red-600">*</span></label>
                <select name="academic_personal_id" id="academic_personal_id" required class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-800 text-sm">
                    <option value="">-- Sélectionner --</option>
                    @foreach($personnels as $p)
                        @php $n = trim(($p->firstname.' '.$p->lastname.' '.$p->name)); @endphp
                        <option value="{{ $p->id }}" @selected(old('academic_personal_id')==$p->id)>{{ $n ?: 'Personnel #'.$p->id }}</option>
                    @endforeach
                </select>
                @error('academic_personal_id')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-sm font-medium mb-1" for="academic_level_id">Niveau académique <span class="text-red-600">*</span></label>
                <select name="academic_level_id" id="academic_level_id" required class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-800 text-sm">
                    <option value="">-- Sélectionner --</option>
                    @foreach($levels as $l)
                        <option value="{{ $l->id }}" @selected(old('academic_level_id')==$l->id)>{{ $l->name }}</option>
                    @endforeach
                </select>
                @error('academic_level_id')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-sm font-medium mb-1" for="type_id">Type <span class="text-red-600">*</span></label>
                <select name="type_id" id="type_id" required class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-800 text-sm">
                    <option value="">-- Sélectionner --</option>
                    @foreach($types as $t)
                        <option value="{{ $t->id }}" @selected(old('type_id')==$t->id)>{{ $t->title }}</option>
                    @endforeach
                </select>
                @error('type_id')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-sm font-medium mb-1" for="registration_date">Date inscription <span class="text-red-600">*</span></label>
                <input type="date" id="registration_date" name="registration_date" value="{{ old('registration_date', date('Y-m-d')) }}" required class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-800 text-sm" />
                @error('registration_date')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
            </div>
            <div class="flex items-center gap-2 pt-6">
                <input type="checkbox" id="registration_status" name="registration_status" value="1" @checked(old('registration_status')) class="rounded border-gray-300 dark:border-gray-600 dark:bg-gray-800" />
                <label for="registration_status" class="text-sm">Active</label>
            </div>
            <div class="md:col-span-2">
                <label class="block text-sm font-medium mb-1" for="note">Note</label>
                <textarea id="note" name="note" rows="3" class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-800 text-sm">{{ old('note') }}</textarea>
                @error('note')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
            </div>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.registrations.index') }}" class="text-sm px-4 py-2 rounded-md border border-gray-300 dark:border-gray-600 hover:bg-gray-100 dark:hover:bg-gray-700">Annuler</a>
            <button type="submit" class="inline-flex items-center gap-2 rounded-md bg-violet-600 px-5 py-2 text-sm font-medium text-white hover:bg-violet-700">
                <iconify-icon icon="lucide:check" width="16" height="16"></iconify-icon>Enregistrer
            </button>
        </div>
    </form>
</x-layouts.backend-layout>

