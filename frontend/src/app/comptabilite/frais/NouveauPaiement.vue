<script setup lang="ts">
import { ref, onMounted, computed } from 'vue'
import ComptaLayout from '@/components/templates/compta/ComptaLayout.vue'
import { useRouter } from 'vue-router'
import { useForm, Field } from 'vee-validate'
import { z } from 'zod'
import { toTypedSchema } from '@vee-validate/zod'
import LayoutSaisieScolarFee from '@/components/templates/LayoutSaisieScolarFee.vue'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Textarea } from '@/components/ui/textarea'
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from '@/components/ui/select'
import ListStudent from '@/utils/widgets/vues/ListStudent.vue'
import ListCurrency from '@/utils/widgets/vues/ListCurrency.vue'
import ListSchoolFee from '@/utils/widgets/vues/ListSchoolFee.vue'
import ListPaymentMethod from '@/utils/widgets/vues/ListPaymentMethod.vue'
import ListBankAccount from '@/utils/widgets/vues/ListBankAccount.vue'
import { usePostApi } from '@/composables/usePostApi.ts'
import { useGetApi } from '@/composables/useGetApi.ts'
import { API_ROUTES } from '@/utils/constants/api_route.ts'
import { showCustomToast } from '@/utils/widgets/custom_toast.ts'
import { eventBus } from '@/utils/eventBus.ts'

// Interface pour les paiements
interface PaymentData {
  id: number
  student_id: number
  fee_id: number
  currency_id: number
  payment_method_id: number
  amount: number
  remaining_amount?: number
  paid_at?: string
  details?: string
  status: string
  created_at: string
  updated_at: string
}

interface PaymentsApiResponse {
  success?: boolean
  message?: string
  data?: string
  payment?: string
  // Format direct (objet créé)
  id?: number
  reference?: string
  student_id?: number
  fee_id?: number
  amount?: number
  [key: string]: any // Pour les autres propriétés dynamiques
}

// Schéma de validation Zod pour les paiements
const paymentSchema = z.object({
  student_id: z
    .union([z.string(), z.number()])
    .transform((val) => {
      if (val === '' || val === null || val === undefined) return null
      return typeof val === 'string' ? parseInt(val) : val
    })
    .refine((val) => val !== null && val > 0, "L'élève est requis"),

  fee_id: z
    .union([z.string(), z.number()])
    .transform((val) => {
      if (val === '' || val === null || val === undefined) return null
      return typeof val === 'string' ? parseInt(val) : val
    })
    .refine((val) => val !== null && val > 0, 'Le frais scolaire est requis'),

  currency_id: z
    .union([z.string(), z.number()])
    .transform((val) => {
      if (val === '' || val === null || val === undefined) return null
      return typeof val === 'string' ? parseInt(val) : val
    })
    .refine((val) => val !== null && val > 0, 'La devise est requise'),

  payment_method_id: z
    .union([z.string(), z.number()])
    .transform((val) => {
      if (val === '' || val === null || val === undefined) return null
      return typeof val === 'string' ? parseInt(val) : val
    })
    .refine((val) => val !== null && val > 0, 'La méthode de paiement est requise'),

  account_id: z
    .union([z.string(), z.number()])
    .transform((val) => {
      if (val === '' || val === null || val === undefined) return null
      return typeof val === 'string' ? parseInt(val) : val
    })
    .refine((val) => val !== null && val > 0, 'Le compte bancaire est requis'),

  amount: z.coerce.number().min(0.01, 'Le montant doit être supérieur à 0'),
  remaining_amount: z.coerce
    .number()
    .min(0, 'Le montant restant ne peut pas être négatif')
    .optional(),
  paid_at: z.string().optional(),
  details: z.string().optional(),
})

type PaymentFormData = z.infer<typeof paymentSchema>

const router = useRouter()

// Configuration du formulaire avec Vee-Validate
const { handleSubmit, resetForm, setValues, setFieldValue } = useForm<PaymentFormData>({
  validationSchema: toTypedSchema(paymentSchema),
  initialValues: {
    student_id: null,
    fee_id: null,
    currency_id: null,
    payment_method_id: null,
    account_id: null,
    amount: 0,
    remaining_amount: 0,
    paid_at: '',
    details: '',
  },
})

// Composables pour les API
const {
  postData,
  loading: creating,
  error: createError,
  response: createResponse,
} = usePostApi<PaymentsApiResponse>()
const {
  data: accountTypes,
  loading: loadingAccountTypes,
  fetchData: fetchAccountTypes,
} = useGetApi<any[]>(API_ROUTES.GET_BANK_ACCOUNTS)

// Fonction pour créer un nouveau paiement
const onSubmitCreate = handleSubmit(async (values: PaymentFormData) => {
  try {
    console.log('📝 Valeurs du formulaire AVANT formatage:', values)
    console.log('📝 Types des valeurs:', {
      student_id: typeof values.student_id,
      fee_id: typeof values.fee_id,
      currency_id: typeof values.currency_id,
      payment_method_id: typeof values.payment_method_id,
      account_id: typeof values.account_id,
      amount: typeof values.amount,
    })

    // Formatage des données pour l'API
    const formattedValues = {
      ...values,
      paid_at: values.paid_at ? values.paid_at + ':00Z' : undefined,
    }

    console.log('📤 Données envoyées au backend:', JSON.stringify(formattedValues, null, 2))

    await postData(API_ROUTES.CREATE_PAYMENT, formattedValues)

    console.log('📡 Réponse complète:', createResponse.value)
    console.log('📊 Réponse stringifiée:', JSON.stringify(createResponse.value, null, 2))
    console.log('✅ createResponse.value?.success:', createResponse.value?.success)
    console.log('📝 createResponse.value?.message:', createResponse.value?.message)
    console.log('📦 createResponse.value?.data:', createResponse.value?.data)
    console.log('❌ Erreur complète:', createError.value)

    if (createError.value) {
      console.error('🔴 Erreur de validation (422):', createError.value)
      showCustomToast({
        message: createError.value,
        type: 'error',
      })
      return
    }

    if (createResponse.value?.success) {
      // Format standard
      showCustomToast({
        message: createResponse.value.message || 'Paiement créé avec succès.',
        type: 'success',
      })

      // Émettre l'événement pour rafraîchir les données
      eventBus.emit('paiementCreated')

      // Rediriger vers la liste des paiements
      router.push('/comptabilite/frais/paiements')
    } else if (createResponse.value?.id) {
      // Format direct : le backend retourne directement l'objet créé avec un ID
      console.log('✅ Paiement créé avec succès (format direct), ID:', createResponse.value.id)
      showCustomToast({
        message: `Paiement créé avec succès (Référence: ${createResponse.value.reference || createResponse.value.id})`,
        type: 'success',
      })

      // Émettre l'événement pour rafraîchir les données
      eventBus.emit('paiementCreated')

      // Rediriger vers la liste des paiements
      router.push('/comptabilite/frais/paiements')
    } else {
      console.warn('⚠️ Réponse sans succès:', createResponse.value)
      showCustomToast({
        message: createResponse.value?.message || 'Erreur lors de la création du paiement.',
        type: 'error',
      })
    }
  } catch (err) {
    console.error('💥 Erreur lors de la soumission:', err)
    showCustomToast({
      message: "Une erreur inattendue s'est produite.",
      type: 'error',
    })
  }
})

// Fonction pour annuler et retourner à la liste
const handleCancel = () => {
  router.push('/comptabilite/frais/paiements')
}

// Charger les types de compte au montage
onMounted(async () => {
  await fetchAccountTypes()
})

// Breadcrumb
const breadcrumbItems = {
  items: [
    { label: 'Accueil', href: '/', icon: 'hugeicons--home-01' },
    { label: 'Élèves', href: '/apprenants', icon: 'hugeicons--student' },
    { label: 'Frais Scolaires', href: '/apprenants/scolar-fee', icon: 'hugeicons--money-bag-01' },
    { label: 'Paiements', href: '/comptabilite/frais/paiements', icon: 'hugeicons--invoice-02' },
    { label: 'Nouveau Paiement', href: '#', icon: 'hugeicons--add-01' },
  ],
}

// Les données sont maintenant chargées automatiquement par les composants ListSchoolFee et ListPaymentMethod
</script>

<template>
  <ComptaLayout
    activeBread="Nouveau paiement"
    active-tag-name="Paiement"
    group="frais"
    :show-header="false"
  >
    <div class="flex flex-col max-w-4xl mx-auto px-4 py-6">
      <!-- En-tête de la page -->
      <div class="flex flex-col max-w-2xl mb-6">
        <h1 class="font-semibold text-xl text-foreground-title">Nouveau Paiement</h1>
        <p class="text-foreground-muted mt-0.5">Créez un nouveau paiement pour un élève.</p>
      </div>

      <!-- Formulaire de création -->
      <div class="bg-white rounded-lg border p-6">
        <form @submit.prevent="onSubmitCreate" class="space-y-6">
          <!-- Élève qui paie -->
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="space-y-2">
              <Label for="student_id">Élève qui paie </Label>
              <Field name="student_id" v-slot="{ field, errorMessage }">
                <ListStudent
                  :model-value="field.value"
                  @update:model-value="
                    (val) =>
                      setFieldValue(
                        'student_id',
                        val === '' || val === undefined
                          ? null
                          : typeof val === 'string'
                            ? Number(val)
                            : val,
                      )
                  "
                  placeholder="Rechercher et sélectionner un élève"
                  :class="{ 'border-red-500': errorMessage }"
                />
                <span v-if="errorMessage" class="text-sm text-red-500">{{ errorMessage }}</span>
              </Field>
            </div>
            <div class="space-y-2">
              <Label for="fee_id">Frais scolaire</Label>
              <Field name="fee_id" v-slot="{ field, errorMessage }">
                <ListSchoolFee
                  :model-value="field.value"
                  @update:model-value="
                    (val) =>
                      setFieldValue(
                        'fee_id',
                        val === '' || val === undefined
                          ? null
                          : typeof val === 'string'
                            ? Number(val)
                            : val,
                      )
                  "
                  placeholder="Sélectionner le frais à payer"
                  :class="{ 'border-red-500': errorMessage }"
                />
                <span v-if="errorMessage" class="text-sm text-red-500">{{ errorMessage }}</span>
              </Field>
            </div>
          </div>

          <!-- Devise et méthode de paiement -->
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="space-y-2">
              <Label for="currency_id">Devise </Label>
              <Field name="currency_id" v-slot="{ field, errorMessage }">
                <ListCurrency
                  :model-value="field.value"
                  @update:model-value="
                    (val) =>
                      setFieldValue(
                        'currency_id',
                        val === '' || val === undefined
                          ? null
                          : typeof val === 'string'
                            ? Number(val)
                            : val,
                      )
                  "
                  placeholder="Sélectionner la devise"
                />
                <span v-if="errorMessage" class="text-sm text-red-500">{{ errorMessage }}</span>
              </Field>
            </div>
            <div class="space-y-2">
              <Label for="payment_method_id">Méthode de paiement </Label>
              <Field name="payment_method_id" v-slot="{ field, errorMessage }">
                <ListPaymentMethod
                  :model-value="field.value"
                  @update:model-value="
                    (val) =>
                      setFieldValue(
                        'payment_method_id',
                        val === '' || val === undefined
                          ? null
                          : typeof val === 'string'
                            ? Number(val)
                            : val,
                      )
                  "
                  placeholder="Sélectionner une méthode de paiement"
                  :class="{ 'border-red-500': errorMessage }"
                />
                <span v-if="errorMessage" class="text-sm text-red-500">{{ errorMessage }}</span>
              </Field>
            </div>
          </div>

          <!-- Compte bancaire -->
          <div class="space-y-2">
            <Label for="account_id">Compte bancaire </Label>
            <Field name="account_id" v-slot="{ field, errorMessage }">
              <ListBankAccount
                :model-value="field.value"
                @update:model-value="
                  (val) =>
                    setFieldValue(
                      'account_id',
                      val === '' || val === undefined
                        ? null
                        : typeof val === 'string'
                          ? Number(val)
                          : val,
                    )
                "
                placeholder="Sélectionner le compte bancaire"
                :class="{ 'border-red-500': errorMessage }"
              />
              <span v-if="errorMessage" class="text-sm text-red-500">{{ errorMessage }}</span>
            </Field>
          </div>

          <!-- Montant et date -->
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="space-y-2">
              <Label for="amount">Montant payé </Label>
              <Field name="amount" v-slot="{ field, errorMessage }">
                <Input
                  id="amount"
                  v-bind="field"
                  type="number"
                  step="0.01"
                  placeholder="1000.00"
                  :class="{ 'border-red-500': errorMessage }"
                />
                <span v-if="errorMessage" class="text-sm text-red-500">{{ errorMessage }}</span>
              </Field>
            </div>
            <div class="space-y-2">
              <Label for="paid_at">Date de paiement</Label>
              <Field name="paid_at" v-slot="{ field, errorMessage }">
                <Input
                  id="paid_at"
                  v-bind="field"
                  type="datetime-local"
                  :class="{ 'border-red-500': errorMessage }"
                />
                <span v-if="errorMessage" class="text-sm text-red-500">{{ errorMessage }}</span>
              </Field>
            </div>
          </div>

          <!-- Montant restant -->
          <div class="space-y-2">
            <Label for="remaining_amount">Montant restant</Label>
            <Field name="remaining_amount" v-slot="{ field, errorMessage }">
              <Input
                id="remaining_amount"
                v-bind="field"
                type="number"
                step="0.01"
                placeholder="0.00"
                :class="{ 'border-red-500': errorMessage }"
              />
              <span v-if="errorMessage" class="text-sm text-red-500">{{ errorMessage }}</span>
            </Field>
          </div>

          <!-- Détails -->
          <div class="space-y-2">
            <Label for="details">Détails</Label>
            <Field name="details" v-slot="{ field, errorMessage }">
              <Textarea
                id="details"
                v-bind="field"
                placeholder="Ajoutez des détails ou commentaires sur ce paiement..."
                rows="4"
                :class="{ 'border-red-500': errorMessage }"
                class="resize-none"
              />
              <span v-if="errorMessage" class="text-sm text-red-500">{{ errorMessage }}</span>
            </Field>
          </div>

          <!-- Boutons d'action -->
          <div class="flex items-center gap-4 pt-6">
            <Button type="submit" :disabled="creating" class="bg-primary text-primary-foreground">
              <span v-if="creating" class="iconify hugeicons--loading-03 animate-spin mr-2"></span>
              <span v-else class="iconify hugeicons--add-01 mr-2"></span>
              {{ creating ? 'Création...' : 'Créer le paiement' }}
            </Button>

            <Button type="button" variant="outline" @click="handleCancel" :disabled="creating">
              <span class="iconify hugeicons--cancel-01 mr-2"></span>
              Annuler
            </Button>
          </div>
        </form>
      </div>
    </div>
  </ComptaLayout>
</template>
