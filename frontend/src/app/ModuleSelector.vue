<script lang="ts" setup>
import CAnimationWrapper from '@/components/atoms/CAnimationWrapper.vue'
import ModuleCard from '@/components/atoms/ModuleCard.vue'
import { modulesItems } from '@/data/navigations'
import DashSibarNavigation from '@/components/molecules/DashSibarNavigation.vue'
import { useAuthStore } from '@/stores/auth'
import { computed } from 'vue'

const authStore = useAuthStore()

const filteredModules = computed(() => {
  return modulesItems.filter((item) => {
    if (!item.permission) return true
    return authStore.can(item.permission)
  })
})
</script>

<template>
  <div class="relative px-4 sm:px-8 lg:px-4 xl:px-0">
    <div class="fixed inset-0 pointer-events-none select-none">
      <img src="/screen-pattern.png" alt="pattern background" class="size-full object-cover" />
    </div>
    <CAnimationWrapper class="relative max-w-4xl mx-auto w-full py-6" as="div">
      <div class="flex justify-center mb-8">
        <img src="/pgfe-logo.png" width="500" class="h-32 w-auto" />
      </div>
      <div class="mb-4">
        <h2 class="text-2xl font-semibold text-fg-title uppercase mb-4">Selectionnez le module</h2>
      </div>
      <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-5 lg:gap-6">
        <ModuleCard
          v-for="item in filteredModules"
          :key="item.id"
          :is-active="item.id === 1"
          v-bind="item"
        />
      </div>
    </CAnimationWrapper>
    <DashSibarNavigation :onlySettings="true" />
  </div>
</template>

