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

const statusOptions = ref([
  { value: 'active', label: 'Actif' },
  { value: 'inactive', label: 'Inactif' },
])

const formData = ref({
  name: '',
  quantity: '',
  daily_price: '',
  status: '',
  description: '',
  serial_number: '',
  mark_model: '',
  tech_specification: '',
  comments: '',
})

const handleCancel = () => {
  router.push('/location/prealables/equipement')
}

const handleSubmit = async () => {
  if (!formData.value.name || !formData.value.quantity || !formData.value.daily_price || !formData.value.status) {
    showCustomToast({
      message: 'Veuillez remplir tous les champs obligatoires',
      type: 'error',
    })
    return
  }

  const payload = {
    name: formData.value.name,
    quantity: Number(formData.value.quantity),
    daily_price: Number(formData.value.daily_price),
    status: formData.value.status,
    description: formData.value.description || null,
    serial_number: formData.value.serial_number || null,
    mark_model: formData.value.mark_model || null,
    tech_specification: formData.value.tech_specification || null,
    comments: formData.value.comments || null,
  }

  await postData(API_ROUTES.CREATE_RENTAL_EQUIPMENT, payload)

  if (success.value) {
    showCustomToast({
      message: 'Équipement enregistré avec succès',
      type: 'success',
    })
    router.push('/location/prealables/equipement')
  }
}
</script>

<template>
  <DashFormLayout
    :link-back="'/location/prealables/equipement'"
    title="Ajouter un Équipement"
    group-route="/location/prealables"
    module="location"
    :breadcrumb="[
      { label: 'Location', href: '/location' },
      { label: 'Préalables', href: '/location/prealables' },
      { label: 'Équipement', href: '/location/prealables/equipement' },
      { label: 'Nouvel Équipement', href: '/location/prealables/equipement/nouveau' },
    ]"
  >
    <form @submit.prevent="handleSubmit" class="w-full flex flex-col space-y-8">
      <FormSection
        title="Informations de base"
        class="grid sm:grid-cols-2 lg:grid-cols-3 gap-y-6 gap-x-10"
      >
        <InputWrapper>
          <Label for="name">Nom de l'équipement<SpanRequired /></Label>
          <Input
            id="name"
            v-model="formData.name"
            placeholder="Ex: Ordinateur portable Dell"
            required
            class="border-gray-200"
          />
        </InputWrapper>

        <InputWrapper>
          <Label for="quantity">Quantité<SpanRequired /></Label>
          <Input
            id="quantity"
            v-model="formData.quantity"
            type="number"
            min="0"
            placeholder="Ex: 10"
            required
            class="border-gray-200"
          />
        </InputWrapper>

        <InputWrapper>
          <Label for="daily_price">Prix journalier<SpanRequired /></Label>
          <Input
            id="daily_price"
            v-model="formData.daily_price"
            type="number"
            min="0"
            placeholder="Ex: 5000"
            required
            class="border-gray-200"
          />
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

        <InputWrapper>
          <Label for="serial_number">N° de série</Label>
          <Input
            id="serial_number"
            v-model="formData.serial_number"
            placeholder="Ex: SN2024-000001"
            class="border-gray-200"
          />
        </InputWrapper>

        <InputWrapper>
          <Label for="mark_model">Marque / Modèle</Label>
          <Input
            id="mark_model"
            v-model="formData.mark_model"
            placeholder="Ex: Dell Latitude 5520"
            class="border-gray-200"
          />
        </InputWrapper>

        <InputWrapper class="sm:col-span-2 lg:col-span-3">
          <Label for="description">Description</Label>
          <Textarea
            id="description"
            v-model="formData.description"
            placeholder="Description de l'équipement"
            class="border-gray-200 min-h-[80px]"
          />
        </InputWrapper>

        <InputWrapper class="sm:col-span-2 lg:col-span-3">
          <Label for="tech_specification">Spécification technique</Label>
          <Textarea
            id="tech_specification"
            v-model="formData.tech_specification"
            placeholder="Ex: Intel i7, 16GB RAM, 512GB SSD"
            class="border-gray-200 min-h-[80px]"
          />
        </InputWrapper>

        <InputWrapper class="sm:col-span-2 lg:col-span-3">
          <Label for="comments">Commentaires</Label>
          <Textarea
            id="comments"
            v-model="formData.comments"
            placeholder="Commentaires supplémentaires"
            class="border-gray-200 min-h-[80px]"
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
