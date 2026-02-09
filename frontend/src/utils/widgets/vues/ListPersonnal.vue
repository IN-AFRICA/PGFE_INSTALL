<template>
  <div class="flex flex-col space-y-1.5 flex-1">
    <Label for="personal_id" class="text-sm font-medium"> Personnels </Label>
    <Select
      id="personal_id"
      name="personal_id"
      :model-value="displayValue"
      @update:modelValue="updateValue"
    >
      <SelectTrigger id="personal_id" class="h-10 w-full">
        <SelectValue :placeholder="props.placeholder || 'Sélectionnez un personnel'" />
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
          <SelectItem v-else-if="error" value="error" disabled>
            <div class="flex items-center gap-2 text-red-500">
              <span class="iconify hugeicons--alert-circle"></span>
              Erreur de chargement
            </div>
          </SelectItem>

          <!-- Liste vide -->
          <SelectItem v-else-if="!personals || personals.length === 0" value="empty" disabled>
            Aucun personnel disponible
          </SelectItem>

          <!-- Données -->
          <SelectItem
            v-else
            v-for="personal in personals"
            :key="personal.id"
            :value="String(personal.id)"
          >
            {{ personal.title || personal.name || personal.label || `Fonction #${personal.id}` }}
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
import { computed, onMounted } from 'vue'

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

const { data: personals, loading, error, fetchData } = useGetApi(API_ROUTES.GET_ACADEMIC_PERSONALS)

onMounted(() => async () => {
  fetchData()
})

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
