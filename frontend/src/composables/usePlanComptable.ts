import { computed, ref } from 'vue'
import { useGetApi } from '@/composables/useGetApi'
import { useDeleteApi } from '@/composables/useDeleteApi'
import { API_ROUTES } from '@/utils/constants/api_route'
import { showCustomToast } from '@/utils/widgets/custom_toast'
import { eventBus } from '@/utils/eventBus'
import type { Account } from '@/models/account'
import type { SubAccount } from '@/models/sub_account'

/**
 * Normalize API response to array format
 */
function normalizeList(raw: any): any[] {
  if (!raw) return []
  if (Array.isArray(raw)) return raw
  if (raw?.data && Array.isArray(raw.data)) return raw.data
  if (raw?.items && Array.isArray(raw.items)) return raw.items
  if (typeof raw === 'object')
    return Object.values(raw).filter((v: any) => v && typeof v === 'object')
  return []
}

/**
 * Composable for managing accounts (comptes)
 */
export function useAccounts() {
  const {
    data: accountsData,
    error: accountsError,
    loading: accountsLoading,
    meta: accountsMeta,
    fetchData: fetchAccounts,
  } = useGetApi<Account[]>(API_ROUTES.GET_COMPTE)

  const { deleting, errorDelete: deleteError, deleteItem } = useDeleteApi()

  const accounts = computed(() =>
    normalizeList(accountsData.value).map((a: any) => ({
      id: a.id,
      code: a.code,
      name: a.name ?? a.label ?? `Compte #${a.id}`,
      class_comptability_id: String(a.class_comptability_id ?? ''),
      category_comptability_id: String(
        a.category_comptability_id ?? a.categorie_comptability_id ?? '',
      ),
      user_id: String(a.user_id ?? ''),
      created_at: a.created_at ?? null,
      updated_at: a.updated_at ?? null,
      school_id: String(a.school_id ?? ''),
    })),
  )

  async function deleteAccount(id: number | string) {
    try {
      const url = (API_ROUTES.DELETE_COMPTE as any)(id)
      await deleteItem(url)
      if (deleteError.value) {
        showCustomToast({ message: deleteError.value as unknown as string, type: 'error' })
        return
      }
      showCustomToast({ message: 'Compte supprimé avec succès.', type: 'success' })
      eventBus.emit('planComptableUpdated')
    } catch (e) {
      showCustomToast({ message: 'Erreur lors de la suppression du compte.', type: 'error' })
    }
  }

  return {
    accounts,
    accountsLoading,
    accountsError,
    accountsMeta,
    fetchAccounts,
    deleteAccount,
    deleting,
  }
}

/**
 * Composable for managing sub-accounts (sous-comptes)
 */
export function useSubAccounts() {
  const {
    data: subAccountsData,
    error: subAccountsError,
    loading: subAccountsLoading,
    fetchData: fetchSubAccounts,
  } = useGetApi<SubAccount[]>(API_ROUTES.GET_SUB_COMPTE)

  const { deleting, errorDelete: deleteError, deleteItem } = useDeleteApi()

  async function deleteSubAccount(id: number | string) {
    try {
      const url = (API_ROUTES.DELETE_SUB_COMPTE as any)(id)
      await deleteItem(url)
      if (deleteError.value) {
        showCustomToast({ message: deleteError.value as unknown as string, type: 'error' })
        return
      }
      showCustomToast({ message: 'Sous-compte supprimé avec succès.', type: 'success' })
      eventBus.emit('subaccountComptableUpdated')
    } catch (e) {
      showCustomToast({ message: 'Erreur lors de la suppression du sous-compte.', type: 'error' })
    }
  }

  return {
    subAccountsData,
    subAccountsLoading,
    subAccountsError,
    fetchSubAccounts,
    deleteSubAccount,
    deleting,
  }
}

/**
 * Composable for managing classes and categories
 */
export function useClassesAndCategories() {
  const {
    data: classesData,
    loading: classesLoading,
    fetchData: fetchClasses,
  } = useGetApi<any[]>(API_ROUTES.GET_CLASS_COMPTE)

  const {
    data: categoriesData,
    loading: categoriesLoading,
    fetchData: fetchCategories,
  } = useGetApi<any[]>(API_ROUTES.GET_CATEGORIE_COMPTE)

  const classNameById = computed(() => {
    const list = normalizeList(classesData.value)
    const map = new Map<string, string>()
    list.forEach((c: any) => {
      const name = c.name ?? c.label ?? `Classe #${c.id}`
      map.set(String(c.id), name)
    })
    return map
  })

  const categoryNameById = computed(() => {
    const list = normalizeList(categoriesData.value)
    const map = new Map<string, string>()
    list.forEach((c: any) => {
      const name = c.name ?? c.label ?? `Catégorie #${c.id}`
      map.set(String(c.id), name)
    })
    return map
  })

  return {
    classesData,
    categoriesData,
    classesLoading,
    categoriesLoading,
    fetchClasses,
    fetchCategories,
    classNameById,
    categoryNameById,
  }
}
