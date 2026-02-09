<script setup lang="ts">
import { cn } from '@/lib/utils'
import { motion } from 'motion-v'

const props = defineProps<{
  noAnimated?: boolean
  as?: 'div' | 'main'
  class?: string | string[] | Record<string, boolean>
}>()

const tag = props.as ?? 'main'

const classIsMain = tag === 'main' ? 'w-full mx-auto max-w-7xl px-4 sm:px-8 md:px-10 lg:px-12' : ''
</script>
<template>
  <component
    :is="motion[tag]"
    v-if="!noAnimated"
    key="page"
    :initial="{
      opacity: 0,
      y: 12,
      filter: 'blur(4px)',
    }"
    :animate="{
      opacity: 1,
      y: 0,
      filter: 'blur(0px)',
    }"
    :transition="{
      duration: 0.6,
      ease: [0.25, 0.1, 0.25, 1],
      delay: 0.05,
    }"
    :class="cn(classIsMain, props.class)"
  >
    <slot />
  </component>
  <component :is="tag" v-else :class="cn(classIsMain, props.class)">
    <slot />
  </component>
</template>
