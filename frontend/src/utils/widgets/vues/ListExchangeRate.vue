<script setup lang="ts">
import { computed, onMounted, onUnmounted, ref, watchEffect } from 'vue'
import { useGetApi } from '@/composables/useGetApi'
import { API_ROUTES } from '@/utils/constants/api_route'
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from '@/components/ui/select'

interface ExchangeRate {
  id: number
  currency_id: number
  rate: number
  date_effective?: string | null
  is_active: boolean
  currency?: { name: string; code: string }
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
  placeholder: 'Sélectionner un taux de change',
})

const emit = defineEmits<Emits>()

// API pour récupérer les taux de change
const {
  data: exchangeRatesData,
  loading,
  error,
  fetchData,
} = useGetApi<any>(API_ROUTES.GET_EXCHANGE_RATES)

// Flag de montage pour éviter les opérations après démontage
const isMounted = ref(true)

// Liste normalisée des taux de change quelle que soit la structure de réponse
const normalizedExchangeRates = computed<ExchangeRate[]>(() => {
  const v: any = exchangeRatesData.value
  if (!v) return []

  // Cas 1: tableau direct
  if (Array.isArray(v))
    return v
      .filter((rate: any) => rate && rate.is_active)
      .map((rate: any) => ({
        ...rate,
        rate: Number(rate.rate), // Coerce rate to number safely
      }))

  // Cas 2: { exchange_rates: [...] } ou { data: [...] }
  if (Array.isArray(v?.exchange_rates))
    return v.exchange_rates
      .filter((rate: any) => rate && rate.is_active)
      .map((rate: any) => ({
        ...rate,
        rate: typeof rate.rate === 'string' ? parseFloat(rate.rate) : Number(rate.rate),
      }))
  if (Array.isArray(v?.data))
    return v.data
      .filter((rate: any) => rate && rate.is_active)
      .map((rate: any) => ({
        ...rate,
        rate: typeof rate.rate === 'string' ? parseFloat(rate.rate) : rate.rate,
      }))

  // Cas 3: objet avec propriétés numériques (ids comme clés)
  if (v && typeof v === 'object') {
    const arr = Object.values(v)
      .filter((item: any) => item && typeof item === 'object' && 'id' in item && item.is_active)
      .map((rate: any) => ({
        ...rate,
        rate: typeof rate.rate === 'string' ? parseFloat(rate.rate) : rate.rate,
      }))
    if (arr.length > 0) return arr as ExchangeRate[]
  }

  return []
})

// Debug: Log des données pour comprendre la structure
watchEffect(() => {
  if (exchangeRatesData.value) {
    console.log('Exchange rates data:', exchangeRatesData.value)
    console.log('Normalized exchange rates:', normalizedExchangeRates.value)
  }
  if (error.value) {
    console.error('Exchange rate API error:', error.value)
  }
})

// Computed pour la valeur d'affichage
const displayValue = computed(() => {
  if (props.modelValue === null || props.modelValue === undefined || props.modelValue === '') {
    return ''
  }
  return props.modelValue.toString()
})

// Computed pour afficher le taux sélectionné dans le trigger
const selectedDisplayText = computed(() => {
  if (!props.modelValue || props.modelValue === '' || props.modelValue === 'null') {
    return null // Permet d'afficher le placeholder
  }

  const rateId = Number(props.modelValue)
  const exchangeRate = normalizedExchangeRates.value.find((r) => r.id === rateId)

  if (exchangeRate) {
    const currencyCode = exchangeRate.currency?.code || 'N/A'
    const rateNum = Number(exchangeRate.rate)
    const rateText = Number.isFinite(rateNum) ? rateNum.toFixed(6) : String(exchangeRate.rate ?? '')
    return `${currencyCode} - ${rateText}`
  }

  return `ID: ${props.modelValue}`
})

// Computed pour le taux sélectionné
const selectedExchangeRate = computed(() => {
  if (!props.modelValue || props.modelValue === '') return null
  const rateId = Number(props.modelValue)
  return normalizedExchangeRates.value.find((rate) => rate.id === rateId) || null
})

// Fonction pour mettre à jour la valeur
function updateValue(value: any) {
  console.log('ListExchangeRate - updateValue called with:', value)

  // Handle special values
  if (value === 'null' || value === 'loading' || value === 'error' || value === 'empty') {
    console.log('ListExchangeRate - Setting empty value')
    emit('update:modelValue', '')
    return
  }

  const newValue = value?.toString() || ''
  console.log('ListExchangeRate - Emitting new value:', newValue)
  emit('update:modelValue', newValue)
}

// Charger les données au montage
onMounted(() => {
  if (!isMounted.value) return
  fetchData()
})

onUnmounted(() => {
  isMounted.value = false
})
</script>

<template>
  <Select :model-value="displayValue" @update:model-value="updateValue">
    <SelectTrigger>
      <SelectValue :placeholder="placeholder">
        <span v-if="selectedDisplayText">{{ selectedDisplayText }}</span>
        <span v-else class="text-muted-foreground">{{ placeholder }}</span>
      </SelectValue>
    </SelectTrigger>
    <SelectContent>
      <SelectItem v-if="loading" value="loading" disabled>
        <div class="flex items-center gap-2">
          <span class="iconify hugeicons--loading-03 animate-spin"></span>
          Chargement des taux...
        </div>
      </SelectItem>

      <SelectItem v-else-if="error" value="error" disabled>
        <div class="flex items-center gap-2 text-red-500">
          <span class="iconify hugeicons--alert-circle"></span>
          Erreur de chargement
        </div>
      </SelectItem>

      <SelectItem v-else-if="normalizedExchangeRates.length === 0" value="empty" disabled>
        Aucun taux de change disponible
      </SelectItem>

      <template v-else>
        <SelectItem value="null">Aucun taux</SelectItem>
        <template v-for="rate in normalizedExchangeRates" :key="rate?.id || Math.random()">
          <SelectItem v-if="rate && rate.id" :value="String(rate.id)">
            <div class="flex items-center justify-between w-full">
              <span
                >{{ rate.currency?.code || 'N/A'
                }}<span v-if="rate.currency?.name"> - {{ rate.currency?.name }}</span></span
              >
              <span class="text-sm text-gray-500 ml-2">{{ Number(rate.rate).toFixed(6) }}</span>
            </div>
          </SelectItem>
        </template>
      </template>
    </SelectContent>
  </Select>
</template>
