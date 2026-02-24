<script setup lang="ts">
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Textarea } from '@/components/ui/textarea'
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
  phone: '',
  email: '',
  address: '',
})

const handleCancel = () => {
  router.push('/location/prealables/locataire')
}

const handleSubmit = async () => {
  // Validation
  if (!formData.value.name) {
    showCustomToast({ message: 'Le nom est requis', type: 'error' })
    return
  }
  if (!formData.value.phone) {
    showCustomToast({ message: 'Le téléphone est requis', type: 'error' })
    return
  }

  const payload = {
    name: formData.value.name,
    phone: formData.value.phone,
    email: formData.value.email || null,
    address: formData.value.address || null,
  }

  await postData(API_ROUTES.CREATE_RENTAL_CLIENT, payload)

  if (success.value) {
    showCustomToast({
      message: 'Client enregistré avec succès',
      type: 'success',
    })
    router.push('/location/prealables/locataire')
  }
}
</script>

<template>
  <DashFormLayout
    :link-back="'/location/prealables/locataire'"
    title="Ajouter un Client"
    group-route="/location/prealables"
    module="location"
    :breadcrumb="[
      { label: 'Location', href: '/location' },
      { label: 'Préalables', href: '/location/prealables' },
      { label: 'Clients', href: '/location/prealables/locataire' },
      { label: 'Nouveau Client', href: '/location/prealables/locataire/nouveau' },
    ]"
  >
    <form @submit.prevent="handleSubmit" class="w-full flex flex-col space-y-8">
      <FormSection
        title="Informations du client"
        class="grid sm:grid-cols-2 gap-y-6 gap-x-10"
      >
        <InputWrapper>
          <Label for="name">Nom / Raison sociale<SpanRequired /></Label>
          <Input
            id="name"
            v-model="formData.name"
            placeholder="Ex: SARL Bukavu Trading"
            required
            class="border-gray-200"
          />
        </InputWrapper>

        <InputWrapper>
          <Label for="phone">Téléphone<SpanRequired /></Label>
          <Input
            id="phone"
            v-model="formData.phone"
            placeholder="Ex: +243 997 123 456"
            required
            class="border-gray-200"
          />
        </InputWrapper>

        <InputWrapper>
          <Label for="email">Email</Label>
          <Input
            id="email"
            v-model="formData.email"
            type="email"
            placeholder="Ex: contact@example.com"
            class="border-gray-200"
          />
        </InputWrapper>

        <InputWrapper>
          <Label for="address">Adresse</Label>
          <Input
            id="address"
            v-model="formData.address"
            placeholder="Ex: Avenue Lumumba, Gombe, Kinshasa"
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
