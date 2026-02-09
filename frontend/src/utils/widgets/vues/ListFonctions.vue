<template>
  <div class="flex flex-col space-y-1.5 flex-1">
    <Label for="fonction_id" class="text-sm font-medium"> Fonctions </Label>
    <Select
      id="fonction_id"
      name="fonction_id"
      :model-value="displayValue"
      @update:modelValue="updateValue"
    >
      <SelectTrigger id="fonction_id" class="h-10 w-full">
        <SelectValue :placeholder="props.placeholder || 'Sélectionnez une fonction'" />
      </SelectTrigger>
      <SelectContent>
        <SelectGroup>
          <!-- État de chargement -->
          <SelectItem v-if="loading" value="loading" disabled>
            <div class="flex items-center gap-2">
              <span
                class="animate-spin h-4 w-4 border-2 border-gray-300 border-t-blue-600 rounded-full"
              ></span>
              Chargement des fonctions...
            </div>
          </SelectItem>

          <!-- État d'erreur -->
          <div v-else-if="error" class="flex flex-row items-center gap-2 p-2">
            <div class="flex items-center gap-2 text-red-500">
              <span class="iconify hugeicons--alert-circle"></span>
              Erreur de chargement
            </div>
            <button
              type="button"
              class="flex items-center gap-1.5 px-3 py-1.5 text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 rounded-md transition-colors"
              @click.stop="fetchData()"
            >
              <span class="iconify hugeicons--refresh"></span>
              Réessayer
            </button>
          </div>

          <!-- Liste vide -->
          <SelectItem v-else-if="!fonctions || fonctions.length === 0" value="empty" disabled>
            Aucune fonction disponible
          </SelectItem>

          <!-- Données -->
          <SelectItem
            v-else
            v-for="fonction in fonctions"
            :key="fonction.id"
            :value="String(fonction.id)"
          >
            {{ fonction.title || fonction.name || fonction.label || `Fonction #${fonction.id}` }}
          </SelectItem>
        </SelectGroup>
      </SelectContent>
    </Select>
  </div>
</template>

<script setup lang="ts">
import Label from '@/components/ui/label/Label.vue'
import Select from '@/components/ui/select/Select.vue'
import SelectContent from '@/components/ui/select/SelectContent.vue'
import SelectGroup from '@/components/ui/select/SelectGroup.vue'
import SelectItem from '@/components/ui/select/SelectItem.vue'
import SelectTrigger from '@/components/ui/select/SelectTrigger.vue'
import SelectValue from '@/components/ui/select/SelectValue.vue'
import { useGetApi } from '@/composables/useGetApi'
import { API_ROUTES } from '@/utils/constants/api_route'
import { defineProps, defineEmits, computed } from 'vue'

// Interface TypeScript pour les props
interface Props {
  modelValue?: string | number | null
  placeholder?: string
}

// Interface TypeScript pour les émissions
interface Emits {
  (e: 'update:modelValue', value: string): void
}

const props = defineProps<Props>()
const emit = defineEmits<Emits>()

const { data: fonctions, loading, error, fetchData } = useGetApi(API_ROUTES.GET_FONCTIONS)
fetchData()

// Computed pour l'affichage de la valeur
const displayValue = computed(() => {
  if (props.modelValue === null || props.modelValue === undefined || props.modelValue === '') {
    return ''
  }
  return props.modelValue.toString()
})

// Fonction de mise à jour sécurisée
function updateValue(value: any) {
  // Gérer les valeurs spéciales
  if (value === 'loading' || value === 'error' || value === 'empty') {
    return
  }
  emit('update:modelValue', value?.toString() || '')
}
</script>
