<script setup lang="ts">
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Textarea } from '@/components/ui/textarea'
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
import { usePostApi } from '@/composables/usePostApi.ts'
import { usePutApi } from '@/composables/usePutApi'
import { useGetApi } from '@/composables/useGetApi.ts'
import { API_ROUTES } from '@/utils/constants/api_route.ts'
import { Field, useForm } from 'vee-validate'
import { z } from 'zod'
import { toTypedSchema } from '@vee-validate/zod'
import { showCustomToast } from '@/utils/widgets/custom_toast.ts'
import { computed, onMounted, watch, ref } from 'vue'
import Spinner from '@/components/ui/spinner/Spinner.vue'
import { useRoute } from 'vue-router'
import { useRouter } from 'vue-router'
import { eventBus } from '@/utils/eventBus.ts'
import { toSqlDatetime } from '@/utils/utils'

interface JournalEntry {
  id: number
  date: string
  description: string
  montant: number
  input_account_id: number
  output_account_id: number
  account_plan_id: number
  sub_account_plan_id: number
  account_id: number
}

// Validation schema for journal entry
const schema = z.object({
  date: z.string().min(1, 'La date est requise'),
  description: z.string().min(1, 'La description est requise').max(255, '255 caractères maximum'),
  montant: z.coerce
    .number({ invalid_type_error: 'Le montant doit être un nombre' })
    .min(0, 'Le montant doit être positif'),
  input_account_id: z
    .union([
      z
        .string()
        .refine((v) => v !== '', "Le compte d'entrée est requis")
        .transform((v) => Number(v)),
      z.number(),
    ])
    .refine((v) => Number(v) > 0, "Le compte d'entrée est requis"),
  output_account_id: z
    .union([
      z
        .string()
        .refine((v) => v !== '', 'Le compte de sortie est requis')
        .transform((v) => Number(v)),
      z.number(),
    ])
    .refine((v) => Number(v) > 0, 'Le compte de sortie est requis'),
  account_plan_id: z
    .union([
      z
        .string()
        .refine((v) => v !== '', 'Le plan comptable est requis')
        .transform((v) => Number(v)),
      z.number(),
    ])
    .refine((v) => Number(v) > 0, 'Le plan comptable est requis'),
  sub_account_plan_id: z
    .union([
      z
        .string()
        .refine((v) => v !== '', 'Le sous-compte est requis')
        .transform((v) => Number(v)),
      z.number(),
    ])
    .refine((v) => Number(v) > 0, 'Le sous-compte est requis'),
  account_id: z
    .union([
      z
        .string()
        .refine((v) => v !== '', 'Le compte est requis')
        .transform((v) => Number(v)),
      z.number(),
    ])
    .refine((v) => Number(v) > 0, 'Le compte est requis'),
})

const { handleSubmit, resetForm, setFieldValue, values } = useForm({
  validationSchema: toTypedSchema(schema),
  initialValues: {
    date: new Date().toISOString().split('T')[0],
    description: '',
    montant: 0,
    input_account_id: '',
    output_account_id: '',
    account_plan_id: '',
    sub_account_plan_id: '',
    account_id: '',
  },
})

// API POST composable - TODO: Remplacer par l'endpoint réel quand disponible
const {
  response: postResponse,
  error: postError,
  loading: postLoading,
  postData,
} = usePostApi<any>()
const { putData, loading: putLoading, error: putError, response: putResponse } = usePutApi<any>()
const router = useRouter()
const route = useRoute()
const journalId = computed(() => route.params?.id)
// If a journalId exists it's a view (not create) by default, so edit mode is off
const editMode = ref(route.query.edit === 'true' || !journalId.value)

const saving = computed(() => postLoading?.value || false || putLoading?.value || false)

const onSubmit = handleSubmit(async (values) => {
  try {
    const formattedValues = {
      ...values,
      date: toSqlDatetime(values.date as string | Date),
    }
    if (journalId.value) {
      await putData(API_ROUTES.UPDATE_JOURNAL(journalId.value as any), formattedValues)
    } else {
      await postData(API_ROUTES.CREATE_JOURNAL, formattedValues)
    }

    const _error = postError?.value || putError?.value
    const _response = postResponse?.value || putResponse?.value

    if (_error) {
      showCustomToast({
        message: typeof _error === 'string' ? _error : 'Erreur lors de la sauvegarde du journal.',
        type: 'error',
      })
      return
    }

    if (_response) {
      if ((_response as any).success) {
        showCustomToast({
          message:
            (_response as any).message ||
            (journalId.value ? 'Journal modifié avec succès' : 'Journal créé avec succès'),
          type: 'success',
        })
        resetForm()
        eventBus.emit('journalUpdated')
        router.push('/comptabilite/saisie-operations')
        // After update, switch back to non-editable view
        editMode.value = false
      } else {
        showCustomToast({
          message: (_response as any).message || 'Erreur lors de la sauvegarde du Journal.',
          type: 'error',
        })
      }
    } else {
      showCustomToast({
        message: 'Aucune réponse du serveur.',
        type: 'error',
      })
    }
  } catch (e: any) {
    console.error('Erreur lors de la création du journal:', e)
    showCustomToast({ message: "Une erreur inattendue s'est produite.", type: 'error' })
  }
})

// === Load backend data for selects ===
const {
  data: plansData,
  loading: loadingPlans,
  error: plansError,
  fetchData: fetchPlans,
} = useGetApi<any[]>(API_ROUTES.GET_COMPTE)
const {
  data: subsData,
  loading: loadingSubs,
  error: subsError,
  fetchData: fetchSubs,
} = useGetApi<any[]>(API_ROUTES.GET_SUB_COMPTE)

// Robust mapping helper
function mapList(v: any): any[] {
  let list: any[] = []
  if (Array.isArray(v)) {
    list = v
  } else if (v?.data && Array.isArray(v.data)) {
    list = v.data
  } else if (v?.items && Array.isArray(v.items)) {
    list = v.items
  } else if (v?.accounts && Array.isArray(v.accounts)) {
    list = v.accounts
  } else if (v && typeof v === 'object') {
    list = Object.values(v).filter(
      (item: any) => item && typeof item === 'object' && (item.id || item.value),
    )
  }
  return list
}

const accountPlans = computed(() => {
  const list = mapList(plansData.value)
  return list.map((it: any) => ({
    id: String(it.id ?? it.value ?? ''),
    name:
      it.name ?? it.label ?? it.libelle ?? it.title ?? it.account ?? `Compte #${it.id ?? it.value}`,
    code: it.code ?? it.number ?? it.code_compte,
  }))
})

const subAccounts = computed(() => {
  const list = mapList(subsData.value)
  return list.map((it: any) => ({
    id: String(it.id ?? it.value ?? ''),
    name:
      it.name ??
      it.label ??
      it.libelle ??
      it.title ??
      it.account ??
      `Sous-compte #${it.id ?? it.value}`,
    code: it.code ?? it.number ?? it.code_compte,
    account_plan_id: it.account_plan_id ?? it.parent_id ?? it.plan_id ?? it.compte_id,
  }))
})

// Filter sub-accounts based on selected account plan
const filteredSubAccounts = computed(() => {
  const sel = values.sub_account_plan_id
  if (!sel) return subAccounts.value
  return subAccounts.value.filter((s: any) => String(s.id ?? '') === String(sel))
})

// GET a specific journal entry if the route param id is present
const {
  data: journalData,
  loading: loadingJournal,
  error: journalError,
  fetchData: fetchJournal,
} = useGetApi<any>(
  journalId.value ? API_ROUTES.GET_ONE_JOURNAL(journalId.value as any) : API_ROUTES.GET_JOURNAL,
)

onMounted(async () => {
  await Promise.all([fetchPlans(), fetchSubs()])

  // Only fetch journal details if an id is provided
  if (journalId.value) {
    await fetchJournal()

    // If error, show a toast and redirect back to list
    if (journalError.value) {
      showCustomToast({
        message: journalError.value || 'Erreur lors de la récupération du journal',
        type: 'error',
      })
      router.push('/comptabilite/saisie-operations')
      return
    }

    // Sometimes the API returns an object with `success: false` without setting an HTTP error
    if (
      journalData.value &&
      typeof journalData.value === 'object' &&
      'success' in (journalData.value as any) &&
      (journalData.value as any).success === false
    ) {
      const message = (journalData.value as any).message || 'Journal introuvable'
      showCustomToast({ message, type: 'error' })
      router.push('/comptabilite/saisie-operations')
      return
    }

    // If we have the data, prefill the form fields
    if (journalData.value) {
      const item = (journalData.value?.data ?? journalData.value) as any
      // Safely set fields using setFieldValue from useForm
      try {
        const date = item?.date ? String(item.date).split(' ')[0] : ''
        setFieldValue('date', date)
        setFieldValue('description', item?.description ?? '')
        setFieldValue('montant', Number(item?.montant ?? 0))

        // Prefer nested object ids when available (e.g. item.input_account?.id)
        // Fallback to top-level *_id fields when nested objects are not returned.
        setFieldValue(
          'input_account_id',
          item?.input_account?.id
            ? String(item.input_account.id)
            : item?.input_account_id
              ? String(item.input_account_id)
              : '',
        )
        setFieldValue(
          'output_account_id',
          item?.output_account?.id
            ? String(item.output_account.id)
            : item?.output_account_id
              ? String(item.output_account_id)
              : '',
        )
        setFieldValue(
          'account_plan_id',
          item?.account_plan?.id
            ? String(item.account_plan.id)
            : item?.account_plan_id
              ? String(item.account_plan_id)
              : '',
        )
        setFieldValue(
          'sub_account_plan_id',
          item?.sub_account_plan?.id
            ? String(item.sub_account_plan.id)
            : item?.sub_account_plan_id
              ? String(item.sub_account_plan_id)
              : '',
        )
        setFieldValue(
          'account_id',
          item?.account?.id
            ? String(item.account.id)
            : item?.account_id
              ? String(item.account_id)
              : '',
        )
      } catch (e) {
        console.error('Erreur lors du remplissage du formulaire avec les données du journal :', e)
      }
    }
  }
})
</script>

<template>
  <ComptaLayout
    activeBread="Nouveau Journal"
    active-tag-name="journal"
    group="operations"
    :show-header="false"
  >
    <div
      v-if="loadingJournal || loadingSubs || loadingPlans"
      class="flex items-center justify-center p-8"
    >
      <div class="flex flex-col items-center justify-center w-full gap-3">
        <span class="iconify hugeicons--loading-03 animate-spin text-4xl text-blue-500"></span>
        <p class="text-sm text-foreground-muted">Chargement du journal...</p>
      </div>
    </div>
    <form v-else @submit.prevent="onSubmit" class="mt-10 space-y-8">
      <FormSection
        class="grid sm:grid-cols-2 lg:grid-cols-3 gap-y-6 gap-x-10"
        title="Écriture de journal"
      >
        <!-- Date -->
        <InputWrapper>
          <Label class="text-sm">
            Date
            <SpanRequired />
          </Label>
          <Field name="date" v-slot="{ field, errorMessage, meta }">
            <Input
              :disabled="!editMode || saving || loadingJournal"
              v-bind="field"
              type="date"
              class="bg-gray-100 transition-all h-10 rounded-md"
            />
            <span v-if="meta.touched && errorMessage" class="text-sm text-red-500">{{
              errorMessage
            }}</span>
          </Field>
        </InputWrapper>

        <!-- Description -->
        <InputWrapper class="lg:col-span-2">
          <Label class="text-sm">
            Description
            <SpanRequired />
          </Label>
          <Field name="description" v-slot="{ field, errorMessage, meta }">
            <Textarea
              :disabled="!editMode || saving || loadingJournal"
              v-bind="field"
              class="bg-gray-100 transition-all rounded-md min-h-[100px]"
              placeholder="Ex: Paiement fournisseur - Facture n°12345"
            />
            <span v-if="meta.touched && errorMessage" class="text-sm text-red-500">{{
              errorMessage
            }}</span>
          </Field>
        </InputWrapper>

        <!-- Montant -->
        <InputWrapper>
          <Label class="text-sm">
            Montant (FC)
            <SpanRequired />
          </Label>
          <Field name="montant" v-slot="{ field, errorMessage, meta }">
            <Input
              :disabled="!editMode || saving || loadingJournal"
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

        <!-- Compte d'entrée -->
        <InputWrapper>
          <Label class="text-sm">
            Compte d'entrée
            <SpanRequired />
          </Label>
          <Field name="input_account_id" v-slot="{ field, errorMessage, meta }">
            <Select
              :model-value="field.value"
              :disabled="!editMode || saving || loadingJournal"
              @update:model-value="(v: any) => field.onChange(v)"
            >
              <SelectTrigger class="!h-10 bg-white w-full">
                <SelectValue placeholder="Sélectionner le compte d'entrée" />
              </SelectTrigger>
              <SelectContent>
                <SelectGroup>
                  <SelectItem v-if="loadingPlans" value="loading" disabled>
                    Chargement des comptes...
                  </SelectItem>
                  <template v-else>
                    <SelectItem v-if="!accountPlans.length" value="empty" disabled>
                      Aucun compte disponible
                    </SelectItem>
                    <SelectItem v-for="ac in accountPlans" :key="ac.id" :value="ac.id">
                      {{ ac.code ? ac.code + ' - ' : '' }}{{ ac.name }}
                    </SelectItem>
                  </template>
                </SelectGroup>
              </SelectContent>
            </Select>
            <span v-if="meta.touched && errorMessage" class="text-sm text-red-500">{{
              errorMessage
            }}</span>
          </Field>
        </InputWrapper>

        <!-- Compte de sortie -->
        <InputWrapper>
          <Label class="text-sm">
            Compte de sortie
            <SpanRequired />
          </Label>
          <Field name="output_account_id" v-slot="{ field, errorMessage, meta }">
            <Select
              :model-value="field.value"
              :disabled="!editMode || saving || loadingJournal"
              @update:model-value="(v: any) => field.onChange(v)"
            >
              <SelectTrigger class="!h-10 bg-white w-full">
                <SelectValue placeholder="Sélectionner le compte de sortie" />
              </SelectTrigger>
              <SelectContent>
                <SelectGroup>
                  <SelectItem v-if="loadingPlans" value="loading" disabled>
                    Chargement des comptes...
                  </SelectItem>
                  <template v-else>
                    <SelectItem v-if="!accountPlans.length" value="empty" disabled>
                      Aucun compte disponible
                    </SelectItem>
                    <SelectItem v-for="ac in accountPlans" :key="ac.id" :value="ac.id">
                      {{ ac.code ? ac.code + ' - ' : '' }}{{ ac.name }}
                    </SelectItem>
                  </template>
                </SelectGroup>
              </SelectContent>
            </Select>
            <span v-if="meta.touched && errorMessage" class="text-sm text-red-500">{{
              errorMessage
            }}</span>
          </Field>
        </InputWrapper>

        <!-- Plan comptable -->
        <InputWrapper>
          <Label class="text-sm">
            Plan comptable
            <SpanRequired />
          </Label>
          <Field name="account_plan_id" v-slot="{ field, errorMessage, meta }">
            <Select
              :model-value="field.value"
              :disabled="!editMode || saving || loadingJournal"
              @update:model-value="(v: any) => field.onChange(v)"
            >
              <SelectTrigger class="!h-10 bg-white w-full">
                <SelectValue placeholder="Sélectionner le plan comptable" />
              </SelectTrigger>
              <SelectContent>
                <SelectGroup>
                  <SelectItem v-if="loadingPlans" value="loading" disabled>
                    Chargement des plans...
                  </SelectItem>
                  <template v-else>
                    <SelectItem v-if="!accountPlans.length" value="empty" disabled>
                      Aucun plan disponible
                    </SelectItem>
                    <SelectItem v-for="ac in accountPlans" :key="ac.id" :value="ac.id">
                      {{ ac.code ? ac.code + ' - ' : '' }}{{ ac.name }}
                    </SelectItem>
                  </template>
                </SelectGroup>
              </SelectContent>
            </Select>
            <span v-if="meta.touched && errorMessage" class="text-sm text-red-500">{{
              errorMessage
            }}</span>
          </Field>
        </InputWrapper>

        <!-- Sous-compte -->
        <InputWrapper>
          <Label class="text-sm">
            Sous-compte
            <SpanRequired />
          </Label>
          <Field name="sub_account_plan_id" v-slot="{ field, errorMessage, meta }">
            <Select
              :model-value="field.value"
              :disabled="!editMode || saving || loadingJournal"
              @update:model-value="(v: any) => field.onChange(v)"
            >
              <SelectTrigger class="!h-10 bg-white w-full">
                <SelectValue placeholder="Sélectionner le sous-compte" />
              </SelectTrigger>
              <SelectContent>
                <SelectGroup>
                  <SelectItem v-if="loadingSubs" value="loading" disabled>
                    Chargement des sous-comptes...
                  </SelectItem>
                  <template v-else>
                    <SelectItem v-if="!filteredSubAccounts.length" value="empty" disabled>
                      Aucun sous-compte disponible
                    </SelectItem>
                    <SelectItem v-for="sc in filteredSubAccounts" :key="sc.id" :value="sc.id">
                      {{ sc.code ? sc.code + ' - ' : '' }}{{ sc.name }}
                    </SelectItem>
                  </template>
                </SelectGroup>
              </SelectContent>
            </Select>
            <span v-if="meta.touched && errorMessage" class="text-sm text-red-500">{{
              errorMessage
            }}</span>
          </Field>
        </InputWrapper>

        <!-- Compte -->
        <InputWrapper>
          <Label class="text-sm">
            Compte
            <SpanRequired />
          </Label>
          <Field name="account_id" v-slot="{ field, errorMessage, meta }">
            <Select
              :model-value="field.value"
              :disabled="!editMode || saving || loadingJournal"
              @update:model-value="(v: any) => field.onChange(v)"
            >
              <SelectTrigger class="!h-10 bg-white w-full">
                <SelectValue placeholder="Sélectionner le compte" />
              </SelectTrigger>
              <SelectContent>
                <SelectGroup>
                  <SelectItem v-if="loadingPlans" value="loading" disabled>
                    Chargement des comptes...
                  </SelectItem>
                  <template v-else>
                    <SelectItem v-if="!accountPlans.length" value="empty" disabled>
                      Aucun compte disponible
                    </SelectItem>
                    <SelectItem v-for="ac in accountPlans" :key="ac.id" :value="ac.id">
                      {{ ac.code ? ac.code + ' - ' : '' }}{{ ac.name }}
                    </SelectItem>
                  </template>
                </SelectGroup>
              </SelectContent>
            </Select>
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
          :disabled="saving || loadingJournal"
          @click="router.push('/comptabilite/saisie-operations')"
        >
          <span class="flex iconify hugeicons--cancel-01 mr-1.5"></span>
          Annuler
        </Button>
        <template v-if="journalId && !editMode">
          <Button size="md" type="button" @click="editMode = true">Modifier</Button>
        </template>
        <template v-else>
          <Button type="submit" :disabled="saving || loadingJournal || !editMode">
            <span class="flex iconify hugeicons--add-01"></span>
            <span v-if="!saving">Enregistrer l'écriture</span>
            <span v-else>Enregistrement...</span>
          </Button>
        </template>
      </div>
    </form>
  </ComptaLayout>
</template>
