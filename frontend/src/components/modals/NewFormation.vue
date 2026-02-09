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
import {
  Select,
  SelectContent,
  SelectGroup,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from '@/components/ui/select'
import { ref } from 'vue'

const metiers = [
  { value: 'Plomberie', label: 'Plomberie' },
  { value: 'Electricité', label: 'Electricité' },
  { value: 'Menuiserie', label: 'Menuiserie' },
]

const selectedMetier = ref('')
const duree = ref('')
import IconifySpinner from '@/components/ui/spinner/IconifySpinner.vue'

const loading = ref(false)
const onSubmit = async () => {
  loading.value = true
  // Simulate API call
  await new Promise((resolve) => setTimeout(resolve, 2000))
  loading.value = false
}
</script>

<template>
  <Dialog>
    <DialogTrigger as-child>
      <Button size="md" class="rounded-md">
        <span class="iconify hugeicons--plus-sign"></span>
        <span class="hidden sm:flex"> Nouvelle formation</span>
      </Button>
    </DialogTrigger>
    <DialogContent class="sm:max-w-[400px]">
      <DialogHeader>
        <DialogTitle>Nouvelle formation</DialogTitle>
        <DialogDescription> Enregistrer une nouvelle formation </DialogDescription>
      </DialogHeader>
      <div class="grid gap-4 py-4">
        <div class="flex flex-col space-y-1.5 flex-1">
          <Label for="metier" class="text-sm font-medium"> Formation-métier </Label>
          <Select v-model="selectedMetier" id="metier">
            <SelectTrigger id="metier" class="!h-10 bg-white w-full">
              <SelectValue placeholder="Sélectionner une formation-métier" />
            </SelectTrigger>
            <SelectContent>
              <SelectGroup>
                <SelectItem v-for="m in metiers" :key="m.value" :value="m.value">
                  {{ m.label }}
                </SelectItem>
              </SelectGroup>
            </SelectContent>
          </Select>
        </div>
        <div class="flex flex-col space-y-1.5">
          <Label for="duree" class="text-sm font-medium"> Durée (mois) </Label>
          <Input
            type="number"
            id="duree"
            name="duree"
            v-model="duree"
            placeholder="Durée en mois"
            class="w-full h-10 border border-gray-200/40 bg-white transition-all"
            min="1"
          />
        </div>
        <div class="mt-1 pb-2">
          <p class="text-sm text-foreground-muted">* Tous les champs sont obligatoires</p>
        </div>
      </div>
      <DialogFooter class="flex justify-end gap-2 items-center">
        <Button size="sm" class="h-9" variant="outline" type="button"> Annuler </Button>
        <Button size="sm" class="h-9" type="submit" :disabled="loading" @click="onSubmit">
          <span v-if="!loading" class="flex items-center gap-2">
            <span class="iconify hugeicons--floppy-disk mr-1"></span>
            <span>Enregistrer</span>
          </span>
          <span v-else class="flex items-center gap-2">
            <IconifySpinner size="lg" />
            <span>Enregistrement...</span>
          </span>
        </Button>
      </DialogFooter>
    </DialogContent>
  </Dialog>
</template>
