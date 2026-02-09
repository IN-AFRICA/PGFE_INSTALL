<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { Button } from '@/components/ui/button'
import { Badge } from '@/components/ui/badge'
import {
  Table,
  TableBody,
  TableCell,
  TableHead,
  TableHeader,
  TableRow,
} from '@/components/ui/table'
import FormSection from '@/components/atoms/FormSection.vue'
import {
  AlertDialog,
  AlertDialogContent,
  AlertDialogHeader,
  AlertDialogTitle,
  AlertDialogDescription,
  AlertDialogFooter,
  AlertDialogCancel,
  AlertDialogAction,
} from '@/components/ui/alert-dialog'
import { Label } from '@/components/ui/label'
import { Input } from '@/components/ui/input'
import { Checkbox } from '@/components/ui/checkbox'
import DashFormLayout from '@/components/templates/DashFormLayout.vue'
import { useGetApi } from '@/composables/useGetApi'
import { usePostApi } from '@/composables/usePostApi'
import { API_ROUTES } from '@/utils/constants/api_route'
import { showCustomToast } from '@/utils/widgets/custom_toast'

// Interfaces TypeScript basées sur la réponse API
interface GeneralDelibBreakdownItem {
  course_id: number
  label: string
  percentage: number
}

interface GeneralDelibCounts {
  total_courses: number
  included: number
  skipped: number
}

interface GeneralDeliberationResult {
  student: { id: number }
  classroom_id: number
  school_year_id: number
  overall_percentage: number
  validated: boolean
  threshold: number
  method: 'simple_average' | 'weighted_average'
  counts: GeneralDelibCounts
  breakdown: GeneralDelibBreakdownItem[]
  course_percentages: { course_id: number; percentage: number }[]
  eligible_for_validation: boolean
}

interface GeneralDeliberationResponse {
  success: boolean
  message: string
  data: GeneralDeliberationResult
}

// Route et paramètres
const route = useRoute()
const router = useRouter()
const studentId = computed(() => route.params.studentId as string)

// Paramètres depuis l'URL
const classroomId = computed(() => route.query.classroom_id as string)
const schoolYearId = computed(() => route.query.school_year_id as string)
const skipMissing = computed(() => route.query.skip_missing === 'true')
const weightByHourly = computed(() => route.query.weight_by_hourly === 'true')

// Infos d'en-tête depuis la navigation (liste)
const studentName = computed(() => (route.query.student_name as string) || '')
const classroomName = computed(() => (route.query.classroom_name as string) || '')
const filiereName = computed(() => (route.query.filiere_name as string) || '')

// Récupérer les inscriptions pour résoudre des labels lisibles si besoin
const { data: registrationsData, fetchData: fetchRegistrations } = useGetApi(
  API_ROUTES.GET_STUDENT_REGISTRATIONS,
)

// Résolution sécurisée depuis registrationsData (qui est un Ref)
const resolvedFromRegistrations = computed(() => {
  const raw: any = (registrationsData as any)?.value
  const arr = Array.isArray(raw) ? raw : raw?.data && Array.isArray(raw.data) ? raw.data : []
  const first: any = arr?.[0] || {}
  // Compose full name when possible
  const parts = [first.student?.name, first.student?.lastname, first.student?.firstname].filter(
    Boolean,
  )
  const fullName = (parts.length ? parts.join(' ') : first.student_name || '') as string
  // Normalize classroom/filiere that can be object or string
  const classroom =
    typeof first.classroom === 'object'
      ? first.classroom?.name || first.classroom?.label || ''
      : first.classroom_name || first.classroom || ''
  const filiere =
    typeof first.filiaire === 'object'
      ? first.filiaire?.name || first.filiaire?.label || ''
      : first.filiaire_name || first.filiere_name || first.filiale_name || first.filiaire || ''
  return {
    student: fullName,
    classroom,
    filiere,
  }
})

const displayStudentName = computed(
  () => studentName.value || resolvedFromRegistrations.value.student,
)
const displayClassroomName = computed(
  () => classroomName.value || resolvedFromRegistrations.value.classroom,
)
const displayFiliereName = computed(
  () => filiereName.value || resolvedFromRegistrations.value.filiere,
)

// API pour charger la délibération générale
const {
  data: generalDelibData,
  loading: loadingGeneral,
  error: errorGeneral,
  fetchData: loadGeneralDelib,
} = useGetApi<GeneralDeliberationResponse>(
  API_ROUTES.GET_GENERAL_DELIB_STUDENT.replace(':student', studentId.value),
)

// API pour valider
const {
  postData: validateDelib,
  loading: validating,
  error: errorValidate,
  success: successValidate,
} = usePostApi()

// États pour le modal de validation
const showValidateDialog = ref(false)
const validateOptions = ref({
  threshold: 50,
  weight_by_hourly: false,
  skip_missing: false,
  force: false,
})

// Computed pour les données de délibération
const deliberationData = computed<GeneralDeliberationResult | undefined>(() => {
  const raw: any = generalDelibData.value as any
  if (!raw) return undefined
  // Gère réponse enveloppée (raw.data) et non enveloppée (raw)
  return (raw.data ? raw.data : raw) as GeneralDeliberationResult
})

// Couleur du badge selon le pourcentage
const getPercentageBadgeVariant = (percentage: number, threshold: number) => {
  if (percentage >= threshold) return 'default' // Vert
  if (percentage >= threshold * 0.7) return 'secondary' // Orange
  return 'destructive' // Rouge
}

// Couleur du badge de validation alignée avec StudentsPresence.vue
const getValidationColorClasses = (validated: boolean, eligible: boolean) => {
  if (validated) return 'bg-green-100 text-green-800 border-green-200'
  if (eligible) return 'bg-orange-100 text-orange-800 border-orange-200'
  return 'bg-red-100 text-red-800 border-red-200'
}

// Déterminer le type (pass/alert/fail) selon le seuil
const getKind = (percentage: number, threshold: number): 'pass' | 'alert' | 'fail' => {
  if (percentage >= threshold) return 'pass'
  if (percentage >= threshold * 0.7) return 'alert'
  return 'fail'
}

// Classes couleurs alignées avec StudentsPresence.vue
const getColorClasses = (kind: 'pass' | 'alert' | 'fail') => {
  switch (kind) {
    case 'pass':
      return 'bg-green-100 text-green-800 border-green-200'
    case 'alert':
      return 'bg-orange-100 text-orange-800 border-orange-200'
    default:
      return 'bg-red-100 text-red-800 border-red-200'
  }
}

// Couleur de texte douce pour libellé explicatif
const getSoftTextClass = (percentage: number, threshold: number) => {
  const kind = getKind(percentage, threshold)
  if (kind === 'pass') return 'text-green-800'
  if (kind === 'alert') return 'text-orange-800'
  return 'text-red-800'
}

// Fonction de validation
const handleValidate = async (force = false) => {
  if (!deliberationData.value) return

  const payload = {
    classroom_id: Number(classroomId.value),
    school_year_id: Number(schoolYearId.value),
    threshold: validateOptions.value.threshold,
    weight_by_hourly: validateOptions.value.weight_by_hourly,
    skip_missing: validateOptions.value.skip_missing,
    force: force,
  }

  const url = API_ROUTES.VALIDATE_GENERAL_DELIB_STUDENT.replace(':student', studentId.value)

  await validateDelib(url, payload)

  if (successValidate.value) {
    showCustomToast({
      message: force ? 'Validation forcée avec succès' : 'Validation effectuée avec succès',
      type: 'success',
    })
    showValidateDialog.value = false
    // Recharger les données
    await loadGeneralDelib({
      classroom_id: classroomId.value,
      school_year_id: schoolYearId.value,
      skip_missing: skipMissing.value,
      weight_by_hourly: weightByHourly.value,
    })
  } else if (errorValidate.value) {
    showCustomToast({
      message: errorValidate.value || 'Erreur lors de la validation',
      type: 'error',
    })
  }
}

// Ouvrir le modal de validation
const openValidateDialog = () => {
  if (deliberationData.value) {
    validateOptions.value.threshold = deliberationData.value.threshold
    validateOptions.value.weight_by_hourly = deliberationData.value.method === 'weighted_average'
  }
  showValidateDialog.value = true
}

// Retour à la liste
const goBack = () => {
  router.back()
}

// Charger les données au montage
onMounted(async () => {
  if (!classroomId.value || !schoolYearId.value) {
    showCustomToast({
      message: 'Paramètres manquants (classe ou année scolaire)',
      type: 'error',
    })
    router.back()
    return
  }

  await loadGeneralDelib({
    classroom_id: classroomId.value,
    school_year_id: schoolYearId.value,
    skip_missing: skipMissing.value,
    weight_by_hourly: weightByHourly.value,
  })

  // Récupérer l'inscription de l'élève pour résoudre les labels d'affichage
  await fetchRegistrations({ student_id: studentId.value })
})
</script>

<template>
  <DashFormLayout
    title="Délibération Générale"
    link-back="/apprenants/operations/deliberation"
    group-route="/apprenants/operations"
    module="students"
    :breadcrumb="[
      { label: 'Élèves', href: '/apprenants' },
      { label: 'Opérations', href: '/apprenants/operations' },
      { label: 'Délibération', href: '/apprenants/operations/deliberation' },
      { label: 'Délibération Générale', href: '#' },
    ]"
  >
    <div
      v-if="loadingGeneral"
      class="flex flex-col items-center justify-center py-10 bg-white rounded-md text-gray-500"
    >
      <svg
        class="animate-spin h-6 w-6 text-gray-400 mb-2"
        xmlns="http://www.w3.org/2000/svg"
        fill="none"
        viewBox="0 0 24 24"
      >
        <circle
          class="opacity-25"
          cx="12"
          cy="12"
          r="10"
          stroke="currentColor"
          stroke-width="4"
        ></circle>
        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"></path>
      </svg>
      <span>Chargement de la délibération générale...</span>
    </div>

    <div v-else-if="errorGeneral" class="bg-red-50 border border-red-200 rounded-lg p-4 mb-6">
      <div class="flex items-center gap-2 text-red-800">
        <span class="iconify hugeicons--alert-circle"></span>
        <span>{{ errorGeneral || 'Erreur lors du chargement de la délibération' }}</span>
      </div>
      <Button variant="outline" class="mt-3" @click="loadGeneralDelib"> Réessayer </Button>
    </div>

    <div v-else-if="deliberationData" class="w-full flex flex-col space-y-8">
      <div class="flex items-center">
        <div class="ml-auto text-right">
          <h2 class="text-xl font-semibold text-foreground-title">
            {{ displayStudentName || '-' }}
          </h2>
          <p class="text-md text-gray-500">
            {{ displayClassroomName || '-' }} · {{ displayFiliereName || '-' }}
          </p>
        </div>
      </div>

      <div class="space-y-6">
        <FormSection title="Synthèse générale">
          <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div class="text-center">
              <div class="text-2xl font-bold mb-1">
                <Badge
                  :class="
                    getColorClasses(
                      getKind(deliberationData.overall_percentage, deliberationData.threshold),
                    ) + ' px-2 py-1 text-xs font-medium border'
                  "
                >
                  {{ deliberationData.overall_percentage.toFixed(2) }}%
                </Badge>
              </div>
              <p class="text-sm text-gray-600">Pourcentage global</p>
            </div>

            <div class="text-center">
              <div class="text-lg font-semibold mb-1">{{ deliberationData.threshold }}%</div>
              <p class="text-sm text-gray-600">Seuil requis</p>
            </div>

            <div class="text-center">
              <Badge
                :class="
                  getValidationColorClasses(
                    deliberationData.validated,
                    deliberationData.eligible_for_validation,
                  ) + ' px-2 py-1 text-xs font-medium border'
                "
              >
                {{
                  deliberationData.validated
                    ? 'Validé'
                    : deliberationData.eligible_for_validation
                      ? 'Éligible'
                      : 'Non éligible'
                }}
              </Badge>
              <p class="text-sm text-gray-600 mt-1">État de validation</p>
            </div>

            <div class="text-center">
              <div class="text-lg font-semibold mb-1">
                {{ deliberationData.counts.included }}/{{ deliberationData.counts.total_courses }}
              </div>
              <p class="text-sm text-gray-600">Cours inclus</p>
            </div>
          </div>
        </FormSection>

        <div class="w-full flex h-px bg-gray-300"></div>

        <FormSection
          :title="`Détail par Cours ( ${deliberationData.counts.skipped} cours ignoré(s) )`"
        >
          <Table>
            <TableHeader>
              <TableRow>
                <TableHead>Cours</TableHead>
                <TableHead class="text-center">Pourcentage</TableHead>
                <TableHead class="text-center">État</TableHead>
              </TableRow>
            </TableHeader>
            <TableBody>
              <TableRow v-for="course in deliberationData.breakdown" :key="course.course_id">
                <TableCell class="font-medium">{{ course.label }}</TableCell>
                <TableCell class="text-center">
                  <Badge
                    :class="
                      getColorClasses(getKind(course.percentage, deliberationData.threshold)) +
                      ' px-2 py-1 text-xs font-medium border'
                    "
                  >
                    {{ course.percentage.toFixed(2) }}%
                  </Badge>
                </TableCell>
                <TableCell class="text-center">
                  <Badge
                    :class="
                      getColorClasses(getKind(course.percentage, deliberationData.threshold)) +
                      ' px-2 py-1 text-xs font-medium border'
                    "
                  >
                    {{ course.percentage >= deliberationData.threshold ? 'Réussi' : 'Échec' }}
                  </Badge>
                </TableCell>
              </TableRow>
            </TableBody>
          </Table>
        </FormSection>
      </div>

      <div class="w-full flex h-px bg-gray-300"></div>
      <div class="flex items-center justify-end gap-2">
        <Button
          @click="openValidateDialog"
          :disabled="validating || loadingGeneral"
          v-if="deliberationData"
        >
          <span v-if="validating" class="iconify hugeicons--loading-03 animate-spin mr-2" />
          {{ deliberationData?.eligible_for_validation ? 'Valider' : 'Forcer la validation' }}
        </Button>
      </div>
    </div>

    <AlertDialog :open="showValidateDialog" @update:open="showValidateDialog = $event">
      <AlertDialogContent>
        <AlertDialogHeader>
          <AlertDialogTitle>Valider la délibération générale</AlertDialogTitle>
          <AlertDialogDescription>
            Configurez les paramètres de validation pour cet élève.
          </AlertDialogDescription>
        </AlertDialogHeader>

        <div class="space-y-4 py-4">
          <div class="space-y-2">
            <Label for="threshold">Seuil de validation (%)</Label>
            <Input
              id="threshold"
              type="number"
              min="0"
              max="100"
              v-model.number="validateOptions.threshold"
            />
          </div>

          <div class="flex items-center space-x-2">
            <Checkbox id="weight_by_hourly" v-model:checked="validateOptions.weight_by_hourly" />
            <Label for="weight_by_hourly">Pondérer par nombre d'heure</Label>
          </div>

          <div class="flex items-center space-x-2">
            <Checkbox id="skip_missing" v-model:checked="validateOptions.skip_missing" />
            <Label for="skip_missing">Ignorer les notes manquantes</Label>
          </div>

          <div class="flex items-center space-x-2">
            <Checkbox id="force" v-model:checked="validateOptions.force" />
            <Label for="force">Forcer la validation (même si non éligible)</Label>
          </div>
        </div>

        <AlertDialogFooter>
          <AlertDialogCancel>Annuler</AlertDialogCancel>
          <AlertDialogAction @click="handleValidate(validateOptions.force)" :disabled="validating">
            <span v-if="validating" class="iconify hugeicons--loading-03 animate-spin mr-2" />
            {{ validateOptions.force ? 'Forcer la validation' : 'Valider' }}
          </AlertDialogAction>
        </AlertDialogFooter>
      </AlertDialogContent>
    </AlertDialog>
  </DashFormLayout>
</template>
