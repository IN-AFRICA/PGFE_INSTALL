<template>
  <div class="flex items-center justify-between">
    <span class="font-bold text-lg">{{ label }}</span>
    <div class="flex gap-0.5">
      <input
        v-for="(value, index) in values"
        :key="index"
        :value="value"
        @input="updateValue(index, ($event.target as HTMLInputElement).value)"
        class="w-7 h-7 text-center border border-black text-[10pt]"
        :class="{ 'cursor-not-allowed pointer-events-none': readonly }"
        maxlength="1"
        type="text"
        :readonly="readonly"
        :tabindex="readonly ? '-1' : undefined"
      />
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue'

interface Props {
  label: string
  values: string[]
  count: number
  readonly?: boolean
}

const props = defineProps<Props>()

const emit = defineEmits<{
  update: [values: string[]]
}>()

const values = computed(() => {
  const result = [...props.values]
  while (result.length < props.count) {
    result.push('')
  }
  return result.slice(0, props.count)
})

const updateValue = (index: number, value: string) => {
  if (props.readonly) return
  const newValues = [...values.value]
  newValues[index] = value
  emit('update', newValues)
}
</script>
