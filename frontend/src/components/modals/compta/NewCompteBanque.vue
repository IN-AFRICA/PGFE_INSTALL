<script setup lang="ts">
import { Button } from '@/components/ui/button'
import {
  Dialog,
  DialogContent,
  DialogDescription,
  DialogFooter,
  DialogHeader,
  DialogTitle,
  DialogTrigger,
} from '@/components/ui/dialog'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import SpanRequired from '@/components/atoms/SpanRequired.vue'
import { ref, computed, watch } from 'vue'
import { useForm, Field } from 'vee-validate'
import { z } from 'zod'
import { toTypedSchema } from '@vee-validate/zod'
import { usePostApi } from '@/composables/usePostApi'
import { usePutApi } from '@/composables/usePutApi'
import { API_ROUTES } from '@/utils/constants/api_route'
import { showCustomToast } from '@/utils/widgets/custom_toast'
import { eventBus } from '@/utils/eventBus'
import type { BankAccount } from '@/models/bank_account'
import api from '@/services/api'

// Props pour le mode édition
const props = defineProps<{
  accountId?: number | null
}>()

// Interface pour la réponse API
interface BankAccountApiResponse {
  data?: string
  message?: string
  success?: boolean
}

// Schéma de validation Zod
const accountSchema = z.object({
  name: z
    .string({ required_error: 'Le nom du compte est requis' })
    .min(1, 'Le nom du compte est requis')
    .max(255, 'Le nom ne doit pas dépasser 255 caractères'),
  code: z
    .string({ required_error: 'Le code est requis' })
    .min(1, 'Le code est requis')
    .max(100, 'Le code ne doit pas dépasser 100 caractères'),
  number: z
    .string({ required_error: 'Le numéro de compte est requis' })
    .min(1, 'Le numéro de compte est requis')
    .max(100, 'Le numéro ne doit pas dépasser 100 caractères'),
})

// État du dialog
const isDialogOpen = ref(false)

// Mode édition
const isEditMode = computed(() => !!props.accountId)

// État de chargement pour la récupération des données
const loadingBankAccount = ref(false)

// Configuration du formulaire avec vee-validate
const { handleSubmit, resetForm, setValues } = useForm({
  validationSchema: toTypedSchema(accountSchema),
})

// API pour créer un compte
const { postData, response, error, loading } = usePostApi<BankAccountApiResponse>()

// API pour mettre à jour un compte
const {
  putData,
  response: putResponse,
  error: putError,
  loading: putLoading,
} = usePutApi<BankAccountApiResponse>()

// Charger les données du compte lors de l'ouverture du dialog en mode édition
const loadAccountData = async () => {
  if (!props.accountId) return

  loadingBankAccount.value = true
  console.log('📥 Chargement du compte ID:', props.accountId)

  try {
    const response = await api.get<{ data: BankAccount }>(
      API_ROUTES.GET_BANK_ACCOUNT_BY_ID(props.accountId),
    )
    const account = response.data?.data

    if (account) {
      console.log('📋 Données du compte récupérées:', account)
      setValues({
        name: account.name,
        code: account.code,
        number: account.number,
      })
    }
  } catch (err: any) {
    console.error('❌ Erreur lors du chargement:', err)
    showCustomToast({
      message: err?.response?.data?.message || 'Erreur lors du chargement du compte',
      type: 'error',
    })
  } finally {
    loadingBankAccount.value = false
  }
}

// Watcher pour charger les données quand le dialog s'ouvre
watch(isDialogOpen, (newValue) => {
  if (newValue && isEditMode.value) {
    loadAccountData()
  }
})

// État de chargement combiné
const isLoading = computed(() => loading.value || putLoading.value)

// Fonction de soumission
const onSubmit = handleSubmit(async (values) => {
  try {
    console.log('📝 Données du formulaire:', values)

    if (isEditMode.value && props.accountId) {
      // Mode édition - utiliser PUT
      console.log('🔄 Mode édition - Mise à jour du compte ID:', props.accountId)
      await putData(API_ROUTES.UPDATE_BANK_ACCOUNT(props.accountId), values)

      if (putError.value) {
        showCustomToast({
          message: putError.value,
          type: 'error',
        })
        return
      }

      if (putResponse.value) {
        const isSuccess =
          putResponse.value.success === true ||
          putResponse.value.success === undefined ||
          putResponse.value.data !== undefined

        if (isSuccess && putResponse.value.success !== false) {
          console.log('✅ Compte mis à jour avec succès')
          showCustomToast({
            message: putResponse.value.message || 'Compte bancaire mis à jour avec succès',
            type: 'success',
          })
          closeDialog()
          eventBus.emit('accountUpdated')
        } else {
          showCustomToast({
            message: putResponse.value.message || 'Erreur lors de la mise à jour du compte',
            type: 'error',
          })
        }
      }
    } else {
      // Mode création - utiliser POST
      await postData(API_ROUTES.CREATE_BANK_ACCOUNT, values)

      console.log('📡 Réponse API complète:', JSON.stringify(response.value, null, 2))
      console.log('❌ Erreur API:', error.value)

      if (error.value) {
        showCustomToast({
          message: error.value,
          type: 'error',
        })
        return
      }

      if (response.value) {
        const isSuccess =
          response.value.success === true ||
          response.value.success === undefined ||
          response.value.data !== undefined

        if (isSuccess && response.value.success !== false) {
          console.log('✅ Compte créé avec succès')
          showCustomToast({
            message: response.value.message || 'Compte bancaire créé avec succès',
            type: 'success',
          })

          // Fermer le dialog et réinitialiser le formulaire
          closeDialog()

          // Émettre l'événement pour rafraîchir la liste
          console.log("🔄 Émission de l'événement accountUpdated")
          eventBus.emit('accountUpdated')
        } else {
          console.log('⚠️ Échec de création:', response.value.message)
          showCustomToast({
            message: response.value.message || 'Erreur lors de la création du compte',
            type: 'error',
          })
        }
      } else {
        console.log('⚠️ Aucune réponse du serveur')
        showCustomToast({
          message: 'Aucune réponse du serveur',
          type: 'error',
        })
      }
    }
  } catch (err) {
    console.error('💥 Erreur lors de la soumission:', err)
    showCustomToast({
      message: "Une erreur inattendue s'est produite",
      type: 'error',
    })
  }
})

// Fonction pour fermer le dialog
const closeDialog = () => {
  try {
    resetForm()
    isDialogOpen.value = false
  } catch (err) {
    console.warn('Erreur lors de la fermeture du dialog:', err)
    isDialogOpen.value = false
  }
}
</script>

<template>
  <Dialog v-model:open="isDialogOpen">
    <DialogTrigger as-child>
      <slot name="trigger">
        <Button size="md" class="rounded-md">
          <span class="iconify hugeicons--plus-sign"></span>
          <span class="hidden sm:flex">
            {{ isEditMode ? 'Modifier le compte' : 'Ajouter un compte' }}
          </span>
        </Button>
      </slot>
    </DialogTrigger>
    <DialogContent
      class="sm:max-w-[540px]"
      @pointer-down-outside="closeDialog"
      @escape-key-down="closeDialog"
    >
      <DialogHeader>
        <DialogTitle>{{
          isEditMode ? 'Modifier le compte bancaire' : 'Ajouter un compte bancaire'
        }}</DialogTitle>
        <DialogDescription>
          {{
            isEditMode
              ? 'Modifier les informations du compte bancaire'
              : 'Enregistrer un nouveau compte bancaire'
          }}
        </DialogDescription>
      </DialogHeader>

      <!-- Loading state when fetching account data -->
      <div v-if="loadingBankAccount" class="flex items-center justify-center py-8">
        <span class="iconify hugeicons--loading-03 animate-spin text-2xl mr-2"></span>
        <span>Chargement des données...</span>
      </div>

      <form v-else @submit.prevent="onSubmit" class="space-y-4">
        <div class="grid gap-4 py-4">
          <!-- Nom du compte -->
          <div class="flex flex-col space-y-1.5">
            <Label for="name" class="text-sm font-medium">
              Nom de la banque
              <SpanRequired />
            </Label>
            <Field name="name" v-slot="{ field, errorMessage }">
              <Input
                v-bind="field"
                id="name"
                type="text"
                placeholder="Ex: Compte principal Ecobank"
                class="w-full h-10 border border-gray-200/40 bg-white transition-all"
              />
              <span v-if="errorMessage" class="text-sm text-red-500">{{ errorMessage }}</span>
            </Field>
          </div>

          <!-- Code -->
          <div class="flex flex-col space-y-1.5">
            <Label for="code" class="text-sm font-medium">
              Code
              <SpanRequired />
            </Label>
            <Field name="code" v-slot="{ field, errorMessage }">
              <Input
                v-bind="field"
                id="code"
                type="text"
                placeholder="Ex: ECO001"
                class="w-full h-10 border border-gray-200/40 bg-white transition-all"
              />
              <span v-if="errorMessage" class="text-sm text-red-500">{{ errorMessage }}</span>
            </Field>
          </div>

          <!-- Numéro de compte -->
          <div class="flex flex-col space-y-1.5">
            <Label for="number" class="text-sm font-medium">
              Numéro de compte
              <SpanRequired />
            </Label>
            <Field name="number" v-slot="{ field, errorMessage }">
              <Input
                v-bind="field"
                id="number"
                type="text"
                placeholder="1234567890"
                class="w-full h-10 border border-gray-200/40 bg-white transition-all"
              />
              <span v-if="errorMessage" class="text-sm text-red-500">{{ errorMessage }}</span>
            </Field>
          </div>

          <div class="mt-1 pb-2">
            <p class="text-sm text-foreground-muted">* Tous les champs sont obligatoires</p>
          </div>
        </div>
        <DialogFooter class="flex justify-end gap-2 items-center">
          <Button type="button" size="sm" class="h-9" variant="outline" @click="closeDialog">
            <span class="iconify flex hugeicons--cancel-01 mr-1"></span>
            Annuler
          </Button>
          <Button type="submit" size="sm" class="h-9" :disabled="isLoading">
            <span
              v-if="isLoading"
              class="iconify flex hugeicons--loading-03 mr-1 animate-spin"
            ></span>
            <span v-else class="iconify flex hugeicons--floppy-disk mr-1"></span>
            {{
              isLoading
                ? isEditMode
                  ? 'Mise à jour...'
                  : 'Enregistrement...'
                : isEditMode
                  ? 'Mettre à jour'
                  : 'Enregistrer'
            }}
          </Button>
        </DialogFooter>
      </form>
    </DialogContent>
  </Dialog>
</template>
