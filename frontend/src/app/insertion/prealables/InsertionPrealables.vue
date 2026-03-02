<script setup lang="ts">
import { computed } from 'vue'
import { useRoute } from 'vue-router'
import BoxPanelWrapper from '@/components/atoms/BoxPanelWrapper.vue'
import DashPageHeader from '@/components/templates/DashPageHeader.vue'
import DashLayout from '@/components/templates/DashLayout.vue'
import { insertionNavPrealables } from '@/components/templates/insertion/insertions_tags_links'

const route = useRoute()

const breadcrumbItems = {
  items: [
    { label: 'Accueil', href: '/', icon: 'hugeicons--home-01' },
    { label: 'Insertion', href: '/insertion' },
    { label: 'Préalables', isActive: true },
  ],
}

const activeTagName = computed(() => {
  const currentPath = route.path
  const matchedTag = insertionNavPrealables.find((tag) => tag.href === currentPath)
  return matchedTag ? matchedTag.name : ''
})

</script>

<template>
  <DashLayout :breadcrumb="breadcrumbItems" active-route="/insertion/prealables" module-name="insertion">
    <div class="pb-6 mx-auto w-full max-w-7xl h-full flex flex-col">
      <DashPageHeader
        title="Préalables Insertion"
        :tags="insertionNavPrealables"
        :active-tag-name="activeTagName"
      />

      <BoxPanelWrapper class="flex-1 flex flex-col min-h-0">
        <div class="flex flex-col items-center justify-center h-full py-10 bg-white rounded-md text-gray-500">
          <span class="iconify hugeicons--package-open text-4xl mb-2"></span>
          <span>Bientôt disponible.</span>
        </div>
      </BoxPanelWrapper>
    </div>
  </DashLayout>
</template>
