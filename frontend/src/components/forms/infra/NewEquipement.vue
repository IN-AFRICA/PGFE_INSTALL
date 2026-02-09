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
import { ref, computed, onMounted } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import DashFormLayout from '@/components/templates/DashFormLayout.vue'
import FormSection from '@/components/atoms/FormSection.vue'
import InputWrapper from '@/components/atoms/InputWrapper.vue'
import SpanRequired from '@/components/atoms/SpanRequired.vue'
import { showCustomToast } from '@/utils/widgets/custom_toast'
import { usePostApi } from '@/composables/usePostApi'
import { usePutApi } from '@/composables/usePutApi'
import { useGetApi } from '@/composables/useGetApi'
import { API_ROUTES } from '@/utils/constants/api_route'
import { eventBus } from '@/utils/eventBus'
import IconifySpinner from '@/components/ui/spinner/IconifySpinner.vue'

const router = useRouter()
const route = useRoute()

interface EquipmentFormData {
  name: string
  type_id: string
  serial_number: string
  location: string
  state_id: string
}

const formData = ref<EquipmentFormData>({
  name: '',
  type_id: '',
  serial_number: '',
  location: '',
  state_id: '',
})

const { data: typesRaw, fetchData: fetchTypes } = useGetApi(API_ROUTES.GET_INFRA_TYPES)
const { data: statesRaw, fetchData: fetchStates } = useGetApi(API_ROUTES.GET_INFRA_STATES)

const types = computed(() => {
  if (!typesRaw.value) return []
  if (Array.isArray(typesRaw.value)) return typesRaw.value
  return (typesRaw.value as any).data || []
})

const states = computed(() => {
  if (!statesRaw.value) return []
  if (Array.isArray(statesRaw.value)) return statesRaw.value
  return (statesRaw.value as any).data || []
})

const { loading: creating, postData, success: successCreate, error: errorCreate } = usePostApi()
const { loading: updating, putData, success: successUpdate, error: errorUpdate } = usePutApi()

const loading = computed(() => creating.value || updating.value)
const isEditing = computed(() => !!route.query.id)

onMounted(async () => {
  fetchTypes()
  fetchStates()

  if (route.query.id && history.state.data) {
    try {
      const data = JSON.parse(history.state.data)
      formData.value = {
        name: data.name || '',
        type_id: data.type_id?.toString() || '',
        serial_number: data.serial_number || '',
        location: data.location || '',
        state_id: data.state_id?.toString() || '',
      }
    } catch (e) {
      console.error('Failed to parse edit data', e)
    }
  }
})

const handleCancel = () => {
  router.push('/infra/operations')
}

const handleSubmit = async () => {
  const payload = {
    name: formData.value.name,
    type_id: parseInt(formData.value.type_id),
    serial_number: formData.value.serial_number || null,
    location: formData.value.location || null,
    state_id: formData.value.state_id ? parseInt(formData.value.state_id) : null,
  }

  if (isEditing.value) {
    await putData(API_ROUTES.UPDATE_INFRA_EQUIPMENT(route.query.id as string), payload)
  } else {
    await postData(API_ROUTES.CREATE_INFRA_EQUIPMENT, payload)
  }

  if (successCreate.value || successUpdate.value) {
    showCustomToast({
      message: isEditing.value
        ? 'Équipement modifié avec succès'
        : 'Équipement enregistré avec succès',
      type: 'success',
    })
    eventBus.emit('infraEquipmentUpdated')
    router.push('/infra/operations')
  } else {
    const errorMsg =
      errorCreate.value || errorUpdate.value || "Une erreur est survenue lors de l'enregistrement"
    showCustomToast({
      message: errorMsg,
      type: 'error',
    })
  }
}
</script>

<template>
  <DashFormLayout
    :link-back="'/infra/operations'"
    :title="isEditing ? 'Modifier Équipement' : 'Ajouter un Équipement'"
    group-route="/infra/operations"
    module="infra"
    :breadcrumb="[
      { label: 'Infrastructure', href: '/infra' },
      { label: 'Opérations', href: '/infra/operations' },
      { label: 'Équipements', href: '/infra/operations' },
      {
        label: isEditing ? 'Modification' : 'Nouvel Équipement',
        href: '/infra/operations/equipements/nouveau',
      },
    ]"
  >
    <form @submit.prevent="handleSubmit" class="w-full flex flex-col space-y-8">
      <FormSection
        :title="isEditing ? 'Modification de l\'Équipement' : 'Détails de l\'Équipement'"
        class="grid sm:grid-cols-2 lg:grid-cols-3 gap-y-6 gap-x-10"
      >
        <InputWrapper>
          <Label for="name">Nom de l'équipement<SpanRequired /></Label>
          <Input
            id="name"
            v-model="formData.name"
            type="text"
            placeholder="Ex: Table Banc"
            required
            class="w-full"
          />
        </InputWrapper>

        <InputWrapper>
          <Label for="type">Type<SpanRequired /></Label>
          <Select v-model="formData.type_id" required>
            <SelectTrigger class="!h-10 bg-white w-full">
              <SelectValue placeholder="Sélectionnez un type" />
            </SelectTrigger>
            <SelectContent>
              <SelectItem v-for="type in types" :key="type.id" :value="type.id.toString()">
                {{ type.name }}
              </SelectItem>
            </SelectContent>
          </Select>
        </InputWrapper>

        <InputWrapper>
          <Label for="serial_number">Numéro de série</Label>
          <Input
            id="serial_number"
            v-model="formData.serial_number"
            type="text"
            placeholder="Ex: SN-2024-001"
            class="w-full"
          />
        </InputWrapper>

        <InputWrapper>
          <Label for="location">Localisation</Label>
          <Input
            id="location"
            v-model="formData.location"
            type="text"
            placeholder="Ex: Salle 1A"
            class="w-full"
          />
        </InputWrapper>

        <InputWrapper>
          <Label for="state">État</Label>
          <Select v-model="formData.state_id">
            <SelectTrigger class="!h-10 bg-white w-full">
              <SelectValue placeholder="Sélectionnez un état" />
            </SelectTrigger>
            <SelectContent>
              <SelectItem v-for="state in states" :key="state.id" :value="state.id.toString()">
                {{ state.name }}
              </SelectItem>
            </SelectContent>
          </Select>
        </InputWrapper>
      </FormSection>

      <div class="flex items-center justify-end gap-2">
        <Button type="button" variant="outline" @click="handleCancel"> Annuler </Button>

        <Button type="submit" :disabled="loading">
          <IconifySpinner v-if="loading" class="mr-2" />
          <span v-if="loading">Enregistrement...</span>
          <span v-else>Enregistrer</span>
        </Button>
      </div>
    </form>
  </DashFormLayout>
</template>
