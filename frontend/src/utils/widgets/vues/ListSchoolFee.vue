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

interface SchoolFee {
  id: number
  name?: string
  label?: string
  amount?: number
  currency_id?: number
  fee_type?: { name?: string }
}

interface Currency {
  id: number
  code?: string
  name?: string
}

interface Props {
  modelValue?: string | number | null
  placeholder?: string
  name?: string
}

interface Emits {
  (e: 'update:modelValue', value: any): void
}

const props = withDefaults(defineProps<Props>(), {
  placeholder: 'Sélectionner un frais scolaire',
})

const emit = defineEmits<Emits>()

// API pour récupérer les frais scolaires
const {
  data: schoolFeesData,
  loading,
  error,
  fetchData,
} = useGetApi<any>(API_ROUTES.GET_SCHOOL_FEES)

// API pour récupérer les devises (pour mapper currency_id -> code)
const { data: currenciesData, fetchData: fetchCurrencies } = useGetApi<any>(
  API_ROUTES.GET_CURRENCIES,
)

// Liste normalisée des frais scolaires quelle que soit la structure de réponse
const normalizedSchoolFees = computed<SchoolFee[]>(() => {
  const v: any = schoolFeesData.value
  if (!v) return []

  // Cas 1: tableau direct
  if (Array.isArray(v)) return v

  // Cas 2: { school_fees: [...] } ou { data: [...] } ou { fees: [...] }
  if (Array.isArray(v?.school_fees)) return v.school_fees
  if (Array.isArray(v?.data)) return v.data
  if (Array.isArray(v?.fees)) return v.fees

  // Cas 3: objet avec IDs comme clés
  if (typeof v === 'object' && !Array.isArray(v)) {
    return Object.values(v).filter(
      (item: any) => item && typeof item === 'object' && (item.id || item.value),
    ) as SchoolFee[]
  }

  return []
})

// Valeur d'affichage pour le Select
const displayValue = computed(() => {
  if (!props.modelValue) return ''
  return String(props.modelValue)
})

// Fonction pour mettre à jour la valeur
function updateValue(value: any) {
  emit('update:modelValue', value)
}

// Charger les données au montage
onMounted(() => {
  fetchData()
  fetchCurrencies()
})

// Surveiller les changements de modelValue
watchEffect(() => {
  if (props.modelValue !== undefined) {
  }
})

// Formatage montant sans devise
function formatAmount(amount?: number) {
  if (amount === null || amount === undefined) return ''
  return new Intl.NumberFormat('fr-CD', {
    style: 'decimal',
    minimumFractionDigits: 0,
    maximumFractionDigits: 2,
  }).format(Number(amount))
}

// Normaliser les devises et créer une Map id -> code
const currenciesMap = computed(() => {
  const v: any = currenciesData?.value
  const map = new Map<number, string>()
  if (!v) return map

  let list: any[] = []
  if (Array.isArray(v)) list = v
  else if (Array.isArray(v?.currencies)) list = v.currencies
  else if (Array.isArray(v?.data)) list = v.data
  else if (v && typeof v === 'object')
    list = Object.values(v).filter((it: any) => it && typeof it === 'object' && 'id' in it)

  list.forEach((c: any) => {
    if (c && c.id) map.set(Number(c.id), c.code || '')
  })
  return map
})

function getCurrencyCode(id?: number) {
  if (!id) return ''
  return currenciesMap.value.get(Number(id)) || ''
}
</script>

<template>
  <Select :model-value="displayValue" @update:model-value="updateValue">
    <SelectTrigger>
      <SelectValue :placeholder="placeholder" />
    </SelectTrigger>
    <SelectContent>
      <SelectItem v-if="loading" value="loading" disabled>
        Chargement des frais scolaires...
      </SelectItem>
      <SelectItem v-else-if="error" value="error" disabled> Erreur de chargement </SelectItem>
      <SelectItem v-else-if="normalizedSchoolFees.length === 0" value="empty" disabled>
        Aucun frais scolaire disponible
      </SelectItem>
      <SelectItem v-for="fee in normalizedSchoolFees" :key="fee.id" :value="String(fee.id)">
        <span>{{ fee.fee_type?.name || fee.name || fee.label || `Frais #${fee.id}` }} :</span>
        <span v-if="fee.amount">{{ formatAmount(fee.amount) }}</span>
        <span v-if="getCurrencyCode(fee.currency_id)" class="ml-1 text-muted-foreground">{{
          getCurrencyCode(fee.currency_id)
        }}</span>
      </SelectItem>
    </SelectContent>
  </Select>
</template>
