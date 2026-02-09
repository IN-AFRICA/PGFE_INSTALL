import { ref, computed } from 'vue'
import { usePostApi } from '@/composables/usePostApi'
import { usePutApi } from '@/composables/usePutApi'
import { useAuthStore } from '@/stores/auth'
import { API_ROUTES } from '@/utils/constants/api_route'
import { showCustomToast } from '@/utils/widgets/custom_toast'
import { eventBus } from '@/utils/eventBus'
import type { PresenceStatus } from '@/components/molecules/presence'
import type { PersonPresence } from '@/models/person_presence'

export interface PresenceItem {
  id: number | string
  personnel_id: number
  personnel_name: string
  fonction_id: number | null
  presence: boolean
  absent_justified: boolean
  sick: boolean
  _status?: PresenceStatus
  _statusConfig?: { label: string; color: string }
}

export function usePresence() {
  const authStore = useAuthStore()
  const currentUserId = computed(() => authStore.user?.id || null)
  const userSchoolId = computed(() => authStore.user?.school_id || null)

  // API handlers
  const {
    error: postError,
    response: postResponse,
    postData,
  } = usePostApi<{
    data: string
    success: boolean
    message: string
  }>()

  const {
    postData: postInitialize,
    loading: initializingPresence,
    response: initializeResponse,
    error: initializeError,
  } = usePostApi<{ success: boolean; message: string; count: number; date: string }>()

  const {
    putData,
    error: putError,
    response: putResponse,
  } = usePutApi<{
    success: boolean
    message: string
    data: string
    date: string
  }>()

  // Loading state
  const loadingPersonnelId = ref<number | string | null>(null)
  const recentlyUpdated = ref<number | string | null>(null)

  // Helper to get status from boolean flags
  const getPresenceStatus = (item: {
    presence: boolean
    absent_justified: boolean
    sick: boolean
  }): PresenceStatus => {
    if (item.sick) return 'sick'
    if (item.absent_justified) return 'absent_justified'
    if (item.presence) return 'present'
    return 'absent'
  }

  // Helper to get status config
  const getPresenceConfig = (status: PresenceStatus) => {
    const configs: Record<PresenceStatus, { label: string; color: string }> = {
      present: { label: 'Présent', color: 'bg-green-100 text-green-800 border-green-200' },
      absent: { label: 'Absent Non Justifié', color: 'bg-red-100 text-red-800 border-red-200' },
      justified: {
        label: 'Absent Justifié',
        color: 'bg-orange-100 text-orange-800 border-orange-200',
      },
      sick: { label: 'Malade', color: 'bg-blue-100 text-blue-800 border-blue-200' },
    }
    return configs[status] || configs.absent
  }

  // Toggle presence
  async function togglePresence(presence: PersonPresence, value: boolean, date?: string) {
    const previous = {
      presence: presence.presence,
      absent_justified: presence.absent_justified,
      sick: presence.sick,
    }

    try {
      loadingPersonnelId.value = presence.id

      const payload: { presence: boolean; date?: string } = {
        presence: value,
      }

      if (date) {
        payload.date = date
      }

      await putData(API_ROUTES.UPDATE_PRESENCE(Number(presence.personnel_id)), payload)

      if (putError.value) {
        Object.assign(presence, previous)
        showCustomToast({ message: putError.value, type: 'error' })
        return
      }

      showCustomToast({
        message: putResponse.value?.message || 'Statut de présence mis à jour.',
        type: 'success',
      })

      recentlyUpdated.value = presence.id
      setTimeout(() => {
        recentlyUpdated.value = null
      }, 1500)

      eventBus.emit('presenceUpdated')
    } catch {
      Object.assign(presence, previous)
      showCustomToast({ message: "Une erreur inattendue s'est produite.", type: 'error' })
    } finally {
      loadingPersonnelId.value = null
    }
  }

  // Change status
  async function changeStatus(presence: PersonPresence, newStatus: PresenceStatus, date?: string) {
    const previous = {
      presence: presence.presence,
      absent_justified: presence.absent_justified,
      sick: presence.sick,
    }

    // Update local state
    presence.presence = newStatus === 'present'
    presence.absent_justified = newStatus === 'justified'
    presence.sick = newStatus === 'sick'

    try {
      loadingPersonnelId.value = presence.id

      const payload: {
        presence: boolean
        absent_justified: boolean
        sick: boolean
        initialize_all: boolean
        personnel_id: number
        date?: string
      } = {
        presence: presence.presence,
        absent_justified: presence.absent_justified,
        sick: presence.sick,
        initialize_all: false,
        personnel_id: Number(presence.personnel_id),
      }

      if (date) {
        payload.date = date
      }

      await putData(API_ROUTES.UPDATE_STATUS_PRESENCE(Number(presence.id)), payload)

      if (putError.value) {
        Object.assign(presence, previous)
        showCustomToast({ message: putError.value, type: 'error' })
        return
      }

      showCustomToast({
        message: putResponse.value?.message || 'Statut de présence mis à jour.',
        type: 'success',
      })

      recentlyUpdated.value = presence.id
      setTimeout(() => {
        recentlyUpdated.value = null
      }, 1500)

      eventBus.emit('presenceUpdated')
    } catch {
      Object.assign(presence, previous)
      showCustomToast({ message: "Une erreur inattendue s'est produite.", type: 'error' })
    } finally {
      loadingPersonnelId.value = null
    }
  }

  // Initialize presence
  async function initializePresence(date: string, onSuccess?: () => void) {
    try {
      let initUrl = API_ROUTES.INITIALISE_PRESENCE
      if (date) {
        initUrl += `?date=${date}`
      }

      await postInitialize(initUrl, {})

      if (initializeError.value) {
        showCustomToast({ message: initializeError.value, type: 'error' })
        return null
      }

      const result = initializeResponse.value
      const createdCount = result?.count || 0

      if (createdCount > 0) {
        showCustomToast({
          message: `Fiche de présence initialisée avec succès (${createdCount} personnel(s))`,
          type: 'success',
        })
      } else {
        showCustomToast({
          message: result?.message || 'Fiche de présence initialisée avec succès',
          type: 'success',
        })
      }

      onSuccess?.()
      return result
    } catch {
      showCustomToast({
        message: "Échec de l'initialisation de la fiche de présence",
        type: 'error',
      })
      return null
    }
  }

  return {
    loadingPersonnelId,
    recentlyUpdated,
    initializingPresence,
    getPresenceStatus,
    getPresenceConfig,
    togglePresence,
    changeStatus,
    initializePresence,
  }
}
