<script setup lang="ts">
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import DashFormLayout from '@/components/templates/DashFormLayout.vue'
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
import CustomDatePicker from '@/components/ui/CustomDatePicker.vue'
import { usePostApi } from '@/composables/usePostApi.ts'
import { useGetApi } from '@/composables/useGetApi.ts'
import { API_ROUTES } from '@/utils/constants/api_route.ts'
import { Field, useForm, useFieldValue } from 'vee-validate'
import { z } from 'zod'
import { toTypedSchema } from '@vee-validate/zod'
import { showCustomToast } from '@/utils/widgets/custom_toast.ts'
import { computed, onMounted, watch } from 'vue'

interface A_NEW_COMPTABILITY {
  id: number
  account_plan_id: number
  sub_account_plan_id: number
  amount: number
  type: string
  justification: string
}

// Validation schema based on backend contract
const schema = z.object({
  account_plan_id: z
    .union([
      z
        .string()
        .refine((v) => v !== '', 'Le compte principal est requis')
        .transform((v) => Number(v)),
      z.number(),
    ])
    .refine((v) => Number(v) > 0, 'Le compte principal est requis'),
  sub_account_plan_id: z
    .union([
      z
        .string()
        .refine((v) => v !== '', 'Le sous-compte est requis')
        .transform((v) => Number(v)),
      z.number(),
    ])
    .refine((v) => Number(v) > 0, 'Le sous-compte est requis'),
  amount: z.coerce
    .number({ invalid_type_error: 'Le montant doit être un nombre' })
    .min(0, 'Le montant doit être positif'),
  type: z.enum(['input', 'output'], { required_error: 'Le type est requis' }),
  justification: z
    .string()
    .min(1, 'La justification est requise')
    .max(255, '255 caractères maximum'),
})

const { handleSubmit, resetForm, setFieldValue } = useForm({
  validationSchema: toTypedSchema(schema),
  initialValues: {
    account_plan_id: '',
    sub_account_plan_id: '',
    amount: undefined,
    type: undefined,
    justification: '',
  },
})

// API POST composable
const { response, error, loading, postData } = usePostApi<any>()

const onSubmit = handleSubmit(async (values) => {
  try {
    await postData(API_ROUTES.CREATE_A_NEW_COMPTABILITY, values)

    if (error.value) {
      showCustomToast({
        message:
          typeof error.value === 'string'
            ? error.value
            : 'Erreur lors de la création de l’écriture.',
        type: 'error',
      })
      return
    }

    if (response.value && (response.value as any).message) {
      showCustomToast({
        message: (response.value as any).message || 'Écriture comptable créée avec succès',
        type: 'success',
      })
      resetForm()
    } else {
      showCustomToast({ message: 'Écriture comptable créée avec succès', type: 'success' })
      resetForm()
    }
  } catch (e) {
    showCustomToast({ message: 'Une erreur inattendue s’est produite.', type: 'error' })
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
const selectedAccountPlanId = useFieldValue<string | number>('account_plan_id')
const filteredSubAccounts = computed(() => {
  const sel = selectedAccountPlanId.value
  if (!sel) return subAccounts.value
  return subAccounts.value.filter((s: any) => String(s.account_plan_id ?? '') === String(sel))
})

// When account plan changes, clear selected sub-account
watch(selectedAccountPlanId, () => {
  setFieldValue('sub_account_plan_id', '')
})

onMounted(async () => {
  await Promise.all([fetchPlans(), fetchSubs()])
})
</script>

<template>
  <DashFormLayout
    title="A-nouveaux"
    link-back="/comptabilite/saisie-prealable"
    group-route="/comptabilite/saisie-prealable"
    module="compta"
    :breadcrumb="[
      { label: 'Comptabilité', href: '/comptabilite' },
      { label: 'Saisie préalable', href: '/comptabilite/saisie-prealable' },
      { label: 'A-nouveaux', href: '/comptabilite/saisie-prealable/a-nouveaux' },
    ]"
  >
    <form @submit.prevent="onSubmit" class="mt-10 space-y-8">
      <FormSection
        class="grid sm:grid-cols-2 lg:grid-cols-3 gap-y-6 gap-x-10"
        title="Informations sur l'écriture comptable"
      >
        <InputWrapper>
          <Label class="text-sm">
            Date de l'écriture
            <SpanRequired />
          </Label>
          <CustomDatePicker />
        </InputWrapper>
        <InputWrapper>
          <Label class="text-sm">
            Compte principal
            <SpanRequired />
          </Label>
          <Field name="account_plan_id" v-slot="{ field, errorMessage, meta }">
            <Select :model-value="field.value" @update:model-value="(v: any) => field.onChange(v)">
              <SelectTrigger class="!h-10 bg-white w-full">
                <SelectValue placeholder="Sélectionner le compte principal" />
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
                    <SelectItem v-for="cp in accountPlans" :key="cp.id" :value="cp.id">
                      {{ cp.code ? cp.code + ' - ' : '' }}{{ cp.name }}
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
        <InputWrapper>
          <Label class="text-sm">
            Sous-compte
            <SpanRequired />
          </Label>
          <Field name="sub_account_plan_id" v-slot="{ field, errorMessage, meta }">
            <Select :model-value="field.value" @update:model-value="(v: any) => field.onChange(v)">
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
        <InputWrapper>
          <Label class="text-sm">
            Justification
            <SpanRequired />
          </Label>
          <Field name="justification" v-slot="{ field, errorMessage, meta }">
            <Input
              v-bind="field"
              class="bg-gray-100 transition-all h-10 rounded-md"
              placeholder="Ex: FAC001 - Paiement fournisseur"
            />
            <span v-if="meta.touched && errorMessage" class="text-sm text-red-500">{{
              errorMessage
            }}</span>
          </Field>
        </InputWrapper>

        <InputWrapper>
          <Label class="text-sm">
            Montant (FC)
            <SpanRequired />
          </Label>
          <Field name="amount" v-slot="{ field, errorMessage, meta }">
            <Input
              v-bind="field"
              type="number"
              class="bg-gray-100 transition-all h-10 rounded-md"
              placeholder="Ex: 150000"
            />
            <span v-if="meta.touched && errorMessage" class="text-sm text-red-500">{{
              errorMessage
            }}</span>
          </Field>
        </InputWrapper>

        <InputWrapper>
          <Label class="text-sm">
            Type d'écriture
            <SpanRequired />
          </Label>
          <Field name="type" v-slot="{ field, errorMessage, meta }">
            <Select :model-value="field.value" @update:model-value="(v: any) => field.onChange(v)">
              <SelectTrigger class="!h-10 bg-white w-full">
                <SelectValue placeholder="Sélectionner le type (entrée/sortie)" />
              </SelectTrigger>
              <SelectContent>
                <SelectGroup>
                  <SelectItem value="input">Entrée (input)</SelectItem>
                  <SelectItem value="output">Sortie (output)</SelectItem>
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
        <Button variant="outline" type="button" :disabled="loading" @click="$router.back()">
          <span class="flex iconify hugeicons--cancel-01 mr-1.5"></span>
          Annuler
        </Button>
        <Button type="submit" :disabled="loading">
          <span class="flex iconify hugeicons--money-bag-02 mr-1.5"></span>
          <span v-if="!loading">Enregistrer l'écriture</span>
          <span v-else>Enregistrement...</span>
        </Button>
      </div>
    </form>
  </DashFormLayout>
</template>
