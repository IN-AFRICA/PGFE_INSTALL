<template>
  <Dialog v-model:open="open">
    <DialogTrigger as-child>
      <Button size="md" class="h-10">
        <span class="iconify hugeicons--add-01"></span>
        <span>Nouvelle école</span>
      </Button>
    </DialogTrigger>

    <DialogContent class="sm:max-w-[640px]">
      <DialogHeader>
        <DialogTitle>Ajouter une école</DialogTitle>
        <DialogDescription>Renseignez les informations de l'école.</DialogDescription>
      </DialogHeader>

      <form @submit.prevent="onSubmit">
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 py-2">
          <div class="flex flex-col space-y-1.5">
            <Label for="name" class="text-sm font-medium">Nom de l'école</Label>
            <Input id="name" v-model="form.name" class="h-10" required :disabled="loading" />
          </div>

          <div class="flex flex-col space-y-1.5">
            <Label for="province" class="text-sm font-medium">Province</Label>
            <select
              id="province"
              v-model.number="form.province_id"
              class="h-10 border rounded-md px-3"
              required
              :disabled="loading"
            >
              <option :value="undefined">-- Sélectionnez une province --</option>
              <option v-for="p in provinces || []" :key="p.id" :value="p.id">{{ p.name }}</option>
            </select>
          </div>
          <div class="flex flex-col space-y-1.5">
            <Label for="province" class="text-sm font-medium">Type</Label>
            <select
              id="province"
              v-model.number="form.type_id"
              class="h-10 border rounded-md px-3"
              required
              :disabled="loading"
            >
              <option :value="undefined">-- Sélectionnez un type --</option>
              <option v-for="t in types || []" :key="t.id" :value="t.id">{{ t.title }}</option>
            </select>
          </div>

          <div class="flex flex-col space-y-1.5">
            <Label for="city" class="text-sm font-medium">Ville</Label>
            <Input id="city" v-model="form.city" class="h-10" required :disabled="loading" />
          </div>

          <div class="flex flex-col space-y-1.5">
            <Label for="address" class="text-sm font-medium">Adresse</Label>
            <Input id="address" v-model="form.address" class="h-10" required :disabled="loading" />
          </div>

          <div class="flex flex-col space-y-1.5">
            <Label for="latitude" class="text-sm font-medium">Latitude</Label>
            <Input
              id="latitude"
              v-model="form.latitude"
              class="h-10"
              type="number"
              step="any"
              :disabled="loading"
            />
          </div>

          <div class="flex flex-col space-y-1.5">
            <Label for="longitude" class="text-sm font-medium">Longitude</Label>
            <Input
              id="longitude"
              v-model="form.longitude"
              class="h-10"
              type="number"
              step="any"
              :disabled="loading"
            />
          </div>

          <div class="flex flex-col space-y-1.5">
            <Label for="phone" class="text-sm font-medium">Téléphone</Label>
            <Input id="phone" v-model="form.phone_number" class="h-10" :disabled="loading" />
          </div>

          <div class="flex flex-col space-y-1.5">
            <Label for="email" class="text-sm font-medium">Email</Label>
            <Input id="email" v-model="form.email" type="email" class="h-10" :disabled="loading" />
          </div>

          <div class="sm:col-span-2 flex flex-col space-y-1.5">
            <Label for="logo" class="text-sm font-medium">Logo (image)</Label>
            <input
              id="logo"
              type="file"
              accept="image/*"
              class="h-10"
              :disabled="loading"
              @change="onFileChange"
            />
          </div>
        </div>

        <DialogFooter class="flex justify-end gap-2 items-center mt-2">
          <Button
            type="button"
            size="sm"
            variant="outline"
            class="h-9"
            :disabled="loading"
            @click="open = false"
          >
            Annuler
          </Button>
          <Button type="submit" size="sm" class="h-9" :disabled="loading">
            <span v-if="!loading" class="flex items-center gap-2">
              <span class="iconify hugeicons--floppy-disk"></span>
              <span>Enregistrer</span>
            </span>
            <span v-else class="flex items-center gap-2">
              <IconifySpinner size="lg" />
              <span>Enregistrement...</span>
            </span>
          </Button>
        </DialogFooter>
      </form>
    </DialogContent>
  </Dialog>
</template>

<script setup lang="ts">
import { ref, onMounted, computed } from 'vue'
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
import { Label } from '@/components/ui/label'
import { Input } from '@/components/ui/input'
import IconifySpinner from '@/components/ui/spinner/IconifySpinner.vue'
import { usePostApi } from '@/composables/usePostApi'
import { useGetApi } from '@/composables/useGetApi'
import { API_ROUTES } from '@/utils/constants/api_route'
import { eventBus } from '@/utils/eventBus'
import { showCustomToast } from '@/utils/widgets/custom_toast'

interface Province {
  id: number
  name: string
  country_id: string
}

interface Type {
  id: number
  title: string
}

const open = ref(false)
const form = ref<{
  province_id?: number
  country_id?: number
  type_id?: number
  city?: string
  name: string
  address?: string
  latitude?: string | number
  longitude?: string | number
  phone_number?: string
  email?: string
  logo?: File | null
}>({
  name: '',
  province_id: undefined,
  country_id: undefined,
  city: '',
  address: '',
  type_id: undefined,
  latitude: '0',
  longitude: '0',
  phone_number: '',
  email: '',
  logo: null,
})

const { data: provinces, fetchData: fetchProvinces } = useGetApi<Province[]>(
  API_ROUTES.GET_PROVINCES,
)
const { data: types, fetchData: fetchTypes } = useGetApi<Type[]>(API_ROUTES.GET_TYPES)
const { postData, loading, error, response } = usePostApi()

const selectedProvince = computed(() => {
  if (!form.value.province_id || !provinces.value) return null
  return provinces.value.find((p) => p.id === form.value.province_id)
})

const countryId = computed(() => {
  return selectedProvince.value ? Number(selectedProvince.value.country_id) : undefined
})

onMounted(() => {
  fetchProvinces()
  fetchTypes()
})

function onFileChange(e: Event) {
  const input = e.target as HTMLInputElement
  const file = input.files?.[0]
  form.value.logo = file || null
}

async function onSubmit() {
  const name = (form.value.name || '').trim()
  const city = (form.value.city || '').trim()
  const address = (form.value.address || '').trim()
  const type = form.value.type_id || undefined
  // Required province_id city name address
  if (!name || !form.value.province_id || !city || !address || !type) {
    showCustomToast({ message: 'Nom, province, type, ville et adresse sont requis', type: 'error' })
    return
  }

  const fd = new FormData()
  fd.append('name', name)
  fd.append('province_id', String(form.value.province_id))
  fd.append('country_id', String(countryId.value))
  fd.append('city', city)
  fd.append('address', address)
  fd.append('type_id', String(type))

  // Optional champs
  if (form.value.latitude !== '' && form.value.latitude != null)
    fd.append('latitude', String(form.value.latitude))
  if (form.value.longitude !== '' && form.value.longitude != null)
    fd.append('longitude', String(form.value.longitude))
  const phone = (form.value.phone_number || '').trim()
  if (phone) fd.append('phone_number', phone)
  const email = (form.value.email || '').trim()
  if (email) fd.append('email', email)

  if (form.value.logo) fd.append('logo', form.value.logo)

  await postData(API_ROUTES.GET_SCHOOLS, fd)

  if (error.value) {
    showCustomToast({ message: error.value || 'Erreur lors de la création', type: 'error' })
    return
  }

  if (response.value) {
    showCustomToast({ message: 'École créée avec succès', type: 'success' })
    open.value = false
    // reset
    form.value = {
      name: '',
      province_id: undefined,
      type_id: undefined,
      city: '',
      address: '',
      latitude: '',
      longitude: '',
      phone_number: '',
      email: '',
      logo: null,
    }
    eventBus.emit('schoolUpdated')
  }
}
</script>

<style scoped></style>
