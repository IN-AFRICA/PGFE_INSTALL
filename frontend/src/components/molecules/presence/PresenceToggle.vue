<script setup lang="ts">
defineProps<{
  checked: boolean
  loading?: boolean
  disabled?: boolean
}>()

const emit = defineEmits<{
  change: [value: boolean]
}>()

const handleChange = (event: Event) => {
  const target = event.target as HTMLInputElement
  emit('change', target.checked)
}
</script>

<template>
  <div class="flex items-center gap-2">
    <label class="relative inline-flex items-center cursor-pointer group">
      <input
        type="checkbox"
        :checked="checked"
        @change="handleChange"
        class="sr-only peer"
        :disabled="disabled || loading"
      />
      <div
        class="w-10 h-6 bg-gray-200 rounded-full peer-checked:bg-primary transition-colors duration-200 group-hover:ring-2 group-hover:ring-primary-300"
      ></div>
      <div
        class="absolute left-1 top-1 w-4 h-4 bg-white rounded-full shadow transition-transform duration-200 peer-checked:translate-x-4"
      ></div>
      <span v-if="loading" class="absolute right-[-24px]">
        <span
          class="animate-spin rounded-full h-4 w-4 border-t-2 border-b-2 border-primary-500"
        ></span>
      </span>
    </label>
  </div>
</template>
