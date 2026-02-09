<script setup lang="ts">
import { cn } from '@/lib/utils'
import LayoutSaisieOperation from './LayoutSaisieOperation.vue'
import CAnimationWrapper from '../atoms/CAnimationWrapper.vue'
import BoxPanelWrapper from '../atoms/BoxPanelWrapper.vue'

defineProps<{
  activeTagName: string
}>()

const items = [
  {
    name: 'index',
    text: "Autorisation de sortie de l'élève",
    href: '/apprenants/operations/gestion-disciplinaire',
    icon: 'hugeicons--user-group',
  },
  {
    name: 'indiscipline',
    text: 'Cas d’indiscipline',
    href: '/apprenants/operations/gestion-disciplinaire/indiscipline',
    icon: 'hugeicons--work-alert',
  },
  {
    name: 'note-conduite',
    text: 'Note de conduite',
    href: '/apprenants/operations/gestion-disciplinaire/note-conduite',
    icon: 'hugeicons--note-done',
  },
  {
    name: 'abandons',
    text: 'Abandons',
    href: '/apprenants/operations/gestion-disciplinaire/abandons',
    icon: 'hugeicons--user-block-01',
  },
]
</script>

<template>
  <LayoutSaisieOperation
    no-animated-wrapper
    group="operations"
    active-tag-name="gestion-disciplinaire"
  >
    <BoxPanelWrapper
      wrapper="lg:before:left-[154px] max-lg:before:top-[154px]"
      class="flex flex-col lg:grid lg:pl-1 lg:grid-cols-[110px_1fr] gap-6"
    >
      <div class="lg:-mt-4">
        <ul class="flex flex-wrap md:flex-row gap-2">
          <li v-for="item in items" :key="item.name" class="flex w-max md:w-full">
            <RouterLink
              :to="item.href"
              :class="
                cn(
                  'flex flex-col items-center justify-center text-center p-2.5 rounded-md w-full md:aspect-square ease-linear duration-300 transition-colors',
                  {
                    'bg-primary text-white': item.name === activeTagName,
                    'bg-white text-foreground-muted shadow-lg shadow-gray-100/20 ':
                      item.name !== activeTagName,
                  },
                )
              "
            >
              <span aria-hidden="true" :class="['flex iconify md:text-3xl', item.icon]"></span>
              <span class="mt-1.5 text-sm">
                {{ item.text }}
              </span>
            </RouterLink>
          </li>
        </ul>
      </div>
      <CAnimationWrapper as="div" class="flex flex-col lg:pl-6">
        <slot />
      </CAnimationWrapper>
    </BoxPanelWrapper>
  </LayoutSaisieOperation>
</template>
