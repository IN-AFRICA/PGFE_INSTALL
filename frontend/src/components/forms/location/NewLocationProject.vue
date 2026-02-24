<script setup lang="ts">
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Textarea } from '@/components/ui/textarea'
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from '@/components/ui/select'
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import DashFormLayout from '@/components/templates/DashFormLayout.vue'
import FormSection from '@/components/atoms/FormSection.vue'
import InputWrapper from '@/components/atoms/InputWrapper.vue'
import SpanRequired from '@/components/atoms/SpanRequired.vue'
import IconifySpinner from '@/components/ui/spinner/IconifySpinner.vue'
import { showCustomToast } from '@/utils/widgets/custom_toast'
import { usePostApi } from '@/composables/usePostApi'
import { API_ROUTES } from '@/utils/constants/api_route'

const router = useRouter()
const { postData, loading, success } = usePostApi()

const formData = ref({
  name: '',
  start_date: '',
  end_date: '',
  description: '',
})

const handleCancel = () => {
  router.push('/location/prealables/projects')
}

const handleSubmit = async () => {
  if (!formData.value.name || !formData.value.start_date) {
    showCustomToast({
      message: 'Veuillez remplir tous les champs obligatoires',
      type: 'error',
    })
    return
  }

  // Validation de la date de fin
  if (formData.value.end_date && formData.value.end_date < formData.value.start_date) {
    showCustomToast({
      message: 'La date de fin doit être supérieure ou égale à la date de début',
      type: 'error',
    })
    return
  }

  const payload: any = {
    name: formData.value.name,
    start_date: formData.value.start_date,
  }

  if (formData.value.end_date) {
    payload.end_date = formData.value.end_date
  }

  if (formData.value.description) {
    payload.description = formData.value.description
  }

  await postData(API_ROUTES.CREATE_RENTAL_PROJECT, payload)

  if (success.value) {
    showCustomToast({
      message: 'Projet enregistré avec succès',
      type: 'success',
    })
    router.push('/location/prealables/projects')
  }
}
</script>

<template>
  <DashFormLayout
    :link-back="'/location/prealables/projects'"
    title="Ajouter un Projet"
    group-route="/location/prealables"
    module="location"
    :breadcrumb="[
      { label: 'Location', href: '/location' },
      { label: 'Préalables', href: '/location/prealables' },
      { label: 'Projets', href: '/location/prealables/projects' },
      { label: 'Nouveau Projet', href: '/location/prealables/projects/nouveau' },
    ]"
  >
    <form @submit.prevent="handleSubmit" class="w-full flex flex-col space-y-8">
      <FormSection
        title="Informations du projet"
        class="grid sm:grid-cols-2 gap-y-6 gap-x-10"
      >
        <InputWrapper>
          <Label for="name">Nom du projet<SpanRequired /></Label>
          <Input
            id="name"
            v-model="formData.name"
            type="text"
            placeholder="Ex: Projet de construction"
            required
            class="border-gray-200"
          />
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
          <Label for="end_date">Date de fin</Label>
          <Input
            id="end_date"
            v-model="formData.end_date"
            type="date"
            class="border-gray-200"
          />
        </InputWrapper>

        <InputWrapper class="sm:col-span-2">
          <Label for="description">Description</Label>
          <Textarea
            id="description"
            v-model="formData.description"
            placeholder="Description du projet..."
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
