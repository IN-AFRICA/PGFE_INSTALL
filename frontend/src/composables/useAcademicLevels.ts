import { computed, ref, type Ref, unref, watch } from 'vue'
import { API_ROUTES } from '@/utils/constants/api_route'
import { useGetApi } from '@/composables/useGetApi'

interface LevelOption {
  id: number
  name: string
  cycleName?: string
  filiereId?: number | string
  cycleId?: number | string
  label: string
}

export function useAcademicLevels(
  filiereId: Ref<number | string | undefined | null>,
  cycleId?: Ref<number | string | undefined | null>,
) {
  const loading = ref(false)
  const error = ref<string | null>(null)

  const {
    data: levelsData,
    fetchData,
    loading: apiLoading,
  } = useGetApi(API_ROUTES.GET_ACADEMIC_LEVELS)

  const rawLevels = computed(() => {
    const v: any = levelsData.value
    if (Array.isArray(v)) return v
    if (v?.data && Array.isArray(v.data)) return v.data
    if (v?.data?.data && Array.isArray(v.data.data)) return v.data.data
    return []
  })

  const filteredLevels = computed(() => {
    let list = rawLevels.value

    const fId = unref(filiereId)
    const cId = cycleId ? unref(cycleId) : null

    console.log('[useAcademicLevels] Filter Inputs:', { fId, cId, listLength: list.length })

    // Normalisation pour éviter les surprises (chaînes "undefined", "null", etc)
    const isValid = (val: any) =>
      val !== null &&
      val !== undefined &&
      String(val) !== '' &&
      String(val) !== 'undefined' &&
      String(val) !== 'null'

    if (isValid(cId)) {
      const filtered = list.filter((l: any) => String(l.cycle_id) === String(cId))
      console.log(`[useAcademicLevels] Filtered by cycleId(${cId}):`, filtered.length)
      return filtered
    }

    if (isValid(fId)) {
      const filtered = list.filter((l: any) => String(l.cycle?.filiaire_id) === String(fId))
      console.log(`[useAcademicLevels] Filtered by filiereId(${fId}):`, filtered.length)
      return filtered
    }

    console.log('[useAcademicLevels] No valid filter, returning all raw levels')
    return list
  })

  const levelOptions = computed<LevelOption[]>(() => {
    const list = filteredLevels.value

    if (cycleId && unref(cycleId)) {
      return list
        .map((l: any) => ({
          id: l.id,
          name: l.name,
          cycleId: l.cycle_id,
          label: l.name,
        }))
        .sort((a: any, b: any) => a.label.localeCompare(b.label))
    }

    return list
      .map((l: any) => {
        const cName = l.cycle?.name || 'Inconnu'
        return {
          id: l.id,
          name: l.name,
          cycleName: cName,
          cycleId: l.cycle_id,
          filiereId: l.cycle?.filiaire_id,
          label: `${l.name} (${cName})`,
        }
      })
      .sort((a: any, b: any) => {
        const cycleA = a.cycleName || ''
        const cycleB = b.cycleName || ''
        const cycleCompare = cycleA.localeCompare(cycleB)
        if (cycleCompare !== 0) return cycleCompare
        return a.label.localeCompare(b.label)
      })
  })

  async function loadLevels() {
    if (!rawLevels.value.length) {
      await fetchData()
    }
  }

  return {
    levels: filteredLevels,
    levelOptions,
    loading: apiLoading,
    loadLevels,
  }
}
