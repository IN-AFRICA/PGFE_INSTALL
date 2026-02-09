<script setup lang="ts">
import { modulesNavigation } from '@/data/navigations'
import DashSibarNavigation from '../molecules/DashSibarNavigation.vue'
import DashHeader from '../molecules/DashHeader.vue'
import { TooltipProvider } from '@/components/ui/tooltip'

defineProps({
  moduleName: {
    type: String as () => keyof typeof modulesNavigation,
    required: true,
  },
  activeRoute: {
    type: String,
    required: true,
  },
  currentMode: {
    type: String as () => 'formel' | 'non-formel',
    default: 'formel',
  },
  nonformelExists: {
    type: Boolean,
    default: true,
  },
  showSwitcher: {
    type: Boolean,
    default: false,
  },
  nonFormelLink: {
    type: String,
    default: '/',
  },
})
</script>
<template>
  <TooltipProvider>
    <DashSibarNavigation :activeRoute="activeRoute" :module-name="moduleName" />
    <div class="w-full h-max md:pl-24 pb-8">
      <DashHeader
        :active-route="activeRoute"
        :nonFormelLink="nonFormelLink"
        :current-mode="currentMode"
        :informel-exists="nonformelExists"
        :show-switcher="showSwitcher"
      />
      <div aria-hidden="true" class="h-16 md:hidden flex"></div>
      <slot />
    </div>
  </TooltipProvider>
</template>
