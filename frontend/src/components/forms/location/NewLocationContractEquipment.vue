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
import { Checkbox } from '@/components/ui/checkbox'
import { ref, onMounted } from 'vue'
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

type ContractEquipmentPayload = {
  rental_contract_id: number
  equipment_id: number
  quantity: number
  price: number
}

const router = useRouter()
const { postData, loading, success } = usePostApi()

// Load contracts and equipments for dropdowns
const { data: contracts, fetchData: fetchContracts } = useGetApi(API_ROUTES.GET_RENTAL_CONTRACTS)
const { data: equipments, fetchData: fetchEquipments } = useGetApi(API_ROUTES.GET_RENTAL_EQUIPMENTS)

const formData = ref({
  rental_contract_id: '',
  equipment_id: '',
  quantity: 1,
  price: 0,
})

onMounted(async () => {
  await Promise.all([fetchContracts(), fetchEquipments()])
})

const handleCancel = () => {
  router.push('/location/prealables/contract-equipments')
}

const handleSubmit = async () => {
  if (!formData.value.rental_contract_id || !formData.value.equipment_id || !formData.value.quantity || formData.value.price === undefined) {
    showCustomToast({
      message: 'Veuillez remplir tous les champs obligatoires',
      type: 'error',
    })
    return
  }

  if (formData.value.quantity < 1) {
    showCustomToast({
      message: 'La quantité doit être au moins 1',
      type: 'error',
    })
    return
  }

  if (formData.value.price < 0) {
    showCustomToast({
      message: 'Le prix doit être supérieur ou égal à 0',
      type: 'error',
    })
    return
  }

  const payload: ContractEquipmentPayload = {
    rental_contract_id: parseInt(formData.value.rental_contract_id),
    equipment_id: parseInt(formData.value.equipment_id),
    quantity: formData.value.quantity,
    price: formData.value.price,
  }

  await postData(API_ROUTES.CREATE_RENTAL_CONTRACT_EQUIPMENT, payload)

  if (success.value) {
    showCustomToast({
      message: 'Équipement de contrat enregistré avec succès',
      type: 'success',
    })
    router.push('/location/prealables/contract-equipments')
  }
}
</script>

<template>
  <DashFormLayout
    :link-back="'/location/prealables/contract-equipments'"
    title="Ajouter un Équipement de Contrat"
    group-route="/location/prealables"
    module="location"
    :breadcrumb="[
      { label: 'Location', href: '/location' },
      { label: 'Préalables', href: '/location/prealables' },
      { label: 'Équipements de Contrat', href: '/location/prealables/contract-equipments' },
      { label: 'Nouveau', href: '/location/prealables/contract-equipments/nouveau' },
    ]"
  >
    <form @submit.prevent="handleSubmit" class="w-full flex flex-col space-y-8">
      <FormSection
        title="Informations de l'équipement de contrat"
        class="grid sm:grid-cols-2 gap-y-6 gap-x-10"
      >


        <InputWrapper>
          <Label for="rental_contract_id">Contrat<SpanRequired /></Label>
          <Select v-model="formData.rental_contract_id" required>
            <SelectTrigger id="rental_contract_id" class="border-gray-200">
              <SelectValue placeholder="Sélectionner un contrat" />
            </SelectTrigger>
            <SelectContent>
              <SelectItem
                v-for="contract in contracts"
                :key="contract.id"
                :value="String(contract.id)"
              >
                {{ contract.contract_code || `Contrat #${contract.id}` }}
              </SelectItem>
            </SelectContent>
          </Select>
        </InputWrapper>

        <InputWrapper>
          <Label for="equipment_id">Équipement<SpanRequired /></Label>
          <Select v-model="formData.equipment_id" required>
            <SelectTrigger id="equipment_id" class="border-gray-200">
              <SelectValue placeholder="Sélectionner un équipement" />
            </SelectTrigger>
            <SelectContent>
              <SelectItem
                v-for="equipment in equipments"
                :key="equipment.id"
                :value="String(equipment.id)"
              >
                {{ equipment.name }}
              </SelectItem>
            </SelectContent>
          </Select>
        </InputWrapper>

        <InputWrapper>
          <Label for="quantity">Quantité<SpanRequired /></Label>
          <Input
            id="quantity"
            v-model.number="formData.quantity"
            type="number"
            min="1"
            required
            class="border-gray-200"
          />
        </InputWrapper>

        <InputWrapper>
          <Label for="price">Prix<SpanRequired /></Label>
          <Input
            id="price"
            v-model.number="formData.price"
            type="number"
            min="0"
            step="0.01"
            required
            class="border-gray-200"
          />
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
