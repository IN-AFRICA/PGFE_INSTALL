<template>
  <DashFormLayout
    title="Informations de l'apprenant"
    link-back="/apprenants/operations"
    group-route="/apprenants/operations"
    module="students"
    :breadcrumb="[
      { label: 'Élèves', href: '/apprenants' },
      { label: 'Opérations', href: '/apprenants/operations' },
      { label: 'Informations Élève', href: '#' },
    ]"
  >
    <div
      v-if="isInitialLoading"
      class="flex gap-2 items-center justify-center py-20 bg-white h-full rounded-md text-gray-500"
    >
      <span class="iconify animate-spin hugeicons--loading-03 text-2xl"></span>
      <span>Chargement des données de l'apprenant...</span>
    </div>

    <div v-else-if="error" class="bg-red-50 border border-red-200 rounded-lg p-4 mb-6">
      <div class="flex items-center gap-2 text-red-800">
        <span class="iconify hugeicons--alert-circle"></span>
        <span>{{ error || "Erreur lors du chargement des données de l'apprenant" }}</span>
      </div>
      <Button variant="outline" class="mt-3" @click="fetchStudentData"> Réessayer </Button>
    </div>

    <div v-else-if="student" class="w-full flex flex-col space-y-8 animate-in fade-in duration-500">
      <!-- Section Informations personnelles -->
      <FormSection
        title="Informations personnelles"
        class="grid sm:grid-cols-2 lg:grid-cols-3 gap-y-6 gap-x-10"
      >
        <!-- Photo Section -->
        <InputWrapper class="lg:row-span-4">
          <label class="text-[10px] font-bold uppercase tracking-wider text-gray-500 mb-1 block"
            >Photo</label
          >
          <div
            class="relative w-full h-64 lg:h-80 bg-gray-50 rounded-lg border-2 border-dashed border-gray-200 shadow-sm overflow-hidden flex items-center justify-center"
          >
            <template v-if="photoSrc">
              <img
                :src="photoSrc"
                alt="Photo élève"
                class="absolute inset-0 w-full h-full object-cover transition-transform duration-500 hover:scale-105"
              />
            </template>
            <template v-else>
              <div class="flex flex-col items-center gap-2 text-gray-300">
                <span class="iconify hugeicons--user-01 text-5xl"></span>
                <span class="text-[10px] font-medium uppercase tracking-widest">Aucune photo</span>
              </div>
            </template>
          </div>
        </InputWrapper>

        <!-- Personal Info Columns -->
        <InputWrapper>
          <label class="text-[10px] font-bold uppercase tracking-wider text-gray-500 mb-1 block"
            >Matricule</label
          >
          <div
            class="h-10 px-3 flex items-center bg-gray-50 border border-gray-100 rounded-md text-sm font-semibold text-gray-900"
          >
            {{ (student as any)?.matricule || '—' }}
          </div>
        </InputWrapper>

        <InputWrapper>
          <label class="text-[10px] font-bold uppercase tracking-wider text-gray-500 mb-1 block"
            >Nom</label
          >
          <div
            class="h-10 px-3 flex items-center bg-white border border-gray-200 rounded-md text-sm font-medium text-gray-800"
          >
            {{ student?.name || '—' }}
          </div>
        </InputWrapper>

        <InputWrapper>
          <label class="text-[10px] font-bold uppercase tracking-wider text-gray-500 mb-1 block"
            >Post-nom</label
          >
          <div
            class="h-10 px-3 flex items-center bg-white border border-gray-200 rounded-md text-sm font-medium text-gray-800"
          >
            {{ student?.lastname || '—' }}
          </div>
        </InputWrapper>

        <InputWrapper>
          <label class="text-[10px] font-bold uppercase tracking-wider text-gray-500 mb-1 block"
            >Prénom</label
          >
          <div
            class="h-10 px-3 flex items-center bg-white border border-gray-200 rounded-md text-sm font-medium text-gray-800"
          >
            {{ student?.firstname || '—' }}
          </div>
        </InputWrapper>

        <InputWrapper>
          <label class="text-[10px] font-bold uppercase tracking-wider text-gray-500 mb-1 block"
            >Genre</label
          >
          <div
            class="h-10 px-3 flex items-center bg-white border border-gray-200 rounded-md text-sm font-medium text-gray-800"
          >
            {{ genderLabel || '—' }}
          </div>
        </InputWrapper>

        <InputWrapper>
          <label class="text-[10px] font-bold uppercase tracking-wider text-gray-500 mb-1 block"
            >Date de naissance</label
          >
          <div
            class="h-10 px-3 flex items-center bg-white border border-gray-200 rounded-md text-sm font-medium text-gray-800"
          >
            {{
              student?.birth_date ? new Date(student.birth_date).toLocaleDateString('fr-FR') : '—'
            }}
          </div>
        </InputWrapper>

        <InputWrapper>
          <label class="text-[10px] font-bold uppercase tracking-wider text-gray-500 mb-1 block"
            >Lieu de naissance</label
          >
          <div
            class="h-10 px-3 flex items-center bg-white border border-gray-200 rounded-md text-sm font-medium text-gray-800"
          >
            {{ student?.birth_place || '—' }}
          </div>
        </InputWrapper>

        <InputWrapper>
          <label class="text-[10px] font-bold uppercase tracking-wider text-gray-500 mb-1 block"
            >État civil</label
          >
          <div
            class="h-10 px-3 flex items-center bg-white border border-gray-200 rounded-md text-sm font-medium text-gray-800"
          >
            {{ student?.civil_status || '—' }}
          </div>
        </InputWrapper>

        <InputWrapper>
          <label class="text-[10px] font-bold uppercase tracking-wider text-gray-500 mb-1 block"
            >Numéro Permanent</label
          >
          <div
            class="h-10 px-3 flex items-center bg-white border border-gray-200 rounded-md text-sm font-medium text-gray-800"
          >
            {{ identityCard || '—' }}
          </div>
        </InputWrapper>
      </FormSection>

      <div class="w-full h-px bg-gray-200 shadow-sm"></div>

      <!-- Section Informations parents -->
      <FormSection
        title="Informations parents"
        class="grid sm:grid-cols-2 lg:grid-cols-3 gap-y-6 gap-x-10"
      >
        <InputWrapper>
          <label class="text-[10px] font-bold uppercase tracking-wider text-gray-500 mb-1 block"
            >Parent Principal</label
          >
          <div
            class="h-10 px-3 flex items-center bg-white border border-gray-200 rounded-md text-sm font-medium text-gray-800"
          >
            {{ resolveParentName((student as any)?.parents_id) || '—' }}
          </div>
        </InputWrapper>

        <InputWrapper>
          <label class="text-[10px] font-bold uppercase tracking-wider text-gray-500 mb-1 block"
            >Téléphone</label
          >
          <div
            class="h-10 px-3 flex items-center bg-white border border-gray-200 rounded-md text-sm font-medium text-gray-800"
          >
            {{ student?.phone_number ?? '—' }}
          </div>
        </InputWrapper>

        <InputWrapper>
          <label class="text-[10px] font-bold uppercase tracking-wider text-gray-500 mb-1 block"
            >Email</label
          >
          <div
            class="h-10 px-3 flex items-center bg-white border border-gray-200 rounded-md text-sm font-medium text-gray-800"
          >
            {{ student?.email ?? '—' }}
          </div>
        </InputWrapper>

        <!-- Parent 2 -->
        <template v-if="(student as any)?.parent2_id">
          <InputWrapper>
            <label class="text-[10px] font-bold uppercase tracking-wider text-gray-500 mb-1 block"
              >Parent 2</label
            >
            <div
              class="h-10 px-3 flex items-center bg-white border border-gray-200 rounded-md text-sm font-medium text-gray-800"
            >
              {{ resolveParentName((student as any)?.parent2_id) || '—' }}
            </div>
          </InputWrapper>
          <InputWrapper>
            <label class="text-[10px] font-bold uppercase tracking-wider text-gray-500 mb-1 block"
              >Téléphone Parent 2</label
            >
            <div
              class="h-10 px-3 flex items-center bg-white border border-gray-200 rounded-md text-sm font-medium text-gray-800"
            >
              {{ (student as any)?.phone_number_2 ?? '—' }}
            </div>
          </InputWrapper>
          <InputWrapper>
            <label class="text-[10px] font-bold uppercase tracking-wider text-gray-500 mb-1 block"
              >Email Parent 2</label
            >
            <div
              class="h-10 px-3 flex items-center bg-white border border-gray-200 rounded-md text-sm font-medium text-gray-800"
            >
              {{ (student as any)?.email_2 ?? '—' }}
            </div>
          </InputWrapper>
        </template>

        <!-- Parent 3 -->
        <template v-if="(student as any)?.parent3_id">
          <InputWrapper>
            <label class="text-[10px] font-bold uppercase tracking-wider text-gray-500 mb-1 block"
              >Parent 3</label
            >
            <div
              class="h-10 px-3 flex items-center bg-white border border-gray-200 rounded-md text-sm font-medium text-gray-800"
            >
              {{ resolveParentName((student as any)?.parent3_id) || '—' }}
            </div>
          </InputWrapper>
          <InputWrapper>
            <label class="text-[10px] font-bold uppercase tracking-wider text-gray-500 mb-1 block"
              >Téléphone Parent 3</label
            >
            <div
              class="h-10 px-3 flex items-center bg-white border border-gray-200 rounded-md text-sm font-medium text-gray-800"
            >
              {{ (student as any)?.phone_number_3 ?? '—' }}
            </div>
          </InputWrapper>
          <InputWrapper>
            <label class="text-[10px] font-bold uppercase tracking-wider text-gray-500 mb-1 block"
              >Email Parent 3</label
            >
            <div
              class="h-10 px-3 flex items-center bg-white border border-gray-200 rounded-md text-sm font-medium text-gray-800"
            >
              {{ (student as any)?.email_3 ?? '—' }}
            </div>
          </InputWrapper>
        </template>
      </FormSection>

      <div class="w-full h-px bg-gray-200 shadow-sm"></div>

      <!-- Section Informations utiles (Géographie & Scolarité) -->
      <FormSection
        title="Informations utiles"
        class="grid sm:grid-cols-2 lg:grid-cols-3 gap-y-6 gap-x-10"
      >
        <InputWrapper>
          <label class="text-[10px] font-bold uppercase tracking-wider text-gray-500 mb-1 block"
            >Pays</label
          >
          <div
            class="h-10 px-3 flex items-center bg-white border border-gray-200 rounded-md text-sm font-medium text-gray-800"
          >
            {{ resolveCountryName((student as any)?.country_id) || '—' }}
          </div>
        </InputWrapper>

        <InputWrapper>
          <label class="text-[10px] font-bold uppercase tracking-wider text-gray-500 mb-1 block"
            >Province</label
          >
          <div
            class="h-10 px-3 flex items-center bg-white border border-gray-200 rounded-md text-sm font-medium text-gray-800"
          >
            {{ resolveProvinceName((student as any)?.province_id) || '—' }}
          </div>
        </InputWrapper>

        <InputWrapper>
          <label class="text-[10px] font-bold uppercase tracking-wider text-gray-500 mb-1 block"
            >Ville / Territoire</label
          >
          <div
            class="h-10 px-3 flex items-center bg-white border border-gray-200 rounded-md text-sm font-medium text-gray-800"
          >
            {{ resolveTerritoryName((student as any)?.territory_id) || '—' }}
          </div>
        </InputWrapper>

        <InputWrapper>
          <label class="text-[10px] font-bold uppercase tracking-wider text-gray-500 mb-1 block"
            >Commune</label
          >
          <div
            class="h-10 px-3 flex items-center bg-white border border-gray-200 rounded-md text-sm font-medium text-gray-800"
          >
            {{ resolveCommuneName((student as any)?.commune_id) || '—' }}
          </div>
        </InputWrapper>

        <InputWrapper class="sm:col-span-2">
          <label class="text-[10px] font-bold uppercase tracking-wider text-gray-500 mb-1 block"
            >Adresse</label
          >
          <div
            class="h-10 px-3 flex items-center bg-white border border-gray-200 rounded-md text-sm font-medium text-gray-800"
          >
            {{ student?.address || '—' }}
          </div>
        </InputWrapper>

        <InputWrapper>
          <label class="text-[10px] font-bold uppercase tracking-wider text-gray-500 mb-1 block"
            >Section</label
          >
          <div
            class="h-10 px-3 flex items-center bg-white border border-gray-200 rounded-md text-sm font-medium text-gray-800"
          >
            {{ filiereName || '—' }}
          </div>
        </InputWrapper>

        <InputWrapper>
          <label class="text-[10px] font-bold uppercase tracking-wider text-gray-500 mb-1 block"
            >Cycle</label
          >
          <div
            class="h-10 px-3 flex items-center bg-white border border-gray-200 rounded-md text-sm font-medium text-gray-800"
          >
            {{ cycleName || '—' }}
          </div>
        </InputWrapper>

        <InputWrapper>
          <label class="text-[10px] font-bold uppercase tracking-wider text-gray-500 mb-1 block"
            >Niveau scolaire</label
          >
          <div
            class="h-10 px-3 flex items-center bg-white border border-gray-200 rounded-md text-sm font-medium text-gray-800"
          >
            {{ academicLevelName || '—' }}
          </div>
        </InputWrapper>

        <InputWrapper>
          <label class="text-[10px] font-bold uppercase tracking-wider text-gray-500 mb-1 block"
            >Classe</label
          >
          <div
            class="h-10 px-3 flex items-center bg-white border border-gray-200 rounded-md text-sm font-medium text-gray-800"
          >
            {{ classroomName || '—' }}
          </div>
        </InputWrapper>

        <InputWrapper class="sm:col-span-3">
          <label class="text-[10px] font-bold uppercase tracking-wider text-gray-500 mb-1 block"
            >Note / Commentaire</label
          >
          <div
            class="min-h-[5rem] p-3 bg-white border border-gray-200 rounded-md text-sm text-gray-800 leading-relaxed"
          >
            {{ student?.note || '—' }}
          </div>
        </InputWrapper>
      </FormSection>

      <div class="w-full h-px bg-gray-200 shadow-sm"></div>

      <!-- Section Inscription -->
      <FormSection title="Inscription" class="grid sm:grid-cols-2 lg:grid-cols-3 gap-y-6 gap-x-10">
        <InputWrapper>
          <label class="text-[10px] font-bold uppercase tracking-wider text-gray-500 mb-1 block"
            >École de provenance</label
          >
          <div
            class="h-10 px-3 flex items-center bg-white border border-gray-200 rounded-md text-sm font-medium text-gray-800"
          >
            {{ previousSchoolDisplay || '—' }}
          </div>
        </InputWrapper>

        <InputWrapper>
          <label class="text-[10px] font-bold uppercase tracking-wider text-gray-500 mb-1 block"
            >Pourcentage obtenu</label
          >
          <div
            class="h-10 px-3 flex items-center bg-white border border-gray-200 rounded-md text-sm font-medium text-gray-800"
          >
            {{
              typeof percentageObtainedDisplay === 'number'
                ? percentageObtainedDisplay.toFixed(2) + ' %'
                : '—'
            }}
          </div>
        </InputWrapper>

        <InputWrapper>
          <label class="text-[10px] font-bold uppercase tracking-wider text-gray-500 mb-1 block"
            >Année scolaire d'inscription</label
          >
          <div
            class="h-10 px-3 flex items-center bg-white border border-gray-200 rounded-md text-sm font-medium text-gray-800"
          >
            {{ schoolYearName || '—' }}
          </div>
        </InputWrapper>
      </FormSection>

      <div class="w-full h-px bg-gray-200 shadow-sm"></div>

      <div class="flex items-center justify-end gap-3">
        <Button
          variant="outline"
          type="button"
          class="h-10 px-6 rounded-md shadow-sm transition-all hover:bg-gray-50"
          as-child
        >
          <RouterLink to="/apprenants/operations">
            <span class="flex iconify hugeicons--arrow-left-02 mr-2"></span>
            Retour à la liste
          </RouterLink>
        </Button>
        <Button
          variant="outline"
          class="h-10 px-6 rounded-md shadow-sm transition-all hover:bg-gray-50"
          as-child
        >
          <RouterLink :to="`/apprenants/operations/edition-eleve/${studentId}`">
            <span class="iconify hugeicons--edit-02 mr-2"></span>
            Modifier
          </RouterLink>
        </Button>
        <Button
          type="button"
          class="h-10 px-6 rounded-md shadow-md transition-all active:scale-95"
          as-child
        >
          <RouterLink :to="`/apprenants/rapports/bulletin-scolaire`">
            <span class="iconify hugeicons--file-02 mr-2 text-lg"></span>
            Bulletin Scolaire
          </RouterLink>
        </Button>
      </div>
    </div>

    <div
      v-else
      class="flex flex-col items-center justify-center py-20 bg-white rounded-md text-gray-500"
    >
      <span class="iconify hugeicons--user-question text-4xl mb-2"></span>
      <span>Apprenant non trouvé</span>
      <Button variant="outline" class="mt-4" as-child>
        <RouterLink to="/apprenants/operations"> Retour à la liste </RouterLink>
      </Button>
    </div>
  </DashFormLayout>
</template>

<script setup lang="ts">
import { ref, onMounted, onBeforeUnmount, computed } from 'vue'
import { useRoute } from 'vue-router'
import { Button } from '@/components/ui/button'
import { Label } from '@/components/ui/label'
import FormSection from '@/components/atoms/FormSection.vue'
import InputWrapper from '@/components/atoms/InputWrapper.vue'
import SpanRequired from '@/components/atoms/SpanRequired.vue'
import DashFormLayout from '@/components/templates/DashFormLayout.vue'
import { useGetApi } from '@/composables/useGetApi'
import api from '@/services/api'
import { API_ROUTES, BASE_URL, SANCTUM_BASE_URL } from '@/utils/constants/api_route'
import SchoolYearService from '@/services/SchoolYearService'
import FiliaireService from '@/services/FiliaireService'
import ClassroomService from '@/services/ClassroomService'

const route = useRoute()
const studentId = route.params.id as string

const endpoint =
  API_ROUTES.GET_STUDENT_BY_ID?.replace(':student', studentId) || `/api/students/${studentId}`
const { data: student, loading, error, fetchData } = useGetApi(endpoint)
const isInitialLoading = ref(false)

const fetchStudentData = async () => {
  try {
    const [studentRes, registrationRes] = await Promise.all([
      api.get(API_ROUTES.GET_STUDENT_BY_ID.replace(':student', studentId)),
      api.get(`${API_ROUTES.GET_STUDENT_REGISTRATIONS}?student_id=${studentId}`),
    ])

    // Student data
    const studentData = studentRes.data.student || studentRes.data
    student.value = studentData

    // Registration data (use manually fetched data to ensure it's available for computed)
    const rawRegs = registrationRes.data
    registrationsData.value = Array.isArray(rawRegs)
      ? rawRegs
      : rawRegs && Array.isArray(rawRegs.data)
        ? rawRegs.data
        : []
  } catch (err) {
    console.error("Erreur lors de la récupération des données de l'apprenant :", err)
  }
}

const calculateAge = (birthDate: string): number => {
  const today = new Date()
  const birth = new Date(birthDate)
  let age = today.getFullYear() - birth.getFullYear()
  const monthDiff = today.getMonth() - birth.getMonth()

  if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birth.getDate())) {
    age--
  }

  return age
}

const anneesScolaires = ref<{ id: number | string; label: string }[]>([])
const filieres = ref<{ id: number | string; name: string }[]>([])
const classrooms = ref<{ id: number | string; name: string }[]>([])

const getAllSchoolYear = async () => {
  try {
    const res = await SchoolYearService.getAllSchoolYear()
    if (res)
      anneesScolaires.value = res?.data?.years?.map((y: any) => ({ id: y.id, label: y.name })) || []
  } catch {}
}

const getAllFiliaire = async () => {
  try {
    const res = await FiliaireService.getAllFiliaire()
    if (res) filieres.value = res?.data?.data?.map((f: any) => ({ id: f.id, name: f.name })) || []
  } catch {}
}

const getAllClassroom = async () => {
  try {
    const res = await ClassroomService.getAllClassroom()
    if (res) classrooms.value = res?.data?.data?.map((c: any) => ({ id: c.id, name: c.name })) || []
  } catch {}
}

// =================== Listes via API_ROUTES (Geo, Parents, Academic Levels) ===================
const { data: countriesData, fetchData: fetchCountries } = useGetApi(API_ROUTES.GET_COUNTRIES)
const { data: provincesData, fetchData: fetchProvinces } = useGetApi(API_ROUTES.GET_PROVINCES)
const { data: territoriesData, fetchData: fetchTerritories } = useGetApi(API_ROUTES.GET_TERRITORIES)
const { data: communesData, fetchData: fetchCommunes } = useGetApi(API_ROUTES.GET_COMMUNES)
const { data: parentsData, fetchData: fetchParents } = useGetApi(API_ROUTES.GET_PARENTS)
const { data: academicLevelsData, fetchData: fetchAcademicLevels } = useGetApi(
  API_ROUTES.GET_ACADEMIC_LEVELS,
)

// Inscriptions
const { data: registrationsData, fetchData: fetchRegistrations } = useGetApi(
  API_ROUTES.GET_STUDENT_REGISTRATIONS,
)

onMounted(async () => {
  isInitialLoading.value = true
  try {
    await Promise.all([
      getAllSchoolYear(),
      getAllFiliaire(),
      getAllClassroom(),
      fetchCountries(),
      fetchProvinces(),
      fetchTerritories(),
      fetchCommunes(),
      fetchParents(),
      fetchAcademicLevels(),
      fetchStudentData(),
    ])
  } finally {
    isInitialLoading.value = false
  }
})

// Helpers de résolution
function resolveSchoolYearName(id?: string | number): string | undefined {
  if (!id) return undefined
  return anneesScolaires.value.find((a) => String(a.id) === String(id))?.label
}
function resolveFiliereName(id?: string | number): string | undefined {
  if (!id) return undefined
  return filieres.value.find((f) => String(f.id) === String(id))?.name
}
function resolveClassroomName(id?: string | number): string | undefined {
  if (!id) return undefined
  return classrooms.value.find((c) => String(c.id) === String(id))?.name
}

function resolveAcademicLevelName(id?: string | number): string | undefined {
  if (!id) return undefined
  const list = (academicLevelsData as any)?.value || []
  const found = list.find((lvl: any) => String(lvl.id) === String(id))
  return found?.name || found?.label
}

function resolveCommuneName(id?: string | number): string | undefined {
  if (!id) return undefined
  const list = communesData?.value || []
  return list.find((c: any) => String(c.id) === String(id))?.name
}

function resolveParentName(id?: string | number): string | undefined {
  if (!id) return undefined
  const list = parentsData?.value || []
  const found = list.find((p: any) => String(p.id) === String(id))
  return found?.name
}

function resolveCountryName(id?: string | number): string | undefined {
  if (!id) return undefined
  const list = countriesData?.value || []
  return list.find((c: any) => String(c.id) === String(id))?.name
}

function resolveProvinceName(id?: string | number): string | undefined {
  if (!id) return undefined
  const list = provincesData?.value || []
  return list.find((c: any) => String(c.id) === String(id))?.name
}

function resolveTerritoryName(id?: string | number): string | undefined {
  if (!id) return undefined
  const list = territoriesData?.value || []
  return list.find((c: any) => String(c.id) === String(id))?.name
}

const currentRegistration = computed(() => {
  const raw = (registrationsData as any)?.value
  const regs = Array.isArray(raw) ? raw : raw && Array.isArray(raw.data) ? raw.data : undefined
  const st = (student as any)?.value
  if (!regs || !st) return null
  const forStudent = regs.filter((r) => String(r.student_id) === String(st.id))
  if (forStudent.length === 0) return null
  // Prefer active registrations, then latest by registration_date or created_at
  const byDate = (a: any, b: any) => {
    const da = new Date(a.registration_date || a.created_at || 0).getTime()
    const db = new Date(b.registration_date || b.created_at || 0).getTime()
    return db - da
  }
  const active = forStudent.filter((r) => r.registration_status === true).sort(byDate)
  if (active.length) return active[0]
  return forStudent.sort(byDate)[0]
})

// Préférer les noms directs si fournis par l'API des inscriptions
const classroomName = computed(() => {
  const reg = (currentRegistration as any)?.value
  return reg?.classroom_name || reg?.classroom || resolveClassroomName(reg?.classroom_id)
})
const academicLevelName = computed(() => {
  const reg = (currentRegistration as any)?.value
  return (
    reg?.academic_level_name ||
    reg?.academic_level ||
    reg?.level ||
    resolveAcademicLevelName(reg?.academic_level_id)
  )
})
const schoolYearName = computed(() => {
  const reg: any = (currentRegistration as any)?.value
  if (!reg) return undefined
  // Try direct name strings first
  const direct = reg.school_year_name || reg.school_year || reg.year_name || reg.year
  if (direct) return direct
  // Try nested objects { school_year: { name } } or { year: { name } }
  const nested = reg.school_year?.name || reg.year?.name || reg.schoolYear?.name
  if (nested) return nested
  // Try resolving by common ID variants
  const id = reg.school_year_id || reg.school_years_id || reg.year_id || reg.schoolYearId
  return resolveSchoolYearName(id)
})

// Mapping des libellés du genre (si le backend renvoie un code)
const genderLabel = computed(() => {
  const g = (student as any)?.value?.gender
  if (!g) return undefined
  const map: Record<string, string> = {
    M: 'Masculin',
    F: 'Féminin',
    Masculin: 'Masculin',
    Féminin: 'Féminin',
    Feminin: 'Féminin',
  }
  return map[g] || g
})

// Identity card resolver (handles multiple possible backend field names)
const identityCard = computed(() => {
  const s: any = (student as any)?.value || {}
  return (
    s.identity_card ||
    s.identity_card_number ||
    s.id_card ||
    s.identity_card_num ||
    s.id_card_num ||
    s.matricule_permanent ||
    s.id_card_number ||
    s.card_number ||
    s.national_id ||
    s.cni ||
    undefined
  )
})

// Enrollment fields (support both camelCase and snake_case)
const previousSchoolDisplay = computed(() => {
  const s: any = (student as any)?.value || {}
  const reg: any = (currentRegistration as any)?.value || {}
  return reg.previousSchool ?? reg.previous_school ?? s.previousSchool ?? s.previous_school
})
const percentageObtainedDisplay = computed(() => {
  const s: any = (student as any)?.value || {}
  const reg: any = (currentRegistration as any)?.value || {}
  const raw =
    reg.percentageObtained ??
    reg.percentage_obtained ??
    s.percentageObtained ??
    s.percentage_obtained
  if (raw === '' || raw === null || raw === undefined) return undefined
  const num = Number(raw)
  return Number.isFinite(num) ? num : undefined
})

// Photo source resolver supporting multiple shapes and relative URLs
const photoSrc = computed(() => {
  const s: any = (student as any)?.value || {}
  let raw: string | undefined = s.image || s.image_url || s.photo || s.avatar || s.picture
  if (!raw || typeof raw !== 'string') return undefined
  const lower = raw.toLowerCase()
  // base64 or absolute
  if (
    lower.startsWith('data:image') ||
    lower.startsWith('http://') ||
    lower.startsWith('https://')
  ) {
    return raw
  }
  if (raw.startsWith('//')) return `https:${raw}`
  if (raw.startsWith('/')) return `${SANCTUM_BASE_URL}${raw}`
  if (raw.includes('storage')) return `${SANCTUM_BASE_URL}/${raw.replace(/^\/?/, '')}`
  return `${BASE_URL}/${raw.replace(/^\/?/, '')}`
})

// Compléments scolaires (filière, cycle, personnel)
const filiereName = computed(() => {
  // essayer via registration puis via résolveur
  const reg = (currentRegistration as any)?.value
  return reg?.filiaire_name || resolveFiliereName((student as any)?.value?.filiaire_id)
})
const cycleName = computed(() => {
  const reg = (currentRegistration as any)?.value
  return reg?.cycle_name || (student as any)?.value?.cycle_name
})
const academicPersonalName = computed(() => {
  const reg = (currentRegistration as any)?.value
  return reg?.academic_personal_name || (student as any)?.value?.academic_personal_name
})

// Image en lecture seule: pas d'upload ni d'aperçu local
onBeforeUnmount(() => {})
</script>

<style scoped>
/* Custom styles if needed */
</style>
