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

interface ExitFormData {
  article_id: string
  quantity: number | null
  reason: string
  exit_date: string
}

const formData = ref<ExitFormData>({
  article_id: '',
  quantity: null,
  reason: '',
  exit_date: new Date().toISOString().split('T')[0],
})

const { data: articlesRaw, fetchData: fetchArticles } = useGetApi(API_ROUTES.GET_STOCK_ARTICLES)

const articles = computed(() => {
  if (!articlesRaw.value) return []
  if (Array.isArray(articlesRaw.value)) return articlesRaw.value
  return (articlesRaw.value as any).data || []
})

const { loading: creating, postData, success: successCreate, error: errorCreate } = usePostApi()
const { loading: updating, putData, success: successUpdate, error: errorUpdate } = usePutApi()

const loading = computed(() => creating.value || updating.value)
const isEditing = computed(() => !!route.query.id)

onMounted(async () => {
  fetchArticles()

  if (route.query.id && history.state.data) {
    try {
      const data = JSON.parse(history.state.data)
      formData.value = {
        article_id: data.article_id?.toString() || '',
        quantity: data.quantity ? Number(data.quantity) : null,
        reason: data.reason || '',
        exit_date: data.exit_date || new Date().toISOString().split('T')[0],
      }
    } catch (e) {
      console.error('Failed to parse edit data', e)
    }
  }
})

const handleCancel = () => {
  router.push('/stock/operations/sorties')
}

const handleSubmit = async () => {
  const payload = {
    article_id: formData.value.article_id,
    quantity: formData.value.quantity,
    reason: formData.value.reason,
    exit_date: formData.value.exit_date,
  }

  if (isEditing.value) {
    await putData(API_ROUTES.UPDATE_STOCK_EXIT(route.query.id as string), payload)
  } else {
    await postData(API_ROUTES.CREATE_STOCK_EXIT, payload)
  }

  if (successCreate.value || successUpdate.value) {
    showCustomToast({
      message: isEditing.value ? 'Sortie modifiée avec succès' : 'Sortie enregistrée avec succès',
      type: 'success',
    })
    eventBus.emit('stockExitUpdated')
    router.push('/stock/operations/sorties')
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
    :link-back="'/stock/operations/sorties'"
    :title="isEditing ? 'Modifier Sortie' : 'Enregistrer une Sortie'"
    group-route="/stock/operations"
    module="stock"
    :breadcrumb="[
      { label: 'Stock', href: '/stock' },
      { label: 'Opérations', href: '/stock/operations' },
      { label: 'Sorties', href: '/stock/operations/sorties' },
      {
        label: isEditing ? 'Modification' : 'Nouvelle Sortie',
        href: '/stock/operations/sorties/nouveau',
      },
    ]"
  >
    <form @submit.prevent="handleSubmit" class="w-full flex flex-col space-y-8">
      <FormSection
        :title="isEditing ? 'Modification de la Sortie' : 'Détails de la Sortie'"
        class="grid sm:grid-cols-2 lg:grid-cols-3 gap-y-6 gap-x-10"
      >
        <InputWrapper>
          <Label for="article">Item<SpanRequired /></Label>
          <Select v-model="formData.article_id" required>
            <SelectTrigger class="!h-10 bg-white w-full">
              <SelectValue placeholder="Sélectionnez un item" />
            </SelectTrigger>
            <SelectContent>
              <SelectItem v-for="art in articles" :key="art.id" :value="art.id.toString()">
                {{ art.name }}
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
            class="w-full"
          />
        </InputWrapper>



        <InputWrapper>
          <Label for="reason">Motif</Label>
          <Input
            id="reason"
            v-model="formData.reason"
            type="text"
            placeholder="Ex: Dotation trimestrielle"
            class="w-full"
          />
        </InputWrapper>

        <InputWrapper>
          <Label for="exit_date">Date de sortie<SpanRequired /></Label>
          <Input id="exit_date" v-model="formData.exit_date" type="date" required class="w-full" />
        </InputWrapper>

      </FormSection>

      <div class="flex items-center justify-end gap-2">
        <Button type="button" variant="outline" @click="handleCancel"> Annuler </Button>

        <Button type="submit" :disabled="loading">
          <IconifySpinner v-if="loading" class="mr-2" />
          <span v-if="loading">Enregistrement...</span>
          <span v-else>Enregistrer Sortie</span>
        </Button>
      </div>
    </form>
  </DashFormLayout>
</template>
