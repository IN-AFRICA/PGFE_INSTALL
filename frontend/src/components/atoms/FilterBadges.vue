<script setup lang="ts">
import { Button } from '@/components/ui/button'
import { computed } from 'vue'

// Interface pour définir un badge de filtre
export interface FilterBadge {
  key: string
  label: string
  value: any
}

// Interface pour les données de référence (pour résoudre les IDs en noms)
export interface FilterReferenceData {
  [key: string]: Array<{ id: number | string; name: string; label?: string }>
}

// Props du composant
interface Props {
  filters: Record<string, any>
  referenceData?: FilterReferenceData
  customLabels?: Record<string, (value: any, data?: any) => string>
}

const props = withDefaults(defineProps<Props>(), {
  referenceData: () => ({}),
  customLabels: () => ({}),
})

// Événements émis
const emit = defineEmits<{
  removeFilter: [key: string]
}>()

// Computed pour générer les badges actifs
const activeFilterTags = computed(() => {
  const tags: FilterBadge[] = []

  Object.entries(props.filters).forEach(([key, value]) => {
    if (value === undefined || value === null || value === '') return

    let label = ''

    // Vérifier s'il y a un label personnalisé
    if (props.customLabels[key]) {
      label = props.customLabels[key](value, props.referenceData[key])
    }
    // Sinon, essayer de résoudre avec les données de référence
    else if (props.referenceData[key]) {
      const item = props.referenceData[key].find(
        (item: any) => item.id == value || item.id === value,
      )
      if (item) {
        label = item.name || item.label || String(value)
      } else {
        label = String(value)
      }
    }
    // Sinon, utiliser la valeur directement
    else {
      label = String(value)
    }

    if (label) {
      tags.push({ key, label, value })
    }
  })

  return tags
})

// Fonction pour supprimer un filtre
const handleRemoveFilter = (key: string) => {
  emit('removeFilter', key)
}
</script>

<template>
  <div v-if="activeFilterTags.length" class="flex flex-wrap gap-2">
    <Button
      v-for="(tag, index) in activeFilterTags"
      :key="index"
      size="sm"
      type="button"
      class="rounded-full gap-2 bg-primary-500 text-white hover:bg-primary-600 transition-colors"
      @click="handleRemoveFilter(tag.key)"
    >
      {{ tag.label }}
      <span class="flex iconify hugeicons--cancel-01"></span>
    </Button>
  </div>
</template>
