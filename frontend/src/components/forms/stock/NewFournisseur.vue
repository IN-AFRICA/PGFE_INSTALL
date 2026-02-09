<script setup lang="ts">
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { ref, computed, onMounted } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import DashFormLayout from '@/components/templates/DashFormLayout.vue'
import FormSection from '@/components/atoms/FormSection.vue'
import InputWrapper from '@/components/atoms/InputWrapper.vue'
import SpanRequired from '@/components/atoms/SpanRequired.vue'
import { showCustomToast } from '@/utils/widgets/custom_toast.ts'
import { usePostApi } from '@/composables/usePostApi'
import { usePutApi } from '@/composables/usePutApi'
import { API_ROUTES } from '@/utils/constants/api_route'
import { eventBus } from '@/utils/eventBus'
import IconifySpinner from '@/components/ui/spinner/IconifySpinner.vue'

const router = useRouter()
const route = useRoute()

interface FournisseurFormData {
  name: string
  contact: string
  address: string
}

const formData = ref<FournisseurFormData>({
  name: '',
  contact: '',
  address: '',
})

const { loading: creating, postData, success: successCreate, error: errorCreate } = usePostApi()
const { loading: updating, putData, success: successUpdate, error: errorUpdate } = usePutApi()

const loading = computed(() => creating.value || updating.value)
const isEditing = computed(() => !!route.query.id)

onMounted(() => {
  if (route.query.id && history.state.data) {
    try {
      const data = JSON.parse(history.state.data)
      formData.value = {
        name: data.name || '',
        contact: data.contact || '',
        address: data.address || '',
      }
    } catch (e) {
      console.error('Failed to parse edit data', e)
    }
  }
})

const handleCancel = () => {
  router.push('/stock/prealables/fournisseurs')
}

const handleSubmit = async () => {
  const payload = {
    name: formData.value.name,
    contact: formData.value.contact,
    address: formData.value.address,
  }

  if (isEditing.value) {
    await putData(API_ROUTES.UPDATE_STOCK_PROVIDER(route.query.id as string), payload)
  } else {
    await postData(API_ROUTES.CREATE_STOCK_PROVIDER, payload)
  }

  if (successCreate.value || successUpdate.value) {
    showCustomToast({
      message: isEditing.value ? 'Fournisseur modifié avec succès' : 'Fournisseur créé avec succès',
      type: 'success',
    })
    eventBus.emit('stockProviderUpdated')
    router.push('/stock/prealables/fournisseurs')
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
    :link-back="'/stock/prealables/fournisseurs'"
    :title="isEditing ? 'Modifier Fournisseur' : 'Nouveau Fournisseur'"
    group-route="/stock/prealables/articles"
    module="stock"
    :breadcrumb="[
      { label: 'Accueil', href: '/', icon: 'hugeicons--home-01' },
      { label: 'Stock', href: '/stock' },
      { label: 'Préalables', href: '/stock/prealables/fournisseurs' },
      { label: 'Fournisseurs', href: '/stock/prealables/fournisseurs' },
      {
        label: isEditing ? 'Modification' : 'Nouveau',
        href: '/stock/prealables/fournisseurs/nouveau',
      },
    ]"
  >
    <form @submit.prevent="handleSubmit" class="w-full flex flex-col space-y-8">
      <FormSection
        title="Informations du fournisseur"
        class="grid sm:grid-cols-2 lg:grid-cols-3 gap-y-6 gap-x-10"
      >
        <InputWrapper class="lg:col-span-2">
          <Label for="name">Nom du fournisseur<SpanRequired /></Label>
          <Input
            id="name"
            v-model="formData.name"
            type="text"
            placeholder="Ex: Papeterie Moderne SARL"
            required
            class="w-full"
          />
        </InputWrapper>

        <InputWrapper>
          <Label for="contact">Contact (Tél/Email)<SpanRequired /></Label>
          <Input
            id="contact"
            v-model="formData.contact"
            type="text"
            placeholder="Ex: +243... ou contact@..."
            required
            class="w-full"
          />
        </InputWrapper>

        <InputWrapper class="lg:col-span-3">
          <Label for="address">Adresse</Label>
          <Input
            id="address"
            v-model="formData.address"
            type="text"
            placeholder="Ex: Avenue Kasavubu, Commune de la Gombe"
            class="w-full"
          />
        </InputWrapper>
      </FormSection>

      <div class="flex items-center justify-end gap-2">
        <Button type="button" variant="outline" @click="handleCancel"> Annuler </Button>

        <Button type="submit" :disabled="loading">
          <IconifySpinner v-if="loading" class="mr-2" />
          <span v-if="loading">Enregistrement...</span>
          <span v-else>{{ isEditing ? 'Modifier' : 'Enregistrer' }} le fournisseur</span>
        </Button>
      </div>
    </form>
  </DashFormLayout>
</template>
