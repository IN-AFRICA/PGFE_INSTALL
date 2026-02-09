<template>
  <p class="flex items-center mb-2">
    <span class="mr-2">{{ label }}:</span>
    <component
      :is="type === 'select' ? 'select' : 'input'"
      :value="modelValue"
      @input="$emit('update:modelValue', ($event.target as HTMLInputElement).value)"
      @change="$emit('update:modelValue', ($event.target as HTMLSelectElement).value)"
      :class="inputClasses"
      :type="type === 'input' ? 'text' : undefined"
    >
      <option
        v-if="type === 'select' && options"
        v-for="option in options"
        :key="option.value"
        :value="option.value"
      >
        {{ option.label }}
      </option>
      <slot v-if="type === 'select' && !options"></slot>
    </component>
    <slot name="after"></slot>
  </p>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import type { SelectOption } from '@/types/BulletinType.ts'

interface Props {
  label: string
  modelValue: string
  type?: 'input' | 'select'
  options?: SelectOption[]
  width?: 'full' | 'auto' | string
}

const props = withDefaults(defineProps<Props>(), {
  type: 'input',
  width: 'full',
})

defineEmits<{
  'update:modelValue': [value: string]
}>()

const inputClasses = computed(() => {
  const baseClasses = 'bg-transparent focus:outline-none border-0'

  if (props.width === 'full') {
    return `flex-grow ${baseClasses}`
  } else if (props.width === 'auto') {
    return baseClasses
  } else {
    return `${props.width} ${baseClasses}`
  }
})
</script>
