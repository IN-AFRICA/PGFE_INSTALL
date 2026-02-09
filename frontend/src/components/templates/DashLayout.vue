<script setup lang="ts">
import { modulesNavigation } from '@/data/navigations'
import DashSibarNavigation from '../molecules/DashSibarNavigation.vue'
import { TooltipProvider } from '@/components/ui/tooltip'
import GlobalDashHeader from '../molecules/GlobalDashHeader.vue'
import AppBreadcrumb from '../molecules/AppBreadcrumb.vue'
import type { BreadcrumbProps } from '@/types'
import { Input } from '../ui/input'

defineProps({
  moduleName: {
    type: String as () => keyof typeof modulesNavigation,
    required: true,
  },
  activeRoute: {
    type: String,
    required: true,
  },
  breadcrumb: {
    type: Object as () => BreadcrumbProps,
    required: true,
  },
})
</script>
<template>
  <TooltipProvider>
    <DashSibarNavigation :activeRoute="activeRoute" :module-name="moduleName" />
    <div class="w-full h-max md:pl-24 pb-8">
      <GlobalDashHeader />
      <div
        class="flex flex-col-reverse md:justify-between md:items-center md:flex-row pt-8 lg:pt-2 mx-auto max-w-7xl px-4 sm:px-8 md:px-10 lg:px-12"
      >
        <div class="flex flex-col max-w-2xl">
          <AppBreadcrumb
            :items="breadcrumb.items"
            :home-icon="breadcrumb.homeIcon"
            :separator-icon="breadcrumb.separatorIcon"
          />
        </div>
      </div>
      <div aria-hidden="true" class="h-16 md:h-8 flex"></div>
      <slot />
    </div>
  </TooltipProvider>
</template>
