<script setup lang="ts">
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import ComptaLayout from '@/components/templates/compta/ComptaLayout.vue'
import FormSection from '@/components/atoms/FormSection.vue'
import InputWrapper from '@/components/atoms/InputWrapper.vue'
import { Label } from '@/components/ui/label'
import SpanRequired from '@/components/atoms/SpanRequired.vue'
import {
  Select,
  SelectTrigger,
  SelectValue,
  SelectItem,
  SelectGroup,
  SelectContent,
} from '@/components/ui/select'
import { usePostApi } from '@/composables/usePostApi'
import { API_ROUTES } from '@/utils/constants/api_route'
import { Field, useForm } from 'vee-validate'
import { z } from 'zod'
import { toTypedSchema } from '@vee-validate/zod'
import { showCustomToast } from '@/utils/widgets/custom_toast'
import { computed, ref } from 'vue'
import { useRouter } from 'vue-router'
import { eventBus } from '@/utils/eventBus'
import { toSqlDatetime } from '@/utils/utils'

// Validation schema for amortissement
const schema = z.object({
  name: z.string().min(1, 'La désignation est requise').max(255, '255 caractères maximum'),
  purchase_date: z.string().min(1, "La date d'acquisition est requise"),
  amount: z.coerce
    .number({ invalid_type_error: 'La valeur doit être un nombre' })
    .min(0, 'La valeur doit être positive'),
  number_years: z.coerce
    .number({ invalid_type_error: 'La durée doit être un nombre' })
    .min(1, "La durée doit être d'au moins 1 an"),
  model: z.enum(['lineaire', 'degressif'], { required_error: 'La méthode est requise' }),
  immo_account_id: z
    .string()
    .min(1, 'Le compte immobilisation est requis')
    .max(50, '50 caractères maximum'),
  immo_sub_account_id: z
    .string()
    .min(1, 'Le compte amortissement est requis')
    .max(50, '50 caractères maximum'),
  code: z.string().min(1, 'Le code est requis').max(50, '50 caractères maximum'),
})

const { handleSubmit, resetForm, values } = useForm({
  validationSchema: toTypedSchema(schema),
  initialValues: {
    name: '',
    purchase_date: new Date().toISOString().split('T')[0],
    amount: 0,
    number_years: 3,
    model: 'lineaire',
    immo_account_id: '',
    immo_sub_account_id: '',
    code: '0',
  },
})

// API POST composable - TODO: Remplacer par l'endpoint réel quand disponible
const { response, error, loading, postData } = usePostApi<any>()
const router = useRouter()

// Calculer le taux d'amortissement
const tauxCalcule = computed(() => {
  const duree = values.number_years || 1
  if (values.model === 'lineaire') {
    return (100 / duree).toFixed(2)
  } else {
    // Méthode dégressive: taux linéaire * coefficient (généralement 1.75 ou 2)
    return ((100 / duree) * 1.75).toFixed(2)
  }
})

const onSubmit = handleSubmit(async (values) => {
  try {
    const formattedValues = {
      ...values,
      date_acquisition: toSqlDatetime(values.purchase_date),
      taux: parseFloat(tauxCalcule.value),
    }

    await postData(API_ROUTES.CREATE_AMORTISSEMENT, formattedValues)

    if (error.value) {
      showCustomToast({
        message:
          typeof error.value === 'string'
            ? error.value
            : "Erreur lors de la création de l'amortissement.",
        type: 'error',
      })
      return
    }

    if (response.value) {
      showCustomToast({
        message: response.value.message || 'Amortissement créé avec succès',
        type: 'success',
      })
      resetForm()
      eventBus.emit('amortissementUpdated')
      router.push('/comptabilite/saisie-operations/amortissements')
    } else {
      showCustomToast({
        message: 'Aucune réponse du serveur.',
        type: 'error',
      })
    }
  } catch (e: unknown) {
    const errorMessage = e instanceof Error ? e.message : "Une erreur inattendue s'est produite."
    console.error("Erreur lors de la création de l'amortissement:", errorMessage)
    showCustomToast({ message: errorMessage, type: 'error' })
  }
})

// Plus besoin de charger les comptes pour les amortissements
</script>

<template>
  <ComptaLayout
    activeBread="Nouvel Amortissement"
    active-tag-name="amortissements"
    group="operations"
    :show-header="false"
  >
    <form @submit.prevent="onSubmit" class="mt-10 space-y-8">
      <FormSection
        class="grid sm:grid-cols-2 lg:grid-cols-3 gap-y-6 gap-x-10"
        title="Nouvel amortissement"
      >
        <!-- Désignation -->
        <InputWrapper class="lg:col-span-2">
          <Label class="text-sm">
            Désignation
            <SpanRequired />
          </Label>
          <Field name="name" v-slot="{ field, errorMessage, meta }">
            <Input
              v-bind="field"
              type="text"
              class="bg-gray-100 transition-all h-10 rounded-md"
              placeholder="Ex: Ordinateur portable HP"
            />
            <span v-if="meta.touched && errorMessage" class="text-sm text-red-500">{{
              errorMessage
            }}</span>
          </Field>
        </InputWrapper>

        <!-- Date d'acquisition -->
        <InputWrapper>
          <Label class="text-sm">
            Date d'acquisition
            <SpanRequired />
          </Label>
          <Field name="purchase_date" v-slot="{ field, errorMessage, meta }">
            <Input v-bind="field" type="date" class="bg-gray-100 transition-all h-10 rounded-md" />
            <span v-if="meta.touched && errorMessage" class="text-sm text-red-500">{{
              errorMessage
            }}</span>
          </Field>
        </InputWrapper>

        <!-- Valeur d'acquisition -->
        <InputWrapper>
          <Label class="text-sm">
            Valeur d'acquisition (FC)
            <SpanRequired />
          </Label>
          <Field name="amount" v-slot="{ field, errorMessage, meta }">
            <Input
              v-bind="field"
              type="number"
              step="0.01"
              min="0"
              class="bg-gray-100 transition-all h-10 rounded-md"
              placeholder="0.00"
            />
            <span v-if="meta.touched && errorMessage" class="text-sm text-red-500">{{
              errorMessage
            }}</span>
          </Field>
        </InputWrapper>

        <!-- Durée d'amortissement -->
        <InputWrapper>
          <Label class="text-sm">
            Durée (années)
            <SpanRequired />
          </Label>
          <Field name="number_years" v-slot="{ field, errorMessage, meta }">
            <Input
              v-bind="field"
              type="number"
              min="1"
              class="bg-gray-100 transition-all h-10 rounded-md"
              placeholder="3"
            />
            <span v-if="meta.touched && errorMessage" class="text-sm text-red-500">{{
              errorMessage
            }}</span>
          </Field>
        </InputWrapper>

        <!-- Méthode d'amortissement -->
        <InputWrapper>
          <Label class="text-sm">
            Méthode d'amortissement
            <SpanRequired />
          </Label>
          <Field name="model" v-slot="{ field, errorMessage, meta }">
            <Select :model-value="field.value" @update:model-value="(v: any) => field.onChange(v)">
              <SelectTrigger class="!h-10 bg-white w-full">
                <SelectValue placeholder="Sélectionner la méthode" />
              </SelectTrigger>
              <SelectContent>
                <SelectGroup>
                  <SelectItem value="lineaire">Linéaire</SelectItem>
                  <SelectItem value="degressif">Dégressif</SelectItem>
                </SelectGroup>
              </SelectContent>
            </Select>
            <span v-if="meta.touched && errorMessage" class="text-sm text-red-500">{{
              errorMessage
            }}</span>
          </Field>
        </InputWrapper>

        <!-- Compte immobilisation -->
        <InputWrapper>
          <Label class="text-sm">
            Compte immobilisation
            <SpanRequired />
          </Label>
          <Field name="immo_account_id" v-slot="{ field, errorMessage, meta }">
            <Input
              v-bind="field"
              type="text"
              class="bg-gray-100 transition-all h-10 rounded-md"
              placeholder="Ex: 2410"
            />
            <span v-if="meta.touched && errorMessage" class="text-sm text-red-500">{{
              errorMessage
            }}</span>
          </Field>
        </InputWrapper>

        <!-- Compte amortissement -->
        <InputWrapper>
          <Label class="text-sm">
            Compte amortissement
            <SpanRequired />
          </Label>
          <Field name="immo_sub_account_id" v-slot="{ field, errorMessage, meta }">
            <Input
              v-bind="field"
              type="text"
              class="bg-gray-100 transition-all h-10 rounded-md"
              placeholder="Ex: 2841"
            />
            <span v-if="meta.touched && errorMessage" class="text-sm text-red-500">{{
              errorMessage
            }}</span>
          </Field>
        </InputWrapper>

        <!-- Valeur résiduelle -->
        <InputWrapper>
          <Label class="text-sm"> Valeur résiduelle (FC) </Label>
          <Field name="code" v-slot="{ field, errorMessage, meta }">
            <Input
              v-bind="field"
              type="text"
              step="0.01"
              min="0"
              class="bg-gray-100 transition-all h-10 rounded-md"
              placeholder="0.00"
            />
            <span v-if="meta.touched && errorMessage" class="text-sm text-red-500">{{
              errorMessage
            }}</span>
          </Field>
        </InputWrapper>

        <!-- Taux calculé (lecture seule) -->
        <InputWrapper class="lg:col-span-2">
          <Label class="text-sm"> Taux d'amortissement calculé </Label>
          <div class="bg-blue-50 p-3 rounded-md border border-blue-200">
            <p class="text-lg font-semibold text-blue-700">{{ tauxCalcule }}%</p>
            <p class="text-xs text-gray-600 mt-1">
              Calculé automatiquement selon la méthode et la durée
            </p>
          </div>
        </InputWrapper>
      </FormSection>
      <div class="flex items-center justify-end gap-2">
        <Button
          variant="outline"
          type="button"
          :disabled="loading"
          @click="router.push('/comptabilite/saisie-operations/amortissements')"
        >
          <span class="flex iconify hugeicons--cancel-01 mr-1.5"></span>
          Annuler
        </Button>
        <Button type="submit" :disabled="loading">
          <span class="flex iconify hugeicons--add-01"></span>
          <span v-if="!loading">Enregistrer l'amortissement</span>
          <span v-else>Enregistrement...</span>
        </Button>
      </div>
    </form>
  </ComptaLayout>
</template>
