<script setup lang="ts">
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from '@/components/ui/select'
import { ref, onMounted, watch, computed, nextTick } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { useAuthStore } from '@/stores/auth.ts'
import DashFormLayout from '@/components/templates/DashFormLayout.vue'
import FormSection from '@/components/atoms/FormSection.vue'
import InputWrapper from '@/components/atoms/InputWrapper.vue'
import SpanRequired from '@/components/atoms/SpanRequired.vue'
import { useGetApi } from '@/composables/useGetApi.ts'
import { usePostApi } from '@/composables/usePostApi.ts'
import { usePutApi } from '@/composables/usePutApi.ts'
import { API_ROUTES, BASE_URL } from '@/utils/constants/api_route.ts'
import { eventBus } from '@/utils/eventBus.ts'
// Interface locale pour les données du formulaire
interface CourseFormData {
  label: string
  cycle_id: number
  level_id: number
  filiere_id: number
  classroom_id: number
  teacher_id: number
  hourly_volume: number
  max_period_1: number
  max_period_2: number
  max_period_3: number
  max_period_4: number
  max_exam_1: number
  max_exam_2: number
}
import { showCustomToast } from '@/utils/widgets/custom_toast.ts'

const router = useRouter()
const route = useRoute()
const auth = useAuthStore()

// Form data
const formData = ref<CourseFormData>({
  label: '',
  cycle_id: 0,
  level_id: 0,
  filiere_id: 0,
  classroom_id: 0,
  teacher_id: 0,
  hourly_volume: 0,
  max_period_1: 0,
  max_period_2: 0,
  max_period_3: 0,
  max_period_4: 0,
  max_exam_1: 0,
  max_exam_2: 0,
})

// Auto-remplissage: P2/P3/P4 prennent la valeur de P1, E1/E2 = 2 * P1
watch(
  () => formData.value.max_period_1,
  (val) => {
    const base = Number(val) || 0
    formData.value.max_period_2 = base
    formData.value.max_period_3 = base
    formData.value.max_period_4 = base
    formData.value.max_exam_1 = base * 2
    formData.value.max_exam_2 = base * 2
  },
  { immediate: true },
)

// Récupérer l'utilisateur connecté
const userSchoolId = computed(() => auth.userSchoolId)

// API composables for dropdown data
const { data: cycles, fetchData: fetchCycles } = useGetApi<any[]>(API_ROUTES.GET_CYCLES)
const { data: levels, fetchData: fetchLevels } = useGetApi<any[]>(API_ROUTES.GET_ACADEMIC_LEVELS)
const { data: filieres, fetchData: fetchFilieres } = useGetApi<any[]>(API_ROUTES.GET_FILLIERES)
const { data: classrooms, fetchData: fetchClassrooms } = useGetApi<any[]>(API_ROUTES.GET_CLASSROOMS)
const { data: teachers, fetchData: fetchTeachers } = useGetApi<any[]>(
  API_ROUTES.GET_ACADEMIC_PERSONALS,
)

// Helper pour extraire le tableau des données (paginées ou non)
const extractArray = (data: any): any[] => {
  if (!data) return []
  if (Array.isArray(data)) return data
  if (data.data && Array.isArray(data.data)) return data.data
  return []
}

// Filtrage en cascade basé sur la hiérarchie: Cycle → Niveau → Filière → Classe
const filteredFilieres = computed(() => {
  const items = extractArray(filieres.value)
  // L'API /filiaires/lists retourne déjà les filières de l'école de l'utilisateur connecté
  return items
})

const filteredCycles = computed(() => {
  if (!formData.value.filiere_id) return []

  // D'abord chercher dans les filières (structure imbriquée de l'API /filiaires/lists)
  const filiereItems = extractArray(filieres.value)
  const selectedFiliere = filiereItems.find(
    (f: any) => String(f.id) === String(formData.value.filiere_id),
  )

  if (selectedFiliere?.cycles && selectedFiliere.cycles.length > 0) {
    return selectedFiliere.cycles
  }

  // Fallback: chercher dans cyclesData si la structure n'est pas imbriquée
  const cycleItems = extractArray(cycles.value)
  return cycleItems.filter((c: any) => String(c.filiaire_id) === String(formData.value.filiere_id))
})

const filteredLevels = computed(() => {
  if (!formData.value.cycle_id) return []

  // D'abord chercher dans les cycles de la filière sélectionnée (structure imbriquée)
  const filiereItems = extractArray(filieres.value)
  const selectedFiliere = filiereItems.find(
    (f: any) => String(f.id) === String(formData.value.filiere_id),
  )

  if (selectedFiliere?.cycles) {
    const selectedCycle = selectedFiliere.cycles.find(
      (c: any) => String(c.id) === String(formData.value.cycle_id),
    )
    if (selectedCycle?.academic_levels && selectedCycle.academic_levels.length > 0) {
      return selectedCycle.academic_levels
    }
  }

  // Fallback: chercher dans levelsData
  const levelItems = extractArray(levels.value)
  return levelItems.filter(
    (level: any) => String(level.cycle_id) === String(formData.value.cycle_id),
  )
})

const filteredClassrooms = computed(() => {
  return extractArray(classrooms.value)
})

// Reset des champs dépendants en cascade (sauf pendant le chargement des données d'édition)
watch(
  () => formData.value.filiere_id,
  (newVal, oldVal) => {
    if (!isLoadingEditData.value && oldVal !== undefined && newVal !== oldVal) {
      formData.value.cycle_id = 0
      formData.value.level_id = 0
      formData.value.classroom_id = 0
      classrooms.value = []
    }
  },
)

watch(
  () => formData.value.cycle_id,
  (newVal, oldVal) => {
    if (!isLoadingEditData.value && oldVal !== undefined && newVal !== oldVal) {
      formData.value.level_id = 0
      formData.value.classroom_id = 0
      classrooms.value = []
    }
  },
)

watch(
  () => formData.value.level_id,
  async (newVal, oldVal) => {
    if (!isLoadingEditData.value && oldVal !== undefined && newVal !== oldVal) {
      formData.value.classroom_id = 0
    }

    if (newVal) {
      await fetchClassrooms({ academic_level_id: newVal })
    } else {
      classrooms.value = []
    }
  },
)

// API composables for creating/updating course
const { postData, loading, error, response, success, status, errorDetails } = usePostApi<any>()
const {
  putData,
  loading: loadingPut,
  error: errorPut,
  response: responsePut,
  success: successPut,
} = usePutApi<any>()

// Mode édition
const isEditing = ref(false)
const editingCourseId = ref<number | null>(null)
const isLoadingEditData = ref(false) // Flag pour désactiver les watchers pendant le chargement

// --- Normaliser teachers pour la template ---
const teacherItems = computed(() => extractArray(teachers.value))

// Load dropdown data on mount
onMounted(async () => {
  await Promise.all([
    fetchCycles(),
    fetchLevels(),
    fetchFilieres(),
    // fetchClassrooms(), // Chargé dynamiquement par niveau
    fetchTeachers(),
  ])

  // Détecter le mode édition
  if (route.query.edit === 'true' && route.query.id) {
    isEditing.value = true
    editingCourseId.value = Number(route.query.id)
    await loadCourseData(editingCourseId.value)
  }
})

// Charger les données du cours à éditer
const loadCourseData = async (courseId: number) => {
  try {
    console.log('[NewCourseForm] Loading course with ID:', courseId)
    const url = API_ROUTES.GET_COURSE.replace(':course', String(courseId))
    console.log('[NewCourseForm] API URL:', url)

    const cleanBaseUrl = BASE_URL.endsWith('/') ? BASE_URL : `${BASE_URL}/`
    const cleanUrl = url.startsWith('/') ? url.slice(1) : url

    console.log('[NewCourseForm] Full URL:', `${cleanBaseUrl}${cleanUrl}`)

    const response = await fetch(`${cleanBaseUrl}${cleanUrl}`, {
      headers: {
        Authorization: `Bearer ${auth.token}`,
        'Content-Type': 'application/json',
      },
    })

    if (!response.ok) {
      throw new Error('Erreur lors du chargement du cours')
    }

    const result = await response.json()
    const course = result.data || result
    console.log('[NewCourseForm] Course data loaded:', course)

    // Vérifier si le backend retourne une erreur
    if (course && course.error) {
      console.error('[NewCourseForm] Backend error:', course.error)
      showCustomToast({
        message: 'Erreur backend: Le modèle Course a une relation incorrecte.',
        type: 'error',
      })
      isLoadingEditData.value = false
      return
    }

    if (course && !course.error) {
      // Désactiver les watchers pendant le chargement
      isLoadingEditData.value = true

      // Attendre que Vue soit prêt
      await nextTick()

      // Pré-remplir le formulaire - Mapper les champs de l'API vers les champs du formulaire
      formData.value = {
        label: course.label || '',
        cycle_id: Number(course.cycle_id) || 0,
        level_id: Number(course.academic_level_id) || 0, // API renvoie academic_level_id
        filiere_id: Number(course.filiaire_id) || 0, // API renvoie filiaire_id
        classroom_id: Number(course.classroom_id) || 0,
        teacher_id: Number(course.teacher_id) || 0,
        hourly_volume: Number(course.hourly_volume) || 0,
        max_period_1: Number(course.max_period_1) || 0,
        max_period_2: Number(course.max_period_2) || 0,
        max_period_3: Number(course.max_period_3) || 0,
        max_period_4: Number(course.max_period_4) || 0,
        max_exam_1: Number(course.max_exam_1) || 0,
        max_exam_2: Number(course.max_exam_2) || 0,
      }

      console.log('[NewCourseForm] Form pre-filled with:', formData.value)

      // Si la valeur teacher_id ne correspond pas directement aux options, essayer de la réconcilier
      try {
        const tList = extractArray(teachers.value)
        if (tList && tList.length > 0 && course.teacher_id) {
          const match = tList.find(
            (t: any) => String(t.id) === String(course.teacher_id) || String(t.user_id) === String(course.teacher_id),
          )
          if (match) {
            formData.value.teacher_id = Number(match.id ?? match.user_id ?? match.user_id)
            console.log('[NewCourseForm] Reconciled teacher_id to:', formData.value.teacher_id)
          }
        }
      } catch (e) {
        console.warn('[NewCourseForm] Teacher reconciliation failed', e)
      }

      // Charger explicitement les classes correspondant au niveau sélectionné en mode édition
      if (formData.value.level_id) {
        try {
          await fetchClassrooms({ academic_level_id: formData.value.level_id })
          console.log('[NewCourseForm] Classrooms loaded for level:', formData.value.level_id)
        } catch (e) {
          console.warn('[NewCourseForm] Unable to load classrooms for edit mode', e)
        }
      }

      // Attendre un autre tick pour que le DOM soit mis à jour
      await nextTick()

      // Réactiver les watchers après que le DOM soit mis à jour
      setTimeout(() => {
        isLoadingEditData.value = false
        console.log('[NewCourseForm] Edit mode ready')
      }, 200)
    } else {
      console.warn('[NewCourseForm] No data returned from API')
    }
  } catch (err) {
    console.error('[NewCourseForm] Error loading course:', err)
    showCustomToast({ message: 'Erreur lors du chargement du cours', type: 'error' })
    isLoadingEditData.value = false
  }
}

const handleCancel = () => {
  router.push('/apprenants/saisie-prealable/cours')
}

const handleSubmit = async () => {
  if (!auth.isAuthenticated) {
    showCustomToast({
      message: 'vous devez etre connecté pour créer un cours',
      type: 'error',
    })
    router.push('/auth/login')
    return
  }
  // Validation
  if (!formData.value.label.trim()) {
    showCustomToast({ message: 'Le libellé du cours est requis', type: 'error' })
    return
  }

  if (
    !formData.value.cycle_id ||
    !formData.value.level_id ||
    !formData.value.filiere_id ||
    !formData.value.classroom_id ||
    !formData.value.teacher_id
  ) {
    showCustomToast({ message: 'Tous les champs obligatoires doivent être remplis', type: 'error' })
    return
  }

  if (formData.value.hourly_volume <= 0) {
    showCustomToast({ message: "Le nombre d'heure doit être supérieur à 0", type: 'error' })
    return
  }

  try {
    // Mapper les champs du formulaire vers les noms attendus par l'API
    const payload = {
      label: formData.value.label,
      academic_level_id: formData.value.level_id, // Mapper level_id → academic_level_id
      filiaire_id: formData.value.filiere_id, // Mapper filiere_id → filiaire_id
      cycle_id: formData.value.cycle_id,
      classroom_id: formData.value.classroom_id,
      teacher_id: formData.value.teacher_id,
      hourly_volume: formData.value.hourly_volume,
      max_period_1: formData.value.max_period_1,
      max_period_2: formData.value.max_period_2,
      max_period_3: formData.value.max_period_3,
      max_period_4: formData.value.max_period_4,
      max_exam_1: formData.value.max_exam_1,
      max_exam_2: formData.value.max_exam_2,
    }
    console.log('📤 [NewCourseForm] Payload envoyé (complet):', JSON.stringify(payload, null, 2))
    console.log('📤 [NewCourseForm] FormData state:', {
      label: formData.value.label,
      cycle_id: formData.value.cycle_id,
      level_id: formData.value.level_id,
      filiere_id: formData.value.filiere_id,
      classroom_id: formData.value.classroom_id,
      teacher_id: formData.value.teacher_id,
      hourly_volume: formData.value.hourly_volume,
    })

    if (isEditing.value && editingCourseId.value) {
      // Mode édition - PUT
      const url = API_ROUTES.UPDATE_COURSE.replace(':course', String(editingCourseId.value))
      console.log('📤 [NewCourseForm] PUT URL:', url)
      await putData(url, payload)

      if (errorPut.value) {
        console.error('❌ [NewCourseForm] Erreur API (PUT):', {
          message: errorPut.value,
        })
        showCustomToast({ message: errorPut.value, type: 'error' })
        return
      }

      if (successPut.value) {
        console.log('✅ Cours modifié:', responsePut.value)
        showCustomToast({ message: 'Cours modifié avec succès', type: 'success' })
        eventBus.emit('courseUpdated')
        router.push('/apprenants/saisie-prealable/cours')
      }
    } else {
      // Mode création - POST
      const postUrl = API_ROUTES.CREATE_COURSE
      console.log('📤 [NewCourseForm] POST URL:', postUrl)
      await postData(postUrl, payload)

      if (error.value) {
        console.error('❌ [NewCourseForm] Erreur API (POST):', {
          message: error.value,
          status: status?.value,
          errorDetails: errorDetails?.value,
        })
        // Affiche aussi les détails d'erreur si disponibles (validation Laravel)
        const detailMsg = errorDetails?.value?.errors
          ? Object.entries(errorDetails.value.errors).map(([key, msgs]: any) => `${key}: ${Array.isArray(msgs) ? msgs.join(', ') : msgs}`).join(' | ')
          : error.value
        showCustomToast({ message: detailMsg || error.value, type: 'error' })
        return
      }

      if (success.value) {
        console.log('✅ Cours créé:', response.value)
        showCustomToast({ message: 'Cours créé avec succès', type: 'success' })
        eventBus.emit('courseUpdated')
        router.push('/apprenants/saisie-prealable/cours')
      }
    }
  } catch (err) {
    console.error('❌ [NewCourseForm] Exception catch:', err)
    showCustomToast({
      message: `Erreur lors de ${isEditing.value ? 'la modification' : 'la création'} du cours`,
      type: 'error',
    })
  }
}
</script>

<template>
  <DashFormLayout
    :link-back="'/apprenants/saisie-prealable/cours'"
    :title="isEditing ? 'Édition du Cours' : 'Nouveau Cours'"
    group-route="/apprenants/saisie-prealable"
    module="students"
    :breadcrumb="[
      { label: 'Élèves', href: '/apprenants' },
      { label: 'Saisie Préalable', href: '/apprenants/saisie-prealable' },
      { label: 'Cours', href: '/apprenants/saisie-prealable/cours' },
      {
        label: isEditing ? 'Édition du Cours' : 'Nouveau Cours',
        href: '/apprenants/saisie-prealable/cours/nouveau',
      },
    ]"
  >
    <form @submit.prevent="handleSubmit" class="w-full flex flex-col space-y-8">
      <FormSection
        title="Informations générales"
        class="grid sm:grid-cols-2 lg:grid-cols-3 gap-y-6 gap-x-10"
      >
        <InputWrapper>
          <Label for="label">Libellé du cours<SpanRequired /></Label>
          <Input
            id="label"
            v-model="formData.label"
            type="text"
            placeholder="Entrez le libellé du cours"
            required
            class="w-full"
          />
        </InputWrapper>

        <InputWrapper>
          <Label for="filiere_id">Section<SpanRequired /></Label>
          <Select v-model="formData.filiere_id" required>
            <SelectTrigger class="!h-10 bg-white w-full">
              <SelectValue placeholder="Sélectionnez une section" />
            </SelectTrigger>
            <SelectContent>
              <SelectItem
                v-for="filiere in filteredFilieres"
                :key="filiere.id"
                :value="Number(filiere.id)"
              >
                {{ filiere.name }}
              </SelectItem>
            </SelectContent>
          </Select>
        </InputWrapper>

        <InputWrapper>
          <Label for="cycle_id">Cycle<SpanRequired /></Label>
          <Select v-model="formData.cycle_id" :disabled="!formData.filiere_id" required>
            <SelectTrigger class="!h-10 bg-white w-full">
              <SelectValue
                :placeholder="
                  formData.filiere_id
                    ? 'Sélectionnez un cycle'
                    : 'Sélectionnez d\'abord une section'
                "
              />
            </SelectTrigger>
            <SelectContent>
              <SelectItem v-for="cycle in filteredCycles" :key="cycle.id" :value="Number(cycle.id)">
                {{ cycle.name }}
              </SelectItem>
            </SelectContent>
          </Select>
        </InputWrapper>

        <InputWrapper>
          <Label for="level_id">Niveau scolaire<SpanRequired /></Label>
          <Select v-model="formData.level_id" :disabled="!formData.cycle_id" required>
            <SelectTrigger class="!h-10 bg-white w-full">
              <SelectValue
                :placeholder="
                  formData.cycle_id ? 'Sélectionnez un niveau' : 'Sélectionnez d\'abord un cycle'
                "
              />
            </SelectTrigger>
            <SelectContent>
              <SelectItem v-for="level in filteredLevels" :key="level.id" :value="Number(level.id)">
                {{ level.name }}
              </SelectItem>
            </SelectContent>
          </Select>
        </InputWrapper>

        <InputWrapper>
          <Label for="classroom_id">Classe<SpanRequired /></Label>
          <Select v-model="formData.classroom_id" :disabled="!formData.level_id" required>
            <SelectTrigger class="!h-10 bg-white w-full">
              <SelectValue
                :placeholder="
                  formData.level_id ? 'Sélectionnez une classe' : 'Sélectionnez d\'abord un niveau'
                "
              />
            </SelectTrigger>
            <SelectContent>
              <SelectItem
                v-for="classroom in filteredClassrooms"
                :key="classroom.id"
                :value="Number(classroom.id)"
              >
                {{ classroom.name }}
              </SelectItem>
            </SelectContent>
          </Select>
        </InputWrapper>

        <InputWrapper>
          <Label for="teacher_id">Enseignant<SpanRequired /></Label>
          <Select v-model="formData.teacher_id" required>
            <SelectTrigger class="!h-10 bg-white w-full">
              <SelectValue placeholder="Sélectionnez un enseignant" />
            </SelectTrigger>
            <SelectContent>
              <SelectItem v-for="teacher in teacherItems" :key="teacher.id" :value="Number(teacher.id)">
                {{ teacher.name }} {{ teacher.firstname }}
              </SelectItem>
            </SelectContent>
          </Select>
        </InputWrapper>

        <InputWrapper>
          <Label for="hourly_volume">Nombre d'heure<SpanRequired /></Label>
          <Input
            id="hourly_volume"
            v-model.number="formData.hourly_volume"
            type="number"
            min="1"
            placeholder="Nombre d'heure en heures"
            required
            class="w-full"
          />
        </InputWrapper>
      </FormSection>

      <FormSection
        title="Maximums par période et examen"
        class="grid sm:grid-cols-2 lg:grid-cols-3 gap-y-6 gap-x-10"
      >
        <InputWrapper>
          <Label for="max_period_1">Maximum Période 1</Label>
          <Input
            id="max_period_1"
            v-model.number="formData.max_period_1"
            type="number"
            min="0"
            placeholder="Maximum P1"
            class="w-full"
          />
        </InputWrapper>

        <InputWrapper>
          <Label for="max_period_2">Maximum Période 2</Label>
          <Input
            id="max_period_2"
            v-model.number="formData.max_period_2"
            type="text"
            min="0"
            placeholder="Maximum P2"
            :readonly="true"
            :tabindex="-1"
            aria-disabled="true"
            class="w-full"
          />
        </InputWrapper>

        <InputWrapper>
          <Label for="max_exam_1">Maximum Examen 1</Label>
          <Input
            id="max_exam_1"
            v-model.number="formData.max_exam_1"
            type="text"
            min="0"
            placeholder="Maximum E1"
            :readonly="true"
            :tabindex="-1"
            aria-disabled="true"
            class="w-full"
          />
        </InputWrapper>

        <InputWrapper>
          <Label for="max_period_3">Maximum Période 3</Label>
          <Input
            id="max_period_3"
            v-model.number="formData.max_period_3"
            type="text"
            min="0"
            placeholder="Maximum P3"
            :readonly="true"
            :tabindex="-1"
            aria-disabled="true"
            class="w-full"
          />
        </InputWrapper>

        <InputWrapper>
          <Label for="max_period_4">Maximum Période 4</Label>
          <Input
            id="max_period_4"
            v-model.number="formData.max_period_4"
            type="text"
            min="0"
            placeholder="Maximum P4"
            :readonly="true"
            :tabindex="-1"
            aria-disabled="true"
            class="w-full"
          />
        </InputWrapper>

        <InputWrapper>
          <Label for="max_exam_2">Maximum Examen 2</Label>
          <Input
            id="max_exam_2"
            v-model.number="formData.max_exam_2"
            type="text"
            min="0"
            placeholder="Maximum E2"
            :readonly="true"
            :tabindex="-1"
            aria-disabled="true"
            class="w-full"
          />
        </InputWrapper>
      </FormSection>

      <div class="flex items-center justify-end gap-2">
        <Button type="button" variant="outline" @click="handleCancel"> Annuler </Button>
        <Button type="submit" :disabled="loading || loadingPut">
          <span v-if="loading || loadingPut">{{
            isEditing ? 'Modification...' : 'Création...'
          }}</span>
          <span v-else>{{ isEditing ? 'Modifier le cours' : 'Créer le cours' }}</span>
        </Button>
      </div>
    </form>
  </DashFormLayout>
</template>
