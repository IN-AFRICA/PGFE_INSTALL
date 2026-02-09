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

interface BankAccount {
  id: number
  name?: string
  code?: string
  number?: string
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
  placeholder: 'Sélectionner un compte bancaire',
})

const emit = defineEmits<Emits>()

// API pour récupérer les comptes bancaires
const {
  data: bankAccountsData,
  loading,
  error,
  fetchData,
} = useGetApi<any>(API_ROUTES.GET_BANK_ACCOUNTS)

// Liste normalisée des comptes bancaires quelle que soit la structure de réponse
const normalizedBankAccounts = computed<BankAccount[]>(() => {
  const v: any = bankAccountsData.value

  console.log('ListBankAccount - Données brutes:', v)

  if (!v) return []

  let list: any[] = []

  // Cas 1: tableau direct
  if (Array.isArray(v)) {
    list = v
  }
  // Cas 2: { accounts: [...] }
  else if (v?.accounts) {
    if (Array.isArray(v.accounts)) {
      list = v.accounts
    } else if (v.accounts.data && Array.isArray(v.accounts.data)) {
      list = v.accounts.data
    }
  }
  // Cas 3: { data: [...] }
  else if (v?.data && Array.isArray(v.data)) {
    list = v.data
  }
  // Cas 4: { bank_accounts: [...] }
  else if (v?.bank_accounts && Array.isArray(v.bank_accounts)) {
    list = v.bank_accounts
  }
  // Cas 5: objet avec IDs comme clés
  else if (typeof v === 'object' && !Array.isArray(v)) {
    list = Object.values(v).filter(
      (item: any) => item && typeof item === 'object' && (item.id || item.value),
    )
  }

  const result = list.map((account: any) => ({
    id: account.id ?? account.value,
    name: account.name ?? account.label ?? account.title,
    code: account.code,
    number: account.number ?? account.account_number,
  }))

  console.log('ListBankAccount - Comptes normalisés:', result)

  return result
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
  console.log('ListBankAccount - Chargement des comptes bancaires...')
  fetchData()
})

// Surveiller les changements de modelValue
watchEffect(() => {
  if (props.modelValue !== undefined) {
    console.log('ListBankAccount - modelValue changé:', props.modelValue)
  }
})
</script>

<template>
  <Select :model-value="displayValue" @update:model-value="updateValue">
    <SelectTrigger>
      <SelectValue :placeholder="placeholder" />
    </SelectTrigger>
    <SelectContent>
      <!-- État de chargement -->
      <SelectItem v-if="loading" value="loading" disabled>
        <div class="flex items-center gap-2">
          <span class="i-hugeicons-loading-03 animate-spin"></span>
          <span>Chargement des comptes...</span>
        </div>
      </SelectItem>

      <!-- État d'erreur -->
      <SelectItem v-else-if="error" value="error" disabled>
        <div class="flex items-center gap-2 text-red-500">
          <span class="i-hugeicons-alert-circle"></span>
          <span>Erreur de chargement</span>
        </div>
      </SelectItem>

      <!-- Liste vide -->
      <SelectItem
        v-else-if="!normalizedBankAccounts || normalizedBankAccounts.length === 0"
        value="empty"
        disabled
      >
        <div class="flex items-center gap-2 text-gray-500">
          <span class="i-hugeicons-bank"></span>
          <span>Aucun compte disponible</span>
        </div>
      </SelectItem>

      <!-- Liste des comptes bancaires -->
      <template v-else>
        <SelectItem
          v-for="account in normalizedBankAccounts"
          :key="account.id"
          :value="account.id ? account.id.toString() : 'null'"
        >
          <div class="flex flex-col">
            <span class="font-medium">{{ account.name || 'Compte sans nom' }}</span>
            <span class="text-xs text-gray-500">
              {{ account.number ? `N° ${account.number}` : '' }}
              {{ account.code ? ` - ${account.code}` : '' }}
            </span>
          </div>
        </SelectItem>
      </template>
    </SelectContent>
  </Select>
</template>
