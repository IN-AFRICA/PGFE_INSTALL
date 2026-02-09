<script setup lang="ts">
import DashPageHeader from '@/components/templates/DashPageHeader.vue'
import { tagRhNavSaisie } from './rh-tags-links'

import { computed } from 'vue'
import DashLayout from '../DashLayout.vue'
import CAnimationWrapper from '@/components/atoms/CAnimationWrapper.vue'
import type { BreadcrumbProps } from '@/types'

const props = defineProps<{
  group: 'saisie'
  activeTagName: string
  noAnimatedWrapper?: boolean
  activeBread: string
}>()

const groups = {
  saisie: tagRhNavSaisie,
}

const routeGroups = {
  saisie: '/rh/saisie/personnel',
  operations: '/rh/operations',
}

const breadcrumbItems: BreadcrumbProps = {
  items: [
    { label: 'Accueil', href: '/', icon: 'hugeicons--home-01' },
    { label: 'GRH', href: '/rh' },
    { label: props.activeBread, isActive: true },
  ],
}

const activeGroupTags = computed(() => groups[props.group])
const activeRouteGroup = computed(() => routeGroups[props.group])
</script>

<template>
  <DashLayout :active-route="activeRouteGroup" module-name="rh" :breadcrumb="breadcrumbItems">
    <CAnimationWrapper no-animated>
      <div class="pb-6 mx-auto w-full max-w-7xl">
        <DashPageHeader
          title="Enseignement formel"
          :tags="activeGroupTags"
          :active-tag-name="activeTagName"
        />
        <CAnimationWrapper :no-animated="noAnimatedWrapper" as="div">
          <slot />
        </CAnimationWrapper>
      </div>
    </CAnimationWrapper>
  </DashLayout>
</template>
