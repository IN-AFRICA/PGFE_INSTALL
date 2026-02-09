<script setup lang="ts">
import DashLayoutWithMode from '@/components/templates/DashLayoutWithMode.vue'
import DashPageHeader from '@/components/templates/DashPageHeader.vue'
import {
  tagNavSaisie,
  tagNavSaisieNonFormel,
  tagNavSaisieOperations,
  tagNavSaisieOperationsNonFormel,
} from './tags-links'
import CAnimationWrapper from '../atoms/CAnimationWrapper.vue'
import { computed } from 'vue'

const props = defineProps<{
  group: 'saisie' | 'operations' | 'saisieNonformel' | 'operationNonFormel'
  activeTagName: string
  noAnimatedWrapper?: boolean
  currentMode?: 'formel' | 'non-formel'
  breadcrumb?: {
    label: string
    href: string
  }[]
}>()

const groups = {
  saisie: tagNavSaisie,
  operations: tagNavSaisieOperations,
  saisieNonformel: tagNavSaisieNonFormel,
  operationNonFormel: tagNavSaisieOperationsNonFormel,
}

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

const activeGroupTags = computed(() => groups[props.group])
const activeRouteGroup = computed(() => routeGroups[props.group])
const nonFormelLink = computed(() => nonFormelLinks[props.group])
</script>

<template>
  <DashLayoutWithMode
    :current-mode="currentMode"
    :active-route="activeRouteGroup"
    module-name="students"
    :non-formel-link="nonFormelLink"
  >
    <CAnimationWrapper no-animated>
      <div class="pb-6 mx-auto w-full max-w-7xl">
        <DashPageHeader
          title="Enseignement formel"
          :tags="activeGroupTags"
          :active-tag-name="activeTagName"
          :breadcrumb="breadcrumb"
        />
        <CAnimationWrapper :no-animated="noAnimatedWrapper" as="div">
          <slot />
        </CAnimationWrapper>
      </div>
    </CAnimationWrapper>
  </DashLayoutWithMode>
</template>
