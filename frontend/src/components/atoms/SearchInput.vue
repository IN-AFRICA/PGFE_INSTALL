<template>
  <div class="relative" :class="wrapperClass">
    <div class="absolute left-3 top-1/2 transform -translate-y-1/2 text-foreground-muted/70">
      <span v-if="!isSearching" class="flex iconify hugeicons--search-01 text-sm"></span>
      <span v-else class="flex iconify hugeicons--loading-03 text-sm animate-spin"></span>
    </div>
    <Input
      type="text"
      :id="id"
      :name="name"
      :placeholder="placeholder"
      :model-value="modelValue"
      @update:model-value="$emit('update:modelValue', $event)"
      class="w-full ps-10 border border-gray-200/40 bg-white transition-all h-10 rounded-md"
      :class="inputClass"
    />
    <button
      v-if="modelValue && showClear"
      type="button"
      class="absolute right-3 top-1/2 transform -translate-y-1/2 text-foreground-muted/70 hover:text-foreground-muted transition-colors"
      @click="$emit('clear')"
    >
      <span class="flex iconify hugeicons--cancel-01 text-sm"></span>
    </button>
  </div>
</template>

<script setup lang="ts">
import { Input } from '@/components/ui/input'

defineProps({
  modelValue: {
    type: String,
    default: '',
  },
  placeholder: {
    type: String,
    default: 'Rechercher...',
  },
  id: {
    type: String,
    default: 'search',
  },
  name: {
    type: String,
    default: 'search',
  },
  isSearching: {
    type: Boolean,
    default: false,
  },
  showClear: {
    type: Boolean,
    default: true,
  },
  wrapperClass: {
    type: String,
    default: 'w-full max-w-xs',
  },
  inputClass: {
    type: String,
    default: '',
  },
})

defineEmits(['update:modelValue', 'clear'])
</script>
