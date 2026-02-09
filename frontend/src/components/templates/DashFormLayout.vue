<script setup lang="ts">
import DashLayout from './DashLayout.vue'
import CAnimationWrapper from '@/components/atoms/CAnimationWrapper.vue'
import type { BreadcrumbItem, BreadcrumbProps } from '@/types'
import FormPageHeader from '../atoms/FormPageHeader.vue'

const props = defineProps<{
  linkBack: string
  title: string
  groupRoute: string
  module: 'students' | 'rh' | 'admin' | 'compta' | 'stock' | 'infra'
  noAnimatedWrapper?: boolean
  breadcrumb: BreadcrumbItem[]
}>()

const breadcrumbItems: BreadcrumbProps = {
  items: [{ label: 'Accueil', href: '/', icon: 'hugeicons--home-01' }, ...props.breadcrumb],
}
</script>

<template>
  <DashLayout :active-route="groupRoute" :module-name="module" :breadcrumb="breadcrumbItems">
    <CAnimationWrapper no-animated>
      <div class="pb-6 mx-auto w-full max-w-7xl space-y-2">
        <FormPageHeader :title="title" :link-back="linkBack" />
        <div class="flex pt-1"></div>
        <CAnimationWrapper :no-animated="noAnimatedWrapper" as="div">
          <slot />
        </CAnimationWrapper>
      </div>
    </CAnimationWrapper>
  </DashLayout>
</template>
