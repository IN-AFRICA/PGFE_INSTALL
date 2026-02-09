<x-layouts.backend-layout :breadcrumbs="[['title'=>'Provinces','url'=>route('admin.provinces.index')],['title'=>'Créer']]">
    <h1 class="text-lg font-semibold mb-6">Créer une province</h1>
    <form method="POST" action="{{ route('admin.provinces.store') }}" class="space-y-6 max-w-lg">
        @csrf
        <div class="grid gap-6 sm:grid-cols-2">
            <div class="sm:col-span-2">
                <label for="name" class="block text-sm font-medium mb-1">Nom <span class="text-red-600">*</span></label>
                <input type="text" id="name" name="name" value="{{ old('name') }}" required class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-800 text-sm" />
                @error('name')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
            </div>
            <div class="sm:col-span-2">
                <label for="country_id" class="block text-sm font-medium mb-1">Pays</label>
                <select id="country_id" name="country_id" class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-800 text-sm">
                    <option value="">— Aucun —</option>
                    @foreach($countries as $c)
                        <option value="{{ $c->id }}" @selected(old('country_id')==$c->id)>{{ $c->name }}</option>
                    @endforeach
                </select>
                @error('country_id')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
            </div>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.provinces.index') }}" class="text-sm px-4 py-2 rounded-md border border-gray-300 dark:border-gray-600 hover:bg-gray-100 dark:hover:bg-gray-700">Annuler</a>
            <button type="submit" class="inline-flex items-center gap-2 rounded-md bg-violet-600 px-5 py-2 text-sm font-medium text-white hover:bg-violet-700">
                <iconify-icon icon="lucide:check" width="16" height="16"></iconify-icon>Enregistrer
            </button>
        </div>
    </form>
</x-layouts.backend-layout>
