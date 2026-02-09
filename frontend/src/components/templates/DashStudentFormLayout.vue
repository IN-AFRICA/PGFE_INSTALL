<script setup lang="ts">
import DashLayout from '@/components/templates/DashLayoutWithMode.vue'
import CAnimationWrapper from '../atoms/CAnimationWrapper.vue'
import { computed } from 'vue'
import { Button } from '../ui/button'
import FormPageHeader from '../atoms/FormPageHeader.vue'

const props = defineProps<{
  group: 'saisie' | 'operations' | 'saisieNonformel' | 'operationNonFormel'
  activeTagName: string
  noAnimatedWrapper?: boolean
  currentMode?: 'formel' | 'non-formel'
  linkBack: string
  title: string
}>()

const routeGroups = {
  saisie: '/apprenants/saisie-prealable',
  saisieNonformel: '/apprenants/saisie-prealable',
  operations: '/apprenants/operations',
  operationNonFormel: '/apprenants/operations',
}

const nonFormelLinks = {
  saisie: '/apprenants/formations',
  operations: '/apprenants/inscriptions',
  saisieNonformel: '/apprenants/formations',
  operationNonFormel: '/apprenants/inscriptions',
}

const activeRouteGroup = computed(() => routeGroups[props.group])
const nonFormelLink = computed(() => nonFormelLinks[props.group])
</script>

<template>
  <DashLayout
    :current-mode="currentMode"
    :active-route="activeRouteGroup"
    module-name="students"
    :non-formel-link="nonFormelLink"
  >
    <CAnimationWrapper class="flex flex-col space-y-2">
      <FormPageHeader :title="title" :link-back="linkBack" />
      <div class="flex pt-1"></div>

      <slot />
    </CAnimationWrapper>
  </DashLayout>
</template>
