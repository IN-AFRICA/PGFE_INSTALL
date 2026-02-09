<script setup lang="ts">
import ModuleMoodSwitcher from '../atoms/ModuleMoodSwitcher.vue'
import RightDashHeader from '../atoms/RightDashHeader.vue'

defineProps({
  currentMode: {
    type: String as () => 'formel' | 'non-formel',
    default: 'formel',
  },
  activeRoute: {
    type: String,
    required: true,
  },
  informelExists: {
    type: Boolean,
    default: true,
  },
  showSwitcher: {
    type: Boolean,
    default: true,
  },
  nonFormelLink: {
    type: String,
    required: true,
  },
})
</script>
<template>
  <header class="flex items-center fixed z-50 md:sticky top-0 w-full">
    <div v-if="showSwitcher" class="hidden w-full md:flex items-center justify-center py-4">
      <ModuleMoodSwitcher
        :current-route="activeRoute"
        :active-route="nonFormelLink"
        :current-mode="currentMode"
        :is-mobile="false"
        :informel-exists="informelExists"
      />
    </div>
    <nav
      class="flex justify-between items-center h-16 border-b border-border/60 w-full bg-white px-4 sm:px-8 md:hidden"
    >
      <div>
        <router-link to="/">
          <img src="/pgfe-logo.png" alt="pattern background" class="size-18 object-cover" />
        </router-link>
      </div>
      <RightDashHeader />
    </nav>
  </header>
</template>
