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

interface ArticleFormData {
  name: string
  category_id: string
  provider_id: string
  min_threshold: number
  max_threshold: number | null
}

const formData = ref<ArticleFormData>({
  name: '',
  category_id: '',
  provider_id: '',
  min_threshold: 0,
  max_threshold: null,
})

const { data: categoriesRaw, fetchData: fetchCategories } = useGetApi(API_ROUTES.GET_STOCK_CATEGORIES)
const { data: providersRaw, fetchData: fetchProviders } = useGetApi(API_ROUTES.GET_STOCK_PROVIDERS)

const categories = computed(() => {
  if (!categoriesRaw.value) return []
  if (Array.isArray(categoriesRaw.value)) return categoriesRaw.value
  return (categoriesRaw.value as any).data || []
})

const providers = computed(() => {
  if (!providersRaw.value) return []
  if (Array.isArray(providersRaw.value)) return providersRaw.value
  return (providersRaw.value as any).data || []
})

const { loading: creating, postData, success: successCreate, error: errorCreate } = usePostApi()
const { loading: updating, putData, success: successUpdate, error: errorUpdate } = usePutApi()

const loading = computed(() => creating.value || updating.value)
const isEditing = computed(() => !!route.query.id)

onMounted(async () => {
  fetchCategories()
  fetchProviders()

  if (route.query.id && history.state.data) {
    try {
      const data = JSON.parse(history.state.data)
      formData.value = {
        name: data.name || '',
        category_id: data.category_id?.toString() || '',
        provider_id: data.provider_id?.toString() || '',
        min_threshold: Number(data.min_threshold) || 0,
        max_threshold: data.max_threshold ? Number(data.max_threshold) : null,
      }
    } catch (e) {
      console.error('Failed to parse edit data', e)
    }
  }
})

const handleCancel = () => {
  router.push('/stock/prealables/articles')
}

const handleSubmit = async () => {
  const payload = {
    name: formData.value.name,
    category_id: formData.value.category_id,
    provider_id: formData.value.provider_id,
    min_threshold: formData.value.min_threshold,
    max_threshold: formData.value.max_threshold,
  }

  if (isEditing.value) {
    await putData(API_ROUTES.UPDATE_STOCK_ARTICLE(route.query.id as string), payload)
  } else {
    await postData(API_ROUTES.CREATE_STOCK_ARTICLE, payload)
  }

  if (successCreate.value || successUpdate.value) {
    showCustomToast({
      message: isEditing.value ? 'Item modifié avec succès' : 'Item créé avec succès',
      type: 'success',
    })
    eventBus.emit('stockArticleUpdated')
    router.push('/stock/prealables/articles')
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
    :link-back="'/stock/prealables/articles'"
    :title="isEditing ? 'Modifier Item' : 'Nouvel Item'"
    group-route="/stock/prealables/articles"
    module="stock"
    :breadcrumb="[
      { label: 'Accueil', href: '/', icon: 'hugeicons--home-01' },
      { label: 'Stock', href: '/stock' },
      { label: 'Préalables', href: '/stock/prealables/articles' },
      { label: 'Items', href: '/stock/prealables/articles' },
      {
        label: isEditing ? 'Modification' : 'Nouveau',
        href: '/stock/prealables/articles/nouveau',
      },
    ]"
  >
    <form @submit.prevent="handleSubmit" class="w-full flex flex-col space-y-8">
      <FormSection
        title="Informations de l'item"
        class="grid sm:grid-cols-2 lg:grid-cols-3 gap-y-6 gap-x-10"
      >
        <InputWrapper class="lg:col-span-3">
          <Label for="name">Désignation de l'item<SpanRequired /></Label>
          <Input
            id="name"
            v-model="formData.name"
            type="text"
            placeholder="Ex: Stylo Bleu Bic"
            required
            class="w-full"
          />
        </InputWrapper>

        <InputWrapper>
          <Label for="category_id">Catégorie<SpanRequired /></Label>
          <Select v-model="formData.category_id" required>
            <SelectTrigger class="!h-10 bg-white w-full">
              <SelectValue placeholder="Sélectionnez une catégorie" />
            </SelectTrigger>
            <SelectContent>
              <SelectItem v-for="cat in categories" :key="cat.id" :value="cat.id.toString()">
                {{ cat.name }}
              </SelectItem>
            </SelectContent>
          </Select>
        </InputWrapper>

        <InputWrapper>
          <Label for="provider_id">Fournisseur par défaut<SpanRequired /></Label>
          <Select v-model="formData.provider_id" required>
            <SelectTrigger class="!h-10 bg-white w-full">
              <SelectValue placeholder="Sélectionnez un fournisseur" />
            </SelectTrigger>
            <SelectContent>
              <SelectItem v-for="p in providers" :key="p.id" :value="p.id.toString()">
                {{ p.name }}
              </SelectItem>
            </SelectContent>
          </Select>
        </InputWrapper>

        <InputWrapper>
          <Label for="min_threshold">Seuil d'alerte (Min)<SpanRequired /></Label>
          <Input
            id="min_threshold"
            v-model.number="formData.min_threshold"
            type="number"
            placeholder="Ex: 10"
            required
            class="w-full"
          />
        </InputWrapper>

        <InputWrapper>
          <Label for="max_threshold">Seuil Maximum</Label>
          <Input
            id="max_threshold"
            v-model.number="formData.max_threshold"
            type="number"
            placeholder="Ex: 100"
            class="w-full"
          />
        </InputWrapper>
      </FormSection>

      <div class="flex items-center justify-end gap-2">
        <Button type="button" variant="outline" @click="handleCancel"> Annuler </Button>

        <Button type="submit" :disabled="loading">
          <IconifySpinner v-if="loading" class="mr-2" />
          <span v-if="loading">Enregistrement...</span>
          <span v-else>{{ isEditing ? 'Modifier' : 'Enregistrer' }} l'item</span>
        </Button>
      </div>
    </form>
  </DashFormLayout>
</template>
