<script setup lang="ts">
import { Button } from '@/components/ui/button'
import DashFormLayout from '@/components/templates/DashFormLayout.vue'
import FormSection from '@/components/atoms/FormSection.vue'
import InputWrapper from '@/components/atoms/InputWrapper.vue'
import { Label } from '@/components/ui/label'
import SpanRequired from '@/components/atoms/SpanRequired.vue'
import CustomDatePicker from '@/components/ui/CustomDatePicker.vue'
import { usePostApi } from '@/composables/usePostApi.ts'
import { API_ROUTES } from '@/utils/constants/api_route.ts'
import { toSqlDatetime } from '@/utils/utils'
import { Field, useForm } from 'vee-validate'
import { z } from 'zod'
import { toTypedSchema } from '@vee-validate/zod'
import { showCustomToast } from '@/utils/widgets/custom_toast.ts'
import { useRouter } from 'vue-router'
import { eventBus } from '@/utils/eventBus.ts'

// Validation schema for clôture d'exercice comptable
const schema = z
  .object({
    date: z.string().min(1, 'La date de clôture est requise'),
    start_date: z.string().min(1, 'La date de début est requise'),
    end_date: z.string().min(1, 'La date de fin est requise'),
  })
  .refine(
    (data) => {
      const start = new Date(data.start_date)
      const end = new Date(data.end_date)
      return start <= end
    },
    {
      message: 'La date de début doit être antérieure ou égale à la date de fin',
      path: ['end_date'],
    },
  )

const { handleSubmit, resetForm } = useForm({
  validationSchema: toTypedSchema(schema),
  initialValues: {
    date: new Date().toISOString().split('T')[0],
    start_date: '',
    end_date: '',
  },
})

const { response, error, loading, postData } = usePostApi<any>()
const router = useRouter()

const onSubmit = handleSubmit(async (values) => {
  try {
    const formattedValues = {
      date: toSqlDatetime(values.date as string | Date),
      start_date: toSqlDatetime(values.start_date as string | Date),
      end_date: toSqlDatetime(values.end_date as string | Date),
    }

    // Envoie vers l'endpoint fourni par l'utilisateur
    await postData(API_ROUTES.CREATE_CLOTURE_EXERCICE, formattedValues)

    // Gestion des erreurs renvoyées par le composable
    if (error.value) {
      showCustomToast({
        message:
          typeof error.value === 'string'
            ? error.value
            : "Erreur lors de la création de la clôture d'exercice.",
        type: 'error',
      })
      return
    }

    if (response.value) {
      if (response.value.user_id) {
        showCustomToast({
          message: response.value.message || "Clôture d'exercice créée avec succès",
          type: 'success',
        })
        resetForm()
        eventBus.emit('clotureExerciceUpdated')
        router.push('/comptabilite/saisie-operations')
      } else if (response.value.message) {
        // Cas d'erreur renvoyé par l'API: { message: '...' }
        showCustomToast({ message: response.value.message, type: 'error' })
      } else {
        showCustomToast({ message: 'Réponse inattendue du serveur.', type: 'error' })
      }
    } else {
      showCustomToast({ message: 'Aucune réponse du serveur.', type: 'error' })
    }
  } catch (e: any) {
    console.error('Erreur lors de la soumission:', e)
    showCustomToast({ message: "Une erreur inattendue s'est produite.", type: 'error' })
  }
})
</script>

<template>
  <DashFormLayout
    title="Clôture d'exercice"
    link-back="/comptabilite/saisie-operations"
    group-route="/comptabilite/saisie-operations"
    module="compta"
    :breadcrumb="[
      { label: 'Comptabilité', href: '/comptabilite' },
      { label: 'saisie opérations', href: '/comptabilite/saisie-operations' },
      { label: 'Clôture d\'exercice', href: '/comptabilite/saisie-operations/cloture-exercice' },
    ]"
  >
    <form @submit.prevent="onSubmit" class="mt-10 space-y-8">
      <FormSection
        class="grid sm:grid-cols-2 lg:grid-cols-3 gap-y-6 gap-x-10"
        title="Clôture d'exercice comptable"
      >
        <!-- Date de clôture -->
        <InputWrapper>
          <Label class="text-sm">
            Date de clôture
            <SpanRequired />
          </Label>
          <Field name="date" v-slot="{ field, errorMessage, meta }">
            <CustomDatePicker
              v-model="field.value"
              @update:model-value="field['onUpdate:modelValue']"
            />
            <span v-if="meta.touched && errorMessage" class="text-sm text-red-500">{{
              errorMessage
            }}</span>
          </Field>
        </InputWrapper>

        <!-- Date de début -->
        <InputWrapper>
          <Label class="text-sm">
            Date de début de l'exercice
            <SpanRequired />
          </Label>
          <Field name="start_date" v-slot="{ field, errorMessage, meta }">
            <CustomDatePicker
              v-model="field.value"
              @update:model-value="field['onUpdate:modelValue']"
            />
            <span v-if="meta.touched && errorMessage" class="text-sm text-red-500">{{
              errorMessage
            }}</span>
          </Field>
        </InputWrapper>

        <!-- Date de fin -->
        <InputWrapper>
          <Label class="text-sm">
            Date de fin de l'exercice
            <SpanRequired />
          </Label>
          <Field name="end_date" v-slot="{ field, errorMessage, meta }">
            <CustomDatePicker
              v-model="field.value"
              @update:model-value="field['onUpdate:modelValue']"
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
          :disabled="loading"
          @click="router.push('/comptabilite/saisie-operations')"
        >
          <span class="flex iconify hugeicons--cancel-01 mr-1.5"></span>
          Annuler
        </Button>
        <Button type="submit" :disabled="loading">
          <span class="flex iconify hugeicons--tick-02 mr-1.5"></span>
          {{ loading ? 'Clôture en cours...' : "Clôturer l'exercice" }}
        </Button>
      </div>
    </form>
  </DashFormLayout>
</template>
