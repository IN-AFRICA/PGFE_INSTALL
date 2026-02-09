<script setup lang="ts">
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import ComptaLayout from '@/components/templates/compta/ComptaLayout.vue'
import FormSection from '@/components/atoms/FormSection.vue'
import InputWrapper from '@/components/atoms/InputWrapper.vue'
import { Label } from '@/components/ui/label'
import SpanRequired from '@/components/atoms/SpanRequired.vue'
// (Only name & code are required for new immobilisation)
import { usePostApi } from '@/composables/usePostApi.ts'
import { useGetApi } from '@/composables/useGetApi'
import Spinner from '@/components/ui/spinner/Spinner.vue'
import { usePutApi } from '@/composables/usePutApi'
import { useRoute } from 'vue-router'
import { API_ROUTES } from '@/utils/constants/api_route.ts'
import { Field, useForm } from 'vee-validate'
import { computed, type Ref } from 'vue'
import { z } from 'zod'
import { toTypedSchema } from '@vee-validate/zod'
import { showCustomToast } from '@/utils/widgets/custom_toast.ts'
import { useRouter } from 'vue-router'
import { eventBus } from '@/utils/eventBus.ts'

// Validation schema for immobilisation: only `name` and `code` are required (strings)
const schema = z.object({
  name: z.string().min(1, 'Le nom est requis').max(255, '255 caractères maximum'),
  code: z.string().min(1, 'Le code est requis').max(50, '50 caractères maximum'),
})

const { handleSubmit, resetForm } = useForm({
  validationSchema: toTypedSchema(schema),
  initialValues: {
    name: '',
    code: '',
  },
})

const { loading: postLoading, postData } = usePostApi<Record<string, unknown>>()
const { loading: putLoading, putData } = usePutApi<Record<string, unknown>>()
const router = useRouter()
const route = useRoute()

// Detect edit mode via query param `id`
const editId = route.query.id ? String(route.query.id) : null
const isEditMode = !!editId

// Consolidate loading state
const isSubmitting = computed(() => postLoading.value || putLoading.value)

// We declare an outer ref so we can assign the loading ref returned by useGetApi
let getLoading: Ref<boolean> | undefined

// If edit mode, fetch existing immobilisation and set form values
if (isEditMode && editId) {
  const endpoint = API_ROUTES.GET_ONE_IMMOBILISATION(editId as unknown as number)
  const {
    data: existingData,
    fetchData,
    loading,
  } = useGetApi<{
    name: string
    code: string
    school_id: number
    user_id: number
  }>(endpoint)
  getLoading = loading
  const loadExisting = async () => {
    await fetchData()
    if (existingData.value) {
      // Set form values
      resetForm({ values: { name: existingData.value.name, code: existingData.value.code } })
    }
  }
  loadExisting()
}

// Combined busy state (fetching for edit or submitting)
const isBusy = computed(() => isSubmitting.value || Boolean(getLoading?.value))

const onSubmit = handleSubmit(async (values) => {
  try {
    const formattedValues = {
      name: values.name,
      code: values.code,
    }

    console.log("Données d'immobilisation à envoyer:", formattedValues)

    // Send to API (POST for create, PUT for update)
    if (isEditMode && editId) {
      const endpoint = API_ROUTES.UPDATE_IMMOBILISATION(editId as unknown as number)
      await putData(endpoint, formattedValues)
    } else {
      await postData(API_ROUTES.CREATE_IMMOBILISATION, formattedValues)
    }

    showCustomToast({
      message: isEditMode
        ? 'Immobilisation mise à jour avec succès'
        : 'Immobilisation créée avec succès',
      type: 'success',
    })

    resetForm()
    eventBus.emit('immobilisationUpdated')
    router.push('immobilisations')
  } catch (e: unknown) {
    const errorMsg = e instanceof Error ? e.message : String(e)
    console.error('Erreur lors de la soumission:', errorMsg)
    showCustomToast({ message: "Une erreur inattendue s'est produite.", type: 'error' })
  }
})
</script>

<template>
  <ComptaLayout
    activeBread="Nouvelle Immobilisation"
    active-tag-name="immobilisations"
    group="operations"
    :show-header="false"
  >
    <div v-if="isEditMode && getLoading" class="mt-10 flex items-center justify-center">
      <div class="flex flex-col items-center justify-center w-full gap-3">
        <span class="iconify hugeicons--loading-03 animate-spin text-4xl text-blue-500"></span>
        <p class="text-sm text-foreground-muted">Chargement de l'immobilisation...</p>
      </div>
    </div>
    <form v-else @submit.prevent="onSubmit" class="mt-10 space-y-8">
      <FormSection
        class="grid sm:grid-cols-2 lg:grid-cols-3 gap-y-6 gap-x-10"
        :title="isEditMode ? 'Modifier l\'immobilisation' : 'Nouvelle immobilisation'"
      >
        <!-- Nom -->
        <InputWrapper class="lg:col-span-1">
          <Label class="text-sm">
            Nom
            <SpanRequired />
          </Label>
          <Field name="name" v-slot="{ field, errorMessage, meta }">
            <Input
              v-bind="field"
              type="text"
              :disabled="isBusy"
              class="bg-gray-100 transition-all h-10 rounded-md"
              placeholder="Nom de l'immobilisation"
            />
            <span v-if="meta.touched && errorMessage" class="text-sm text-red-500">{{
              errorMessage
            }}</span>
          </Field>
        </InputWrapper>

        <!-- Code -->
        <InputWrapper class="lg:col-span-1">
          <Label class="text-sm">
            Code
            <SpanRequired />
          </Label>
          <Field name="code" v-slot="{ field, errorMessage, meta }">
            <Input
              v-bind="field"
              type="text"
              :disabled="isBusy"
              class="bg-gray-100 transition-all h-10 rounded-md"
              placeholder="Ex: IMM-2025"
            />
            <span v-if="meta.touched && errorMessage" class="text-sm text-red-500">{{
              errorMessage
            }}</span>
          </Field>
        </InputWrapper>
      </FormSection>
      <div class="flex items-center justify-end gap-2">
        <Button
          variant="outline"
          type="button"
          :disabled="isBusy"
          @click="router.push('/comptabilite/saisie-operations/immobilisations')"
        >
          <span class="flex iconify hugeicons--cancel-01 mr-1.5"></span>
          Annuler
        </Button>
        <Button type="submit" :disabled="isBusy">
          <span class="flex iconify hugeicons--add-01"></span>
          <span v-if="!isSubmitting"
            >{{ isEditMode ? 'Mettre à jour' : 'Enregistrer' }} l'immobilisation</span
          >
          <span v-else>Enregistrement...</span>
        </Button>
      </div>
    </form>
  </ComptaLayout>
</template>
