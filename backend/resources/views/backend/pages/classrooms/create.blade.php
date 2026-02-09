<x-layouts.backend-layout :breadcrumbs="[['title'=>'Classes','url'=>route('admin.classrooms.index')],['title'=>'Créer']]">
    <h1 class="text-lg font-semibold mb-6">Créer une classe</h1>
    <form method="POST" action="{{ route('admin.classrooms.store') }}" class="space-y-8 max-w-2xl">
        @csrf
        <div class="grid gap-6 md:grid-cols-2">
            <div class="md:col-span-2">
                <label for="name" class="block text-sm font-medium mb-1">Nom <span class="text-red-600">*</span></label>
                <input type="text" id="name" name="name" value="{{ old('name') }}" required class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-800 text-sm" placeholder="Ex: 6ème A" />
                @error('name')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="school_id" class="block text-sm font-medium mb-1">École <span class="text-red-600">*</span></label>
                <select id="school_id" name="school_id" required class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-800 text-sm">
                    <option value="">-- Sélectionner --</option>
                    @foreach($schools as $s)
                        <option value="{{ $s->id }}" @selected(old('school_id')==$s->id)>{{ $s->name }}</option>
                    @endforeach
                </select>
                @error('school_id')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="filiaire_id" class="block text-sm font-medium mb-1">Filière <span class="text-red-600">*</span></label>
                <select id="filiaire_id" name="filiaire_id" required class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-800 text-sm">
                    <option value="">-- Sélectionner --</option>
                    @foreach($filiaires as $f)
                        <option value="{{ $f->id }}" @selected(old('filiaire_id')==$f->id)>{{ $f->name }}</option>
                    @endforeach
                </select>
                @error('filiaire_id')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
            </div>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.classrooms.index') }}" class="text-sm px-4 py-2 rounded-md border border-gray-300 dark:border-gray-600 hover:bg-gray-100 dark:hover:bg-gray-700">Annuler</a>
            <button type="submit" class="inline-flex items-center gap-2 rounded-md bg-violet-600 px-5 py-2 text-sm font-medium text-white hover:bg-violet-700">
                <iconify-icon icon="lucide:check" width="16" height="16"></iconify-icon>Enregistrer
            </button>
        </div>
    </form>
</x-layouts.backend-layout>

