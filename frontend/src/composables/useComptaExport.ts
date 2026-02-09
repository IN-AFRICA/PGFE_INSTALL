import { useFileExport } from '@/composables/useFileExport'
import { API_ROUTES } from '@/utils/constants/api_route'

/**
 * Composable for exporting accounts
 */
export function useAccountsExport() {
  const { loading, exportMultiFormat } = useFileExport()

  const exportAccounts = async (format: 'pdf' | 'excel' = 'excel') => {
    await exportMultiFormat(
      API_ROUTES.EXPORT_ACCOUNTS_PDF,
      API_ROUTES.EXPORT_ACCOUNTS_EXCEL,
      format,
      'accounts',
    )
  }

  return {
    loading,
    exportAccounts,
  }
}

/**
 * Composable for exporting sub-accounts
 */
export function useSubAccountsExport() {
  const { loading, exportMultiFormat } = useFileExport()

  const exportSubAccounts = async (format: 'pdf' | 'excel' = 'excel') => {
    await exportMultiFormat(
      API_ROUTES.EXPORT_SUB_ACCOUNTS_PDF,
      API_ROUTES.EXPORT_SUB_ACCOUNTS_EXCEL,
      format,
      'subaccounts',
    )
  }

  return {
    loading,
    exportSubAccounts,
  }
}
