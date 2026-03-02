<script setup lang="ts">
import { computed } from 'vue'
import { useRoute } from 'vue-router'
import BoxPanelWrapper from '@/components/atoms/BoxPanelWrapper.vue'
import DashPageHeader from '@/components/templates/DashPageHeader.vue'
import DashLayout from '@/components/templates/DashLayout.vue'
import { insertionNavOperations } from '@/components/templates/insertion/insertions_tags_links'

const route = useRoute()

const breadcrumbItems = {
  items: [
    { label: 'Accueil', href: '/', icon: 'hugeicons--home-01' },
    { label: 'Insertion', href: '/insertion' },
    { label: 'Opérations', isActive: true },
  ],
}

const activeTagName = computed(() => {
  const currentPath = route.path
  const matchedTag = insertionNavOperations.find((tag) => tag.href === currentPath)
  return matchedTag ? matchedTag.name : ''
})

</script>

<template>
  <DashLayout :breadcrumb="breadcrumbItems" active-route="/insertion/operations" module-name="insertion">
    <div class="pb-6 mx-auto w-full max-w-7xl h-full flex flex-col">
      <DashPageHeader
        title="Opérations Insertion"
        :tags="insertionNavOperations"
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
