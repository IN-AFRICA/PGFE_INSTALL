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
import { usePutApi } from '@/composables/usePutApi'
import type { Visit } from '@/models/visit'
import api from '@/services/api'
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
import { onMounted, ref } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import z from 'zod'

import { useAuthStore } from '@/stores/auth'
const visit = ref<Visit | null>(null)

const router = useRouter()
const authStore = useAuthStore()

const schemaForm = z.object({
  school_id: z
    .number({ required_error: "Veuillez sélectionner l'école" })
    .min(1, "Veuillez sélectionner l'école"),
  subject: z.string({ required_error: 'Veuillez saisir le sujet' }).min(2).max(100),
  classroom_id: z
    .number({ required_error: 'Veuillez sélectionner la classe' })
    .min(1, 'Veuillez sélectionner la classe'),
  personal_id: z
    .number({ required_error: 'Veuillez sélectionner le personnel scolaire' })
    .min(1, 'Veuillez sélectionner le personnel scolaire'),
  cot_doc_prof: z
    .number({ required_error: 'Veuillez saisir la cote du document professeur' })
    .min(0),
  cot_meth_proc: z
    .number({ required_error: 'Veuillez saisir la cote de la méthode/procédure' })
    .min(0),
  cot_matiere: z.number({ required_error: 'Veuillez saisir la cote de la matière' }).min(0),
  cot_march_lecon: z
    .number({ required_error: 'Veuillez saisir la cote de la marche de la leçon' })
    .min(0),
  cot_enseignant: z.number({ required_error: "Veuillez saisir la cote de l'enseignant" }).min(0),
  cot_eleve: z.number({ required_error: "Veuillez saisir la cote de l'élève" }).min(0),
  ctrl_doc_eleve: z
    .number({ invalid_type_error: "Le contrôle document de l'élève doit être un nombre" })
    .optional()
    .nullable(),
  visit_hour: z
    .string({ required_error: "Veuillez saisir l'heure de début" })
    .regex(/^([0-1]\d|2[0-3]):([0-5]\d)$/, 'Veuillez saisir une heure valide au format HH:mm'),
  end_time: z
    .string({ required_error: "Veuillez saisir l'heure de fin" })
    .regex(/^([0-1]\d|2[0-3]):([0-5]\d)$/, 'Veuillez saisir une heure valide au format HH:mm'),
  summary: z.string({ required_error: 'Veuillez saisir le résumé' }).min(2).max(500),
  visiteur: z.string().optional(),
  fonction_id: z.number().optional(),
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
const { value: personal_id, errorMessage: academicPersonalIdError } = useField<number>(
  'personal_id',
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
const { value: ctrl_doc_eleve, errorMessage: ctrlDocEleveError } = useField<number>(
  'ctrl_doc_eleve',
  undefined,
  { initialValue: 0 },
)
const { value: visit_hour, errorMessage: visitHourError } = useField<string>(
  'visit_hour',
  undefined,
  { initialValue: '' },
)
const { value: end_time, errorMessage: endTimeError } = useField<string>('end_time', undefined, {
  initialValue: '',
})
const { value: summary, errorMessage: summaryError } = useField<string>('summary', undefined, {
  initialValue: '',
})
const { value: visiteur, errorMessage: visiteurError } = useField<string>('visiteur', undefined, {
  initialValue: '',
})
const { value: fonction_id, errorMessage: fonctionIdError } = useField<number>(
  'fonction_id',
  undefined,
  { initialValue: 0 },
)

const { loading, error, response, putData } = usePutApi()
const { id } = useRoute().params

const onSubmit = handleSubmit(async (values) => {
  console.log('=== EDIT FORM SUBMISSION ===')
  console.log('All fields:', JSON.stringify(values, null, 2))

  // Convert visit_hour from "HH:mm" to ISO datetime
  if (values.visit_hour) {
    const today = new Date()
    const [hours, minutes] = values.visit_hour.split(':')
    today.setHours(parseInt(hours), parseInt(minutes), 0, 0)
    values.visit_hour = today.toISOString()
    console.log('visit_hour converted to:', values.visit_hour)
  }
  await putData(API_ROUTES.UPDATE_VISITE_CLASSE.replace(':id', id as string), values)
  eventBus.emit('studentUpdated')
  if (error.value) {
    showCustomToast({
      message: error.value,
      type: 'error',
    })
    return
  } else if (response.value) {
    showCustomToast({
      message: 'Visite de classe mise à jour avec succès',
      type: 'success',
    })
    resetForm()
    router.push('/apprenants/operations/visite-classe')
  }
})

//fetchStudentById(id as string)
const fetchVisitById = async () => {
  try {
    console.log('Fetching visit with ID:', id)
    const { data } = await api.get(API_ROUTES.GET_VISIT_BY_ID.replace(':id', id as string))
    visit.value = data // adapte selon la structure retournée
    console.log(visit.value)
    if (visit.value) {
      subject.value = visit.value.subject
      // Convert IDs to numbers to match useField types
      school_id.value = Number(visit.value.school_id)
      classroom_id.value = Number(visit.value.classroom_id)
      personal_id.value = Number(visit.value.personal_id)
      cot_doc_prof.value = visit.value.cot_doc_prof
      cot_meth_proc.value = visit.value.cot_meth_proc
      cot_matiere.value = visit.value.cot_matiere
      cot_march_lecon.value = visit.value.cot_march_lecon
      cot_enseignant.value = visit.value.cot_enseignant
      cot_eleve.value = visit.value.cot_eleve
      ctrl_doc_eleve.value = visit.value.ctrl_doc_eleve || 0
      if (visit.value.visit_hour) {
        const date = new Date(visit.value.visit_hour)
        visit_hour.value = `${date.getHours().toString().padStart(2, '0')}:${date.getMinutes().toString().padStart(2, '0')}`
      }
      if (visit.value.end_time) {
        end_time.value = visit.value.end_time
      }
      summary.value = visit.value.summary
      visiteur.value = visit.value.visiteur || ''
      fonction_id.value = visit.value.fonction_id ? Number(visit.value.fonction_id) : 0
    }
  } catch (error) {
    console.error('Erreur lors de la récupération de la visite :', error)
  }
}

onMounted(() => {
  // Pré-remplir school_id avec l'école de l'utilisateur connecté
  if (authStore.user?.school_id) {
    school_id.value = Number(authStore.user.school_id)
  }

  // Pré-remplir personal_id avec l'ID du personnel de l'utilisateur connecté
  if (authStore.user?.id) {
    personal_id.value = Number(authStore.user.id)
  }

  if (id) {
    fetchVisitById()
  }
})
</script>
<template>
  <DashFormLayout
    title="Modifier une visite de classe"
    link-back="/apprenants/operations/visite-classe"
    group-route="/apprenants/operations"
    module="students"
    :breadcrumb="[
      { label: 'Élèves', href: '/apprenants' },
      { label: 'Opérations', href: '/apprenants/operations' },
      { label: 'Visite de Classes', href: '/apprenants/operations/visite-classe' },
      { label: 'Modifier Visite', href: '#' },
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
            Sujet
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
            Cote de l'élève
            <SpanRequired />
          </Label>
          <Input
            type="number"
            class="bg-white transition-all h-10 rounded-md"
            v-model="cot_eleve"
          />
          <span v-if="cotEleveError" class="text-xs text-red-500">{{ cotEleveError }}</span>
        </InputWrapper>
        <!-- Contrôle document de l'élève: ctrl_doc_eleve -->
        <InputWrapper>
          <Label class="text-sm"> Contrôle documents des élèves </Label>
          <Input
            type="number"
            class="bg-white transition-all h-10 rounded-md"
            v-model="ctrl_doc_eleve"
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
        <!-- Heure de fin: end_time -->
        <InputWrapper>
          <Label class="text-sm">
            Heure de fin
            <SpanRequired />
          </Label>
          <Input type="time" class="bg-white transition-all h-10 rounded-md" v-model="end_time" />
          <span v-if="endTimeError" class="text-xs text-red-500">{{ endTimeError }}</span>
        </InputWrapper>

        <!-- Visiteur -->
        <InputWrapper>
          <Label class="text-sm"> Visiteur </Label>
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

        <!-- Résumé: summary -->
        <InputWrapper class="sm:col-span-2 lg:col-span-3">
          <Label class="text-sm">
            Résumé
            <SpanRequired />
          </Label>
          <Input
            class="bg-white transition-all h-10 rounded-md"
            placeholder="Résumé de la visite"
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
