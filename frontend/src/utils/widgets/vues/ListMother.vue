<template>
  <div class="flex flex-col space-y-1.5 flex-1">
    <Label for="mother_id" class="text-sm font-medium"> Mère </Label>
    <Select
      id="mother_id"
      name="mother_id"
      :model-value="displayValue"
      @update:modelValue="updateValue"
    >
      <SelectTrigger id="mother_id" class="h-10 w-full">
        <SelectValue placeholder="Sélectionnez une mère" />
      </SelectTrigger>
      <SelectContent>
        <SelectGroup>
          <!-- État de chargement -->
          <SelectItem v-if="loading" value="loading" disabled>
            <div class="flex items-center gap-2">
              <span
                class="animate-spin h-4 w-4 border-2 border-gray-300 border-t-blue-600 rounded-full"
              ></span>
              Chargement des mères...
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
          <SelectItem v-else-if="mothers.length === 0" value="empty" disabled>
            Aucune mère disponible
          </SelectItem>

          <!-- Option pour aucune mère -->
          <SelectItem value="null"> Aucune mère </SelectItem>

          <!-- Liste des mères -->
          <SelectItem v-for="mother in mothers" :key="mother.id" :value="mother.id.toString()">
            {{ getFullName(mother) }}
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

// Filtrer pour ne garder que les mères (genre féminin)
const mothers = computed(() => {
  const v: unknown = parentsData.value

  let list: Parent[] = []

  // Gérer différents formats de réponse API
  if (Array.isArray(v)) {
    list = v as Parent[]
  } else if (v && typeof v === 'object' && 'parents' in v) {
    const parentObj = v as { parents: Parent[] }
    if (Array.isArray(parentObj.parents)) {
      list = parentObj.parents
    }
  } else if (v && typeof v === 'object' && 'data' in v) {
    const dataObj = v as { data: Parent[] }
    if (Array.isArray(dataObj.data)) {
      list = dataObj.data
    }
  } else if (v && typeof v === 'object') {
    list = Object.values(v as Record<string, Parent>).filter(
      (item): item is Parent => item && typeof item === 'object' && 'id' in item,
    )
  }

  // Filtrer pour ne garder que les femmes (mères potentielles)
  return list
    .filter(
      (parent: Parent) =>
        !parent.gender ||
        parent.gender.toLowerCase() === 'féminin' ||
        parent.gender.toLowerCase() === 'female' ||
        parent.gender.toLowerCase() === 'f',
    )
    .map((parent: Parent) => ({
      id: parent.id,
      name: parent.name || '',
      firstname: parent.firstname || '',
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
function updateValue(value: unknown) {
  // Gérer les valeurs spéciales
  if (value === 'null' || value === 'loading' || value === 'error' || value === 'empty') {
    emit('update:modelValue', '')
    return
  }
  emit('update:modelValue', value?.toString() || '')
}
</script>
