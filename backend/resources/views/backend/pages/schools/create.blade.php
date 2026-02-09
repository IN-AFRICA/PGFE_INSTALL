<x-layouts.backend-layout :breadcrumbs="[['title'=>'Écoles','url'=>route('admin.schools.index')],['title'=>'Créer']]">
    <h1 class="text-lg font-semibold mb-6">Créer une école</h1>

    <form method="POST" action="{{ route('admin.schools.store') }}" enctype="multipart/form-data" class="space-y-6 max-w-3xl">
        @csrf
        <div class="grid gap-6 sm:grid-cols-2">
            <div class="sm:col-span-2">
                <label class="block text-sm font-medium mb-1" for="name">Nom <span class="text-red-600">*</span></label>
                <input type="text" name="name" id="name" value="{{ old('name') }}" class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-800 text-sm" required />
                @error('name')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-sm font-medium mb-1" for="city">Ville <span class="text-red-600">*</span></label>
                <input type="text" name="city" id="city" value="{{ old('city') }}" class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-800 text-sm" required />
                @error('city')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-sm font-medium mb-1" for="address">Adresse <span class="text-red-600">*</span></label>
                <input type="text" name="address" id="address" value="{{ old('address') }}" class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-800 text-sm" required />
                @error('address')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-sm font-medium mb-1" for="country_id">Pays <span class="text-red-600">*</span></label>
                <select name="country_id" id="country_id" class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-800 text-sm" required>
                    <option value="">-- Sélectionner --</option>
                    @foreach($countries as $c)
                        <option value="{{ $c->id }}" @selected(old('country_id')==$c->id)>{{ $c->name }}</option>
                    @endforeach
                </select>
                @error('country_id')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-sm font-medium mb-1" for="type_id">Type <span class="text-red-600">*</span></label>
                <select name="type_id" id="type_id" class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-800 text-sm" required>
                    <option value="">-- Sélectionner --</option>
                    @foreach($types as $t)
                        <option value="{{ $t->id }}" @selected(old('type_id')==$t->id)>{{ $t->title }}</option>
                    @endforeach
                </select>
                @error('type_id')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-sm font-medium mb-1" for="phone_number">Téléphone</label>
                <input type="text" name="phone_number" id="phone_number" value="{{ old('phone_number') }}" class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-800 text-sm" />
                @error('phone_number')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-sm font-medium mb-1" for="email">Email</label>
                <input type="email" name="email" id="email" value="{{ old('email') }}" class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-800 text-sm" />
                @error('email')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-sm font-medium mb-1" for="latitude">Latitude</label>
                <input type="text" name="latitude" id="latitude" value="{{ old('latitude') }}" class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-800 text-sm" />
                @error('latitude')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-sm font-medium mb-1" for="longitude">Longitude</label>
                <input type="text" name="longitude" id="longitude" value="{{ old('longitude') }}" class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-800 text-sm" />
                @error('longitude')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
            </div>
            <div class="sm:col-span-2">
                <label class="block text-sm font-medium mb-1" for="logo">Logo (PNG/JPG, max 1MB)</label>
                <input type="file" name="logo" id="logo" accept="image/*" class="w-full text-sm" />
                @error('logo')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
            </div>
        </div>
        <div class="flex items-center gap-3 pt-2">
            <a href="{{ route('admin.schools.index') }}" class="text-sm px-4 py-2 rounded-md border border-gray-300 dark:border-gray-600 hover:bg-gray-100 dark:hover:bg-gray-700">Annuler</a>
            <button type="submit" class="inline-flex items-center gap-2 rounded-md bg-violet-600 px-5 py-2 text-sm font-medium text-white hover:bg-violet-700 focus:outline-none focus:ring-2 focus:ring-violet-500 focus:ring-offset-2 dark:focus:ring-offset-gray-900">
                <iconify-icon icon="lucide:check" width="16" height="16"></iconify-icon>
                Enregistrer
            </button>
        </div>
    </form>
</x-layouts.backend-layout>
