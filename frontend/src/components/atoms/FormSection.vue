<script lang="ts" setup>
import { cn } from '@/lib/utils'
import BoxPanelWrapper from './BoxPanelWrapper.vue'

const props = defineProps<{ class?: string; title: string; wrapper?: string }>()
</script>
<template>
  <div :class="cn('flex flex-col w-full', wrapper ?? '')">
    <h4 class="font-medium text-sm text-foreground-muted mb-3">
      {{ title }}
    </h4>
    <BoxPanelWrapper
      :class="cn('flex flex-col w-full md:p-6 lg:p-10', props.class ?? '')"
      wrapper="mt-0 h-max lg:min-h-0 before:bg-white bg-muted"
    >
      <slot />
    </BoxPanelWrapper>
  </div>
</template>
