<template>
  <div class="bg-white rounded-lg shadow-lg shadow-gray-100/40 p-4 md:p-5 border border-border/30">
    <div class="flex items-center">
      <div
        :style="{ background: color }"
        class="size-8 text-white flex items-center justify-center rounded-full"
        role="img"
        :aria-label="`${title} icon`"
      >
        <span :class="['iconify', icon]" aria-hidden="true" />
      </div>
      <h3 class="flex-1 flex text-foreground-subtitle ml-3">
        {{ title }}
      </h3>
    </div>
    <div class="py-3 flex text-center justify-center font-semibold text-3xl text-foreground-title">
      {{ value }}
    </div>
    <div class="flex justify-between items-center">
      <p class="text-xs text-foreground-muted flex-1 line-clamp-1">
        {{ description }}
      </p>
      <div
        :style="{ background: color }"
        class="size-6 text-white text-sm rounded-full flex items-center justify-center"
        role="img"
        aria-label="Trend indicator"
      >
        <span aria-hidden="true" class="iconify hugeicons--arrow-up-right-01"></span>
      </div>
    </div>
  </div>
</template>

<script lang="ts" setup>
import { computed } from 'vue'

const props = defineProps<{
  /** HugeIcons icon class name (e.g., 'hugeicons--user') */
  icon: string
  /** Numeric value to display */
  value: number
  /** Title/label for the statistic */
  title: string
  /** Description text */
  description: string
  /** Background color for the icon containers */
  color: string
}>()

const formattedValue = computed(() => {
  // Format large numbers with appropriate suffixes
  if (props.value >= 1000000) {
    return (props.value / 1000000).toFixed(1) + 'M'
  } else if (props.value >= 1000) {
    return (props.value / 1000).toFixed(1) + 'K'
  }
  return props.value.toLocaleString()
})
</script>
