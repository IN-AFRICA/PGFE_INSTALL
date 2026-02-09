import { ref } from 'vue'
import api from '@/services/api'
import { downloadBlob, extractFilenameFromHeaders } from '@/utils/utils'
import { showCustomToast } from '@/utils/widgets/custom_toast'

export interface ExportOptions {
  endpoint: string
  defaultFilename?: string
  format?: 'pdf' | 'excel'
  successMessage?: string
  errorMessage?: string
  timeout?: number
}

/**
 * Composable pour gérer l'export de fichiers (PDF, Excel, etc.)
 * Gère le téléchargement via API et affiche les notifications appropriées
 */
export const useFileExport = () => {
  const loading = ref(false)
  const error = ref<string | null>(null)

  /**
   * Exporte un fichier depuis une API
   * @param options Configuration de l'export
   */
  const exportFile = async (options: ExportOptions): Promise<boolean> => {
    loading.value = true
    error.value = null

    try {
      const response = await api.get(options.endpoint, {
        responseType: 'blob',
        timeout: options.timeout || 60000, // Default 60s for exports
      })

      // Extraire le nom de fichier depuis les headers ou utiliser le nom par défaut
      let filename = extractFilenameFromHeaders(response.headers)

      if (!filename) {
        const timestamp = new Date().toISOString().replace(/[:.]/g, '-')
        const extension = options.format === 'pdf' ? 'pdf' : 'xlsx'
        filename = options.defaultFilename
          ? `${options.defaultFilename}_${timestamp}.${extension}`
          : `export_${timestamp}.${extension}`
      }

      // Créer et télécharger le blob
      const blob = new Blob([response.data], { type: response.headers['content-type'] })
      downloadBlob(blob, filename)

      // Afficher le message de succès
      const successMsg =
        options.successMessage ||
        `Export ${options.format?.toUpperCase() || 'fichier'} téléchargé avec succès`

      showCustomToast({
        message: successMsg,
        type: 'success',
      })

      return true
    } catch (err) {
      console.error('Export file error:', err)
      error.value = options.errorMessage || "Erreur lors de l'export"

      showCustomToast({
        message: error.value,
        type: 'error',
      })

      return false
    } finally {
      loading.value = false
    }
  }

  /**
   * Export multi-format simplifié (PDF ou Excel)
   * @param pdfEndpoint Endpoint pour l'export PDF
   * @param excelEndpoint Endpoint pour l'export Excel
   * @param format Format désiré ('pdf' ou 'excel')
   * @param defaultFilename Nom de fichier par défaut (sans extension)
   */
  const exportMultiFormat = async (
    pdfEndpoint: string,
    excelEndpoint: string,
    format: 'pdf' | 'excel' = 'excel',
    defaultFilename?: string,
  ): Promise<boolean> => {
    const endpoint = format === 'pdf' ? pdfEndpoint : excelEndpoint

    return exportFile({
      endpoint,
      format,
      defaultFilename,
      successMessage: `Export ${format.toUpperCase()} téléchargé avec succès`,
      errorMessage: "Erreur lors de l'export",
    })
  }

  return {
    loading,
    error,
    exportFile,
    exportMultiFormat,
  }
}
