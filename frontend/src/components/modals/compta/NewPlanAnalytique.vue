<script setup lang="ts">
import { ref } from 'vue'
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
import { usePostApi } from '@/composables/usePostApi'
import { API_ROUTES } from '@/utils/constants/api_route'
import { showCustomToast } from '@/utils/widgets/custom_toast'
import { eventBus } from '@/utils/eventBus'

const isOpen = ref(false)
const code = ref('')
const name = ref('')

const { response, error, loading: posting, postData } = usePostApi<any>()

function resetForm() {
  code.value = ''
  name.value = ''
}

async function onSubmit() {
  // validations simples
  if (!code.value.trim() || !name.value.trim()) {
    showCustomToast({ message: 'Le code et le nom sont requis.', type: 'error' })
    return
  }
  if (code.value.length > 255 || name.value.length > 255) {
    showCustomToast({
      message: 'Le code et le nom doivent contenir au plus 255 caractères.',
      type: 'error',
    })
    return
  }
  try {
    await postData(API_ROUTES.CREATE_ANALYTICS_PLAN, {
      code: code.value.trim(),
      name: name.value.trim(),
    })
    if (error.value) {
      showCustomToast({ message: String(error.value), type: 'error' })
      return
    }
    if (response.value) {
      showCustomToast({
        message: response.value.message || 'Plan analytique créé avec succès',
        type: 'success',
      })
      eventBus.emit('analyticsPlanUpdated')
      resetForm()
      isOpen.value = false
    } else {
      showCustomToast({ message: 'Aucune réponse du serveur.', type: 'error' })
    }
  } catch (e) {
    showCustomToast({ message: 'Erreur lors de la création du plan analytique.', type: 'error' })
  }
}
</script>

<template>
  <Dialog v-model:open="isOpen">
    <DialogTrigger as-child>
      <Button size="md" class="rounded-md">
        <span class="iconify hugeicons--plus-sign"></span>
        <span class="hidden sm:flex"> Nouveau plan analytique </span>
      </Button>
    </DialogTrigger>
    <DialogContent class="sm:max-w-[440px]">
      <DialogHeader>
        <DialogTitle>Nouveau plan analytique</DialogTitle>
      </DialogHeader>
      <form class="grid grid-cols-1 gap-4 py-4" @submit.prevent="onSubmit">
        <div class="flex flex-col space-y-1.5">
          <Label for="numero" class="text-sm font-medium">
            Code
            <SpanRequired />
          </Label>
          <Input
            v-model="code"
            type="text"
            id="numero"
            name="code"
            placeholder="Code"
            class="w-full h-10 border border-gray-200/40 bg-white transition-all"
          />
        </div>
        <div class="flex flex-col space-y-1.5">
          <Label for="designation" class="text-sm font-medium"> Nom </Label>
          <Input
            v-model="name"
            type="text"
            id="designation"
            name="name"
            placeholder="Nom"
            class="w-full h-10 border border-gray-200/40 bg-white transition-all"
          />
        </div>
        <div class="mt-1 pb-2">
          <p class="text-sm text-foreground-muted">Les champs marqués sont obligatoires</p>
        </div>
        <DialogFooter class="flex justify-end gap-2 items-center">
          <Button type="button" size="sm" class="h-9" variant="outline" @click="isOpen = false">
            <span class="iconify flex hugeicons--cancel-01 mr-1"></span>
            Annuler
          </Button>
          <Button
            size="sm"
            class="h-9"
            type="submit"
            :disabled="posting"
            :aria-busy="posting"
            @click="onSubmit"
          >
            <span class="iconify flex hugeicons--floppy-disk mr-1"></span>
            {{ posting ? 'Enregistrement...' : 'Enregistrer' }}
          </Button>
        </DialogFooter>
      </form>
    </DialogContent>
  </Dialog>
</template>
