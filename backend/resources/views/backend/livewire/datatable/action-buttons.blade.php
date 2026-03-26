<div class="flex items-center justify-end gap-2 text-sm">
    {!! $beforeActionView !!}

    @if (isset($routes['view']) && $routes['view'] ?? false && $componentPermissions['view'] ?? false)
        <a href="{{ $viewRouteUrl }}" class="inline-flex items-center gap-1 rounded-md border border-gray-200 bg-white px-3 py-1.5 text-gray-700 shadow-sm hover:bg-gray-50">
            <iconify-icon icon="{{ $viewButtonIcon }}" class="text-base"></iconify-icon>
            <span>{{ $viewButtonLabel }}</span>
        </a>
    @endif

    {!! $afterActionView !!}

    @if (isset($routes['edit']) && $routes['edit'] ?? false && $componentPermissions['edit'] ?? false && (($componentPermissions['edit'] === true) || auth()->user()->can('update', $item)))
        <a href="{{ $editRouteUrl }}" class="inline-flex items-center gap-1 rounded-md border border-gray-200 bg-white px-3 py-1.5 text-gray-700 shadow-sm hover:bg-gray-50">
            <iconify-icon icon="{{ $editButtonIcon }}" class="text-base"></iconify-icon>
            <span>{{ $editButtonLabel }}</span>
        </a>
    @endif

    {!! $afterActionEdit !!}

    @if (isset($routes['delete']) && $routes['delete'] ?? false && (($componentPermissions['delete'] === true) || auth()->user()->can('delete', $item)))
        @if($deleteAction['livewire'] ?? false)
            <button type="button" wire:click="deleteItem({{ $item->id }})" class="inline-flex items-center gap-1 rounded-md border border-red-200 bg-red-50 px-3 py-1.5 text-red-700 shadow-sm hover:bg-red-100" onclick="return confirm('{{ __('Are you sure you want to delete this :model?', ['model' => $modelNameSingular]) }}')">
                <iconify-icon icon="{{ $deleteButtonIcon }}" class="text-base"></iconify-icon>
                <span>{{ $deleteButtonLabel }}</span>
            </button>
        @else
            <form action="{{ $deleteAction['url'] ?? $deleteRouteUrl }}" method="POST" onsubmit="return confirm('{{ __('Are you sure you want to delete this :model?', ['model' => $modelNameSingular]) }}')">
                @csrf
                @method('DELETE')
                <button type="submit" class="inline-flex items-center gap-1 rounded-md border border-red-200 bg-red-50 px-3 py-1.5 text-red-700 shadow-sm hover:bg-red-100">
                    <iconify-icon icon="{{ $deleteButtonIcon }}" class="text-base"></iconify-icon>
                    <span>{{ $deleteButtonLabel }}</span>
                </button>
            </form>
        @endif
    @endif

    {!! $afterActionDelete !!}
</div>

