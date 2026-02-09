<script setup lang="ts">
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
import {
  Select,
  SelectContent,
  SelectGroup,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from '@/components/ui/select'
import SpanRequired from '@/components/atoms/SpanRequired.vue'
</script>

<template>
  <Dialog>
    <DialogTrigger as-child>
      <Button size="md" class="rounded-md">
        <span class="iconify hugeicons--plus-sign"></span>
        <span class="hidden sm:flex"> Nouvelle ligne budgétaire </span>
      </Button>
    </DialogTrigger>
    <DialogContent class="sm:max-w-[540px]">
      <DialogHeader>
        <DialogTitle>Nouvelle ligne budgétaire</DialogTitle>
      </DialogHeader>
      <div class="grid gap-4 py-4">
        <div class="flex flex-col space-y-1.5">
          <Label for="budget" class="text-sm font-medium">
            Ligne budgetaire
            <SpanRequired />
          </Label>
          <Input />
        </div>
        <div class="flex flex-col space-y-1.5">
          <Label for="rubrique" class="text-sm font-medium">
            Catégorie
            <SpanRequired />
          </Label>
          <Select id="rubrique">
            <SelectTrigger id="rubrique" class="h-10 w-full">
              <SelectValue placeholder="Sélectionner la rubrique" />
            </SelectTrigger>
            <SelectContent>
              <SelectGroup>
                <SelectItem value="RUB001">RUB001 - Frais de personnel</SelectItem>
                <SelectItem value="RUB002">RUB002 - Frais de fonctionnement</SelectItem>
                <SelectItem value="RUB003">RUB003 - Frais de maintenance</SelectItem>
                <SelectItem value="RUB004">RUB004 - Frais de scolarité</SelectItem>
                <SelectItem value="RUB005">RUB005 - Frais de cantine</SelectItem>
                <SelectItem value="RUB006">RUB006 - Frais de transport</SelectItem>
                <SelectItem value="RUB007">RUB007 - Frais de fournitures</SelectItem>
                <SelectItem value="RUB008">RUB008 - Frais d'électricité</SelectItem>
              </SelectGroup>
            </SelectContent>
          </Select>
        </div>
        <div class="mt-1 pb-2">
          <p class="text-sm text-foreground-muted">* Tous les champs sont obligatoires</p>
        </div>
      </div>
      <DialogFooter class="flex justify-end gap-2 items-center">
        <Button size="sm" class="h-9" variant="outline">
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
