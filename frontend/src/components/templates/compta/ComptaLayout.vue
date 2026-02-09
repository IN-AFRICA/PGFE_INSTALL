<script setup lang="ts">
import DashPageHeader from '@/components/templates/DashPageHeader.vue'
import { tagComptaNavSaisie, tagComptaNavOperations, tagComptaNavFrais } from './compta-tags-links'

import { computed } from 'vue'
import DashLayout from '../DashLayout.vue'
import CAnimationWrapper from '@/components/atoms/CAnimationWrapper.vue'
import type { BreadcrumbProps } from '@/types'

const props = withDefaults(
  defineProps<{
    group: 'saisie' | 'operations' | 'frais'
    activeTagName: string
    noAnimatedWrapper?: boolean
    activeBread: string
    showHeader?: boolean
  }>(),
  {
    showHeader: true,
  },
)

const groups = {
  saisie: tagComptaNavSaisie,
  operations: tagComptaNavOperations,
  frais: tagComptaNavFrais,
}

const titles = {
  saisie: 'Saisie préalable',
  operations: 'Saisie des opérations',
  frais: 'Frais scolaires',
}

const routeGroups = {
  saisie: '/comptabilite/saisie-prealable',
  operations: '/comptabilite/saisie-operations',
  frais: '/comptabilite/frais',
}

const breadcrumbItems: BreadcrumbProps = {
  items: [
    { label: 'Accueil', href: '/', icon: 'hugeicons--home-01' },
    { label: 'Comptabilité', href: '/comptabilite' },
    { label: props.activeBread, isActive: true },
  ],
}

const activeGroupTags = computed(() => groups[props.group])
const activeRouteGroup = computed(() => routeGroups[props.group])
const pageTitle = computed(() => titles[props.group])
</script>

<template>
  <DashLayout :active-route="activeRouteGroup" module-name="compta" :breadcrumb="breadcrumbItems">
    <CAnimationWrapper no-animated>
      <div class="pb-6 mx-auto w-full max-w-7xl">
        <DashPageHeader
          v-if="props.showHeader"
          :title="pageTitle"
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
