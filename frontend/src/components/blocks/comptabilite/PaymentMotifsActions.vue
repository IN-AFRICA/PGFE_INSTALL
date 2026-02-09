<template>
  <div class="flex items-center gap-3 justify-between mb-4">
    <!-- Barre de recherche -->
    <div class="relative flex-1 max-w-md">
      <Input
        type="text"
        :model-value="searchQuery"
        @update:model-value="(value) => $emit('update:search-query', String(value))"
        placeholder="Rechercher par nom, code ou description..."
        class="pl-10"
      />
      <span
        class="iconify hugeicons--search-01 absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"
      ></span>
    </div>

    <div class="flex items-center gap-2">
      <!-- Actions groupées -->
      <Button
        v-if="selectedCount > 0"
        variant="destructive"
        size="sm"
        @click="$emit('delete-selected')"
        :disabled="deleteLoading"
      >
        <span class="iconify hugeicons--delete-02 mr-2"></span>
        Supprimer ({{ selectedCount }})
      </Button>

      <!-- Bouton d'export -->
      <DropdownMenu>
        <DropdownMenuTrigger as-child>
          <Button variant="outline" size="sm">
            <span class="iconify hugeicons--download-01 mr-2"></span>
            Exporter
          </Button>
        </DropdownMenuTrigger>
        <DropdownMenuContent>
          <DropdownMenuItem @click="$emit('export', 'excel')">
            <span class="iconify hugeicons--file-02 mr-2"></span>
            Excel
          </DropdownMenuItem>
          <DropdownMenuItem @click="$emit('export', 'pdf')">
            <span class="iconify hugeicons--file-02 mr-2"></span>
            PDF
          </DropdownMenuItem>
        </DropdownMenuContent>
      </DropdownMenu>

      <!-- Bouton de création -->
      <Button size="md" @click="$emit('create-motif')" class="bg-primary text-primary-foreground">
        <span class="iconify hugeicons--add-01 mr-2"></span>
        Nouveau motif
      </Button>
    </div>
  </div>
</template>

<script setup lang="ts">
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import {
  DropdownMenu,
  DropdownMenuContent,
  DropdownMenuItem,
  DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu'

interface Props {
  searchQuery: string
  selectedCount: number
  deleteLoading?: boolean
}

interface Emits {
  (e: 'update:search-query', value: string): void
  (e: 'delete-selected'): void
  (e: 'export', format: 'excel' | 'pdf'): void
  (e: 'create-motif'): void
}

withDefaults(defineProps<Props>(), {
  deleteLoading: false,
})

defineEmits<Emits>()
</script>
