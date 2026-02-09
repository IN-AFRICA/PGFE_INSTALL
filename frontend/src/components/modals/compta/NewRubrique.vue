<script setup lang="ts">
import { ref } from 'vue'
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
import {
  Select,
  SelectContent,
  SelectGroup,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from '@/components/ui/select'
import SpanRequired from '@/components/atoms/SpanRequired.vue'

const isDialogOpen = ref(false)

const closeDialog = () => {
  isDialogOpen.value = false
}
</script>

<template>
  <Dialog v-model:open="isDialogOpen">
    <DialogTrigger as-child>
      <Button size="md" class="rounded-md">
        <span class="iconify hugeicons--plus-sign"></span>
        <span class="hidden sm:flex"> Nouvelle rubrique </span>
      </Button>
    </DialogTrigger>
    <DialogContent class="sm:max-w-[540px]">
      <DialogHeader>
        <DialogTitle>Nouvelle rubrique analytique</DialogTitle>
        <DialogDescription> Créer une nouvelle rubrique analytique pour l'école </DialogDescription>
      </DialogHeader>
      <form @submit.prevent="onSubmit" class="space-y-4">
        <div class="grid gap-4 py-4">
          <div class="flex flex-col space-y-1.5">
            <Label for="code" class="text-sm font-medium">
              Rubrique analytique
              <SpanRequired />
            </Label>
            <Input
              type="text"
              id="code"
              name="code"
              placeholder=""
              class="w-full h-10 border border-gray-200/40 bg-white transition-all"
            />
          </div>
          <div class="mt-1 pb-2">
            <p class="text-sm text-foreground-muted">* Les champs marqués sont obligatoires</p>
          </div>
        </div>
      </form>

      <DialogFooter class="flex justify-end gap-2 items-center">
        <Button size="sm" class="h-9" variant="outline" @click="closeDialog">
          <span class="iconify flex hugeicons--cancel-01 mr-1"></span>
          Annuler
        </Button>
        <Button size="sm" class="h-9" type="submit">
          <span class="iconify flex hugeicons--floppy-disk mr-1"></span>
          Enregistrer
        </Button>
      </DialogFooter>
    </DialogContent>
  </Dialog>
</template>
