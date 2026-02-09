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

interface Currency {
  id: number
  name: string
  code: string
  symbol?: string
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
  placeholder: 'Sélectionner une devise',
})

const emit = defineEmits<Emits>()

// API pour récupérer les devises
const {
  data: currenciesData,
  loading,
  error,
  fetchData,
} = useGetApi<any>(API_ROUTES.GET_CURRENCIES)

// Liste normalisée des devises quelle que soit la structure de réponse
const normalizedCurrencies = computed<Currency[]>(() => {
  const v: any = currenciesData.value
  if (!v) return []

  // Cas 1: tableau direct
  if (Array.isArray(v)) return v

  // Cas 2: { currencies: [...] } ou { data: [...] }
  if (Array.isArray(v?.currencies)) return v.currencies
  if (Array.isArray(v?.data)) return v.data

  // Cas 3: objet avec propriétés numériques (ids comme clés)
  if (v && typeof v === 'object') {
    const arr = Object.values(v).filter(
      (item: any) => item && typeof item === 'object' && 'id' in item,
    )
    if (arr.length > 0) return arr as Currency[]
  }

  return []
})

// Debug: Log des données pour comprendre la structure
watchEffect(() => {
  if (currenciesData.value) {
    console.log('Currencies data:', currenciesData.value)
    console.log('Normalized currencies:', normalizedCurrencies.value)
  }
  if (error.value) {
    console.error('Currency API error:', error.value)
  }
})

// Computed pour la valeur d'affichage
const displayValue = computed(() => {
  if (props.modelValue === null || props.modelValue === undefined || props.modelValue === '') {
    return ''
  }
  return props.modelValue.toString()
})

// Computed pour afficher la devise sélectionnée dans le trigger
const selectedDisplayText = computed(() => {
  if (!props.modelValue || props.modelValue === '' || props.modelValue === 'null') {
    return null // Permet d'afficher le placeholder
  }

  const currencyId = Number(props.modelValue)
  const currency = normalizedCurrencies.value.find((c) => c.id === currencyId)

  if (currency) {
    return `${currency.code || 'N/A'} - ${currency.name}`
  }

  return `ID: ${props.modelValue}`
})

// Computed pour la devise sélectionnée
const selectedCurrency = computed(() => {
  if (!props.modelValue || props.modelValue === '') return null
  const currencyId = Number(props.modelValue)
  return normalizedCurrencies.value.find((currency) => currency.id === currencyId) || null
})

// Fonction pour mettre à jour la valeur
function updateValue(value: any) {
  console.log('ListCurrency - updateValue called with:', value)

  // Handle special values
  if (value === 'null' || value === 'loading' || value === 'error' || value === 'empty') {
    console.log('ListCurrency - Setting empty value')
    emit('update:modelValue', '')
    return
  }

  const newValue = value?.toString() || ''
  console.log('ListCurrency - Emitting new value:', newValue)
  emit('update:modelValue', newValue)
}

// Charger les données au montage
onMounted(() => {
  fetchData()
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
          Chargement...
        </div>
      </SelectItem>

      <SelectItem v-else-if="error" value="error" disabled>
        <div class="flex items-center gap-2 text-red-500">
          <span class="iconify hugeicons--alert-circle"></span>
          Erreur de chargement
        </div>
      </SelectItem>

      <SelectItem v-else-if="normalizedCurrencies.length === 0" value="empty" disabled>
        Aucune devise disponible
      </SelectItem>

      <template v-else>
        <SelectItem value="null">Aucune devise</SelectItem>
        <template v-for="currency in normalizedCurrencies" :key="currency?.id || Math.random()">
          <SelectItem v-if="currency && currency.id && currency.name" :value="String(currency.id)">
            {{ currency.code || 'N/A' }} - {{ currency.name }}
          </SelectItem>
        </template>
      </template>
    </SelectContent>
  </Select>
</template>
