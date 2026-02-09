<template>
  <div class="flex flex-col space-y-1.5 flex-1">
    <Label for="gender" class="text-sm font-medium"> Genre </Label>
    <Select
      id="gender"
      name="gender"
      :model-value="displayValue || ''"
      @update:modelValue="updateValue"
    >
      <SelectTrigger id="gender" class="h-10 w-full">
        <SelectValue placeholder="Sélectionnez un genre" />
      </SelectTrigger>
      <SelectContent>
        <SelectGroup>
          <SelectItem value="M">Masculin</SelectItem>
          <SelectItem value="F">Féminin</SelectItem>
        </SelectGroup>
      </SelectContent>
    </Select>
  </div>
</template>

<script setup lang="ts">
import Label from '@/components/ui/label/Label.vue'
import {
  Select,
  SelectValue,
  SelectContent,
  SelectGroup,
  SelectItem,
  SelectTrigger,
} from '@/components/ui/select'
import { defineProps, defineEmits, computed } from 'vue'

const props = defineProps({
  modelValue: String,
  useFullLabel: {
    type: Boolean,
    default: false,
  },
})

const emit = defineEmits(['update:modelValue'])

const displayValue = computed(() => {
  if (props.useFullLabel && props.modelValue) {
    return props.modelValue === 'Masculin'
      ? 'M'
      : props.modelValue === 'Féminin'
        ? 'F'
        : props.modelValue
  }
  return props.modelValue || ''
})

function updateValue(value: string) {
  if (props.useFullLabel) {
    const fullValue = value === 'M' ? 'Masculin' : value === 'F' ? 'Féminin' : value
    emit('update:modelValue', fullValue || '')
  } else {
    emit('update:modelValue', value || '')
  }
}
</script>
