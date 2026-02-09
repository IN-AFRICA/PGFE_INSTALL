import { api } from '@/services/api'
import { API_ROUTES } from '@/utils/constants/api_route'

export interface ExcelExportResponse {
  success: boolean
  message: string
  file_url?: string
}

export interface ExcelImportResponse {
  success: boolean
  message: string
  data?: string
  imported_count?: number
  errors?: string[]
}

export class ExcelService {
  /**
   * Exporter les données de fiche cotation vers Excel via l'API
   */
  static async exportFicheCotation(filters?: any): Promise<Blob> {
    try {
      const response = await api.post(API_ROUTES.EXPORT_FICHE_COTATION, filters || {}, {
        responseType: 'blob',
        headers: {
          Accept: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        },
      })

      return response.data
    } catch (error: any) {
      console.error("Erreur lors de l'export:", error)
      console.error('Status:', error.response?.status)
      console.error('Data:', error.response?.data)

      // Déterminer le message d'erreur selon le type d'erreur
      let errorMessage = `Erreur ${error.response?.status || 'réseau'} lors de l'export`
      if (error.response?.status === 401) {
        errorMessage = "Erreur d'authentification - Veuillez vous reconnecter"
      } else if (error.response?.status === 403) {
        errorMessage = "Accès refusé pour l'export"
      } else if (error.response?.status === 500) {
        errorMessage = "Erreur serveur lors de l'export"
      }

      throw new Error(errorMessage)
    }
  }

  /**
   * Importer un fichier Excel de fiche cotation via l'API
   */
  static async importFicheCotation(file: File): Promise<ExcelImportResponse> {
    try {
      const formData = new FormData()
      formData.append('file', file)

      const response = await api.post<ExcelImportResponse>(
        API_ROUTES.IMPORT_FICHE_COTATION,
        formData,
        {
          headers: {
            'Content-Type': 'multipart/form-data',
          },
        },
      )

      return response.data
    } catch (error: any) {
      console.error("Erreur lors de l'import:", error)

      // Déterminer le message d'erreur selon le type d'erreur
      let errorMessage = "Erreur lors de l'import du fichier"
      if (error.response?.status === 401) {
        errorMessage = "Erreur d'authentification - Veuillez vous reconnecter"
      } else if (error.response?.status === 413) {
        errorMessage = 'Fichier trop volumineux'
      } else if (error.response?.status === 422) {
        errorMessage = 'Format de fichier invalide'
      }

      if (error.response?.data) {
        return error.response.data
      }

      throw new Error(errorMessage)
    }
  }

  /**
   * Télécharger un template Excel pour l'import
   */
  static async downloadTemplate(): Promise<Blob> {
    try {
      const response = await api.get(`${API_ROUTES.EXPORT_FICHE_COTATION}/template`, {
        responseType: 'blob',
        headers: {
          Accept: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        },
      })

      return response.data
    } catch (error: any) {
      console.error('Erreur lors du téléchargement du template:', error)

      // Déterminer le message d'erreur selon le type d'erreur
      let errorMessage = 'Erreur lors du téléchargement du template'
      if (error.response?.status === 401) {
        errorMessage = "Erreur d'authentification - Veuillez vous reconnecter"
      } else if (error.response?.status === 404) {
        errorMessage = 'Template non trouvé'
      }

      throw new Error(errorMessage)
    }
  }

  /**
   * Utilitaire pour télécharger un blob en tant que fichier
   */
  static downloadBlob(blob: Blob, filename: string): void {
    const url = window.URL.createObjectURL(blob)
    const link = document.createElement('a')
    link.href = url
    link.download = filename
    document.body.appendChild(link)
    link.click()
    document.body.removeChild(link)
    window.URL.revokeObjectURL(url)
  }
}
