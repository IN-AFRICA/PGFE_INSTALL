import { api } from '@/services/api'
import { API_ROUTES } from '@/utils/constants/api_route'

export interface PdfExportResponse {
  success: boolean
  message: string
  file_url?: string
}

export class PdfService {
  /**
   * Exporter les données de présence étudiante vers PDF via l'API
   */
  static async exportStudentPresence(filters?: any): Promise<Blob> {
    try {
      // Construire les paramètres de requête
      const params = new URLSearchParams()
      if (filters?.date) {
        params.append('date', filters.date)
      }
      if (filters?.classroom_id) {
        params.append('classroom_id', filters.classroom_id)
      }
      if (filters?.idClasse) {
        params.append('idClasse', filters.idClasse)
      }
      if (filters?.status) {
        params.append('status', filters.status)
      }
      if (filters?.school_id) {
        params.append('school_id', filters.school_id)
      }

      const queryString = params.toString()
      const url = queryString
        ? `${API_ROUTES.EXPORT_STUDENT_PRESENCE_PDF}?${queryString}`
        : API_ROUTES.EXPORT_STUDENT_PRESENCE_PDF

      const response = await api.get(url, {
        responseType: 'blob',
        headers: {
          Accept: 'application/pdf',
        },
      })

      return response.data
    } catch (error: any) {
      console.error("Erreur lors de l'export PDF:", error)
      console.error('Status:', error.response?.status)
      console.error('Data:', error.response?.data)

      // Déterminer le message d'erreur selon le type d'erreur
      let errorMessage = `Erreur ${error.response?.status || 'réseau'} lors de l'export PDF`
      if (error.response?.status === 401) {
        errorMessage = "Erreur d'authentification - Veuillez vous reconnecter"
      } else if (error.response?.status === 403) {
        errorMessage = "Accès refusé pour l'export PDF"
      } else if (error.response?.status === 404) {
        errorMessage = "Aucune donnée trouvée pour l'export PDF"
      } else if (error.response?.status === 500) {
        errorMessage = "Erreur serveur lors de l'export PDF"
      }

      throw new Error(errorMessage)
    }
  }

  /**
   * Exporter les données de présence étudiante vers Excel via l'API
   */
  static async exportStudentPresenceExcel(filters?: any): Promise<Blob> {
    try {
      // Construire les paramètres de requête
      const params = new URLSearchParams()
      if (filters?.date) {
        params.append('date', filters.date)
      }
      if (filters?.classroom_id) {
        params.append('classroom_id', filters.classroom_id)
      }
      if (filters?.idClasse) {
        params.append('idClasse', filters.idClasse)
      }
      if (filters?.status) {
        params.append('status', filters.status)
      }
      if (filters?.school_id) {
        params.append('school_id', filters.school_id)
      }

      const queryString = params.toString()
      const url = queryString
        ? `${API_ROUTES.EXPORT_STUDENT_PRESENCE_EXCEL}?${queryString}`
        : API_ROUTES.EXPORT_STUDENT_PRESENCE_EXCEL

      const response = await api.get(url, {
        responseType: 'blob',
        headers: {
          Accept: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        },
      })

      return response.data
    } catch (error: any) {
      console.error("Erreur lors de l'export Excel:", error)
      console.error('Status:', error.response?.status)
      console.error('Data:', error.response?.data)

      // Déterminer le message d'erreur selon le type d'erreur
      let errorMessage = `Erreur ${error.response?.status || 'réseau'} lors de l'export Excel`
      if (error.response?.status === 401) {
        errorMessage = "Erreur d'authentification - Veuillez vous reconnecter"
      } else if (error.response?.status === 403) {
        errorMessage = "Accès refusé pour l'export Excel"
      } else if (error.response?.status === 404) {
        errorMessage = "Aucune donnée trouvée pour l'export Excel"
      } else if (error.response?.status === 500) {
        errorMessage = "Erreur serveur lors de l'export Excel"
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

  /**
   * Exporter et télécharger automatiquement les présences en PDF
   */
  static async exportAndDownloadPdf(filters?: any, filename?: string): Promise<void> {
    try {
      const blob = await this.exportStudentPresence(filters)
      const defaultFilename =
        filename || `presences-etudiantes-${new Date().toISOString().split('T')[0]}.pdf`
      this.downloadBlob(blob, defaultFilename)
    } catch (error) {
      console.error("Erreur lors de l'export et téléchargement PDF:", error)
      throw error
    }
  }

  /**
   * Exporter et télécharger automatiquement les présences en Excel
   */
  static async exportAndDownloadExcel(filters?: any, filename?: string): Promise<void> {
    try {
      const blob = await this.exportStudentPresenceExcel(filters)
      const defaultFilename =
        filename || `presences-etudiantes-${new Date().toISOString().split('T')[0]}.xlsx`
      this.downloadBlob(blob, defaultFilename)
    } catch (error) {
      console.error("Erreur lors de l'export et téléchargement Excel:", error)
      throw error
    }
  }
}
