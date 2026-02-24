<script setup lang="ts">
import { Button } from '@/components/ui/button'
import { Label } from '@/components/ui/label'
import { Textarea } from '@/components/ui/textarea'
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

type Client = {
  id: number
  name: string
}

type Equipment = {
  id: number
  name: string
  equipment_code: string | null
}

const { data: clientsData, fetchData: fetchClients } = useGetApi<Client[]>(API_ROUTES.GET_RENTAL_CLIENTS)
const { data: equipmentsData, fetchData: fetchEquipments } = useGetApi<Equipment[]>(API_ROUTES.GET_RENTAL_EQUIPMENTS)

const statusOptions = ref([
  { value: 'pending', label: 'En attente' },
  { value: 'active', label: 'Active' },
  { value: 'completed', label: 'Terminée' },
  { value: 'cancelled', label: 'Annulée' },
])

const formData = ref({
  equipment_id: '',
  client_id: '',
  status: '',
  description: '',
})

onMounted(async () => {
  await Promise.all([fetchClients(), fetchEquipments()])
})

const clientOptions = computed(() => {
  if (!clientsData.value) return []
  return clientsData.value.map((client) => ({
    value: String(client.id),
    label: client.name,
  }))
})

const equipmentOptions = computed(() => {
  if (!equipmentsData.value) return []
  return equipmentsData.value.map((equipment) => ({
    value: String(equipment.id),
    label: `${equipment.equipment_code || `EQP-${equipment.id}`} - ${equipment.name}`,
  }))
})

const handleCancel = () => {
  router.push('/location/operations/cessions')
}

const handleSubmit = async () => {
  if (!formData.value.equipment_id || !formData.value.client_id || !formData.value.status) {
    showCustomToast({
      message: 'Veuillez remplir tous les champs obligatoires',
      type: 'error',
    })
    return
  }

  const payload = {
    equipment_id: Number(formData.value.equipment_id),
    client_id: Number(formData.value.client_id),
    status: formData.value.status,
    description: formData.value.description || null,
  }

  await postData(API_ROUTES.CREATE_RENTAL_SESSION, payload)

  if (success.value) {
    showCustomToast({
      message: 'Session enregistrée avec succès',
      type: 'success',
    })
    router.push('/location/operations/cessions')
  }
}
</script>

<template>
  <DashFormLayout
    :link-back="'/location/operations/cessions'"
    title="Ajouter une Session"
    group-route="/location/operations"
    module="location"
    :breadcrumb="[
      { label: 'Location', href: '/location' },
      { label: 'Opérations', href: '/location/operations' },
      { label: 'Sessions', href: '/location/operations/cessions' },
      { label: 'Nouvelle Session', href: '/location/operations/cessions/nouveau' },
    ]"
  >
    <form @submit.prevent="handleSubmit" class="w-full flex flex-col space-y-8">
      <FormSection
        title="Informations de la session"
        class="grid sm:grid-cols-2 gap-y-6 gap-x-10"
      >
        <InputWrapper>
          <Label for="equipment_id">Équipement<SpanRequired /></Label>
          <Select v-model="formData.equipment_id" required>
            <SelectTrigger class="border-gray-200">
              <SelectValue placeholder="Sélectionner un équipement" />
            </SelectTrigger>
            <SelectContent>
              <SelectItem v-for="option in equipmentOptions" :key="option.value" :value="option.value">
                {{ option.label }}
              </SelectItem>
            </SelectContent>
          </Select>
        </InputWrapper>

        <InputWrapper>
          <Label for="client_id">Client/Locataire<SpanRequired /></Label>
          <Select v-model="formData.client_id" required>
            <SelectTrigger class="border-gray-200">
              <SelectValue placeholder="Sélectionner un client" />
            </SelectTrigger>
            <SelectContent>
              <SelectItem v-for="option in clientOptions" :key="option.value" :value="option.value">
                {{ option.label }}
              </SelectItem>
            </SelectContent>
          </Select>
        </InputWrapper>

        <InputWrapper>
          <Label for="status">Statut<SpanRequired /></Label>
          <Select v-model="formData.status" required>
            <SelectTrigger class="border-gray-200">
              <SelectValue placeholder="Sélectionner un statut" />
            </SelectTrigger>
            <SelectContent>
              <SelectItem v-for="option in statusOptions" :key="option.value" :value="option.value">
                {{ option.label }}
              </SelectItem>
            </SelectContent>
          </Select>
        </InputWrapper>

        <InputWrapper class="sm:col-span-2">
          <Label for="description">Description</Label>
          <Textarea
            id="description"
            v-model="formData.description"
            placeholder="Description de la session..."
            class="border-gray-200"
            rows="3"
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
