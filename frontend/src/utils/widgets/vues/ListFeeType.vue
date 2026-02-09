<script setup lang="ts">
import { computed, onMounted, watchEffect } from 'vue'
import { useGetApi } from '@/composables/useGetApi'
import { API_ROUTES } from '@/utils/constants/api_route'
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from '@/components/ui/select'

interface FeeType {
  id: number
  name: string
  description?: string
  label?: string
  libelle?: string
}

interface Props {
  modelValue?: string | number | null
  placeholder?: string
  name?: string
}

interface Emits {
  (e: 'update:modelValue', value: string): void
}

const props = withDefaults(defineProps<Props>(), {
  placeholder: 'Sélectionner un type de frais',
})

const emit = defineEmits<Emits>()

// API pour récupérer les types de frais
const { data: feeTypesData, loading, error, fetchData } = useGetApi<any>(API_ROUTES.GET_FEE_TYPES)

// Computed pour gérer différents formats de réponse API
const feeTypes = computed(() => {
  const v: any = feeTypesData.value

  console.log('ListFeeType - feeTypes computed, feeTypesData.value:', v)

  let list: any[] = []

  // Cas 1: Tableau direct
  if (Array.isArray(v)) {
    list = v
  }
  // Cas 2: Structure avec 'fee_types'
  else if (v?.fee_types) {
    if (Array.isArray(v.fee_types)) {
      list = v.fee_types
    } else if (v.fee_types.data && Array.isArray(v.fee_types.data)) {
      list = v.fee_types.data
    }
  }
  // Cas 3: Structure avec 'data'
  else if (v?.data && Array.isArray(v.data)) {
    list = v.data
  }
  // Cas 4: Structure avec 'types'
  else if (v?.types && Array.isArray(v.types)) {
    list = v.types
  }
  // Cas 5: Objet avec propriétés numériques (IDs comme clés)
  else if (v && typeof v === 'object' && !Array.isArray(v)) {
    list = Object.values(v).filter(
      (item: any) => item && typeof item === 'object' && (item.id || item.value),
    )
  }

  const result = list.map((feeType: any) => ({
    id: String(feeType.id ?? feeType.value ?? ''),
    name:
      feeType.name ??
      feeType.label ??
      feeType.libelle ??
      feeType.title ??
      `Type #${feeType.id ?? feeType.value}`,
  }))

  console.log('ListFeeType - feeTypes résultat:', result)

  return result
})

// Computed pour la valeur d'affichage
const displayValue = computed(() => {
  if (props.modelValue === null || props.modelValue === undefined || props.modelValue === '') {
    return 'null'
  }
  return props.modelValue.toString()
})

// Fonction pour mettre à jour la valeur
function updateValue(value: any) {
  // Handle special values
  if (value === 'null' || value === 'loading' || value === 'error' || value === 'empty') {
    emit('update:modelValue', '')
    return
  }
  emit('update:modelValue', value?.toString() || '')
}

// Charger les données au montage
onMounted(() => {
  fetchData()
})
</script>

<template>
  <Select :model-value="displayValue" @update:model-value="updateValue">
    <SelectTrigger>
      <SelectValue :placeholder="placeholder" />
    </SelectTrigger>
    <SelectContent>
      <SelectItem v-if="loading" value="loading" disabled>
        <div class="flex items-center gap-2">
          <span class="iconify hugeicons--loading-03 animate-spin"></span>
          Chargement...
        </div>
      </SelectItem>

      <SelectItem v-else-if="error" value="error" disabled>
        <div class="flex items-center gap-2 text-red-500">
          <span class="iconify hugeicons--alert-circle"></span>
          Erreur de chargement
        </div>
      </SelectItem>

      <SelectItem v-else-if="!feeTypes || feeTypes.length === 0" value="empty" disabled>
        Aucun type de frais disponible
      </SelectItem>

      <template v-else>
        <SelectItem value="null" disabled>Aucun type</SelectItem>
        <template v-for="feeType in feeTypes" :key="feeType?.id || Math.random()">
          <SelectItem v-if="feeType && feeType.id && feeType.name" :value="String(feeType.id)">
            {{ feeType.name }}
          </SelectItem>
        </template>
      </template>
    </SelectContent>
  </Select>
</template>
