<template>
  <div class="flex flex-col space-y-1.5 flex-1">
    <Label for="date_filter" class="text-sm font-medium">
      {{ label }}
    </Label>
    <Input
      id="date_filter"
      type="date"
      :value="modelValue || ''"
      @input="updateValue"
      class="h-10 w-full"
    />
  </div>
</template>

<script setup lang="ts">
import Label from '@/components/ui/label/Label.vue'
import { Input } from '@/components/ui/input'
import { defineProps, defineEmits } from 'vue'

const props = defineProps({
  modelValue: String,
  label: {
    type: String,
    default: 'Date',
  },
})

const emit = defineEmits(['update:modelValue'])

function updateValue(event: Event) {
  const target = event.target as HTMLInputElement
  emit('update:modelValue', target.value || undefined)
}
</script>
