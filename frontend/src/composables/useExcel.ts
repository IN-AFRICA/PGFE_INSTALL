import { ref } from 'vue'

export interface ExcelExportOptions {
  filename?: string
  sheetName?: string
  data: any[]
}

export interface ExcelImportResult {
  data: any[]
  error?: string
}

export const useExcel = () => {
  const isLoading = ref(false)
  const error = ref<string | null>(null)

  // Fonction pour exporter des données vers Excel
  const exportToExcel = async (options: ExcelExportOptions): Promise<boolean> => {
    isLoading.value = true
    error.value = null

    try {
      if (!options.data || options.data.length === 0) {
        error.value = 'Aucune donnée à exporter'
        return false
      }

      // Import dynamique des dépendances
      const XLSX = await import('xlsx').catch(() => null)
      const FileSaver = await import('file-saver').catch(() => null)

      if (!XLSX || !FileSaver) {
        error.value =
          'Les dépendances Excel ne sont pas installées. Veuillez installer xlsx et file-saver.'
        return false
      }

      // Créer un nouveau workbook
      const wb = XLSX.utils.book_new()

      // Convertir les données en worksheet
      const ws = XLSX.utils.json_to_sheet(options.data)

      // Ajouter le worksheet au workbook
      XLSX.utils.book_append_sheet(wb, ws, options.sheetName || 'Sheet1')

      // Générer le fichier Excel
      const excelBuffer = XLSX.write(wb, { bookType: 'xlsx', type: 'array' })

      // Créer un blob et télécharger
      const blob = new Blob([excelBuffer], {
        type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
      })
      FileSaver.saveAs(blob, `${options.filename || 'export'}.xlsx`)

      return true
    } catch (err) {
      console.error("Erreur lors de l'export Excel:", err)
      error.value = "Erreur lors de l'export Excel"
      return false
    } finally {
      isLoading.value = false
    }
  }

  // Fonction pour importer un fichier Excel
  const importFromExcel = async (file: File): Promise<ExcelImportResult> => {
    isLoading.value = true
    error.value = null

    return new Promise((resolve) => {
      const reader = new FileReader()

      reader.onload = async (e) => {
        try {
          // Import dynamique de xlsx
          const XLSX = await import('xlsx').catch(() => null)

          if (!XLSX) {
            const result: ExcelImportResult = {
              data: [],
              error:
                'Les dépendances Excel ne sont pas installées. Veuillez installer xlsx et file-saver.',
            }
            error.value = result.error || null
            resolve(result)
            return
          }

          const data = new Uint8Array(e.target?.result as ArrayBuffer)
          const workbook = XLSX.read(data, { type: 'array' })

          // Lire la première feuille
          const firstSheetName = workbook.SheetNames[0]
          const worksheet = workbook.Sheets[firstSheetName]

          // Convertir en JSON
          const jsonData = XLSX.utils.sheet_to_json(worksheet)

          resolve({ data: jsonData })
        } catch (err) {
          console.error("Erreur lors de l'import Excel:", err)
          const result: ExcelImportResult = {
            data: [],
            error: "Erreur lors de l'import Excel",
          }
          error.value = result.error || null
          resolve(result)
        }
      }

      reader.onerror = () => {
        const result: ExcelImportResult = {
          data: [],
          error: 'Erreur lors de la lecture du fichier',
        }
        error.value = result.error || null
        resolve(result)
      }

      reader.onloadend = () => {
        isLoading.value = false
      }

      reader.readAsArrayBuffer(file)
    })
  }

  // Fonction utilitaire pour créer un input file et déclencher la sélection
  const selectExcelFile = (): Promise<File | null> => {
    return new Promise((resolve) => {
      const input = document.createElement('input')
      input.type = 'file'
      input.accept = '.xlsx,.xls'

      input.onchange = (e) => {
        const target = e.target as HTMLInputElement
        const file = target.files?.[0] || null
        resolve(file)
      }

      input.oncancel = () => {
        resolve(null)
      }

      input.click()
    })
  }

  // Fonction combinée pour import avec sélection de fichier
  const importExcelWithFileSelection = async (): Promise<ExcelImportResult> => {
    const file = await selectExcelFile()

    if (!file) {
      return {
        data: [],
        error: 'Aucun fichier sélectionné',
      }
    }

    return importFromExcel(file)
  }

  return {
    isLoading,
    error,
    exportToExcel,
    importFromExcel,
    selectExcelFile,
    importExcelWithFileSelection,
  }
}
