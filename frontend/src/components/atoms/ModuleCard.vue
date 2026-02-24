<script lang="ts" setup>
import { cn } from '@/lib/utils'

const props = defineProps<{
  link: string
  icon: string
  text: string
  isActive?: boolean
  id?: number
}>()

const emit = defineEmits<{
  click: []
}>()

const handleClick = (e: Event) => {
  // Uniquement pour le module Synchroniser (id: 10)
  if (props.id === 10) {
    e.preventDefault()
    emit('click')
  }
}
</script>
<template>
  <RouterLink
    :to="link"
    @click="handleClick"
    :class="
      cn(
        'flex flex-col items-center p-4 md:p-6 rounded-lg group transition-all duration-200 ease-linear',
        'hover:bg-primary hover:text-white',
        'bg-white border border-border/50 shadow-lg shadow-gray-200/30 hover:shadow-xl text-foreground-muted',
      )
    "
  >
    <span aria-hidden="true" :class="['text-4xl md:text-5xl flex iconify', icon]" />
    <span class="text-center select-none mt-4 font-medium">{{ text }}</span>
  </RouterLink>
</template>
