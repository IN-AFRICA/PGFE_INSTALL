<script setup lang="ts">
import FormSection from '@/components/atoms/FormSection.vue'
import InputWrapper from '@/components/atoms/InputWrapper.vue'
import SpanRequired from '@/components/atoms/SpanRequired.vue'
import DashFormLayout from '@/components/templates/DashFormLayout.vue'
import { Button } from '@/components/ui/button'
// import CustomDatePicker from '@/components/ui/CustomDatePicker.vue';
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import Spinner from '@/components/ui/spinner/Spinner.vue'
import { usePostApi } from '@/composables/usePostApi'
import { API_ROUTES } from '@/utils/constants/api_route'
import { eventBus } from '@/utils/eventBus'
import { showCustomToast } from '@/utils/widgets/custom_toast'
import ListClassRoom from '@/utils/widgets/vues/ListClassRoom.vue'
import ListPersonnalAcademic from '@/utils/widgets/vues/ListPersonnalAcademic.vue'
import ListSchool from '@/utils/widgets/vues/ListSchool.vue'
import ListFonction from '@/utils/widgets/vues/ListFonction.vue'
// import ListClassRoom from '@/utils/widgets/vues/ListClassRoom.vue';

import { toTypedSchema } from '@vee-validate/zod'
import { useField, useForm } from 'vee-validate'
import { useRouter } from 'vue-router'
import z from 'zod'
import { onMounted } from 'vue'
import { useAuthStore } from '@/stores/auth.ts'
import { Textarea } from '@/components/ui/textarea'
import { useGetApi } from '@/composables/useGetApi'

const router = useRouter()
const authStore = useAuthStore()

const schemaForm = z.object({
  /*school_id: z.number({
    required_error: "Veuillez sélectionner l'école",
  }).min(1, "Veuillez sélectionner l'école"),*/
  subject: z
    .string({ required_error: 'Veuillez saisir le sujet' })
    .min(2, 'Le sujet doit contenir au moins 2 caractères')
    .max(100, 'Le sujet ne doit pas dépasser 100 caractères'),
  classroom_id: z
    .number({
      required_error: 'Veuillez sélectionner la classe',
      invalid_type_error: 'La classe doit être un nombre',
    })
    .min(1, 'Veuillez sélectionner la classe'),
  cot_doc_prof: z
    .number({
      required_error: 'Veuillez saisir la cote du document professeur',
      invalid_type_error: 'La cote du document professeur doit être un nombre',
    })
    .min(0, 'La cote du document professeur doit être au moins 0')
    .max(20, 'La cote du document professeur ne doit pas dépasser 20'),
  cot_meth_proc: z
    .number({
      required_error: 'Veuillez saisir la cote de la méthode/procédure',
      invalid_type_error: 'La cote de la méthode/procédure doit être un nombre',
    })
    .min(0, 'La cote de la méthode/procédure doit être au moins 0')
    .max(12, 'La cote de la méthode/procédure ne doit pas dépasser 12'),
  cot_matiere: z
    .number({
      required_error: 'Veuillez saisir la cote de la matière',
      invalid_type_error: 'La cote de la matière doit être un nombre',
    })
    .min(0, 'La cote de la matière doit être au moins 0')
    .max(12, 'La cote de la matière ne doit pas dépasser 12'),
  cot_march_lecon: z
    .number({
      required_error: 'Veuillez saisir la cote de la marche de la leçon',
      invalid_type_error: 'La cote de la marche de la leçon doit être un nombre',
    })
    .min(0, 'La cote de la marche de la leçon doit être au moins 0')
    .max(25, 'La cote de la marche de la leçon ne doit pas dépasser 25'),
  cot_enseignant: z
    .number({
      required_error: "Veuillez saisir la cote de l'enseignant",
      invalid_type_error: "La cote de l'enseignant doit être un nombre",
    })
    .min(0, "La cote de l'enseignant doit être au moins 0")
    .max(20, "La cote de l'enseignant ne doit pas dépasser 20"),
  cot_eleve: z
    .number({
      required_error: "Veuillez saisir la cote de l'élève",
      invalid_type_error: "La cote de l'élève doit être un nombre",
    })
    .min(0, "La cote de l'élève doit être au moins 0")
    .max(20, "La cote de l'élève ne doit pas dépasser 20"),
  cot_doc_eleve: z
    .number({
      invalid_type_error: "Le contrôle document de l'élève doit être un nombre",
    })
    .optional()
    .nullable(),
  visit_hour: z
    .string({
      required_error: "Veuillez saisir l'heure de début",
      invalid_type_error: "L'heure de début doit être une chaîne de caractères",
    })
    .regex(/^([0-1]\d|2[0-3]):([0-5]\d)$/, 'Veuillez saisir une heure valide au format HH:mm'),
  datefin: z
    .string()
    .regex(/^([0-1]\d|2[0-3]):([0-5]\d)$/, 'Veuillez saisir une heure valide au format HH:mm')
    .optional(),
  summary: z
    .string({ required_error: 'Veuillez saisir le Commentaire' })
    .min(2, 'Le commentaire doit contenir au moins 2 caractères')
    .max(500, 'Le commentaire ne doit pas dépasser 500 caractères'),
  visiteur: z
    .string({ required_error: 'Veuillez saisir le visiteur' })
    .min(2, 'Le nom du visiteur doit contenir au moins 2 caractères')
    .max(100, 'Le nom du visiteur ne doit pas dépasser 100 caractères'),
  fonction_id: z.number({ required_error: 'Veuillez sélectionner la fonction' }),
})

// Validation
const { handleSubmit, resetForm } = useForm({
  validationSchema: toTypedSchema(schemaForm),
})

const { value: subject, errorMessage: subjectError } = useField<string>('subject', undefined, {
  initialValue: '',
})
const { value: school_id, errorMessage: schoolIdError } = useField<number>('school_id', undefined, {
  initialValue: 0,
})
const { value: classroom_id, errorMessage: classroomIdError } = useField<number>(
  'classroom_id',
  undefined,
  { initialValue: 0 },
)

const { value: cot_doc_prof, errorMessage: cotDocProfError } = useField<number>(
  'cot_doc_prof',
  undefined,
  { initialValue: 0 },
)
const { value: cot_meth_proc, errorMessage: cotMethProcError } = useField<number>(
  'cot_meth_proc',
  undefined,
  { initialValue: 0 },
)
const { value: cot_matiere, errorMessage: cotMatiereError } = useField<number>(
  'cot_matiere',
  undefined,
  { initialValue: 0 },
)
const { value: cot_march_lecon, errorMessage: cotMarchLeconError } = useField<number>(
  'cot_march_lecon',
  undefined,
  { initialValue: 0 },
)
const { value: cot_enseignant, errorMessage: cotEnseignantError } = useField<number>(
  'cot_enseignant',
  undefined,
  { initialValue: 0 },
)
const { value: cot_eleve, errorMessage: cotEleveError } = useField<number>('cot_eleve', undefined, {
  initialValue: 0,
})
const { value: cot_doc_eleve, errorMessage: ctrlDocEleveError } = useField<number>(
  'cot_doc_eleve',
  undefined,
  { initialValue: 0 },
)
const { value: visit_hour, errorMessage: visitHourError } = useField<string>(
  'visit_hour',
  undefined,
  { initialValue: '' },
)
const { value: datefin, errorMessage: endTimeError } = useField<string>('datefin', undefined)
const { value: summary, errorMessage: summaryError } = useField<string>('summary', undefined, {
  initialValue: '',
})
const { value: visiteur, errorMessage: visiteurError } = useField<string>('visiteur', undefined, {
  initialValue: '',
})
const { value: fonction_id, errorMessage: fonctionIdError } = useField<number>(
  'fonction_id',
  undefined,
  { initialValue: undefined },
)
const { loading, error, response, postData } = usePostApi()
const {
  loading: personalLoading,
  error: personalError,
  data: personalData,
  fetchData: fetchPersonals,
} = useGetApi(API_ROUTES.GET_PERSONALS)

const onSubmit = handleSubmit(
  async (values) => {
    console.log('=== FORM SUBMISSION ===')
    console.log('All fields:', JSON.stringify(values, null, 2))

    // Convert visit_hour from "HH:mm" to ISO datetime
    // Helper function to convert time string to ISO datetime
    const convertTimeToISO = (timeString: string): string => {
      const today = new Date()
      const [hours, minutes] = timeString.split(':')
      today.setHours(parseInt(hours), parseInt(minutes), 0, 0)
      return today.toISOString()
    }

    if (values.visit_hour) {
      values.visit_hour = convertTimeToISO(values.visit_hour)
      console.log('visit_hour converted to:', values.visit_hour)
    }

    if (values.datefin) {
      values.datefin = convertTimeToISO(values.datefin)
      console.log('datefin converted to:', values.datefin)
    }

    await postData(API_ROUTES.CREATE_VISITE_CLASSE, values)
    eventBus.emit('studentUpdated')
    if (error.value) {
      console.error('=== BACKEND ERROR ===')
      console.error('Error details:', JSON.stringify(error.value, null, 2))

      showCustomToast({
        message: error.value,
        type: 'error',
      })
      return
    } else if (response.value) {
      showCustomToast({
        message: 'Visite de classe créée avec succès',
        type: 'success',
      })
      resetForm()
      router.push('/apprenants/operations/visite-classe')
    }
  },
  (errors) => {
    console.log('❌ Form validation errors:', errors)
    showCustomToast({
      message: 'Veuillez corriger les erreurs dans le formulaire',
      type: 'error',
    })
  },
)

onMounted(() => async () => {
  if (authStore.user?.school_id) {
    school_id.value = Number(authStore.user.school_id)
  }
  await fetchPersonals({ school_id: school_id.value })
})
</script>
<template>
  <DashFormLayout
    title="Ajouter une visite de classe"
    link-back="/apprenants/operations/visite-classe"
    module="students"
    group-route="/apprenants/operations"
    :breadcrumb="[
      { label: 'Élèves', href: '/apprenants' },
      { label: 'Opérations', href: '/apprenants/operations' },
      { label: 'Visite de Classes', href: '/apprenants/operations/visite-classe' },
      { label: 'Nouvelle Visite', href: '/apprenants/operations/visite-classe/nouvelle' },
    ]"
  >
    <form class="w-full flex flex-col space-y-8" @submit.prevent="onSubmit">
      <FormSection
        class="grid sm:grid-cols-2 lg:grid-cols-3 gap-y-6 gap-x-10"
        title="Informations de la visite de classe"
      >
        <!-- Classe: classroom_id -->
        <InputWrapper>
          <ListClassRoom v-model="classroom_id" />
          <span v-if="classroomIdError" class="text-xs text-red-500">{{ classroomIdError }}</span>
        </InputWrapper>
        <InputWrapper>
          <Label class="text-sm">
            Sujet de la leçon
            <SpanRequired />
          </Label>
          <Input
            class="bg-white transition-all h-10 rounded-md"
            placeholder="Isaac"
            v-model="subject"
          />
          <span v-if="subjectError" class="text-xs text-red-500">{{ subjectError }}</span>
        </InputWrapper>
        <!-- Cotation du document professeur: cot_doc_prof -->
        <InputWrapper>
          <Label class="text-sm">
            Cote du document professeur
            <SpanRequired />
          </Label>
          <Input
            type="number"
            class="bg-white transition-all h-10 rounded-md"
            v-model="cot_doc_prof"
          />
          <span v-if="cotDocProfError" class="text-xs text-red-500">{{ cotDocProfError }}</span>
        </InputWrapper>
        <!-- Cotation de la méthode/procédure: cot_meth_proc -->
        <InputWrapper>
          <Label class="text-sm">
            Cote de la méthode/procédure
            <SpanRequired />
          </Label>
          <Input
            type="number"
            class="bg-white transition-all h-10 rounded-md"
            v-model="cot_meth_proc"
          />
          <span v-if="cotMethProcError" class="text-xs text-red-500">{{ cotMethProcError }}</span>
        </InputWrapper>
        <!-- Cotation de la matière: cot_matiere -->
        <InputWrapper>
          <Label class="text-sm">
            Cote de la matière
            <SpanRequired />
          </Label>
          <Input
            type="number"
            class="bg-white transition-all h-10 rounded-md"
            v-model="cot_matiere"
          />
          <span v-if="cotMatiereError" class="text-xs text-red-500">{{ cotMatiereError }}</span>
        </InputWrapper>
        <!-- Cotation de la marche de la leçon: cot_march_lecon -->
        <InputWrapper>
          <Label class="text-sm">
            Cote de la marche de la leçon
            <SpanRequired />
          </Label>
          <Input
            type="number"
            class="bg-white transition-all h-10 rounded-md"
            v-model="cot_march_lecon"
          />
          <span v-if="cotMarchLeconError" class="text-xs text-red-500">{{
            cotMarchLeconError
          }}</span>
        </InputWrapper>
        <!-- Cotation de l'enseignant: cot_enseignant -->
        <InputWrapper>
          <Label class="text-sm">
            Cote de l'enseignant
            <SpanRequired />
          </Label>
          <Input
            type="number"
            class="bg-white transition-all h-10 rounded-md"
            v-model="cot_enseignant"
          />
          <span v-if="cotEnseignantError" class="text-xs text-red-500">{{
            cotEnseignantError
          }}</span>
        </InputWrapper>
        <!-- Cotation de l'élève: cot_eleve -->
        <InputWrapper>
          <Label class="text-sm">
            Participation des élèves
            <SpanRequired />
          </Label>
          <Input
            type="number"
            class="bg-white transition-all h-10 rounded-md"
            v-model="cot_eleve"
          />
          <span v-if="cotEleveError" class="text-xs text-red-500">{{ cotEleveError }}</span>
        </InputWrapper>
        <!-- Contrôle document de l'élève: cot_doc_eleve -->
        <InputWrapper>
          <Label class="text-sm"> Contrôle documents des élèves </Label>
          <Input
            type="number"
            class="bg-white transition-all h-10 rounded-md"
            v-model="cot_doc_eleve"
          />
          <span v-if="ctrlDocEleveError" class="text-xs text-red-500">{{ ctrlDocEleveError }}</span>
        </InputWrapper>
        <!-- Heure de début: visit_hour -->
        <InputWrapper>
          <Label class="text-sm">
            Heure de début
            <SpanRequired />
          </Label>
          <Input type="time" class="bg-white transition-all h-10 rounded-md" v-model="visit_hour" />
          <span v-if="visitHourError" class="text-xs text-red-500">{{ visitHourError }}</span>
        </InputWrapper>
        <!-- Heure de fin: datefin -->
        <InputWrapper>
          <Label class="text-sm"> Heure de fin </Label>
          <Input type="time" class="bg-white transition-all h-10 rounded-md" v-model="datefin" />
          <span v-if="endTimeError" class="text-xs text-red-500">{{ endTimeError }}</span>
        </InputWrapper>

        <!-- Visiteur -->
        <InputWrapper>
          <Label class="text-sm">
            Visiteur
            <SpanRequired />
          </Label>
          <Input
            class="bg-white transition-all h-10 rounded-md"
            placeholder="Nom du visiteur"
            v-model="visiteur"
          />
          <span v-if="visiteurError" class="text-xs text-red-500">{{ visiteurError }}</span>
        </InputWrapper>

        <!-- Fonction -->
        <InputWrapper>
          <ListFonction v-model="fonction_id" />
          <span v-if="fonctionIdError" class="text-xs text-red-500">{{ fonctionIdError }}</span>
        </InputWrapper>

        <!-- Personnel académique automatiquement rempli avec l'utilisateur connecté -->

        <!-- Commentaire: summary -->
        <InputWrapper class="sm:col-span-2 lg:col-span-3">
          <Label class="text-sm">
            Commentaire
            <SpanRequired />
          </Label>
          <Textarea
            class="bg-white transition-all h-10 rounded-md"
            placeholder="Commentaire"
            v-model="summary"
          />
          <span v-if="summaryError" class="text-xs text-red-500">{{ summaryError }}</span>
        </InputWrapper>
      </FormSection>
      <div class="w-full flex h-px bg-gray-300"></div>
      <div class="flex items-center justify-end gap-2">
        <Button
          variant="outline"
          type="button"
          @click="router.push('/apprenants/operations/visite-classe')"
        >
          <span class="flex iconify hugeicons--cancel-01 mr-1.5"></span>
          Annuler
        </Button>
        <Button type="submit">
          <span v-if="!loading" class="flex items-center gap-2">
            <span class="iconify hugeicons--floppy-disk mr-1"></span>
            <span>Enregistrer</span>
          </span>
          <span v-else>
            <div class="flex items-center gap-2">
              <Spinner />
              <span>Enregistrement en cours...</span>
            </div>
          </span>
        </Button>
      </div>
    </form>
  </DashFormLayout>
</template>
