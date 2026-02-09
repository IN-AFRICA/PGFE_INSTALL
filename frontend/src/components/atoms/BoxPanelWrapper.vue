<script setup lang="ts">
import { cn } from '@/lib/utils'

const props = defineProps<{
  wrapper?: string
  class?: string
}>()
</script>
<template>
  <div
    :class="
      cn(
        'bg-white mt-7 shadow-lg shadow-gray-100/20 h-[500px] lg:min-h-[calc(100vh-13.5rem)] relative [--card-padding:calc(var(--spacing)*1.5)] [--card-radius:var(--radius-lg)]',
        'rounded-(--card-radius) p-(--card-padding) before:absolute before:inset-2 before:rounded-[calc(var(--card-radius)_-_var(--card-padding))] before:bg-muted ',
        props.wrapper,
      )
    "
  >
    <div :class="cn('relative p-4 h-full w-full overflow-hidden flex flex-col', props.class)">
      <slot />
    </div>
  </div>
</template>
