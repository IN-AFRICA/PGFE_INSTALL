<template>
  <form @submit.prevent="onSubmit" class="space-y-4">
    <div class="grid grid-cols-2 gap-4">
      <div class="space-y-2">
        <Label :for="`${mode}-fee_type_id`">Type de frais *</Label>
        <Field name="fee_type_id" v-slot="{ field, errorMessage }">
          <ListFeeType
            :model-value="field.value"
            @update:model-value="field.onChange"
            placeholder="Sélectionner un type"
          />
          <span v-if="errorMessage" class="text-sm text-red-500">{{ errorMessage }}</span>
        </Field>
      </div>

      <div class="space-y-2">
        <Label :for="`${mode}-code`">Code du motif *</Label>
        <Field name="code" v-slot="{ field, errorMessage }">
          <Input
            :id="`${mode}-code`"
            v-bind="field"
            placeholder="Ex: SCOL, EXAM, INSC..."
            :class="{ 'border-red-500': errorMessage }"
          />
          <p v-if="errorMessage" class="text-sm text-red-500 mt-1">
            {{ errorMessage }}
          </p>
        </Field>
      </div>
    </div>

    <div class="space-y-2">
      <Label :for="`${mode}-name`">Nom du motif *</Label>
      <Field name="name" v-slot="{ field, errorMessage }">
        <Input
          :id="`${mode}-name`"
          v-bind="field"
          placeholder="Ex: Frais de scolarité, Frais d'examen..."
          :class="{ 'border-red-500': errorMessage }"
        />
        <p v-if="errorMessage" class="text-sm text-red-500 mt-1">{{ errorMessage }}</p>
      </Field>
    </div>

    <div class="space-y-2">
      <Label :for="`${mode}-description`">Description *</Label>
      <Field name="description" v-slot="{ field, errorMessage }">
        <Textarea
          :id="`${mode}-description`"
          v-bind="field"
          placeholder="Décrivez le motif de paiement..."
          rows="3"
          :class="{ 'border-red-500': errorMessage }"
        />
        <p v-if="errorMessage" class="text-sm text-red-500 mt-1">{{ errorMessage }}</p>
      </Field>
    </div>

    <DialogFooter>
      <Button type="button" variant="outline" @click="$emit('cancel')"> Annuler </Button>
      <Button type="submit" :disabled="loading">
        <span v-if="loading" class="iconify hugeicons--loading-03 mr-2 animate-spin"></span>
        {{ loading ? loadingText : submitText }}
      </Button>
    </DialogFooter>
  </form>
</template>

<script setup lang="ts">
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Textarea } from '@/components/ui/textarea'
import { DialogFooter } from '@/components/ui/dialog'
import ListFeeType from '@/utils/widgets/vues/ListFeeType.vue'
import { useForm, Field } from 'vee-validate'
import { z } from 'zod'
import { toTypedSchema } from '@vee-validate/zod'
import { watch, onMounted, computed } from 'vue'
import type { PaymentMotif, PaymentMotifFormData } from '@/composables/usePaymentMotifs'
import { showCustomToast } from '@/utils/widgets/custom_toast'

interface Props {
  mode: 'create' | 'edit'
  loading?: boolean
  motif?: PaymentMotif | null
}

interface Emits {
  (e: 'submit', data: PaymentMotifFormData): void
  (e: 'cancel'): void
}

const props = withDefaults(defineProps<Props>(), {
  loading: false,
  motif: null,
})

const emit = defineEmits<Emits>()

// Schéma de validation Zod
const paymentMotifSchema = z.object({
  fee_type_id: z
    .string()
    .transform((val) => (val === '' ? null : Number(val)))
    .nullable()
    .refine((val) => val !== null, 'Le type de frais est requis'),
  name: z
    .string()
    .min(1, 'Le nom est requis')
    .max(255, 'Le nom ne peut pas dépasser 255 caractères'),
  code: z
    .string()
    .min(1, 'Le code est requis')
    .max(50, 'Le code ne peut pas dépasser 50 caractères'),
  description: z
    .string()
    .min(1, 'La description est requise')
    .max(500, 'La description ne peut pas dépasser 500 caractères'),
})

// Configuration du formulaire
const { handleSubmit, resetForm, setFieldValue } = useForm({
  validationSchema: toTypedSchema(paymentMotifSchema),
  initialValues: {
    fee_type_id: '',
    name: '',
    code: '',
    description: '',
  },
})

// Computed properties pour les textes
const submitText = computed(() => {
  return props.mode === 'create' ? 'Créer le motif' : 'Modifier le motif'
})

const loadingText = computed(() => {
  return props.mode === 'create' ? 'Création...' : 'Modification...'
})

// Fonction de soumission
const onSubmit = handleSubmit(
  async (values) => {
    const formattedValues: PaymentMotifFormData = {
      ...values,
      fee_type_id: Number(values.fee_type_id),
    }
    emit('submit', formattedValues)
  },
  async (errors) => {
    console.log('❌ Form validation errors:', errors)
    showCustomToast({
      message: 'Veuillez corriger les erreurs dans le formulaire',
      type: 'error',
    })
  },
)

// Pré-remplir le formulaire en mode édition
const fillFormForEdit = () => {
  if (props.mode === 'edit' && props.motif) {
    setFieldValue('fee_type_id', props.motif.fee_type_id.toString())
    setFieldValue('name', props.motif.name)
    setFieldValue('code', props.motif.code)
    setFieldValue('description', props.motif.description)
  }
}

// Réinitialiser le formulaire
const reset = () => {
  resetForm()
}

onMounted(() => {
  if (props.mode === 'create') {
    resetForm()
  } else {
    fillFormForEdit()
  }
})

// Exposer la méthode reset pour le composant parent
defineExpose({
  reset,
})
</script>
