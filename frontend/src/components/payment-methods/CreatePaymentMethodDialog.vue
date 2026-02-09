<template>
  <Dialog :open="isOpen" @update:open="$emit('update:isOpen', $event)">
    <DialogTrigger as-child>
      <Button
        size="md"
        @click="$emit('update:isOpen', true)"
        class="bg-primary text-primary-foreground"
      >
        <span class="iconify hugeicons--add-01 mr-2"></span>
        Nouvelle méthode
      </Button>
    </DialogTrigger>
    <DialogContent class="sm:max-w-[500px]">
      <DialogHeader>
        <DialogTitle>Créer une nouvelle méthode de paiement</DialogTitle>
        <DialogDescription>
          Ajoutez une nouvelle méthode de paiement au système.
        </DialogDescription>
      </DialogHeader>

      <form @submit.prevent="handleFormSubmit" class="space-y-4">
        <div class="space-y-2">
          <Label for="create-name">Nom de la méthode *</Label>
          <Input
            id="create-name"
            v-model="formData.name"
            placeholder="Ex: Carte bancaire, Espèces, Virement..."
            :class="{ 'border-red-500': errors.name }"
          />
          <p v-if="errors.name" class="text-sm text-red-500 mt-1">{{ errors.name }}</p>
        </div>

        <div class="space-y-2">
          <Label for="create-code">Code de la méthode *</Label>
          <Input
            id="create-code"
            v-model="formData.code"
            placeholder="Ex: CARD, CASH, WIRE..."
            :class="{ 'border-red-500': errors.code }"
          />
          <p v-if="errors.code" class="text-sm text-red-500 mt-1">{{ errors.code }}</p>
        </div>
        <DialogFooter>
          <Button type="button" variant="outline" @click="handleCancel"> Annuler </Button>
          <Button type="submit" :disabled="isLoading">
            <span v-if="isLoading" class="iconify hugeicons--loading-03 mr-2 animate-spin"></span>
            {{ isLoading ? 'Création...' : 'Créer la méthode' }}
          </Button>
        </DialogFooter>
      </form>
    </DialogContent>
  </Dialog>
</template>

<script setup lang="ts">
import { ref, reactive, watch } from 'vue'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import {
  Dialog,
  DialogContent,
  DialogDescription,
  DialogFooter,
  DialogHeader,
  DialogTitle,
  DialogTrigger,
} from '@/components/ui/dialog'
import type { PaymentMethodFormData } from '@/types/PaymentMethodTypes'

interface Props {
  isOpen: boolean
  isLoading: boolean
}

interface Emits {
  (e: 'update:isOpen', value: boolean): void
  (e: 'submit', data: PaymentMethodFormData): void
}

const props = defineProps<Props>()
const emit = defineEmits<Emits>()

const formData = reactive({
  name: '',
  code: '',
})

const errors = reactive({
  name: '',
  code: '',
})

// Reset form when dialog opens
watch(
  () => props.isOpen,
  (isOpen) => {
    if (isOpen) {
      formData.name = ''
      formData.code = ''
      errors.name = ''
      errors.code = ''
    }
  },
)

const validateForm = (): boolean => {
  let isValid = true
  errors.name = ''
  errors.code = ''

  if (!formData.name || formData.name.trim() === '') {
    errors.name = 'Le nom est requis'
    isValid = false
  } else if (formData.name.length > 255) {
    errors.name = 'Le nom ne peut pas dépasser 255 caractères'
    isValid = false
  }

  if (!formData.code || formData.code.trim() === '') {
    errors.code = 'Le code est requis'
    isValid = false
  } else if (formData.code.length > 50) {
    errors.code = 'Le code ne peut pas dépasser 50 caractères'
    isValid = false
  }

  return isValid
}

const handleFormSubmit = () => {
  if (validateForm()) {
    emit('submit', {
      name: formData.name.trim(),
      code: formData.code.trim(),
    })
  }
}

const handleCancel = () => {
  emit('update:isOpen', false)
}
</script>
