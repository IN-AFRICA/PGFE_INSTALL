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
import { usePostApi } from '@/composables/usePostApi.ts'
import { useGetApi } from '@/composables/useGetApi.ts'
import { usePutApi } from '@/composables/usePutApi.ts'
import { API_ROUTES } from '@/utils/constants/api_route.ts'
import { eventBus } from '@/utils/eventBus.ts'
import { showCustomToast } from '@/utils/widgets/custom_toast.ts'
import ListFather from '@/utils/widgets/vues/ListFather.vue'
import ListMother from '@/utils/widgets/vues/ListMother.vue'
import ListFonctions from '@/utils/widgets/vues/ListFonctions.vue'
import ListType from '@/utils/widgets/vues/ListType.vue'
import ListAcademicalLevel from '@/utils/widgets/vues/ListAcademicalLevel.vue'
import ListSchool from '@/utils/widgets/vues/ListSchool.vue'
import { toTypedSchema } from '@vee-validate/zod'
import { useField, useForm } from 'vee-validate'
import { useRoute, useRouter } from 'vue-router'
import z from 'zod'
import { onBeforeUnmount, onMounted, ref, computed, watch } from 'vue'
import { useAuthStore } from '@/stores/auth'
import api from '@/services/api'
import type { GetAcademicPersonalResponse, AcademicPersonalApi } from '@/models/academic_personal'
import ListCountrie from '@/utils/widgets/vues/ListCountrie.vue'
import ListProvince from '@/utils/widgets/vues/ListProvince.vue'
import ListTerritory from '@/utils/widgets/vues/ListTerritory.vue'
import ListCommune from '@/utils/widgets/vues/ListCommune.vue'

const router = useRouter()
const route = useRoute()
const loadingData = ref(false)

type Gender = 'Masculin' | 'Féminin' | 'Non spécifié'
type CivilStatus = 'Célibataire' | 'Marié(e)' | 'Divorcé(e)' | 'Veuf/Veuve'

const academicPersonalId = computed<number | null>(() => {
  const raw = route.query?.id
  if (raw === undefined || raw === null || raw === '') return null
  const parsed = Number(raw)
  return Number.isFinite(parsed) ? parsed : null
})

const isEditMode = computed(() => academicPersonalId.value !== null)

const pageTitle = computed(() =>
  isEditMode.value ? 'Modifier un personnel' : 'Ajouter un nouveau personnel',
)
const breadcrumb = computed(() => [
  { label: 'Personnel', href: '/rh/saisie/personnel' },
  isEditMode.value
    ? {
        label: 'Modifier Personnel',
        href: `/rh/saisie/personnel/${academicPersonalId.value}/modifier`,
      }
    : { label: 'Nouveau Personnel', href: '/rh/saisie/personnel/nouveau' },
])

//Create form schema - Adapté pour correspondre à la structure backend
const schemaForm = z.object({
  // Champs requis par le backend
  country_id: z.coerce.number({ required_error: 'Veuillez sélectionner le pays' }),
  province_id: z.coerce.number({ required_error: 'Veuillez sélectionner la province' }),
  territory_id: z.coerce.number({ required_error: 'Veuillez sélectionner le territoire' }),
  commune_id: z.coerce.number({ required_error: 'Veuillez sélectionner la commune' }),
  school_id: z
    .string({ required_error: "Veuillez sélectionner l'école" })
    .min(1, "Veuillez sélectionner l'école"),
  type_id: z
    .string({ required_error: 'Veuillez sélectionner le type' })
    .min(1, 'Veuillez sélectionner le type'),
  father_id: z
    .string({ required_error: 'Veuillez sélectionner le père' })
    .min(1, 'Veuillez sélectionner le père'),
  mother_id: z
    .string({ required_error: 'Veuillez sélectionner la mère' })
    .min(1, 'Veuillez sélectionner la mère'),
  academic_level_id: z
    .string({ required_error: 'Veuillez sélectionner le niveau scolaire' })
    .min(1, 'Veuillez sélectionner le niveau scolaire'),
  fonction_id: z
    .string({ required_error: 'Veuillez sélectionner la fonction' })
    .min(1, 'Veuillez sélectionner la fonction'),
  matricule: z
    .string({ required_error: 'Veuillez saisir le matricule' })
    .min(2, 'Le matricule doit contenir au moins 2 caractères')
    .max(100, 'Le matricule doit contenir au maximum 100 caractères'),
  name: z
    .string({ required_error: 'Veuillez saisir le nom' })
    .min(2, 'Le nom doit contenir au moins 2 caractères')
    .max(100, 'Le nom doit contenir au maximum 100 caractères'),
  firstname: z
    .string({ required_error: 'Veuillez saisir le prénom' })
    .min(2, 'Le prénom doit contenir au moins 2 caractères')
    .max(100, 'Le prénom doit contenir au maximum 100 caractères'),
  username: z
    .string({ required_error: "Veuillez saisir le nom d'utilisateur" })
    .min(2, "Le nom d'utilisateur doit contenir au moins 2 caractères")
    .max(100, "Le nom d'utilisateur doit contenir au maximum 100 caractères"),
  phone_number: z
    .string({ required_error: 'Veuillez saisir le numéro de téléphone' })
    .min(2, 'Le numéro de téléphone doit contenir au moins 2 caractères')
    .max(20, 'Le numéro de téléphone doit contenir au maximum 20 caractères'),
  email: z.string({ required_error: "Veuillez saisir l'email" }).email('Email invalide'),
  identity_card: z
    .string({ required_error: "Veuillez saisir le numéro de carte d'identité" })
    .min(2, "Le numéro de carte d'identité doit contenir au moins 2 caractères")
    .max(100, "Le numéro de carte d'identité doit contenir au maximum 100 caractères"),
  gender: z.enum(['Masculin', 'Féminin', 'Non spécifié'], {
    required_error: 'Veuillez sélectionner le genre',
  }),
  civil_status: z.enum(['Célibataire', 'Marié(e)', 'Divorcé(e)', 'Veuf/Veuve'], {
    required_error: "Veuillez sélectionner l'état civil",
  }),
  birth_date: z.string({ required_error: 'Veuillez saisir la date de naissance' }),
  birth_place: z
    .string({ required_error: 'Veuillez saisir le lieu de naissance' })
    .min(2, 'Le lieu de naissance doit contenir au moins 2 caractères')
    .max(100, 'Le lieu de naissance doit contenir au maximum 100 caractères'),
  address: z
    .string({ required_error: "Veuillez saisir l'adresse" })
    .min(2, "L'adresse doit contenir au moins 2 caractères")
    .max(255, "L'adresse doit contenir au maximum 255 caractères"),
  // Champ optionnel
  image: z.string().optional(),
})

//Validation
const { handleSubmit, resetForm } = useForm({
  validationSchema: toTypedSchema(schemaForm),
})

const { value: matricule, errorMessage: matriculeError } = useField<string>('matricule')
const { value: name, errorMessage: nameError } = useField<string>('name')
const { value: firstname, errorMessage: firstnameError } = useField<string>('firstname')
const { value: username, errorMessage: usernameError } = useField<string>('username')
const { value: gender, errorMessage: genderError } = useField<Gender>('gender')
const { value: civil_status, errorMessage: civilStatusError } =
  useField<CivilStatus>('civil_status')
const { value: address, errorMessage: addressError } = useField<string>('address')
const { value: birth_date, errorMessage: birthDateError } = useField<string>('birth_date')
const { value: birth_place, errorMessage: birthPlaceError } = useField<string>('birth_place')
const { value: identity_card, errorMessage: identityCardError } = useField<string>('identity_card')
const { value: phone_number, errorMessage: phoneNumberError } = useField<string>('phone_number')
const { value: email, errorMessage: emailError } = useField<string>('email')
const { value: country_id, errorMessage: countryIdError } = useField<number>('country_id')
const { value: province_id, errorMessage: provinceIdError } = useField<number>('province_id')
const { value: territory_id, errorMessage: territoryIdError } = useField<number>('territory_id')
const { value: commune_id, errorMessage: communeIdError } = useField<number>('commune_id')
const { value: school_id, errorMessage: schoolIdError } = useField<string>('school_id')
const { value: type_id, errorMessage: typeIdError } = useField<string>('type_id')
const { value: father_id, errorMessage: fatherIdError } = useField<string>('father_id')
const { value: mother_id, errorMessage: motherIdError } = useField<string>('mother_id')
const { value: academic_level_id, errorMessage: academicLevelIdError } =
  useField<string>('academic_level_id')
const { value: fonction_id, errorMessage: fonctionIdError } = useField<string>('fonction_id')
const { value: image } = useField<string>('image')

const { loading, error, response, postData, success } = usePostApi()
const {
  loading: putLoading,
  error: putError,
  response: putResponse,
  success: putSuccess,
  putData,
} = usePutApi()

const submitting = computed(() => (isEditMode.value ? putLoading.value : loading.value))

// Récupérer l'utilisateur connecté
const authStore = useAuthStore()
const currentUserId = computed(() => authStore.user?.id || null)

function coerceId(value: unknown): number {
  const num = Number(value)
  return Number.isFinite(num) ? num : 0
}

function mapApiToFormValues(personal: AcademicPersonalApi) {
  const allowedGenders: Gender[] = ['Masculin', 'Féminin', 'Non spécifié']
  const allowedCivilStatuses: CivilStatus[] = [
    'Célibataire',
    'Marié(e)',
    'Divorcé(e)',
    'Veuf/Veuve',
  ]

  const resolvedGender: Gender = allowedGenders.includes(personal.gender as Gender)
    ? (personal.gender as Gender)
    : 'Non spécifié'

  const resolvedCivilStatus: CivilStatus = allowedCivilStatuses.includes(
    personal.civil_status as CivilStatus,
  )
    ? (personal.civil_status as CivilStatus)
    : 'Célibataire'

  return {
    country_id: coerceId(personal.country_id),
    province_id: coerceId(personal.province_id),
    territory_id: coerceId(personal.territory_id),
    commune_id: coerceId(personal.commune_id),

    school_id: String(personal.school_id ?? ''),
    type_id: String(personal.type_id ?? ''),
    father_id: String(personal.father_id ?? ''),
    mother_id: String(personal.mother_id ?? ''),
    academic_level_id: String(personal.academic_level_id ?? ''),
    fonction_id: String(personal.fonction_id ?? ''),

    matricule: personal.matricule ?? '',
    name: personal.name ?? '',
    firstname: (personal.firstname ?? personal.pre_name ?? '') as string,
    username: personal.username ?? '',
    phone_number: (personal.phone_number ?? personal.phone ?? '') as string,
    email: personal.email ?? '',
    identity_card: (personal.identity_card ?? personal.identity_card_number ?? '') as string,
    gender: resolvedGender,
    civil_status: resolvedCivilStatus,
    birth_date: personal.birth_date ?? '',
    birth_place: personal.birth_place ?? '',
    address: (personal.address ?? personal.physical_address ?? '') as string,
    image: personal.image ?? '',
  }
}

async function fetchAcademicPersonalForEdit() {
  if (!isEditMode.value || !academicPersonalId.value) return
  loadingData.value = true
  try {
    const res = await api.get<GetAcademicPersonalResponse>(
      API_ROUTES.GET_ACADEMIC_PERSONAL(academicPersonalId.value),
    )
    const personal = res.data?.data
    if (!personal) {
      showCustomToast({ message: 'Données du personnel introuvables', type: 'error' })
      return
    }
    const values = mapApiToFormValues(personal)
    resetForm({ values })

    if (values.image && typeof values.image === 'string' && values.image.trim() !== '') {
      previewUrl.value = values.image
    }
  } catch (e: unknown) {
    const err = e as { response?: { data?: { message?: string } }; message?: string }
    showCustomToast({
      message: err?.response?.data?.message || err?.message || 'Erreur lors du chargement',
      type: 'error',
    })
  } finally {
    loadingData.value = false
  }
}

onMounted(async () => {
  await fetchAcademicPersonalForEdit()
})

// Reset des champs géographiques en cascade
watch(country_id, (newVal, oldVal) => {
  if (oldVal !== undefined && newVal !== oldVal) {
    province_id.value = 0
    territory_id.value = 0
    commune_id.value = 0
  }
})

watch(province_id, (newVal, oldVal) => {
  if (oldVal !== undefined && newVal !== oldVal) {
    territory_id.value = 0
    commune_id.value = 0
  }
})

const onSubmit = handleSubmit(async (values) => {
  // Vérifier que l'ID utilisateur existe
  if (!currentUserId.value) {
    showCustomToast({
      message: "Impossible de récupérer l'utilisateur connecté",
      type: 'error',
    })
    return
  }

  // Créer FormData pour multipart/form-data
  const formData = new FormData()

  // Champs requis
  formData.append('country_id', String(values.country_id))
  formData.append('province_id', String(values.province_id))
  formData.append('territory_id', String(values.territory_id))
  formData.append('commune_id', String(values.commune_id))
  formData.append('type_id', String(values.type_id))
  formData.append('academic_level_id', String(values.academic_level_id))
  formData.append('fonction_id', String(values.fonction_id))
  formData.append('matricule', values.matricule)
  formData.append('name', values.name)
  formData.append('firstname', values.firstname)
  formData.append('phone_number', values.phone_number)
  formData.append('email', values.email)
  formData.append('identity_card', values.identity_card)
  formData.append('gender', values.gender)
  formData.append('civil_status', values.civil_status)
  formData.append('birth_date', values.birth_date)
  formData.append('address', values.address)

  // Champs optionnels - ajouter seulement s'ils ont une valeur
  if (values.school_id && values.school_id !== '0') {
    formData.append('school_id', values.school_id)
  }

  if (values.father_id && values.father_id !== '0') {
    formData.append('father_id', values.father_id)
  }

  if (values.mother_id && values.mother_id !== '0') {
    formData.append('mother_id', values.mother_id)
  }

  if (values.username && values.username.trim() !== '') {
    formData.append('username', values.username)
  }

  if (values.birth_place && values.birth_place.trim() !== '') {
    formData.append('birth_place', values.birth_place)
  }

  // Image (si présente)
  if (image.value && image.value.trim() !== '') {
    formData.append('image', image.value)
  }

  if (isEditMode.value && academicPersonalId.value) {
    await putData(API_ROUTES.PUT_ACADEMIC_PERSONAL(academicPersonalId.value), formData)

    if (putError.value) {
      showCustomToast({
        message: putError.value || 'Erreur lors de la modification du personnel',
        type: 'error',
      })
      return
    }

    if (putSuccess.value) {
      showCustomToast({
        message:
          typeof putResponse.value === 'object' &&
          putResponse.value &&
          'message' in (putResponse.value as object)
            ? String((putResponse.value as { message?: unknown }).message ?? '') ||
              'Personnel modifié avec succès'
            : 'Personnel modifié avec succès',
        type: 'success',
      })
      eventBus.emit('personalUpdated')
      router.push('/rh/saisie/personnel')
    }

    return
  }

  await postData(API_ROUTES.CREATE_ACADEMIC_PERSONAL, formData)

  if (error.value) {
    showCustomToast({
      message: error.value || 'Erreur lors de la création du personnel',
      type: 'error',
    })
    return
  }

  if (success.value) {
    showCustomToast({
      message: response.value?.message || 'Personnel créé avec succès',
      type: 'success',
    })
    eventBus.emit('personalUpdated')
    resetForm()
    router.push('/rh/saisie/personnel')
  }
})

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
</script>
<template>
  <DashFormLayout
    :title="pageTitle"
    link-back="/rh/saisie/personnel"
    group-route="/rh/personnel"
    module="rh"
    :breadcrumb="breadcrumb"
  >
    <div v-if="isEditMode && loadingData" class="mt-4 p-8 text-center bg-white rounded-md">
      <div class="flex items-center justify-center gap-2">
        <span class="iconify hugeicons--loading-03 animate-spin text-2xl"></span>
        <span>Chargement des données...</span>
      </div>
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
                alt="Photo personnel"
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

        <!-- Champs à droite -->
        <InputWrapper>
          <Label class="text-sm">
            Matricule
            <SpanRequired />
          </Label>
          <Input
            class="bg-white transition-all h-10 rounded-md"
            placeholder="Matricule"
            v-model="matricule"
          />
          <span v-if="matriculeError" class="text-xs text-red-500">{{ matriculeError }}</span>
        </InputWrapper>

        <InputWrapper>
          <Label class="text-sm">
            Nom
            <SpanRequired />
          </Label>
          <Input class="bg-white transition-all h-10 rounded-md" placeholder="Nom" v-model="name" />
          <span v-if="nameError" class="text-xs text-red-500">{{ nameError }}</span>
        </InputWrapper>

        <InputWrapper>
          <Label class="text-sm">
            Prénom
            <SpanRequired />
          </Label>
          <Input
            class="bg-white transition-all h-10 rounded-md"
            placeholder="Prénom"
            v-model="firstname"
          />
          <span v-if="firstnameError" class="text-xs text-red-500">{{ firstnameError }}</span>
        </InputWrapper>

        <InputWrapper>
          <Label class="text-sm">
            Nom d'utilisateur
            <SpanRequired />
          </Label>
          <Input
            class="bg-white transition-all h-10 rounded-md"
            placeholder="Nom d'utilisateur"
            v-model="username"
          />
          <span v-if="usernameError" class="text-xs text-red-500">{{ usernameError }}</span>
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
                <SelectItem value="Non spécifié">Non spécifié</SelectItem>
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
          <Label class="text-sm">
            Lieu de naissance
            <SpanRequired />
          </Label>
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
                <SelectItem value="Veuf/Veuve">Veuf(ve)</SelectItem>
              </SelectGroup>
            </SelectContent>
          </Select>
          <span v-if="civilStatusError" class="text-xs text-red-500">{{ civilStatusError }}</span>
        </InputWrapper>

        <InputWrapper>
          <Label class="text-sm">
            Numéro Carte d'identité
            <SpanRequired />
          </Label>
          <Input class="bg-white transition-all h-10 rounded-md" v-model="identity_card" />
          <span v-if="identityCardError" class="text-xs text-red-500">{{ identityCardError }}</span>
        </InputWrapper>
      </FormSection>

      <div class="w-full flex h-px bg-gray-300"></div>

      <FormSection
        class="grid sm:grid-cols-2 lg:grid-cols-3 gap-y-6 gap-x-10"
        title="Informations de contact"
      >
        <InputWrapper>
          <Label class="text-sm">
            Téléphone
            <SpanRequired />
          </Label>
          <Input class="bg-white transition-all h-10 rounded-md" v-model="phone_number" />
          <span v-if="phoneNumberError" class="text-xs text-red-500">{{ phoneNumberError }}</span>
        </InputWrapper>
        <InputWrapper>
          <Label class="text-sm">
            Adresse email
            <SpanRequired />
          </Label>
          <Input class="bg-white transition-all h-10 rounded-md" v-model="email" />
          <span v-if="emailError" class="text-xs text-red-500">{{ emailError }}</span>
        </InputWrapper>
        <InputWrapper>
          <Label class="text-sm">
            Adresse physique
            <SpanRequired />
          </Label>
          <Input class="bg-white transition-all h-10 rounded-md" v-model="address" />
          <span v-if="addressError" class="text-xs text-red-500">{{ addressError }}</span>
        </InputWrapper>
      </FormSection>

      <div class="w-full flex h-px bg-gray-300"></div>

      <FormSection
        class="grid sm:grid-cols-2 lg:grid-cols-3 gap-y-6 gap-x-10"
        title="Informations parents"
      >
        <InputWrapper :error="fatherIdError">
          <ListFather v-model="father_id" />
          <span v-if="fatherIdError" class="text-xs text-red-500">{{ fatherIdError }}</span>
        </InputWrapper>

        <InputWrapper :error="motherIdError">
          <ListMother v-model="mother_id" />
          <span v-if="motherIdError" class="text-xs text-red-500">{{ motherIdError }}</span>
        </InputWrapper>
      </FormSection>

      <div class="w-full flex h-px bg-gray-300"></div>

      <FormSection
        class="grid sm:grid-cols-2 lg:grid-cols-3 gap-y-6 gap-x-10"
        title="Informations professionnelles"
      >
        <InputWrapper>
          <ListSchool v-model="school_id" />
          <span v-if="schoolIdError" class="text-xs text-red-500">{{ schoolIdError }}</span>
        </InputWrapper>

        <InputWrapper>
          <ListType v-model="type_id" />
          <span v-if="typeIdError" class="text-xs text-red-500">{{ typeIdError }}</span>
        </InputWrapper>

        <InputWrapper>
          <ListFonctions v-model="fonction_id" />
          <span v-if="fonctionIdError" class="text-xs text-red-500">{{ fonctionIdError }}</span>
        </InputWrapper>

        <InputWrapper>
          <ListAcademicalLevel v-model="academic_level_id" />
          <span v-if="academicLevelIdError" class="text-xs text-red-500">{{
            academicLevelIdError
          }}</span>
        </InputWrapper>
      </FormSection>

      <div class="w-full flex h-px bg-gray-300"></div>

      <FormSection class="grid sm:grid-cols-2 lg:grid-cols-3 gap-y-6 gap-x-10" title="Localisation">
        <ListCountrie v-model="country_id" />
        <ListProvince v-model="province_id" :disabled="!country_id" :country_id="country_id" />
        <ListTerritory v-model="territory_id" :disabled="!province_id" :province_id="province_id" />
        <ListCommune v-model="commune_id" :disabled="!province_id" :province_id="province_id" />
      </FormSection>

      <div class="w-full flex h-px bg-gray-300"></div>
      <div class="flex items-center justify-end gap-2">
        <Button variant="outline" type="button" @click="router.push('/rh/saisie/personnel')">
          <span class="flex iconify hugeicons--cancel-01 mr-1.5"></span>
          Annuler
        </Button>
        <Button type="submit">
          <span v-if="!submitting" class="flex items-center gap-2">
            <span class="iconify hugeicons--floppy-disk mr-1"></span>
            <span>{{ isEditMode ? 'Modifier' : 'Enregistrer' }}</span>
          </span>
          <span v-else>
            <div class="flex items-center gap-2">
              <Spinner />
              <span>{{
                isEditMode ? 'Modification en cours...' : 'Enregistrement en cours...'
              }}</span>
            </div>
          </span>
        </Button>
      </div>
    </form>
  </DashFormLayout>
</template>
```
