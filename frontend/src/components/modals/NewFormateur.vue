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
import IconifySpinner from '@/components/ui/spinner/IconifySpinner.vue'
import { ref } from 'vue'

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
        <span class="hidden sm:flex"> Nouveau formatteur</span>
      </Button>
    </DialogTrigger>
    <DialogContent class="sm:max-w-[600px]">
      <DialogHeader>
        <DialogTitle>Nouveau cours</DialogTitle>
        <DialogDescription> Enregistrer un nouveau cours </DialogDescription>
      </DialogHeader>
      <div class="grid gap-4 py-4">
        <!-- Block 1: Informations du cours -->
        <div class="grid sm:grid-cols-2 gap-x-6 gap-y-3">
          <div class="flex flex-col space-y-1.5">
            <Label for="intitule_cours" class="text-sm font-medium"> Intitulé du cours </Label>
            <Input
              type="text"
              id="intitule_cours"
              name="intitule_cours"
              placeholder="Entrez l'intitulé du cours"
              class="w-full h-10 border border-gray-200/40 bg-white transition-all"
            />
          </div>
          <div class="flex flex-col space-y-1.5">
            <Label for="niveau" class="text-sm font-medium"> Niveau </Label>
            <Select>
              <SelectTrigger id="niveau" class="!h-10 bg-white w-full">
                <SelectValue placeholder="Sélectionnez un niveau" />
              </SelectTrigger>
              <SelectContent>
                <SelectGroup>
                  <SelectItem value="l1">L1</SelectItem>
                  <SelectItem value="l2">L2</SelectItem>
                  <SelectItem value="l3">L3</SelectItem>
                  <SelectItem value="m1">M1</SelectItem>
                  <SelectItem value="m2">M2</SelectItem>
                </SelectGroup>
              </SelectContent>
            </Select>
          </div>
          <div class="flex flex-col space-y-1.5">
            <Label for="filiere" class="text-sm font-medium"> Section </Label>
            <Select>
              <SelectTrigger id="filiere" class="!h-10 bg-white w-full">
                <SelectValue placeholder="Sélectionnez une section" />
              </SelectTrigger>
              <SelectContent>
                <SelectGroup>
                  <SelectItem value="lettres">Lettres</SelectItem>
                  <SelectItem value="langues">Langues</SelectItem>
                  <SelectItem value="arts">Spécialisations en arts</SelectItem>
                  <SelectItem value="sciences">Sciences</SelectItem>
                </SelectGroup>
              </SelectContent>
            </Select>
          </div>
          <div class="flex flex-col space-y-1.5">
            <Label for="titulaire" class="text-sm font-medium"> Titulaire </Label>
            <Input
              type="text"
              id="titulaire"
              name="titulaire"
              placeholder="Nom du titulaire"
              class="w-full h-10 border border-gray-200/40 bg-white transition-all"
            />
          </div>
          <div class="flex flex-col space-y-1.5">
            <Label for="volume_horaire" class="text-sm font-medium"> Volume Horaire </Label>
            <Input
              type="number"
              id="volume_horaire"
              name="volume_horaire"
              placeholder="Heures"
              class="w-full h-10 border border-gray-200/40 bg-white transition-all"
            />
          </div>
        </div>
        <div class="mt-4 mb-1.5">
          <span>Maximum</span>
        </div>
        <div class="grid sm:grid-cols-2 gap-x-6 gap-y-3">
          <div class="flex flex-col space-y-1.5">
            <Label for="periode1" class="text-sm font-medium"> Période 1 </Label>
            <Select>
              <SelectTrigger id="periode1" class="!h-10 bg-white w-full">
                <SelectValue placeholder="Sélectionnez la période 1" />
              </SelectTrigger>
              <SelectContent>
                <SelectGroup>
                  <SelectItem value="septembre">Septembre</SelectItem>
                  <SelectItem value="octobre">Octobre</SelectItem>
                  <SelectItem value="novembre">Novembre</SelectItem>
                  <SelectItem value="decembre">Décembre</SelectItem>
                </SelectGroup>
              </SelectContent>
            </Select>
          </div>
          <div class="flex flex-col space-y-1.5">
            <Label for="periode2" class="text-sm font-medium"> Période 2 </Label>
            <Select>
              <SelectTrigger id="periode2" class="!h-10 bg-white w-full">
                <SelectValue placeholder="Sélectionnez la période 2" />
              </SelectTrigger>
              <SelectContent>
                <SelectGroup>
                  <SelectItem value="janvier">Janvier</SelectItem>
                  <SelectItem value="fevrier">Février</SelectItem>
                  <SelectItem value="mars">Mars</SelectItem>
                  <SelectItem value="avril">Avril</SelectItem>
                </SelectGroup>
              </SelectContent>
            </Select>
          </div>
          <div class="flex flex-col space-y-1.5">
            <Label for="periode3" class="text-sm font-medium"> Période 3 </Label>
            <Select>
              <SelectTrigger id="periode3" class="!h-10 bg-white w-full">
                <SelectValue placeholder="Sélectionnez la période 3" />
              </SelectTrigger>
              <SelectContent>
                <SelectGroup>
                  <SelectItem value="mai">Mai</SelectItem>
                  <SelectItem value="juin">Juin</SelectItem>
                  <SelectItem value="juillet">Juillet</SelectItem>
                  <SelectItem value="aout">Août</SelectItem>
                </SelectGroup>
              </SelectContent>
            </Select>
          </div>
          <div class="flex flex-col space-y-1.5">
            <Label for="periode4" class="text-sm font-medium"> Période 4 </Label>
            <Select>
              <SelectTrigger id="periode4" class="!h-10 bg-white w-full">
                <SelectValue placeholder="Sélectionnez la période 4" />
              </SelectTrigger>
              <SelectContent>
                <SelectGroup>
                  <SelectItem value="septembre2">Septembre (2ème semestre)</SelectItem>
                  <SelectItem value="octobre2">Octobre (2ème semestre)</SelectItem>
                  <SelectItem value="novembre2">Novembre (2ème semestre)</SelectItem>
                  <SelectItem value="decembre2">Décembre (2ème semestre)</SelectItem>
                </SelectGroup>
              </SelectContent>
            </Select>
          </div>
          <div class="flex flex-col space-y-1.5">
            <Label for="examen1" class="text-sm font-medium"> Examen 1 </Label>
            <Input
              type="text"
              id="examen1"
              name="examen1"
              placeholder="Type d'examen 1"
              class="w-full h-10 border border-gray-200/40 bg-white transition-all"
            />
          </div>
          <div class="flex flex-col space-y-1.5">
            <Label for="examen2" class="text-sm font-medium"> Examen 2 </Label>
            <Input
              type="text"
              id="examen2"
              name="examen2"
              placeholder="Type d'examen 2"
              class="w-full h-10 border border-gray-200/40 bg-white transition-all"
            />
          </div>
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
