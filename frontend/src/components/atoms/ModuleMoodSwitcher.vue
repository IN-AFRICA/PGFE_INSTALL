<script lang="ts" setup>
import { cn } from '@/lib/utils'

defineProps<{
  currentMode: 'formel' | 'non-formel'
  currentRoute: string
  isMobile: boolean
  informelExists?: boolean
  activeRoute: string
}>()
</script>
<template>
  <div v-if="!isMobile" class="hidden md:flex">
    <div
      class="w-max flex items-center bg-white shadow-lg shadow-gray-200/20 [--card-padding:calc(var(--spacing)*1)] [--card-radius:var(--radius-lg)] rounded-(--card-radius) p-(--card-padding)"
    >
      <router-link
        :to="currentRoute"
        :class="
          cn(
            'flex flex-col lg:flex-row justify-center text-center text-nowrap w-full items-center gap-1 px-10 py-2 raduis-inner text-foreground-muted ease-linear transition-colors',
            {
              'bg-primary text-white': currentMode === 'formel',
              'hover:bg-muted': currentMode !== 'formel',
            },
          )
        "
      >
        Formel
      </router-link>
      <router-link
        :to="`/non-formel${activeRoute}`"
        :class="
          cn(
            'flex flex-col lg:flex-row justify-center text-center text-nowrap w-full items-center gap-1 px-10 py-2 raduis-inner text-foreground-muted ease-linear transition-colors',
            {
              'bg-primary text-white': currentMode === 'non-formel',
              'hover:bg-muted': currentMode !== 'non-formel',
              'cursor-not-allowed opacity-50': !informelExists,
            },
          )
        "
        :disabled="!informelExists"
      >
        Non formel
      </router-link>
    </div>
  </div>
  <div v-if="isMobile" class="flex md:hidden"></div>
</template>
