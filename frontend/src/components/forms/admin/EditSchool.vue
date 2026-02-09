<script setup lang="ts">
import { ref, onMounted, computed } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import DashFormLayout from '@/components/templates/DashFormLayout.vue'
import FormSection from '@/components/atoms/FormSection.vue'
import InputWrapper from '@/components/atoms/InputWrapper.vue'
import SpanRequired from '@/components/atoms/SpanRequired.vue'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import IconifySpinner from '@/components/ui/spinner/IconifySpinner.vue'
import { usePutApi } from '@/composables/usePutApi'
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

interface School {
  id: number
  name: string
  province_id?: number
  country_id?: string
  type_id?: string
  city?: string
  address?: string
  latitude?: string | number
  longitude?: string | number
  phone_number?: string
  email?: string
  logo?: string
}

const router = useRouter()
const route = useRoute()
const schoolId = computed(() => Number(route.params.id))

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
const {
  data: schools,
  loading: loadingSchool,
  fetchData: fetchSchools,
} = useGetApi<School[]>(API_ROUTES.GET_SCHOOLS)
const school = computed(() => (schools.value || []).find((s) => s.id === schoolId.value))
const { putData, loading, error, response } = usePutApi()

const selectedProvince = computed(() => {
  if (!form.value.province_id || !provinces.value) return null
  return provinces.value.find((p) => p.id === form.value.province_id)
})

const countryId = computed(() => {
  return selectedProvince.value ? Number(selectedProvince.value.country_id) : undefined
})

onMounted(async () => {
  await Promise.all([fetchProvinces(), fetchTypes(), fetchSchools()])

  // Pre-fill form with school data
  if (school.value) {
    form.value.name = school.value.name || ''
    form.value.province_id = school.value.province_id
    form.value.type_id = school.value.type_id ? Number(school.value.type_id) : undefined
    form.value.city = school.value.city || ''
    form.value.address = school.value.address || ''
    form.value.latitude = school.value.latitude || ''
    form.value.longitude = school.value.longitude || ''
    form.value.phone_number = school.value.phone_number || ''
    form.value.email = school.value.email || ''
  }
})

function onFileChange(e: Event) {
  const input = e.target as HTMLInputElement
  const file = input.files?.[0]
  form.value.logo = file || null
}

function handleCancel() {
  router.push('/admin/ecoles')
}

async function handleSubmit() {
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

  await putData(`${API_ROUTES.GET_SCHOOLS}/${schoolId.value}`, fd)

  if (error.value) {
    showCustomToast({ message: error.value || 'Erreur lors de la modification', type: 'error' })
    return
  }

  if (response.value) {
    showCustomToast({ message: 'École modifiée avec succès', type: 'success' })
    eventBus.emit('schoolUpdated')
    router.push('/admin/ecoles')
  }
}
</script>

<template>
  <DashFormLayout
    :link-back="'/admin/ecoles'"
    title="Modifier École"
    group-route="/admin/ecoles"
    module="admin"
    :breadcrumb="[
      { label: 'Administration', href: '/admin' },
      { label: 'Écoles', href: '/admin/ecoles' },
      { label: 'Modifier École', href: '#' },
    ]"
  >
    <div v-if="loadingSchool" class="flex items-center justify-center p-8">
      <IconifySpinner size="lg" />
      <span class="ml-2">Chargement...</span>
    </div>

    <form v-else class="w-full flex flex-col space-y-8" @submit.prevent="handleSubmit">
      <FormSection
        title="Informations de l'école"
        class="grid sm:grid-cols-2 lg:grid-cols-3 gap-y-6 gap-x-10"
      >
        <InputWrapper>
          <Label for="name" class="text-sm font-medium"> Nom de l'école <SpanRequired /> </Label>
          <Input id="name" v-model="form.name" class="h-10" required :disabled="loading" />
        </InputWrapper>

        <InputWrapper>
          <Label for="province" class="text-sm font-medium"> Province <SpanRequired /> </Label>
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
        </InputWrapper>

        <InputWrapper>
          <Label for="type" class="text-sm font-medium"> Type <SpanRequired /> </Label>
          <select
            id="type"
            v-model.number="form.type_id"
            class="h-10 border rounded-md px-3"
            required
            :disabled="loading"
          >
            <option :value="undefined">-- Sélectionnez un type --</option>
            <option v-for="t in types || []" :key="t.id" :value="t.id">{{ t.title }}</option>
          </select>
        </InputWrapper>

        <InputWrapper>
          <Label for="city" class="text-sm font-medium"> Ville <SpanRequired /> </Label>
          <Input id="city" v-model="form.city" class="h-10" required :disabled="loading" />
        </InputWrapper>

        <InputWrapper>
          <Label for="address" class="text-sm font-medium"> Adresse <SpanRequired /> </Label>
          <Input id="address" v-model="form.address" class="h-10" required :disabled="loading" />
        </InputWrapper>

        <InputWrapper>
          <Label for="latitude" class="text-sm font-medium">Latitude</Label>
          <Input
            id="latitude"
            v-model="form.latitude"
            class="h-10"
            type="number"
            step="any"
            :disabled="loading"
          />
        </InputWrapper>

        <InputWrapper>
          <Label for="longitude" class="text-sm font-medium">Longitude</Label>
          <Input
            id="longitude"
            v-model="form.longitude"
            class="h-10"
            type="number"
            step="any"
            :disabled="loading"
          />
        </InputWrapper>

        <InputWrapper>
          <Label for="phone" class="text-sm font-medium">Téléphone</Label>
          <Input id="phone" v-model="form.phone_number" class="h-10" :disabled="loading" />
        </InputWrapper>

        <InputWrapper>
          <Label for="email" class="text-sm font-medium">Email</Label>
          <Input id="email" v-model="form.email" type="email" class="h-10" :disabled="loading" />
        </InputWrapper>

        <InputWrapper class="sm:col-span-2 lg:col-span-3">
          <Label for="logo" class="text-sm font-medium">Logo (image)</Label>
          <input
            id="logo"
            type="file"
            accept="image/*"
            class="h-10"
            :disabled="loading"
            @change="onFileChange"
          />
        </InputWrapper>
      </FormSection>

      <div class="flex justify-end gap-4">
        <Button type="button" variant="outline" @click="handleCancel" :disabled="loading">
          <span class="iconify hugeicons--cancel-01"></span>
          Annuler
        </Button>
        <Button type="submit" :disabled="loading">
          <span v-if="!loading" class="flex items-center gap-2">
            <span class="iconify hugeicons--floppy-disk"></span>
            <span>Enregistrer</span>
          </span>
          <span v-else class="flex items-center gap-2">
            <IconifySpinner size="lg" />
            <span>Modification...</span>
          </span>
        </Button>
      </div>
    </form>
  </DashFormLayout>
</template>
