@props([
  'editUrl' => null,
  'deleteUrl' => null,
  'deleteMethod' => 'DELETE',
  'confirm' => 'Confirmer la suppression ?'
])
<div class="flex items-center gap-2">
  @if($editUrl)
    <a href="{{ $editUrl }}" class="inline-flex h-8 w-8 items-center justify-center rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-600 dark:text-gray-300 hover:bg-violet-50 dark:hover:bg-violet-600/20 hover:text-violet-700 transition" title="Modifier">
      <iconify-icon icon="lucide:pen" width="16" height="16"></iconify-icon>
      <span class="sr-only">Modifier</span>
    </a>
  @endif
  @if($deleteUrl)
    <form method="POST" action="{{ $deleteUrl }}" onsubmit="return confirm(this.dataset.confirm);" data-confirm="{{ $confirm }}" class="inline">
      @csrf
      @method($deleteMethod)
      <button type="submit" class="inline-flex h-8 w-8 items-center justify-center rounded-md border border-red-300 dark:border-red-600 bg-white dark:bg-gray-800 text-red-600 hover:bg-red-50 dark:hover:bg-red-600/20 hover:text-red-700 transition" title="Supprimer">
        <iconify-icon icon="lucide:trash-2" width="16" height="16"></iconify-icon>
        <span class="sr-only">Supprimer</span>
      </button>
    </form>
  @endif
</div>

