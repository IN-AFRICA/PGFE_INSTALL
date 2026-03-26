@if ($errors->any())
    <div class="mb-6 rounded-lg border border-red-200 bg-red-50/90 px-4 py-3 text-sm text-red-900">
        <p class="mb-2 font-bold">Veuillez corriger les champs suivants :</p>
        <ul class="list-disc space-y-1 pl-5">
            @foreach ($errors->all() as $err)
                <li>{{ $err }}</li>
            @endforeach
        </ul>
    </div>
@endif
