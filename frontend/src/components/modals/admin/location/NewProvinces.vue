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
import IconifySpinner from '@/components/ui/spinner/IconifySpinner.vue'
import {
  Select,
  SelectContent,
  SelectGroup,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from '@/components/ui/select'
import { usePostApi } from '@/composables/usePostApi.ts'
import { useGetApi } from '@/composables/useGetApi.ts'
import { API_ROUTES } from '@/utils/constants/api_route.ts'
import { eventBus } from '@/utils/eventBus.ts'
import { showCustomToast } from '@/utils/widgets/custom_toast.ts'
import { toTypedSchema } from '@vee-validate/zod'
import { useField, useForm } from 'vee-validate'
import z from 'zod'
// Récupère les provinces du serveur
const open = ref(false)
const { loading, error, response, postData, success } = usePostApi()

interface Country {
  id: number
  name: string
}
const {
  data: countries,
  loading: loadingCountries,
  error: errorCountries,
  fetchData: fetchCountries,
} = useGetApi<Country[]>(API_ROUTES.GET_COUNTRIES)

onMounted(() => {
  fetchCountries()
})

const schemaForm = z.object({
  name: z
    .string({
      required_error: 'Veuillez saisir le nom de la province',
    })
    .min(2, 'Le nom doit contenir au moins 2 caractères')
    .max(100),
  country_id: z
    .string({
      required_error: 'Veuillez sélectionner un pays',
    })
    .min(1, 'Veuillez sélectionner un pays'),
})

//Validation
const { handleSubmit, resetForm } = useForm({
  validationSchema: toTypedSchema(schemaForm),
})
const { value: name, errorMessage: nameError } = useField<string>('name')
const { value: country_id, errorMessage: country_idError } = useField<number>('country_id')

const onSubmit = handleSubmit(async (values) => {
  const payload = { name: values.name.trim(), country_id: Number(values.country_id) }
  await postData(API_ROUTES.CREATE_PROVINCE, payload)

  if (error.value) {
    showCustomToast({ message: error.value, type: 'error' })
    return
  }

  if (success.value) {
    eventBus.emit('provinceUpdated')
    showCustomToast({
      message: response.value?.message || 'Province ajoutée avec succès',
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
        <span class="hidden sm:flex"> Nouvelle province </span>
      </Button>
    </DialogTrigger>
    <DialogContent class="sm:max-w-[540px]">
      <DialogHeader>
        <DialogTitle>Nouvelle province</DialogTitle>
        <DialogDescription> Enregistrer une nouvelle province </DialogDescription>
      </DialogHeader>
      <form @submit.prevent="onSubmit">
        <div class="flex flex-col gap-3.5">
          <div class="flex flex-col space-y-1.5">
            <Label for="name" class="text-sm font-medium"> Nom </Label>
            <Input
              type="text"
              id="name"
              name="name"
              v-model="name"
              placeholder="Entrez le nom de la province"
              class="w-full h-10 border border-gray-200/40 bg-white transition-all"
            />
            <span v-if="nameError" class="text-xs text-red-500">{{ nameError }}</span>
          </div>
          <div class="flex flex-col space-y-1.5 flex-1">
            <Label for="country_id" class="text-sm font-medium"> Associer à un pays </Label>
            <Select
              id="country_id"
              name="country_id"
              v-model="country_id"
              :disabled="loading || loadingCountries"
            >
              <SelectTrigger id="country_id" class="h-10 w-full">
                <SelectValue placeholder="Sélectionnez un pays" />
              </SelectTrigger>
              <SelectContent>
                <SelectGroup>
                  <SelectItem
                    v-for="country in countries || []"
                    :key="country.id"
                    :value="String(country.id)"
                  >
                    {{ country.name }}
                  </SelectItem>
                </SelectGroup>
              </SelectContent>
            </Select>
            <span v-if="country_idError" class="text-xs text-red-500">{{ country_idError }}</span>
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
            :disabled="loading"
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
