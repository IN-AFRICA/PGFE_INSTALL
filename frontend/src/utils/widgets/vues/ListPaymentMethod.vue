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

interface PaymentMethod {
  id: number
  name?: string
  code?: string
  label?: string
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
  placeholder: 'Sélectionner une méthode de paiement',
})

const emit = defineEmits<Emits>()

// API pour récupérer les méthodes de paiement
const {
  data: paymentMethodsData,
  loading,
  error,
  fetchData,
} = useGetApi<any>(API_ROUTES.GET_PAYMENT_METHODE)

// Liste normalisée des méthodes de paiement quelle que soit la structure de réponse
const normalizedPaymentMethods = computed<PaymentMethod[]>(() => {
  const v: any = paymentMethodsData.value
  if (!v) return []

  // Cas 1: tableau direct
  if (Array.isArray(v)) return v

  // Cas 2: { payment_methods: [...] } ou { data: [...] } ou { methods: [...] }
  if (Array.isArray(v?.payment_methods)) return v.payment_methods
  if (Array.isArray(v?.data)) return v.data
  if (Array.isArray(v?.methods)) return v.methods

  // Cas 3: objet avec IDs comme clés
  if (typeof v === 'object' && !Array.isArray(v)) {
    return Object.values(v).filter(
      (item: any) => item && typeof item === 'object' && (item.id || item.value),
    ) as PaymentMethod[]
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
})

// Surveiller les changements de modelValue
watchEffect(() => {
  if (props.modelValue !== undefined) {
    // La valeur est déjà synchronisée via displayValue
  }
})
</script>

<template>
  <Select :model-value="displayValue" @update:model-value="updateValue">
    <SelectTrigger>
      <SelectValue :placeholder="placeholder" />
    </SelectTrigger>
    <SelectContent>
      <SelectItem v-if="loading" value="loading" disabled>
        Chargement des méthodes de paiement...
      </SelectItem>
      <SelectItem v-else-if="error" value="error" disabled> Erreur de chargement </SelectItem>
      <SelectItem v-else-if="normalizedPaymentMethods.length === 0" value="empty" disabled>
        Aucune méthode de paiement disponible
      </SelectItem>
      <SelectItem
        v-for="method in normalizedPaymentMethods"
        :key="method.id"
        :value="String(method.id)"
      >
        {{ method.name || method.label || `Méthode #${method.id}` }}
        <span v-if="method.code" class="text-gray-500 ml-2"> ({{ method.code }}) </span>
      </SelectItem>
    </SelectContent>
  </Select>
</template>
