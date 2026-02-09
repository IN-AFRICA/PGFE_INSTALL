<script setup lang="ts">
import DashLayout from '@/components/templates/DashLayoutWithMode.vue'
defineProps<{
  activeRoute: string
  linkActive: string
}>()
</script>

<template>
  <DashLayout
    current-mode="non-formel"
    :active-route="`/non-formel/${activeRoute}`"
    :non-formel-link="linkActive ?? '/formations'"
    module-name="students"
  >
    <slot />
  </DashLayout>
</template>
