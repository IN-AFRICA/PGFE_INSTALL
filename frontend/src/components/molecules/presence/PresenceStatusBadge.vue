<script setup lang="ts">
import { Badge } from '@/components/ui/badge'
import { computed } from 'vue'

export type PresenceStatus = 'present' | 'absent' | 'absent_justified' | 'sick'

interface Props {
  status: PresenceStatus
}

const props = defineProps<Props>()

const statusConfig = computed(() => {
  const configs: Record<PresenceStatus, { label: string; color: string }> = {
    present: { label: 'Présent', color: 'bg-green-100 text-green-800 border-green-200' },
    absent: { label: 'Absent Non Justifié', color: 'bg-red-100 text-red-800 border-red-200' },
    absent_justified: {
      label: 'Absent Justifié',
      color: 'bg-orange-100 text-orange-800 border-orange-200',
    },
    sick: { label: 'Malade', color: 'bg-blue-100 text-blue-800 border-blue-200' },
  }
  return configs[props.status] || configs.absent
})
</script>

<template>
  <Badge :class="statusConfig.color" class="px-2 py-1 text-xs font-medium border">
    {{ statusConfig.label }}
  </Badge>
</template>
