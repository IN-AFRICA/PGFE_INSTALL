<script setup lang="ts">
import RightDashHeader from '@/components/atoms/RightDashHeader.vue'
import { modulesNavigation } from '@/data/navigations'
import { cn } from '@/lib/utils'

const props = defineProps<{
  moduleName: keyof typeof modulesNavigation
  activeRoute: string
}>()

const items = modulesNavigation[props.moduleName]
</script>

<template>
  <header
    class="flex items-center mx-auto px-4 sm:px-8 lg:px-4 lg:max-w-[85rem] h-20 justify-between gap-5"
  >
    <router-link to="/">
      <img src="/pgfe-logo.png" alt="pattern background" class="size-18 object-cover" />
    </router-link>

    <RightDashHeader />
  </header>
</template>
