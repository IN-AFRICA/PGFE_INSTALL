import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import api from '@/services/api'
import { API_ROUTES } from '@/utils/constants/api_route'
import { useAuthStore } from '@/stores/auth'
import type { BulletinNotes, Grade, Ecole } from '@/types/BulletinType'

interface Classe {
  id: string | number
  name: string
  niveau?: string
  filiere?: string
  anneeScolaire?: string
}

interface Eleve {
  id: string | number
  full_name: string
  lastname?: string
  firstname?: string
  matricule?: string
}

export const useBulletinStore = defineStore('bulletin', () => {
  const authStore = useAuthStore()

  // State
  const rawClasses = ref<Classe[]>([])
  const eleves = ref<Eleve[]>([])
  const schoolYears = ref<any[]>([])
  const selectedClasseId = ref<string>('')
  const selectedEleveId = ref<string>('')
  const selectedSchoolYearId = ref<string>('')
  const selectedFiliereId = ref<string>('all')
  const selectedLevelId = ref<string>('all')
  const selectedPeriod = ref<string>('') // P1, P2, E1, S1, E2, S2, etc.
  const bulletinNotes = ref<BulletinNotes | null>(null)
  const currentEcole = ref<Ecole | null>(null)
  const isLoading = ref(false)
  const error = ref<string | null>(null)

  // Getters
  const classes = computed(() => {
    let list = rawClasses.value

    if (selectedFiliereId.value && selectedFiliereId.value !== 'all') {
      list = list.filter((c: any) => {
        const classFiliereId = c?.academic_level?.cycle?.filiaire?.id
        return String(classFiliereId) === String(selectedFiliereId.value)
      })
    }

    if (selectedLevelId.value && selectedLevelId.value !== 'all') {
      list = list.filter((c: any) => String(c.academic_level_id) === String(selectedLevelId.value))
    }
    return list
  })

  const selectedClasse = computed(() => {
    return rawClasses.value.find((c) => String(c.id) === String(selectedClasseId.value)) || null
  })

  const selectedEleve = computed(() => {
    return eleves.value.find((e) => String(e.id) === String(selectedEleveId.value)) || null
  })

  // Helper pour le numéro permanent (tableau de chiffres pour affichage)
  const getNumeroPermanentArray = computed(() => {
    const matricule = bulletinNotes.value?.student?.matricule || ''
    return matricule.split('').slice(0, 14)
  })

  // Actions
  async function loadClassesByEcole() {
    const schoolId = authStore.user?.school_id
    if (!schoolId) {
      error.value = 'Aucune école associée à cet utilisateur'
      return
    }

    isLoading.value = true
    error.value = null

    try {
      const response = await api.get(API_ROUTES.GET_CLASSROOMS, {
        params: { school_id: schoolId, per_page: 100 }, // Charger plus d'items par page
      })

      // L'API retourne les classes directement dans response.data.data (tableau)
      // Format: { success: true, message: "...", data: [...classes] }
      const classroomArray = Array.isArray(response.data.data)
        ? response.data.data
        : response.data.data?.data || []

      console.log('Classes chargées:', classroomArray.length)

      // Filtrer les valeurs nulles/undefined et s'assurer que chaque classe a un ID
      // Et mapper les propriétés imbriquées vers le niveau supérieur pour faciliter l'accès
      rawClasses.value = classroomArray
        .filter((c: any) => c && c.id)
        .map((c: any) => ({
          ...c,
          niveau: c.academic_level?.name,
          // Normaliser le nom de la filière pour la comparaison (minuscules)
          filiere: c.academic_level?.cycle?.filiaire?.name?.toLowerCase(),
        }))

      // Récupérer les infos de l'école depuis la première classe si disponible
      if (classroomArray[0]?.academic_level?.cycle?.filiaire?.school_id) {
        // Initialiser les infos de l'école
        await loadSchoolInfo()
      }
    } catch (err: any) {
      error.value = err.message || 'Erreur lors du chargement des classes'
      console.error('Erreur loadClassesByEcole:', err)
    } finally {
      isLoading.value = false
    }
  }

  async function loadSchoolInfo() {
    const schoolId = authStore.user?.school_id || authStore.userSchoolId
    if (!schoolId) return

    try {
      const response = await api.get(API_ROUTES.GET_SCHOOLS)
      const schools = response.data?.data || []
      const mySchool = schools.find((s: any) => String(s.id) === String(schoolId))

      if (mySchool) {
        currentEcole.value = mySchool
      }
    } catch (err) {
      console.error('Erreur chargement info école', err)
    }
  }

  async function loadElevesByClasse(classeId: string | number) {
    if (!classeId) return

    isLoading.value = true
    error.value = null

    try {
      const response = await api.get(API_ROUTES.GET_STUDENT_REGISTRATIONS, {
        params: { classroom_id: classeId },
      })
      const registrations = response.data.data || response.data || []
      eleves.value = registrations
        .filter((reg: any) => reg && reg.student && reg.student.id)
        .map((reg: any) => ({
          id: reg.student.id,
          full_name:
            reg.student.full_name ||
            `${reg.student.firstname || ''} ${reg.student.lastname || ''}`.trim() ||
            'Sans nom',
          lastname: reg.student.lastname,
          firstname: reg.student.firstname,
          matricule: reg.student.matricule,
        }))
    } catch (err: any) {
      error.value = err.message || 'Erreur lors du chargement des élèves'
      console.error('Erreur loadElevesByClasse:', err)
    } finally {
      isLoading.value = false
    }
  }

  async function loadSchoolYears() {
    const schoolId = authStore.user?.school_id || 1 // Fallback temporaire si authStore n'est pas prêt, mais idéalement il devrait l'être
    // Si authStore n'a pas school_id, on ne peut pas charger les années spécifiques à l'école sans school_id
    if (!schoolId && !authStore.user) return

    try {
      const response = await api.get(API_ROUTES.GET_SCHOOL_YEARS, {
        params: { school_id: schoolId, per_page: 100 },
      })

      // Gestion robuste de la structure de réponse
      let data = []
      if (response.data?.years) {
        // Structure spécifique vue dans les logs: { success, message, years: [...] }
        data = response.data.years
      } else if (response.data?.data?.data) {
        // Pagination Laravel (ResourceCollection)
        data = response.data.data.data
      } else if (Array.isArray(response.data?.data)) {
        // Wrapper 'data' simple
        data = response.data.data
      } else if (Array.isArray(response.data)) {
        // Tableau direct
        data = response.data
      }

      schoolYears.value = data

      // Sélectionner l'année active par défaut
      const activeYear = schoolYears.value.find(
        (y: any) => y.is_active || y.active || y.status === 'active' || y.encours === 1,
      )

      // Sélectionner l'année active par défaut

      if (activeYear && !selectedSchoolYearId.value) {
        selectedSchoolYearId.value = String(activeYear.id)
      }
    } catch (err: any) {
      console.error('Erreur loadSchoolYears:', err)
    }
  }

  async function fetchBulletinData(studentId: string | number) {
    if (!studentId) return

    isLoading.value = true
    error.value = null

    try {
      const params: any = { student_id: studentId }

      // Ajouter les filtres si définis
      if (selectedSchoolYearId.value) {
        params.school_year_id = selectedSchoolYearId.value
      }
      if (selectedPeriod.value) {
        params.period = selectedPeriod.value
      }

      const response = await api.get(API_ROUTES.GET_BULLETIN_JSON, { params })
      const responseData = response.data?.data || response.data
      bulletinNotes.value = responseData

      // Mise à jour de l'école depuis les données du bulletin : DESACTIVÉ pour éviter d'écraser les infos complètes avec des partielles
      // if (responseData?.registration?.school) {
      //   currentEcole.value = responseData.registration.school
      // }
    } catch (err: any) {
      error.value = err.message || 'Erreur lors du chargement du bulletin'
      console.error('Erreur fetchBulletinData:', err)
      bulletinNotes.value = null
    } finally {
      isLoading.value = false
    }
  }

  function selectClasse(classeId: string) {
    selectedClasseId.value = classeId
    selectedEleveId.value = ''
    bulletinNotes.value = null
    if (classeId) {
      loadElevesByClasse(classeId)
    }
  }

  function selectEleve(eleveId: string) {
    selectedEleveId.value = eleveId
    if (eleveId) {
      fetchBulletinData(eleveId)
    }
  }

  function resetState() {
    rawClasses.value = []
    eleves.value = []
    selectedClasseId.value = ''
    selectedEleveId.value = ''
    bulletinNotes.value = null
    currentEcole.value = null
    error.value = null
  }

  return {
    // State
    classes,
    rawClasses,
    eleves,
    schoolYears,
    selectedClasseId,
    selectedEleveId,
    selectedSchoolYearId,
    selectedPeriod,
    selectedFiliereId,
    selectedLevelId,
    bulletinNotes,
    currentEcole,
    isLoading,
    error,

    // Getters
    selectedClasse,
    selectedEleve,
    getNumeroPermanentArray,

    // Actions
    loadClassesByEcole,
    loadSchoolYears,
    loadSchoolInfo,
    loadElevesByClasse,
    fetchBulletinData,
    selectClasse,
    selectEleve,
    resetState,
  }
})
