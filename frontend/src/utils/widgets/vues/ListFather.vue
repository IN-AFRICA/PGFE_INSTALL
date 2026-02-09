<template>
  <div class="flex flex-col space-y-1.5 flex-1">
    <Label for="father_id" class="text-sm font-medium"> Père </Label>
    <Select
      id="father_id"
      name="father_id"
      :model-value="displayValue"
      @update:modelValue="updateValue"
    >
      <SelectTrigger id="father_id" class="h-10 w-full">
        <SelectValue placeholder="Sélectionnez un père" />
      </SelectTrigger>
      <SelectContent>
        <SelectGroup>
          <!-- État de chargement -->
          <SelectItem v-if="loading" value="loading" disabled>
            <div class="flex items-center gap-2">
              <span
                class="animate-spin h-4 w-4 border-2 border-gray-300 border-t-blue-600 rounded-full"
              ></span>
              Chargement des pères...
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
          <SelectItem v-else-if="fathers.length === 0" value="empty" disabled>
            Aucun père disponible
          </SelectItem>

          <!-- Option pour aucun père -->
          <SelectItem value="null"> Aucun père </SelectItem>

          <!-- Liste des pères -->
          <SelectItem v-for="father in fathers" :key="father.id" :value="father.id.toString()">
            {{ getFullName(father) }}
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
import { computed } from 'vue'

// Interface pour les parents
interface Parent {
  id: number
  name: string
  firstname?: string
  gender?: string
}

// Props et émissions
interface Props {
  modelValue?: string | number | null
}

interface Emits {
  (e: 'update:modelValue', value: string): void
}

const props = defineProps<Props>()
const emit = defineEmits<Emits>()

// API pour récupérer les parents
const { data: parentsData, loading, error, fetchData } = useGetApi<Parent[]>(API_ROUTES.GET_PARENTS)
fetchData()

// Filtrer pour ne garder que les pères (genre masculin)
const fathers = computed(() => {
  const v: any = parentsData.value

  let list: any[] = []

  // Gérer différents formats de réponse API
  if (Array.isArray(v)) {
    list = v
  } else if (v?.parents && Array.isArray(v.parents)) {
    list = v.parents
  } else if (v?.data && Array.isArray(v.data)) {
    list = v.data
  } else if (v && typeof v === 'object' && !Array.isArray(v)) {
    list = Object.values(v).filter((item: any) => item && typeof item === 'object' && item.id)
  }

  // Filtrer pour ne garder que les hommes (pères potentiels)
  return list
    .filter(
      (parent: any) =>
        !parent.gender ||
        parent.gender.toLowerCase() === 'masculin' ||
        parent.gender.toLowerCase() === 'male' ||
        parent.gender.toLowerCase() === 'm',
    )
    .map((parent: any) => ({
      id: parent.id,
      name: parent.name || '',
      firstname: parent.firstname || parent.first_name || '',
    }))
})

// Valeur d'affichage pour le sélecteur
const displayValue = computed(() => {
  if (props.modelValue === null || props.modelValue === undefined || props.modelValue === '') {
    return 'null'
  }
  return props.modelValue.toString()
})

// Fonction pour obtenir le nom complet
function getFullName(parent: Parent): string {
  const firstName = parent.firstname || ''
  const lastName = parent.name || ''
  return `${firstName} ${lastName}`.trim() || `Parent #${parent.id}`
}

// Fonction de mise à jour de la valeur
function updateValue(value: any) {
  // Gérer les valeurs spéciales
  if (value === 'null' || value === 'loading' || value === 'error' || value === 'empty') {
    emit('update:modelValue', '')
    return
  }
  emit('update:modelValue', value?.toString() || '')
}
</script>
