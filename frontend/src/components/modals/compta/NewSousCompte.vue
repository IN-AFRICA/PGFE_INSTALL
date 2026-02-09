<script setup lang="ts">
import { ref, computed, onMounted, watch } from 'vue'
import { Button } from '@/components/ui/button'
import {
  Dialog,
  DialogContent,
  DialogFooter,
  DialogHeader,
  DialogTitle,
  DialogTrigger,
} from '@/components/ui/dialog'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import SpanRequired from '@/components/atoms/SpanRequired.vue'
import { Form, Field } from 'vee-validate'
import { toTypedSchema } from '@vee-validate/zod'
import * as z from 'zod'
import { usePostApi } from '@/composables/usePostApi'
import { useGetApi } from '@/composables/useGetApi'
import { showCustomToast } from '@/utils/widgets/custom_toast'
import { eventBus } from '@/utils/eventBus'
import { API_ROUTES } from '@/utils/constants/api_route'

// Schéma de validation sous-compte
const subAccountSchema = toTypedSchema(
  z.object({
    name: z
      .string({ required_error: 'Le nom est requis' })
      .min(1, 'Le nom est requis')
      .max(255, 'Max 255 caractères'),
    code: z
      .string({ required_error: 'Le code est requis' })
      .min(1, 'Le code est requis')
      .max(50, 'Max 50 caractères'),
    account_id: z.union([
      z
        .string({ required_error: 'Le compte parent est requis' })
        .refine((v) => v !== '', 'Le compte parent est requis'),
      z.number().transform((v) => String(v)),
    ]),
  }),
)

// API
const {
  postData: createSubAccount,
  loading: creating,
  error: createError,
  response: createResponse,
} = usePostApi<any>()
const {
  data: accountsData,
  loading: accountsLoading,
  fetchData: fetchAccounts,
} = useGetApi<any[]>(API_ROUTES.GET_COMPTE)

const accounts = computed(() => {
  const raw: any = accountsData.value
  let list: any[] = []
  if (Array.isArray(raw)) list = raw
  else if (raw?.data && Array.isArray(raw.data)) list = raw.data
  else if (raw && typeof raw === 'object')
    list = Object.values(raw).filter((v: any) => v && typeof v === 'object')
  return list.map((a: any) => ({
    id: String(a.id ?? a.value ?? ''),
    name: a.name ?? a.label ?? a.libelle ?? `Compte #${a.id ?? a.value}`,
    code: a.code ?? a.code_account ?? a.codeCompte ?? a.codeValue ?? '',
  }))
})

const isOpen = ref(false)

onMounted(async () => {
  await fetchAccounts()
})

// Rafraîchir à l'ouverture pour éviter des listes vides et corriger le rendu tardif du select
watch(isOpen, async (open) => {
  if (open) {
    await fetchAccounts()
  }
})

async function onSubmit(values: any) {
  try {
    const payload = {
      name: values.name,
      code: values.code,
      account_plan_id: Number(values.account_id),
    }
    await createSubAccount(API_ROUTES.CREATE_SUB_COMPTE, payload)
    if (createError.value) {
      showCustomToast({ message: createError.value, type: 'error' })
      return
    }
    const msg = (createResponse.value as any)?.message || 'Sous-compte créé avec succès.'
    showCustomToast({ message: msg, type: 'success' })
    isOpen.value = false
    eventBus.emit('subaccountComptableUpdated')
  } catch (err) {
    showCustomToast({ message: 'Une erreur est survenue', type: 'error' })
  }
}

function handleParentAccountChange(
  value: string,
  onChange: (val: any) => void,
  setFieldValue: (field: string, value: any) => void,
) {
  // Met à jour la valeur du champ account_id
  onChange(value)
  // Récupère le compte parent sélectionné et remplit le code
  const selected = accounts.value.find((a: any) => a.id === value)
  if (selected && selected.code) {
    setFieldValue('code', selected.code)
  }
}
</script>

<template>
  <Dialog v-model:open="isOpen">
    <DialogTrigger as-child>
      <Button size="md" class="rounded-md">
        <span class="iconify hugeicons--plus-sign"></span>
        <span class="hidden sm:flex"> Ajouter </span>
      </Button>
    </DialogTrigger>
    <DialogContent class="sm:max-w-[540px]">
      <DialogHeader>
        <DialogTitle>Ajouter un sous compte</DialogTitle>
      </DialogHeader>
      <Form @submit="onSubmit" :validation-schema="subAccountSchema" v-slot="{ setFieldValue }">
        <div class="grid gap-4 py-4">
          <div class="flex flex-col space-y-1.5 flex-1">
            <Label for="code" class="text-sm font-medium">Code <SpanRequired /></Label>
            <Field name="code" v-slot="{ field, errorMessage }">
              <Input id="code" v-bind="field" placeholder="Ex: SUB-001" />
              <span v-if="errorMessage" class="text-sm text-red-500">{{ errorMessage }}</span>
            </Field>
          </div>
          <div class="flex flex-col space-y-1.5">
            <Label for="name" class="text-sm font-medium">Nom <SpanRequired /></Label>
            <Field name="name" v-slot="{ field, errorMessage }">
              <Input id="name" v-bind="field" placeholder="Ex: Caisse secondaire" />
              <span v-if="errorMessage" class="text-sm text-red-500">{{ errorMessage }}</span>
            </Field>
          </div>
          <div class="flex flex-col space-y-1.5 flex-1">
            <Label class="text-sm font-medium">Compte parent <SpanRequired /></Label>
            <Field name="account_id" v-slot="{ field, errorMessage }">
              <select
                :key="isOpen ? 'open-acc' : 'closed-acc'"
                class="w-full h-10 border border-gray-200/40 bg-white rounded-md px-2"
                :disabled="accountsLoading"
                :value="field.value"
                @change="
                  handleParentAccountChange(
                    ($event.target as HTMLSelectElement).value,
                    field.onChange,
                    setFieldValue,
                  )
                "
              >
                <option value="" disabled v-if="accountsLoading">Chargement des comptes...</option>
                <option value="" v-else>Sélectionner un compte</option>
                <option v-for="a in accounts" :key="a.id" :value="a.id">{{ a.name }}</option>
              </select>
              <span v-if="errorMessage" class="text-sm text-red-500">{{ errorMessage }}</span>
            </Field>
          </div>
          <div class="mt-1 pb-2">
            <p class="text-sm text-foreground-muted">Les champs marqués sont obligatoires</p>
          </div>
        </div>
        <DialogFooter class="flex justify-end gap-2 items-center">
          <Button size="sm" class="h-9" variant="outline" type="button" @click="isOpen = false">
            <span class="iconify flex hugeicons--cancel-01 mr-1"></span>
            Annuler
          </Button>
          <Button size="sm" class="h-9" type="submit" :disabled="creating">
            <span class="iconify flex hugeicons--floppy-disk mr-1"></span>
            {{ creating ? 'Enregistrement...' : 'Enregistrer' }}
          </Button>
        </DialogFooter>
      </Form>
    </DialogContent>
  </Dialog>
</template>
