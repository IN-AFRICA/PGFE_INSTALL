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

interface EntryFormData {
  article_id: string
  quantity: number | null
  entry_date: string
  note: string
}

const formData = ref<EntryFormData>({
  article_id: '',
  quantity: null,
  entry_date: new Date().toISOString().split('T')[0],
  note: '',
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
        entry_date: data.entry_date || new Date().toISOString().split('T')[0],
        note: data.note || '',
      }
    } catch (e) {
      console.error('Failed to parse edit data', e)
    }
  }
})

const handleCancel = () => {
  router.push('/stock/operations/entrees')
}

const handleSubmit = async () => {
  const payload = {
    article_id: formData.value.article_id,
    quantity: formData.value.quantity,
    entry_date: formData.value.entry_date,
    note: formData.value.note,
  }

  if (isEditing.value) {
    await putData(API_ROUTES.UPDATE_STOCK_ENTRY(route.query.id as string), payload)
  } else {
    await postData(API_ROUTES.CREATE_STOCK_ENTRY, payload)
  }

  if (successCreate.value || successUpdate.value) {
    showCustomToast({
      message: isEditing.value ? 'Entrée modifiée avec succès' : 'Entrée enregistrée avec succès',
      type: 'success',
    })
    eventBus.emit('stockEntryUpdated')
    router.push('/stock/operations/entrees')
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
    :link-back="'/stock/operations/entrees'"
    :title="isEditing ? 'Modifier Entrée' : 'Enregistrer une Entrée'"
    group-route="/stock/operations"
    module="stock"
    :breadcrumb="[
      { label: 'Stock', href: '/stock' },
      { label: 'Opérations', href: '/stock/operations' },
      { label: 'Entrées', href: '/stock/operations/entrees' },
      {
        label: isEditing ? 'Modification' : 'Nouvelle Entrée',
        href: '/stock/operations/entrees/nouveau',
      },
    ]"
  >
    <form @submit.prevent="handleSubmit" class="w-full flex flex-col space-y-8">
      <FormSection
        :title="isEditing ? 'Modification de l\'Entrée' : 'Détails de l\'Entrée'"
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
          <Label for="entry_date">Date d'entrée<SpanRequired /></Label>
          <Input id="entry_date" v-model="formData.entry_date" type="date" required class="w-full" />
        </InputWrapper>

        <InputWrapper>
          <Label for="note">Note</Label>
          <Input
            id="note"
            v-model="formData.note"
            type="text"
            placeholder="Ex: Livraison partielle"
            class="w-full"
          />
        </InputWrapper>
      </FormSection>

      <div class="flex items-center justify-end gap-2">
        <Button type="button" variant="outline" @click="handleCancel"> Annuler </Button>

        <Button type="submit" :disabled="loading">
          <IconifySpinner v-if="loading" class="mr-2" />
          <span v-if="loading">Enregistrement...</span>
          <span v-else>Enregistrer Entrée</span>
        </Button>
      </div>
    </form>
  </DashFormLayout>
</template>
