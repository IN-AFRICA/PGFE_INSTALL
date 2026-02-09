<script setup lang="ts">
import { ref, onMounted } from 'vue'
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
import { usePostApi } from '@/composables/usePostApi.ts'
import { API_ROUTES } from '@/utils/constants/api_route.ts'
import { eventBus } from '@/utils/eventBus.ts'
import { showCustomToast } from '@/utils/widgets/custom_toast.ts'
import { toTypedSchema } from '@vee-validate/zod'
import { useField, useForm } from 'vee-validate'
import z from 'zod'
import { useGetApi } from '@/composables/useGetApi.ts'
import IconifySpinner from '@/components/ui/spinner/IconifySpinner.vue'
import ListCountrie from '@/utils/widgets/vues/ListCountrie.vue'

const selectedCountryId = ref<number | undefined>()

const { loading, error, response, postData, success } = usePostApi()

const open = ref(false)
// Liste des provinces
interface Province {
  id: number
  name: string
}

const {
  data: provinces,
  loading: loadingProvinces,
  error: errorProvinces,
  fetchData: fetchProvinces,
} = useGetApi<Province[]>(API_ROUTES.GET_PROVINCES)

onMounted(() => {
  fetchProvinces()
})

import { watch } from 'vue'
watch(selectedCountryId, (newId) => {
  province_id.value = ''
  if (newId) {
    fetchProvinces({ country_id: newId })
  } else {
    fetchProvinces()
  }
})

const schemaForm = z.object({
  name: z
    .string({ required_error: 'Veuillez saisir le nom de la commune' })
    .min(2, 'Le nom doit contenir au moins 2 caractères')
    .max(100),
  province_id: z
    .string({ required_error: 'Veuillez sélectionner une province' })
    .min(1, 'Veuillez sélectionner une province'),
})

const { handleSubmit, resetForm } = useForm({ validationSchema: toTypedSchema(schemaForm) })
const { value: name, errorMessage: nameError } = useField<string>('name')
const { value: province_id, errorMessage: provinceIdError } = useField<string>('province_id')

const onSubmit = handleSubmit(async (values) => {
  const payload: { name: string; province_id: number } = {
    name: values.name.trim(),
    province_id: Number(values.province_id),
  }
  await postData(API_ROUTES.CREATE_COMMUNE, payload)

  if (error.value) {
    showCustomToast({ message: error.value, type: 'error' })
    return
  }

  if (success.value) {
    eventBus.emit('communeUpdated')
    showCustomToast({
      message: response.value?.message || 'Commune ajoutée avec succès',
      type: 'success',
    })
    resetForm()
    open.value = false
  }
})
</script>

<template>
  <Dialog v-model:open="open">
    <DialogTrigger as-child>
      <Button size="md" class="rounded-md">
        <span class="iconify hugeicons--plus-sign"></span>
        <span class="hidden sm:flex"> Nouvelle commune </span>
      </Button>
    </DialogTrigger>
    <DialogContent class="sm:max-w-[540px]">
      <DialogHeader>
        <DialogTitle>Nouvelle commune</DialogTitle>
        <DialogDescription> Enregistrer une nouvelle commune </DialogDescription>
      </DialogHeader>
      <form @submit.prevent="onSubmit">
        <div class="grid gap-4 py-4">
          <div class="flex flex-col space-y-1.5">
            <Label for="name" class="text-sm font-medium"> Nom </Label>
            <Input
              type="text"
              id="name"
              name="name"
              v-model="name"
              placeholder="Entrez le nom de la commune"
              class="w-full h-10 border border-gray-200/40 bg-white transition-all"
              :disabled="loading || loadingProvinces"
            />
            <span v-if="nameError" class="text-xs text-red-500">{{ nameError }}</span>
          </div>
          <div class="flex flex-col gap-3.5">
            <ListCountrie v-model="selectedCountryId" />
            <div class="flex flex-col space-y-1.5 flex-1">
              <Label for="province_id" class="text-sm font-medium"> Province </Label>
              <Select
                id="province_id"
                name="province_id"
                v-model="province_id"
                :disabled="loading || loadingProvinces"
              >
                <SelectTrigger id="province_id" class="h-10 w-full">
                  <SelectValue placeholder="Sélectionnez une province" />
                </SelectTrigger>
                <SelectContent>
                  <SelectGroup>
                    <SelectItem
                      v-for="province in provinces || []"
                      :key="province.id"
                      :value="String(province.id)"
                    >
                      {{ province.name }}
                    </SelectItem>
                  </SelectGroup>
                </SelectContent>
              </Select>
              <span v-if="provinceIdError" class="text-xs text-red-500">{{ provinceIdError }}</span>
            </div>
          </div>
        </div>
        <DialogFooter class="flex justify-end gap-2 items-center">
          <Button
            size="sm"
            class="h-9"
            variant="outline"
            type="button"
            @click="
              () => {
                resetForm()
                open = false
              }
            "
          >
            <span class="iconify flex hugeicons--cancel-01 mr-1"></span>
            Annuler
          </Button>

          <Button size="sm" class="h-9" type="submit" :disabled="loading">
            <span v-if="!loading" class="flex items-center gap-2">
              <span class="iconify hugeicons--floppy-disk mr-1"></span>
              <span>Enregistrer</span>
            </span>
            <span v-else class="flex items-center gap-2">
              <IconifySpinner size="lg" />
              <span>Enregistrement en cours...</span>
            </span>
          </Button>
        </DialogFooter>
      </form>
    </DialogContent>
  </Dialog>
</template>
