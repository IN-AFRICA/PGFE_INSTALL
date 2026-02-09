<script setup lang="ts">
import DashLayoutWithMode from '@/components/templates/DashLayoutWithMode.vue'
import DashPageHeader from '@/components/templates/DashPageHeader.vue'
//import { tagNavSaisieScoolarFee } from './tags-links'
import CAnimationWrapper from '../atoms/CAnimationWrapper.vue'
import { computed } from 'vue'

const props = defineProps<{
  group: 'scolar-fee'
  activeTagName: string
  noAnimatedWrapper?: boolean
  currentMode?: 'formel' | 'non-formel'
}>()

const groups = {
  //'scolar-fee': tagNavSaisieScoolarFee,
}

const routeGroups = {
  'scolar-fee': '/apprenants/saisie-prealable',
}

const nonFormelLinks = {
  'scolar-fee': '/apprenants/scolar-fees',
}

const activeGroupTags = computed(() => groups[props.group])
const activeRouteGroup = computed(() => routeGroups[props.group])
const nonFormelLink = computed(() => nonFormelLinks[props.group])
</script>

<template>
  <DashLayoutWithMode
    :current-mode="currentMode"
    :active-route="activeRouteGroup"
    module-name="compta"
    :non-formel-link="nonFormelLink"
  >
    <CAnimationWrapper no-animated>
      <div class="pb-6 mx-auto w-full max-w-7xl">
        <DashPageHeader
          title="Frais Scolaires"
          :tags="activeGroupTags"
          :active-tag-name="activeTagName"
        />
        <CAnimationWrapper :no-animated="noAnimatedWrapper" as="div">
          <slot />
        </CAnimationWrapper>
      </div>
    </CAnimationWrapper>
  </DashLayoutWithMode>
</template>
