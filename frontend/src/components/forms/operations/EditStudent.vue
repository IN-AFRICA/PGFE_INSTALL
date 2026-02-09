<script setup lang="ts">
import FormSection from '@/components/atoms/FormSection.vue'
import InputWrapper from '@/components/atoms/InputWrapper.vue'
import SpanRequired from '@/components/atoms/SpanRequired.vue'
import DashFormLayout from '@/components/templates/DashFormLayout.vue'
import { Button } from '@/components/ui/button'
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
import Spinner from '@/components/ui/spinner/Spinner.vue'
import { usePutApi } from '@/composables/usePutApi.ts'
import { useGetApi } from '@/composables/useGetApi.ts'
import api from '@/services/api'
import { API_ROUTES } from '@/utils/constants/api_route.ts'
import { eventBus } from '@/utils/eventBus.ts'
import { showCustomToast } from '@/utils/widgets/custom_toast.ts'
import ListParents from '@/utils/widgets/vues/ListParents.vue'
import { toTypedSchema } from '@vee-validate/zod'
import { useField, useForm } from 'vee-validate'
import { useRoute, useRouter } from 'vue-router'
import z from 'zod'
import { onBeforeUnmount, onMounted, ref, computed, watch } from 'vue'
import { Textarea } from '@/components/ui/textarea'
import { useAuthStore } from '@/stores/auth'
import { useAcademicLevels } from '@/composables/useAcademicLevels'

const router = useRouter()

//Create form schema
const schemaForm = z.object({
  country_id: z.preprocess(
    (val) => (val === '' || val === undefined ? undefined : Number(val)),
    z.number({ required_error: 'Veuillez sélectionner le pays' }).min(1, 'Le pays est requis'),
  ),
  province_id: z.preprocess(
    (val) => (val === '' || val === undefined ? undefined : Number(val)),
    z
      .number({ required_error: 'Veuillez sélectionner la province' })
      .min(1, 'La province est requise'),
  ),
  territory_id: z.preprocess(
    (val) => (val === '' || val === undefined ? undefined : Number(val)),
    z
      .number({ required_error: 'Veuillez sélectionner le territoire' })
      .min(1, 'Le territoire est requis'),
  ),
  commune_id: z.preprocess(
    (val) => (val === '' || val === undefined ? undefined : Number(val)),
    z
      .number({ required_error: 'Veuillez sélectionner la commune' })
      .min(1, 'La commune est requise'),
  ),
  classroom_id: z.preprocess(
    (val) => (val === '' || val === undefined ? undefined : Number(val)),
    z.number({ required_error: 'Veuillez sélectionner la classe' }).min(1, 'La classe est requise'),
  ),
  parents_id: z.preprocess(
    (val) => (val === '' || val === undefined ? undefined : Number(val)),
    z.number({ required_error: 'Veuillez sélectionner le parent' }).min(1, 'Le parent est requis'),
  ),
  cycle_id: z.preprocess(
    (val) => (val === '' || val === undefined ? undefined : Number(val)),
    z.number({ required_error: 'Veuillez sélectionner le cycle' }).min(1, 'Le cycle est requis'),
  ),
  academic_level_id: z.preprocess(
    (val) => (val === '' || val === undefined ? undefined : Number(val)),
    z
      .number({ required_error: 'Veuillez sélectionner le niveau scolaire' })
      .min(1, 'Le niveau scolaire est requis'),
  ),
  filiaire_id: z.preprocess(
    (val) => (val === '' || val === undefined ? undefined : Number(val)),
    z
      .number({ required_error: 'Veuillez sélectionner la filière' })
      .min(1, 'La filière est requise'),
  ),
  name: z.string({ required_error: 'Veuillez saisir le nom' }).min(2).max(100),
  lastname: z.string({ required_error: 'Veuillez saisir le post nom' }).min(2).max(100),
  firstname: z.string({ required_error: 'Veuillez saisir le prénom' }).min(2).max(100),
  gender: z.string({ required_error: 'Veuillez sélectionner le genre' }),
  civil_status: z.string({ required_error: "Veuillez sélectionner l'état civil" }),
  address: z.string({ required_error: "Veuillez saisir l'adresse" }).min(2).max(255),
  birth_date: z.string({ required_error: 'Veuillez saisir la date de naissance' }),
  birth_place: z.string({ required_error: 'Veuillez saisir le lieu de naissance' }).min(2).max(100),
  identity_card: z.string().min(2).max(100).optional(),
  phone_number: z
    .string({ required_error: 'Veuillez saisir le numéro de téléphone' })
    .min(2)
    .max(30)
    .nullable(),
  email: z.string({ required_error: "Veuillez saisir l'email" }).email('Email invalide').nullable(),
  image: z.string().optional(),
  note: z.string().optional(),
  // Champs d'inscription (optionnels) - à confirmer côté backend
  previousSchool: z.string().max(255).optional(),
  percentageObtained: z
    .union([z.coerce.number().min(0).max(100), z.literal('')])
    .optional()
    .default(''),
})

//Validation
const { handleSubmit, resetForm } = useForm({
  validationSchema: toTypedSchema(schemaForm),
})

const { value: name, errorMessage: nameError } = useField<string>('name')
const { value: lastname, errorMessage: lastnameError } = useField<string>('lastname')
const { value: firstname, errorMessage: firstnameError } = useField<string>('firstname')
const { value: gender, errorMessage: genderError } = useField<string>('gender')
const { value: civil_status, errorMessage: civilStatusError } = useField<string>('civil_status')
const { value: address, errorMessage: addressError } = useField<string>('address')
const { value: birth_date, errorMessage: birthDateError } = useField<string>('birth_date')
const { value: birth_place, errorMessage: birthPlaceError } = useField<string>('birth_place')
const { value: identity_card, errorMessage: identityCardError } = useField<string>('identity_card')
const { value: phone_number, errorMessage: phoneNumberError } = useField<string>('phone_number')
const { value: email, errorMessage: emailError } = useField<string>('email')

// IDs en tant que n'importe quel type (string | number) mais traités comme string pour Radix Select
const { value: country_id, errorMessage: countryIdError } = useField<any>('country_id')
const { value: province_id, errorMessage: provinceIdError } = useField<any>('province_id')
const { value: territory_id, errorMessage: territoryIdError } = useField<any>('territory_id')
const { value: commune_id, errorMessage: communeIdError } = useField<any>('commune_id')
const { value: classroom_id, errorMessage: classroomIdError } = useField<any>('classroom_id')
const { value: parents_id, errorMessage: parentIdError } = useField<any>('parents_id')
const { value: academic_level_id, errorMessage: academicLevelIdError } =
  useField<any>('academic_level_id')
const { value: filiaire_id, errorMessage: filiaireIdError } = useField<any>('filiaire_id')
const { value: cycle_id, errorMessage: cycleIdError } = useField<any>('cycle_id')

const { value: note, errorMessage: noteError } = useField<string>('note')
// Champs inscription (optionnels)
const { value: previousSchool, errorMessage: previousSchoolError } =
  useField<string>('previousSchool')
const { value: percentageObtained, errorMessage: percentageObtainedError } =
  useField<any>('percentageObtained')
const { loading, error, response, putData, success } = usePutApi()
const route = useRoute()
const studentId = route.params.id as string
const isInitialLoading = ref(false)

// Récupérer l'utilisateur connecté
const authStore = useAuthStore()
const userSchoolId = computed(() => authStore.user?.school_id || null)
const currentUserId = computed(() => authStore.user?.id || null)
// Charger toutes les données de référence
const { data: cyclesData, fetchData: fetchCycles } = useGetApi(API_ROUTES.GET_CYCLES)
const { data: academicLevelsData, fetchData: fetchAcademicLevels } = useGetApi(
  API_ROUTES.GET_ACADEMIC_LEVELS,
)
const { data: filiaresData, fetchData: fetchFiliaires } = useGetApi(API_ROUTES.GET_FILLIERES)
const { data: classroomsData, fetchData: fetchClassrooms } = useGetApi(API_ROUTES.GET_CLASSROOMS)

// Charger les données géographiques
const { data: countriesData, fetchData: fetchCountries } = useGetApi(API_ROUTES.GET_COUNTRIES)
const { data: provincesData, fetchData: fetchProvinces } = useGetApi(API_ROUTES.GET_PROVINCES)
const { data: territoriesData, fetchData: fetchTerritories } = useGetApi(API_ROUTES.GET_TERRITORIES)
const { data: communesData, fetchData: fetchCommunes } = useGetApi(API_ROUTES.GET_COMMUNES)

onMounted(async () => {
  isInitialLoading.value = true
  await Promise.all([fetchCycles(), fetchAcademicLevels(), fetchFiliaires(), fetchCountries()])

  if (studentId) {
    await fetchStudentById()
  } else {
    console.error('No studentId found in route params')
  }

  // Appeler loadLevelsComposable après avoir pré-chargé les données pour être sûr
  await loadLevelsComposable()
  isInitialLoading.value = false
})
// Helper pour formater la date au format YYYY-MM-DD
const formatDateForInput = (dateString?: string): string => {
  if (!dateString) return ''
  try {
    const d = new Date(dateString)
    if (isNaN(d.getTime())) return ''
    return d.toISOString().split('T')[0]
  } catch {
    return ''
  }
}

const fetchStudentById = async () => {
  try {
    console.log('Fetching student with ID:', studentId)

    // Fetch both student info and registration info concurrently
    const [studentRes, registrationRes] = await Promise.all([
      api.get(API_ROUTES.GET_STUDENT_BY_ID.replace(':student', studentId)),
      api.get(`${API_ROUTES.GET_STUDENT_REGISTRATIONS}?student_id=${studentId}`),
    ])

    console.log('Student API response:', studentRes.data)
    console.log('Registration API response:', registrationRes.data)

    const student = studentRes.data.student || studentRes.data
    const registrations = extractArray(registrationRes.data)
    // Find active registration or just the first one if multiple
    const registration =
      registrations.find((r: any) => r.registration_status === true) || registrations[0]

    if (!student) {
      console.error('No student data found in response')
      return
    }

    // Personal Info
    name.value = student.name
    lastname.value = student.lastname
    firstname.value = student.firstname
    gender.value = student.gender
    civil_status.value = student.civil_status
    birth_date.value = formatDateForInput(student.birth_date)
    birth_place.value = student.birth_place
    identity_card.value = student.identity_card || student.id_card || student.identity_card_number
    phone_number.value = student.phone_number
    email.value = student.email
    address.value = student.address
    note.value = student.note

    // Registration Info (Academic logic)
    if (registration) {
      previousSchool.value =
        registration.previousSchool ||
        registration.previous_school ||
        student.previousSchool ||
        student.previous_school
      percentageObtained.value =
        registration.percentageObtained ||
        registration.percentage_obtained ||
        student.percentageObtained ||
        student.percentage_obtained

      // Academic hierarchy
      filiaire_id.value = String(registration.filiaire_id)
      cycle_id.value = String(registration.cycle_id)

      // Trigger level loading and classroom fetch
      await loadLevelsComposable()
      academic_level_id.value = String(registration.academic_level_id)

      await fetchClassrooms({ academic_level_id: registration.academic_level_id })
      classroom_id.value = String(registration.classroom_id)
    }

    if (student.image) {
      previewUrl.value = student.image
      image.value = student.image
    }

    // Geographical Info
    country_id.value = String(student.country_id)
    await fetchProvinces({ country_id: student.country_id })
    province_id.value = String(student.province_id)

    await Promise.all([
      fetchTerritories({ province_id: student.province_id }),
      fetchCommunes({ province_id: student.province_id }),
    ])
    territory_id.value = String(student.territory_id)
    commune_id.value = String(student.commune_id)

    // Parents
    parents_id.value = String(student.parents_id)
    if (student.parent2_id) parents_id_2.value = String(student.parent2_id)
    if (student.parent3_id) parents_id_3.value = String(student.parent3_id)

    phone_number_2.value = student.phone_number_2 || ''
    email_2.value = student.email_2 || ''
    phone_number_3.value = student.phone_number_3 || ''
    email_3.value = student.email_3 || ''
  } catch (err) {
    showCustomToast({
      message: "Erreur lors de la récupération des données de l'apprenant",
      type: 'error',
    })
    console.error(err)
  }
}

// Filtrage en cascade basé sur la hiérarchie: Cycle → Niveau → Filière → Classe
// Helper pour extraire le tableau des données (paginées ou non)
const extractArray = (data: any): any[] => {
  if (!data) return []
  if (Array.isArray(data)) return data
  if (data.data && Array.isArray(data.data)) return data.data
  return []
}

// L'API /filiaires/lists retourne déjà les filières de l'école de l'utilisateur connecté
// Pas besoin de filtrer par school_id
const filteredFilieres = computed(() => {
  const items = extractArray(filiaresData.value)
  console.log('Filières récupérées:', items)
  return items
})

// Les cycles sont extraits depuis les cycles imbriqués dans la filière sélectionnée
const filteredCycles = computed(() => {
  if (!filiaire_id.value) return []

  // D'abord chercher dans les filières (structure imbriquée de l'API /filiaires/lists)
  const filieres = extractArray(filiaresData.value)
  const selectedFiliere = filieres.find((f: any) => String(f.id) === String(filiaire_id.value))

  if (selectedFiliere?.cycles && selectedFiliere.cycles.length > 0) {
    console.log('Cycles depuis filière sélectionnée:', selectedFiliere.cycles)
    return selectedFiliere.cycles
  }

  // Fallback: chercher dans cyclesData si la structure n'est pas imbriquée
  const cycleItems = extractArray(cyclesData.value)
  const filtered = cycleItems.filter(
    (c: any) => String(c.filiaire_id) === String(filiaire_id.value),
  )
  console.log('Cycles depuis API cycles:', filtered)
  return filtered
})

const {
  levels: filteredAcademicLevels,
  levelOptions,
  loadLevels: loadLevelsComposable,
} = useAcademicLevels(filiaire_id, cycle_id)

// Redondant avec le premier onMounted, supprimé pour éviter les conflits

const filteredClassrooms = computed(() => {
  return extractArray(classroomsData.value)
})

const filteredProvinces = computed(() => {
  return extractArray(provincesData.value)
})

const filteredTerritories = computed(() => {
  return extractArray(territoriesData.value)
})

const filteredCommunes = computed(() => {
  return extractArray(communesData.value)
})

// Reset des champs dépendants en cascade
watch(filiaire_id, (newVal, oldVal) => {
  if (!isInitialLoading.value && oldVal !== undefined && newVal !== oldVal) {
    cycle_id.value = undefined as any
    academic_level_id.value = undefined as any
    classroom_id.value = undefined as any
    // Reset manual de la liste quand on change
    classroomsData.value = []
  }
})

watch(cycle_id, (newVal, oldVal) => {
  if (!isInitialLoading.value && oldVal !== undefined && newVal !== oldVal) {
    academic_level_id.value = undefined as any
    classroom_id.value = undefined as any
    classroomsData.value = []
  }
})

watch(academic_level_id, async (newVal, oldVal) => {
  if (!isInitialLoading.value && oldVal !== undefined && newVal !== oldVal) {
    classroom_id.value = undefined as any
  }

  // Charger dynamiquement les classes quand le niveau change
  if (newVal && !isInitialLoading.value) {
    await fetchClassrooms({ academic_level_id: newVal })
  } else if (!isInitialLoading.value) {
    classroomsData.value = []
  }
})

// Reset des champs géographiques en cascade
watch(country_id, async (newVal, oldVal) => {
  if (!isInitialLoading.value && oldVal !== undefined && newVal !== oldVal) {
    province_id.value = undefined as any
    territory_id.value = undefined as any
    commune_id.value = undefined as any
  }

  if (newVal && !isInitialLoading.value) {
    await fetchProvinces({ country_id: newVal })
  } else if (!isInitialLoading.value) {
    provincesData.value = []
  }
})

watch(province_id, async (newVal, oldVal) => {
  if (!isInitialLoading.value && oldVal !== undefined && newVal !== oldVal) {
    territory_id.value = undefined as any
    commune_id.value = undefined as any
  }

  if (newVal && !isInitialLoading.value) {
    await fetchTerritories({ province_id: newVal })
    await fetchCommunes({ province_id: newVal })
  } else if (!isInitialLoading.value) {
    territoriesData.value = []
    communesData.value = []
  }
})

watch(territory_id, (newVal, oldVal) => {
  if (oldVal !== undefined && newVal !== oldVal) {
    // Territoire ne reset plus commune car commune dépend de province
  }
})

const onSubmit = handleSubmit(async (values) => {
  // Intégration parents (comportement en cascade conservé)
  const parent1 = values.parents_id ? Number(values.parents_id) : undefined
  const parent2 = parents_id_2.value ? Number(parents_id_2.value) : undefined
  const parent3 = parents_id_3.value ? Number(parents_id_3.value) : undefined

  const payload: Record<string, any> = {
    ...values,
    // Mapper selon les champs backend
    parents_id: parent1,
    ...(typeof parent2 === 'number' && !Number.isNaN(parent2) ? { parent2_id: parent2 } : {}),
    ...(typeof parent3 === 'number' && !Number.isNaN(parent3) ? { parent3_id: parent3 } : {}),
    // Préparer l'envoi des coordonnées supplémentaires si le backend les expose
    ...(parents_id_2.value && phone_number_2.value ? { phone_number_2: phone_number_2.value } : {}),
    ...(parents_id_2.value && email_2.value ? { email_2: email_2.value } : {}),
    ...(parents_id_3.value && phone_number_3.value ? { phone_number_3: phone_number_3.value } : {}),
    ...(parents_id_3.value && email_3.value ? { email_3: email_3.value } : {}),
    academic_personal_id: currentUserId.value,
  }

  // Convertir tous les IDs restants en Number
  const idFields = [
    'country_id',
    'province_id',
    'territory_id',
    'commune_id',
    'classroom_id',
    'cycle_id',
    'academic_level_id',
    'filiaire_id',
  ]
  idFields.forEach((field) => {
    if (payload[field]) payload[field] = Number(payload[field])
  })

  // Mapper les champs d'inscription SI présents (clé API à confirmer via Postman)
  if (previousSchool?.value) {
    payload.previousSchool = previousSchool.value
  }
  if (percentageObtained?.value !== undefined && percentageObtained?.value !== '') {
    payload.percentageObtained = Number(percentageObtained.value)
  }

  // Vérifier que l'ID utilisateur existe
  if (!currentUserId.value) {
    showCustomToast({
      message: "Impossible de récupérer l'utilisateur connecté",
      type: 'error',
    })
    return
  }

  await putData(API_ROUTES.UPDATE_STUDENT.replace(':student', studentId), payload)

  if (error.value) {
    showCustomToast({
      message: error.value || "Erreur lors de la modification de l'apprenant",
      type: 'error',
    })
    return
  }

  if (success.value) {
    showCustomToast({
      message: response.value?.message || 'Élève modifié avec succès',
      type: 'success',
    })
    eventBus.emit('studentUpdated')
    resetForm()
    router.push('/apprenants/operations')
  }
})

const { value: image, errorMessage: imageError } = useField<string>('image')

// Photo picker (matches template refs)
const fileInput = ref<HTMLInputElement | null>(null)
const previewUrl = ref<string | null>(null)
function onPhotoSelected(e: Event) {
  const input = e.target as HTMLInputElement
  const file = input?.files?.[0]
  if (!file) return

  // Convertir le fichier en base64
  const reader = new FileReader()
  reader.onload = (event) => {
    const base64String = event.target?.result as string
    image.value = base64String
    previewUrl.value = base64String
  }
  reader.readAsDataURL(file)
}

onBeforeUnmount(() => {
  if (previewUrl.value) URL.revokeObjectURL(previewUrl.value)
})

// Champs pour Parent 2 et Parent 3
const parents_id_2 = ref<string>('')
const parents_id_3 = ref<string>('')

// Coordonnées optionnelles pour Parent 2 et Parent 3
const phone_number_2 = ref<string>('')
const email_2 = ref<string>('')
const phone_number_3 = ref<string>('')
const email_3 = ref<string>('')

// Empêcher les doublons: exclure des listes les parents déjà choisis
const excludeForParent2 = computed(() =>
  [parents_id.value, parents_id_3.value].filter((v) => v !== undefined && v !== ''),
)
const excludeForParent3 = computed(() =>
  [parents_id.value, parents_id_2.value].filter((v) => v !== undefined && v !== ''),
)

// Si Parent 2 est vidé, on vide automatiquement Parent 3 pour garder une UI propre
watch(parents_id_2, (val) => {
  if (!val) {
    parents_id_3.value = ''
    phone_number_2.value = ''
    email_2.value = ''
    phone_number_3.value = ''
    email_3.value = ''
  }
})

// Si Parent 3 est vidé, on vide aussi ses coordonnées
watch(parents_id_3, (val) => {
  if (!val) {
    phone_number_3.value = ''
    email_3.value = ''
  }
})
</script>
<template>
  <DashFormLayout
    title="Modifier l'apprenant"
    link-back="/apprenants/operations"
    group-route="/apprenants/operations"
    module="students"
    :breadcrumb="[
      { label: 'Élèves', href: '/apprenants' },
      { label: 'Opérations', href: '/apprenants/operations' },
      { label: 'Inscriptions', href: '/apprenants/operations' },
      { label: 'Modifier Élève', href: '#' },
    ]"
  >
    <div
      v-if="isInitialLoading"
      class="flex gap-2 items-center justify-center py-20 bg-white h-full rounded-md text-gray-500"
    >
      <span class="iconify animate-spin hugeicons--loading-03 text-2xl"></span>
      <span>Chargement des données de l'apprenant...</span>
    </div>
    <form v-else class="w-full flex flex-col space-y-8" @submit.prevent="onSubmit">
      <FormSection
        class="grid sm:grid-cols-2 lg:grid-cols-3 gap-y-6 gap-x-10"
        title="Informations personnelles"
      >
        <!-- Photo -->
        <InputWrapper class="lg:row-span-4">
          <Label class="text-sm font-medium text-gray-700">Photo</Label>
          <div
            class="relative w-full h-64 lg:h-80 bg-gray-100 rounded-lg border-2 border-dashed border-gray-300 flex flex-col items-center justify-center cursor-pointer text-center overflow-hidden"
            @click="() => fileInput?.click()"
          >
            <template v-if="previewUrl">
              <img
                :src="previewUrl"
                alt="Photo élève"
                class="absolute inset-0 w-full h-full object-cover"
              />
              <div
                class="absolute bottom-2 right-2 w-8 h-8 rounded-full bg-white/90 border border-gray-200 flex items-center justify-center shadow"
              >
                <span class="iconify hugeicons--plus-sign text-blue-600 text-lg"></span>
              </div>
            </template>
            <template v-else>
              <div class="w-16 h-16 bg-blue-50 rounded-full flex items-center justify-center mb-2">
                <span class="iconify hugeicons--plus-sign text-blue-600 text-xl"></span>
              </div>
              <span class="text-xs text-gray-500">Ajouter une image</span>
            </template>
            <input
              ref="fileInput"
              type="file"
              accept="image/*"
              class="hidden"
              @change="onPhotoSelected"
            />
          </div>
        </InputWrapper>

        <!-- Champs à droite - ordre linéaire pour Tab -->
        <InputWrapper>
          <Label class="text-sm">
            Nom
            <SpanRequired />
          </Label>
          <Input
            class="bg-white transition-all h-10 rounded-md"
            placeholder="Kasongo"
            v-model="name"
          />
          <span v-if="nameError" class="text-xs text-red-500">{{ nameError }}</span>
        </InputWrapper>

        <InputWrapper>
          <Label class="text-sm">
            Post nom
            <SpanRequired />
          </Label>
          <Input
            class="bg-white transition-all h-10 rounded-md"
            placeholder="Muleka"
            v-model="lastname"
          />
          <span v-if="lastnameError" class="text-xs text-red-500">{{ lastnameError }}</span>
        </InputWrapper>

        <InputWrapper>
          <Label class="text-sm">
            Prenom
            <SpanRequired />
          </Label>
          <Input
            class="bg-white transition-all h-10 rounded-md"
            placeholder="Isaac"
            v-model="firstname"
          />
          <span v-if="firstnameError" class="text-xs text-red-500">{{ firstnameError }}</span>
        </InputWrapper>

        <InputWrapper>
          <Label class="text-sm">
            Genre
            <SpanRequired />
          </Label>
          <Select v-model="gender">
            <SelectTrigger id="genre" class="!h-10 bg-white w-full">
              <SelectValue placeholder="Sélectionner le genre" />
            </SelectTrigger>
            <SelectContent>
              <SelectGroup>
                <SelectItem value="Masculin">Masculin</SelectItem>
                <SelectItem value="Féminin">Feminin</SelectItem>
              </SelectGroup>
            </SelectContent>
          </Select>
          <span v-if="genderError" class="text-xs text-red-500">{{ genderError }}</span>
        </InputWrapper>

        <InputWrapper>
          <Label class="text-sm">
            Date de naissance
            <SpanRequired />
          </Label>
          <Input type="date" class="bg-white transition-all h-10 rounded-md" v-model="birth_date" />
          <span v-if="birthDateError" class="text-xs text-red-500">{{ birthDateError }}</span>
        </InputWrapper>
        <InputWrapper>
          <Label class="text-sm"> Lieu de naissance </Label>
          <Input class="bg-white transition-all h-10 rounded-md" v-model="birth_place" />
          <span v-if="birthPlaceError" class="text-xs text-red-500">{{ birthPlaceError }}</span>
        </InputWrapper>

        <InputWrapper>
          <Label class="text-sm">
            Etat civil
            <SpanRequired />
          </Label>
          <Select v-model="civil_status">
            <SelectTrigger id="etat-civil" class="!h-10 bg-white w-full">
              <SelectValue placeholder="Sélectionner l'état civil" />
            </SelectTrigger>
            <SelectContent>
              <SelectGroup>
                <SelectItem value="Célibataire">Célibataire</SelectItem>
                <SelectItem value="Marié(e)">Marié(e)</SelectItem>
                <SelectItem value="Divorcé(e)">Divorcé(e)</SelectItem>
                <SelectItem value="Veuf/Veuve">Veuf/Veuve</SelectItem>
              </SelectGroup>
            </SelectContent>
          </Select>
          <span v-if="civilStatusError" class="text-xs text-red-500">{{ civilStatusError }}</span>
        </InputWrapper>

        <InputWrapper>
          <Label class="text-sm"> Numéro Permanent </Label>
          <Input class="bg-white transition-all h-10 rounded-md" v-model="identity_card" />
          <span v-if="identityCardError" class="text-xs text-red-500">{{ identityCardError }}</span>
        </InputWrapper>

        <!-- Reste des champs en grille normale -->
      </FormSection>

      <div class="w-full flex h-px bg-gray-300"></div>

      <FormSection
        class="grid sm:grid-cols-2 lg:grid-cols-3 gap-y-6 gap-x-10"
        title="Informations parents"
      >
        <!-- Parent 1 (requis) -->
        <InputWrapper :error="parentIdError">
          <ListParents v-model="parents_id" />
          <span v-if="parentIdError" class="text-xs text-red-500">{{ parentIdError }}</span>
        </InputWrapper>

        <!-- Coordonnées principales (rattachées au dossier) -->
        <InputWrapper>
          <Label class="text-sm">Téléphone</Label>
          <Input class="bg-white transition-all h-10 rounded-md" v-model="phone_number" />
          <span v-if="phoneNumberError" class="text-xs text-red-500">{{ phoneNumberError }}</span>
        </InputWrapper>
        <InputWrapper>
          <Label class="text-sm">Adresse email</Label>
          <Input class="bg-white transition-all h-10 rounded-md" v-model="email" />
          <span v-if="emailError" class="text-xs text-red-500">{{ emailError }}</span>
        </InputWrapper>

        <!-- Parent 2 (optionnel) - s'affiche si Parent 1 est renseigné -->
        <InputWrapper v-if="parents_id" class="relative">
          <ListParents v-model="parents_id_2" :exclude-ids="excludeForParent2" />
          <button
            v-if="parents_id_2"
            type="button"
            class="mt-1 text-xs text-red-600 underline self-start"
            @click="parents_id_2 = ''"
          >
            Supprimer ce parent
          </button>
        </InputWrapper>
        <InputWrapper v-if="parents_id_2">
          <Label class="text-sm">Téléphone parent 2</Label>
          <Input class="bg-white transition-all h-10 rounded-md" v-model="phone_number_2" />
        </InputWrapper>
        <InputWrapper v-if="parents_id_2">
          <Label class="text-sm">Email parent 2</Label>
          <Input class="bg-white transition-all h-10 rounded-md" v-model="email_2" />
        </InputWrapper>

        <!-- Parent 3 (optionnel) - s'affiche si Parent 2 est renseigné -->
        <InputWrapper v-if="parents_id_2" class="relative">
          <ListParents v-model="parents_id_3" :exclude-ids="excludeForParent3" />
          <button
            v-if="parents_id_3"
            type="button"
            class="mt-1 text-xs text-red-600 underline self-start"
            @click="parents_id_3 = ''"
          >
            Supprimer ce parent
          </button>
        </InputWrapper>
        <InputWrapper v-if="parents_id_3">
          <Label class="text-sm">Téléphone parent 3</Label>
          <Input class="bg-white transition-all h-10 rounded-md" v-model="phone_number_3" />
        </InputWrapper>
        <InputWrapper v-if="parents_id_3">
          <Label class="text-sm">Email parent 3</Label>
          <Input class="bg-white transition-all h-10 rounded-md" v-model="email_3" />
        </InputWrapper>
      </FormSection>

      <div class="w-full flex h-px bg-gray-300"></div>

      <FormSection
        class="grid sm:grid-cols-2 lg:grid-cols-3 gap-y-6 gap-x-10"
        title="Informations utiles"
      >
        <!-- Pays -->
        <InputWrapper>
          <Label class="text-sm font-medium">
            Pays
            <SpanRequired />
          </Label>
          <Select v-model="country_id">
            <SelectTrigger class="!h-10 bg-white w-full">
              <SelectValue placeholder="Sélectionnez un pays" />
            </SelectTrigger>
            <SelectContent>
              <SelectGroup>
                <SelectItem
                  v-for="country in extractArray(countriesData)"
                  :key="country.id"
                  :value="String(country.id)"
                >
                  {{ country.name }}
                </SelectItem>
              </SelectGroup>
            </SelectContent>
          </Select>
          <span v-if="countryIdError" class="text-xs text-red-500">{{ countryIdError }}</span>
        </InputWrapper>

        <!-- Province (dépend du pays) -->
        <InputWrapper>
          <Label class="text-sm font-medium">
            Province
            <SpanRequired />
          </Label>
          <Select v-model="province_id" :disabled="!country_id">
            <SelectTrigger class="!h-10 bg-white w-full">
              <SelectValue
                :placeholder="
                  country_id ? 'Sélectionnez une province' : 'Sélectionnez d\'abord un pays'
                "
              />
            </SelectTrigger>
            <SelectContent>
              <SelectGroup>
                <SelectItem
                  v-for="province in filteredProvinces"
                  :key="province.id"
                  :value="String(province.id)"
                >
                  {{ province.name }}
                </SelectItem>
              </SelectGroup>
            </SelectContent>
          </Select>
          <span v-if="provinceIdError" class="text-xs text-red-500">{{ provinceIdError }}</span>
        </InputWrapper>

        <!-- Territoire (dépend de la province) -->
        <InputWrapper>
          <Label class="text-sm font-medium">
            Ville/Territoire
            <SpanRequired />
          </Label>
          <Select v-model="territory_id" :disabled="!province_id">
            <SelectTrigger class="!h-10 bg-white w-full">
              <SelectValue
                :placeholder="
                  province_id ? 'Sélectionnez un territoire' : 'Sélectionnez d\'abord une province'
                "
              />
            </SelectTrigger>
            <SelectContent>
              <SelectGroup>
                <SelectItem
                  v-for="territory in filteredTerritories"
                  :key="territory.id"
                  :value="String(territory.id)"
                >
                  {{ territory.name }}
                </SelectItem>
              </SelectGroup>
            </SelectContent>
          </Select>
          <span v-if="territoryIdError" class="text-xs text-red-500">{{ territoryIdError }}</span>
        </InputWrapper>

        <!-- Commune (dépend de la province) -->
        <InputWrapper>
          <Label class="text-sm font-medium">
            Commune
            <SpanRequired />
          </Label>
          <Select v-model="commune_id" :disabled="!province_id">
            <SelectTrigger class="!h-10 bg-white w-full">
              <SelectValue
                :placeholder="
                  province_id ? 'Sélectionnez une commune' : 'Sélectionnez d\'abord une province'
                "
              />
            </SelectTrigger>
            <SelectContent>
              <SelectGroup>
                <SelectItem
                  v-for="commune in filteredCommunes"
                  :key="commune.id"
                  :value="String(commune.id)"
                >
                  {{ commune.name }}
                </SelectItem>
              </SelectGroup>
            </SelectContent>
          </Select>
          <span v-if="communeIdError" class="text-xs text-red-500">{{ communeIdError }}</span>
        </InputWrapper>
        <InputWrapper>
          <Label class="text-sm">
            Adresse
            <SpanRequired />
          </Label>
          <Input class="bg-white transition-all h-10 rounded-md" v-model="address" />
          <span v-if="addressError" class="text-xs text-red-500">{{ addressError }}</span>
        </InputWrapper>

        <!-- Filière (Section) - 1er choix -->
        <InputWrapper>
          <Label class="text-sm font-medium">
            Section
            <SpanRequired />
          </Label>
          <Select v-model="filiaire_id">
            <SelectTrigger class="!h-10 bg-white w-full">
              <SelectValue placeholder="Sélectionner une section" />
            </SelectTrigger>
            <SelectContent>
              <SelectGroup>
                <SelectItem
                  v-for="filiere in filteredFilieres"
                  :key="filiere.id"
                  :value="String(filiere.id)"
                >
                  {{ filiere.name }}
                </SelectItem>
              </SelectGroup>
            </SelectContent>
          </Select>
          <span v-if="filiaireIdError" class="text-xs text-red-500">{{ filiaireIdError }}</span>
        </InputWrapper>

        <!-- Cycle (2e choix) -->
        <InputWrapper>
          <Label class="text-sm font-medium">
            Cycle
            <SpanRequired />
          </Label>
          <Select v-model="cycle_id" :disabled="!filiaire_id">
            <SelectTrigger class="!h-10 bg-white w-full">
              <SelectValue
                :placeholder="
                  filiaire_id ? 'Sélectionner un cycle' : 'Sélectionnez d\'abord une section'
                "
              />
            </SelectTrigger>
            <SelectContent>
              <SelectGroup>
                <SelectItem
                  v-for="cycle in filteredCycles"
                  :key="cycle.id"
                  :value="String(cycle.id)"
                >
                  {{ cycle.name }}
                </SelectItem>
              </SelectGroup>
            </SelectContent>
          </Select>
          <span v-if="cycleIdError" class="text-xs text-red-500">{{ cycleIdError }}</span>
        </InputWrapper>

        <!-- Niveau scolaire (3e choix - dépend du cycle) -->
        <InputWrapper>
          <Label class="text-sm font-medium">
            Niveau scolaire
            <SpanRequired />
          </Label>
          <Select v-model="academic_level_id" :disabled="!filiaire_id">
            <SelectTrigger class="!h-10 bg-white w-full">
              <SelectValue
                :placeholder="
                  cycle_id ? 'Sélectionner un niveau' : 'Sélectionnez un niveau (Cycle auto)'
                "
              />
            </SelectTrigger>
            <SelectContent>
              <SelectGroup>
                <SelectItem v-for="level in levelOptions" :key="level.id" :value="String(level.id)">
                  {{ level.label }}
                </SelectItem>
              </SelectGroup>
            </SelectContent>
          </Select>
          <span v-if="academicLevelIdError" class="text-xs text-red-500">{{
            academicLevelIdError
          }}</span>
        </InputWrapper>

        <!-- Classe (4e choix - dépend de la section) -->
        <InputWrapper>
          <Label class="text-sm font-medium">
            Classe
            <SpanRequired />
          </Label>
          <Select v-model="classroom_id" :disabled="!academic_level_id">
            <SelectTrigger class="!h-10 bg-white w-full">
              <SelectValue
                :placeholder="
                  academic_level_id ? 'Sélectionner une classe' : 'Sélectionnez d\'abord un niveau'
                "
              />
            </SelectTrigger>
            <SelectContent>
              <SelectGroup>
                <SelectItem
                  v-for="classroom in filteredClassrooms"
                  :key="classroom.id"
                  :value="String(classroom.id)"
                >
                  {{ classroom.name }}
                </SelectItem>
              </SelectGroup>
            </SelectContent>
          </Select>
          <span v-if="classroomIdError" class="text-xs text-red-500">{{ classroomIdError }}</span>
        </InputWrapper>

        <!-- Note/commentaire -->
        <InputWrapper>
          <Label>Note</Label>
          <Textarea v-model="note" placeholder="Commentaire optionnel" />
        </InputWrapper>
      </FormSection>

      <div class="w-full flex h-px bg-gray-300"></div>

      <FormSection class="grid sm:grid-cols-2 lg:grid-cols-3 gap-y-6 gap-x-10" title="Inscription">
        <InputWrapper>
          <Label class="text-sm">École de provenance</Label>
          <Input
            class="bg-white transition-all h-10 rounded-md"
            placeholder="Nom de l'école précédente"
            v-model="previousSchool"
          />
          <span v-if="previousSchoolError" class="text-xs text-red-500">{{
            previousSchoolError
          }}</span>
        </InputWrapper>

        <InputWrapper>
          <Label class="text-sm">Pourcentage obtenu</Label>
          <Input
            class="bg-white transition-all h-10 rounded-md"
            type="number"
            min="0"
            max="100"
            step="0.01"
            placeholder="Ex: 75%"
            v-model="percentageObtained"
          />
          <span v-if="percentageObtainedError" class="text-xs text-red-500">{{
            percentageObtainedError
          }}</span>
        </InputWrapper>
      </FormSection>

      <div class="w-full flex h-px bg-gray-300"></div>

      <div class="flex items-center justify-end gap-2">
        <Button variant="outline" type="button" @click="router.push('/apprenants/operations')">
          <span class="flex iconify hugeicons--cancel-01 mr-1.5"></span>
          Annuler
        </Button>
        <Button type="submit">
          <span v-if="!loading" class="flex items-center gap-2">
            <span class="iconify hugeicons--floppy-disk mr-1"></span>
            <span>Enregistrer</span>
          </span>
          <span v-else class="flex items-center gap-2">
            <span class="iconify animate-spin hugeicons--loading-03 text-lg"></span>
            <span>Enregistrement en cours...</span>
          </span>
        </Button>
      </div>
    </form>
  </DashFormLayout>
</template>
