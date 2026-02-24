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
import { computed, onMounted, ref } from 'vue'
import { useRouter } from 'vue-router'
import DashFormLayout from '@/components/templates/DashFormLayout.vue'
import FormSection from '@/components/atoms/FormSection.vue'
import InputWrapper from '@/components/atoms/InputWrapper.vue'
import SpanRequired from '@/components/atoms/SpanRequired.vue'
import IconifySpinner from '@/components/ui/spinner/IconifySpinner.vue'
import { showCustomToast } from '@/utils/widgets/custom_toast'
import { useGetApi } from '@/composables/useGetApi'
import { usePostApi } from '@/composables/usePostApi'
import { API_ROUTES } from '@/utils/constants/api_route'

const router = useRouter()

// Fetch clients from API
const { data: rawClients, loading: loadingClients, fetchData: fetchClients } = useGetApi(API_ROUTES.GET_RENTAL_CLIENTS)
const { postData, loading: submitting, success: submitSuccess } = usePostApi()

const clients = computed(() => {
  if (!rawClients.value) return []
  if (Array.isArray(rawClients.value)) return rawClients.value
  return (rawClients.value as any).data || []
})

const statusOptions = ref([
  { value: 'active', label: 'Actif' },
  { value: 'expired', label: 'Expiré' },
  { value: 'terminated', label: 'Résilié' },
  { value: 'pending', label: 'En attente' },
])

const formData = ref({
  client_id: '',
  start_date: '',
  end_date: '',
  status: 'active',
})

onMounted(() => {
  fetchClients()
})

const handleCancel = () => {
  router.push('/location/operations')
}

const handleSubmit = async () => {
  // Validation
  if (!formData.value.client_id) {
    showCustomToast({ message: 'Le client est requis', type: 'error' })
    return
  }
  if (!formData.value.start_date) {
    showCustomToast({ message: 'La date de début est requise', type: 'error' })
    return
  }
  if (!formData.value.end_date) {
    showCustomToast({ message: 'La date de fin est requise', type: 'error' })
    return
  }
  if (!formData.value.status) {
    showCustomToast({ message: 'Le statut est requis', type: 'error' })
    return
  }

  const payload = {
    client_id: Number(formData.value.client_id),
    start_date: formData.value.start_date,
    end_date: formData.value.end_date,
    status: formData.value.status,
  }

  await postData(API_ROUTES.CREATE_RENTAL_CONTRACT, payload)

  if (submitSuccess.value) {
    showCustomToast({
      message: 'Contrat enregistré avec succès',
      type: 'success',
    })
    router.push('/location/operations')
  }
}
</script>

<template>
  <DashFormLayout
    :link-back="'/location/operations'"
    title="Ajouter un Contrat"
    group-route="/location/operations"
    module="location"
    :breadcrumb="[
      { label: 'Location', href: '/location' },
      { label: 'Opérations', href: '/location/operations' },
      { label: 'Contrats', href: '/location/operations' },
      { label: 'Nouveau Contrat', href: '/location/operations/contrats/nouveau' },
    ]"
  >
    <form @submit.prevent="handleSubmit" class="w-full flex flex-col space-y-8">
      <FormSection
        title="Informations du contrat"
        class="grid sm:grid-cols-2 gap-y-6 gap-x-10"
      >
        <InputWrapper>
          <Label for="client_id">Client<SpanRequired /></Label>
          <Select v-model="formData.client_id" required>
            <SelectTrigger class="border-gray-200">
              <SelectValue placeholder="Sélectionner un client" />
            </SelectTrigger>
            <SelectContent>
              <SelectItem v-if="loadingClients" value="loading" disabled>
                Chargement...
              </SelectItem>
              <SelectItem v-for="client in clients" :key="client.id" :value="String(client.id)">
                {{ client.name || client.nom || `Client #${client.id}` }}
              </SelectItem>
            </SelectContent>
          </Select>
        </InputWrapper>

        <InputWrapper>
          <Label for="status">Statut<SpanRequired /></Label>
          <Select v-model="formData.status" required>
            <SelectTrigger class="border-gray-200">
              <SelectValue placeholder="Sélectionner le statut" />
            </SelectTrigger>
            <SelectContent>
              <SelectItem v-for="option in statusOptions" :key="option.value" :value="option.value">
                {{ option.label }}
              </SelectItem>
            </SelectContent>
          </Select>
        </InputWrapper>

        <InputWrapper>
          <Label for="start_date">Date de début<SpanRequired /></Label>
          <Input
            id="start_date"
            v-model="formData.start_date"
            type="date"
            required
            class="border-gray-200"
          />
        </InputWrapper>

        <InputWrapper>
          <Label for="end_date">Date de fin<SpanRequired /></Label>
          <Input
            id="end_date"
            v-model="formData.end_date"
            type="date"
            required
            class="border-gray-200"
          />
        </InputWrapper>
      </FormSection>

      <div class="flex justify-end gap-3">
        <Button type="button" variant="outline" @click="handleCancel" :disabled="submitting">
          Annuler
        </Button>
        <Button type="submit" :disabled="submitting">
          <IconifySpinner v-if="submitting" class="mr-2" />
          <span v-if="submitting">Enregistrement...</span>
          <span v-else>Enregistrer</span>
        </Button>
      </div>
    </form>
  </DashFormLayout>
</template>
