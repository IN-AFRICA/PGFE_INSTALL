<script setup lang="ts">
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from '@/components/ui/select'
import { ref, onMounted, computed } from 'vue'
import { useRouter } from 'vue-router'
import DashFormLayout from '@/components/templates/DashFormLayout.vue'
import FormSection from '@/components/atoms/FormSection.vue'
import InputWrapper from '@/components/atoms/InputWrapper.vue'
import SpanRequired from '@/components/atoms/SpanRequired.vue'
import IconifySpinner from '@/components/ui/spinner/IconifySpinner.vue'
import { showCustomToast } from '@/utils/widgets/custom_toast'
import { usePostApi } from '@/composables/usePostApi'
import { useGetApi } from '@/composables/useGetApi'
import { API_ROUTES } from '@/utils/constants/api_route'

const router = useRouter()
const { postData, loading, success } = usePostApi()

type Contract = {
  id: number
  contract_code: string | null
  client_id: number
  total_amount: string
  status: string
  client?: {
    name: string
  }
}

const { data: contractsData, fetchData: fetchContracts } = useGetApi<Contract[]>(API_ROUTES.GET_RENTAL_CONTRACTS)

const paymentMethodOptions = ref([
  { value: 'cash', label: 'Espèces' },
  { value: 'bank_transfer', label: 'Virement bancaire' },
  { value: 'mobile_money', label: 'Mobile Money' },
  { value: 'check', label: 'Chèque' },
])

const formData = ref({
  rental_contract_id: '',
  amount: '',
  payment_method: '',
  paid_at: '',
})

onMounted(async () => {
  await fetchContracts()
})

const contractOptions = computed(() => {
  if (!contractsData.value) return []
  return contractsData.value.map((contract) => ({
    value: String(contract.id),
    label: `${contract.contract_code || `CNT-${contract.id}`} - ${contract.client?.name || 'Client'}`,
  }))
})

const handleCancel = () => {
  router.push('/location/operations/versements')
}

const handleSubmit = async () => {
  if (!formData.value.rental_contract_id || !formData.value.amount || !formData.value.payment_method || !formData.value.paid_at) {
    showCustomToast({
      message: 'Veuillez remplir tous les champs obligatoires',
      type: 'error',
    })
    return
  }

  const payload = {
    rental_contract_id: Number(formData.value.rental_contract_id),
    amount: Number(formData.value.amount),
    payment_method: formData.value.payment_method,
    paid_at: formData.value.paid_at,
  }

  await postData(API_ROUTES.CREATE_RENTAL_PAYMENT, payload)

  if (success.value) {
    showCustomToast({
      message: 'Versement enregistré avec succès',
      type: 'success',
    })
    router.push('/location/operations/versements')
  }
}
</script>

<template>
  <DashFormLayout
    :link-back="'/location/operations/versements'"
    title="Ajouter un Versement"
    group-route="/location/operations"
    module="location"
    :breadcrumb="[
      { label: 'Location', href: '/location' },
      { label: 'Opérations', href: '/location/operations' },
      { label: 'Versements', href: '/location/operations/versements' },
      { label: 'Nouveau Versement', href: '/location/operations/versements/nouveau' },
    ]"
  >
    <form @submit.prevent="handleSubmit" class="w-full flex flex-col space-y-8">
      <FormSection
        title="Informations du versement"
        class="grid sm:grid-cols-2 gap-y-6 gap-x-10"
      >
        <InputWrapper>
          <Label for="paid_at">Date du versement<SpanRequired /></Label>
          <Input
            id="paid_at"
            v-model="formData.paid_at"
            type="date"
            required
            class="border-gray-200"
          />
        </InputWrapper>

        <InputWrapper>
          <Label for="contract">Contrat<SpanRequired /></Label>
          <Select v-model="formData.rental_contract_id" required>
            <SelectTrigger class="border-gray-200">
              <SelectValue placeholder="Sélectionner un contrat" />
            </SelectTrigger>
            <SelectContent>
              <SelectItem v-for="option in contractOptions" :key="option.value" :value="option.value">
                {{ option.label }}
              </SelectItem>
            </SelectContent>
          </Select>
        </InputWrapper>

        <InputWrapper>
          <Label for="amount">Montant ($)<SpanRequired /></Label>
          <Input
            id="amount"
            v-model="formData.amount"
            type="number"
            step="0.01"
            min="0"
            placeholder="Ex: 500.00"
            required
            class="border-gray-200"
          />
        </InputWrapper>

        <InputWrapper>
          <Label for="payment_method">Méthode de paiement<SpanRequired /></Label>
          <Select v-model="formData.payment_method" required>
            <SelectTrigger class="border-gray-200">
              <SelectValue placeholder="Sélectionner une méthode" />
            </SelectTrigger>
            <SelectContent>
              <SelectItem v-for="option in paymentMethodOptions" :key="option.value" :value="option.value">
                {{ option.label }}
              </SelectItem>
            </SelectContent>
          </Select>
        </InputWrapper>
      </FormSection>

      <div class="flex justify-end gap-3">
        <Button type="button" variant="outline" @click="handleCancel" :disabled="loading">
          Annuler
        </Button>
        <Button type="submit" :disabled="loading">
          <IconifySpinner v-if="loading" class="mr-2" />
          <span v-if="loading">Enregistrement...</span>
          <span v-else>Enregistrer</span>
        </Button>
      </div>
    </form>
  </DashFormLayout>
</template>
