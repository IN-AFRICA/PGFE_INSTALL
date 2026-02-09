<script setup lang="ts">
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from '@/components/ui/select'
import PresenceStatusBadge, { type PresenceStatus } from './PresenceStatusBadge.vue'

defineProps<{
  modelValue: PresenceStatus
  loading?: boolean
  disabled?: boolean
}>()

const emit = defineEmits<{
  'update:modelValue': [value: PresenceStatus]
}>()

const handleChange = (value: unknown) => {
  if (value && typeof value === 'string') {
    emit('update:modelValue', value as PresenceStatus)
  }
}

const statuses: PresenceStatus[] = ['present', 'absent', 'absent_justified', 'sick']
</script>

<template>
  <div class="flex items-center gap-2">
    <Select
      :model-value="modelValue"
      @update:model-value="handleChange"
      :disabled="disabled || loading"
    >
      <SelectTrigger class="w-[200px] h-9">
        <SelectValue>
          <PresenceStatusBadge :status="modelValue" />
        </SelectValue>
      </SelectTrigger>
      <SelectContent>
        <SelectItem v-for="status in statuses" :key="status" :value="status">
          <div class="flex items-center gap-2">
            <PresenceStatusBadge :status="status" />
          </div>
        </SelectItem>
      </SelectContent>
    </Select>
    <span v-if="loading">
      <span
        class="animate-spin rounded-full h-4 w-4 border-t-2 border-b-2 border-primary-500"
      ></span>
    </span>
  </div>
</template>
