<template>
  <div class="flex items-center gap-3 justify-between mb-4">
    <!-- Search Bar -->
    <div class="relative flex-1 max-w-md">
      <Input
        type="text"
        :value="searchQuery"
        @input="$emit('update:searchQuery', $event.target.value)"
        placeholder="Rechercher par nom ou code..."
        class="pl-10"
      />
      <span
        class="iconify hugeicons--search-01 absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"
      ></span>
    </div>

    <div class="flex items-center gap-2">
      <!-- Delete Selected Button -->
      <Button
        v-if="selectedCount > 0"
        variant="destructive"
        size="sm"
        @click="$emit('delete-selected')"
        :disabled="isDeleting"
      >
        <span class="iconify hugeicons--delete-02 mr-2"></span>
        Supprimer ({{ selectedCount }})
      </Button>

      <!-- Export Button -->
      <DropdownMenu>
        <DropdownMenuTrigger as-child>
          <Button variant="outline" size="sm">
            <span class="iconify hugeicons--download-01 mr-2"></span>
            Exporter
          </Button>
        </DropdownMenuTrigger>
        <DropdownMenuContent>
          <DropdownMenuItem>
            <span class="iconify hugeicons--file-02 mr-2"></span>
            Excel
          </DropdownMenuItem>
          <DropdownMenuItem>
            <span class="iconify hugeicons--file-02 mr-2"></span>
            PDF
          </DropdownMenuItem>
        </DropdownMenuContent>
      </DropdownMenu>

      <!-- Create Button (passed as slot) -->
      <slot name="create"></slot>
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
  isDeleting: boolean
}

interface Emits {
  (e: 'update:searchQuery', value: string): void
  (e: 'delete-selected'): void
}

defineProps<Props>()
defineEmits<Emits>()
</script>
