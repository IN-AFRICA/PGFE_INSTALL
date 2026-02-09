<script setup lang="ts">
import { ref, computed } from 'vue'
import { Button } from '@/components/ui/button'
import {
  DropdownMenu,
  DropdownMenuContent,
  DropdownMenuItem,
  DropdownMenuTrigger,
  DropdownMenuSeparator,
} from '@/components/ui/dropdown-menu'
import { ExcelService } from '@/services/excelService'
import { api } from '@/services/api'

interface ExcelManagerWithAPIProps {
  // Props pour l'apparence
  buttonText?: string
  buttonVariant?: 'default' | 'destructive' | 'outline' | 'secondary' | 'ghost' | 'link'
  buttonSize?: 'default' | 'sm' | 'lg' | 'icon' | 'md'
  buttonClass?: string

  // Props pour les fonctionnalités
  showImport?: boolean
  showExport?: boolean // legacy: alias de showExportExcel si fourni
  showExportExcel?: boolean // contrôle spécifique export Excel
  showExportPdf?: boolean // contrôle spécifique export PDF
  showTemplate?: boolean

  // Props pour les URLs
  importUrl?: string
  exportUrl?: string
  exportPdfUrl?: string
  templateUrl?: string

  // Props pour l'export
  exportFilters?: any
  exportFilename?: string

  // Props pour l'import
  importAccept?: string
}

const props = withDefaults(defineProps<ExcelManagerWithAPIProps>(), {
  buttonText: 'Excel',
  buttonVariant: 'ghost',
  buttonSize: 'md',
  buttonClass: 'bg-white border border-border rounded-md',

  showImport: true,
  showExport: undefined, // si fourni, il prime sur showExportExcel
  showExportExcel: true,
  showExportPdf: false,
  showTemplate: true,

  exportFilename: 'export',
  importAccept: '.xlsx,.xls',
})

// Compat: prioriser showExport si fourni, sinon utiliser showExportExcel
const canShowExportExcel = computed(() => {
  return typeof props.showExport === 'boolean' ? props.showExport : props.showExportExcel
})

// Affichage du PDF uniquement si explicitement activé
const canShowExportPdf = computed(() => props.showExportPdf === true)

const emit = defineEmits<{
  importSuccess: [data: any]
  importError: [message: string]
  exportSuccess: []
  exportError: [message: string]
  templateSuccess: []
  templateError: [message: string]
  beforeExport: []
}>()

const isLoading = ref(false)
const fileInput = ref<HTMLInputElement | null>(null)

// Export via API
const exportToExcel = async () => {
  if (isLoading.value) return

  // Émettre l'événement beforeExport pour synchroniser les filtres
  emit('beforeExport')

  isLoading.value = true

  try {
    console.log('🔄 Export en cours...')
    console.log('📍 URL:', props.exportUrl || '/fiche-cotations/export')
    console.log('📋 Filtres:', props.exportFilters)

    // Construire les query parameters pour GET
    const params = new URLSearchParams()
    if (props.exportFilters) {
      Object.entries(props.exportFilters).forEach(([key, value]) => {
        if (value !== null && value !== undefined && value !== '') {
          params.append(key, String(value))
        }
      })
    }

    const queryString = params.toString()
    const url = queryString
      ? `${props.exportUrl || '/fiche-cotations/export'}?${queryString}`
      : props.exportUrl || '/fiche-cotations/export'
    console.log('🌐 URL complète:', url)

    const response = await api.get(url, {
      responseType: 'blob',
      headers: {
        Accept: 'application/json',
      },
    })

    console.log('✅ Réponse reçue:', response)
    console.log('📦 Type de blob:', response.data.type, 'Taille:', response.data.size)

    const blob = response.data
    const filename = `${props.exportFilename}_${new Date().toISOString().split('T')[0]}.xlsx`

    ExcelService.downloadBlob(blob, filename)
    emit('exportSuccess')
  } catch (error: any) {
    console.error("❌ Erreur lors de l'export:", error)
    console.error('📍 URL tentée:', props.exportUrl)
    console.error('📋 Filtres envoyés:', props.exportFilters)
    console.error('🔍 Détails erreur:', {
      message: error?.message,
      status: error?.response?.status,
      statusText: error?.response?.statusText,
      data: error?.response?.data,
    })
    emit('exportError', error.message || "Erreur lors de l'export")
  } finally {
    isLoading.value = false
  }
}

// Export via API
const exportToPdf = async () => {
  if (isLoading.value) return

  // Émettre l'événement beforeExport pour synchroniser les filtres
  emit('beforeExport')

  isLoading.value = true

  try {
    console.log('🔄 Export en cours...')
    console.log('📍 URL:', props.exportPdfUrl || '/fiche-cotations/export')
    console.log('📋 Filtres:', props.exportFilters)

    // Construire les query parameters pour GET
    const params = new URLSearchParams()
    if (props.exportFilters) {
      Object.entries(props.exportFilters).forEach(([key, value]) => {
        if (value !== null && value !== undefined && value !== '') {
          params.append(key, String(value))
        }
      })
    }

    const queryString = params.toString()
    const url = queryString
      ? `${props.exportPdfUrl || '/fiche-cotations/export'}?${queryString}`
      : props.exportPdfUrl || '/fiche-cotations/export'
    console.log('🌐 URL complète:', url)

    const response = await api.get(url, {
      responseType: 'blob',
      headers: {
        Accept: 'application/json',
      },
    })

    console.log('✅ Réponse reçue:', response)
    console.log('📦 Type de blob:', response.data.type, 'Taille:', response.data.size)

    const blob = response.data
    const filename = `${props.exportFilename}_${new Date().toISOString().split('T')[0]}.pdf`

    ExcelService.downloadBlob(blob, filename)
    emit('exportSuccess')
  } catch (error: any) {
    console.error("❌ Erreur lors de l'export:", error)
    console.error('📍 URL tentée:', props.exportPdfUrl)
    console.error('📋 Filtres envoyés:', props.exportFilters)
    console.error('🔍 Détails erreur:', {
      message: error?.message,
      status: error?.response?.status,
      statusText: error?.response?.statusText,
      data: error?.response?.data,
    })
    emit('exportError', error.message || "Erreur lors de l'export")
  } finally {
    isLoading.value = false
  }
}

// Import via API
const importFromExcel = async (event: Event) => {
  const target = event.target as HTMLInputElement
  const file = target.files?.[0]

  if (!file) {
    console.log('Aucun fichier sélectionné')
    return
  }

  if (isLoading.value) {
    console.log('Import déjà en cours')
    return
  }

  console.log("Début de l'import du fichier:", file.name)
  isLoading.value = true

  try {
    const formData = new FormData()
    formData.append('file', file)

    const response = await api.post(props.importUrl || '/fiche-cotations/import', formData, {
      headers: {
        'Content-Type': 'multipart/form-data',
      },
    })

    const result = response.data
    console.log("Résultat de l'import:", result)

    if (result.success) {
      console.log("Import réussi, émission de l'événement importSuccess")
      emit('importSuccess', result)
    } else {
      console.log("Import échoué, émission de l'événement importError")
      console.log('Structure des erreurs:', result.errors)

      // Compose a more informative error message if backend provides details
      const baseMsg = result.message || "Erreur lors de l'import"
      let details = ''

      if (result.errors) {
        if (Array.isArray(result.errors)) {
          // Format ancien: errors = ["erreur1", "erreur2", ...]
          details = `\n\nDétails:\n- ${result.errors
            .slice(0, 5)
            .map((e: any) => e.message || e)
            .join('\n- ')}`
        } else if (typeof result.errors === 'object') {
          // Format nouveau: errors = {field1: ["erreur1"], field2: ["erreur2"], ...}
          const errorMessages = []
          for (const [field, fieldErrors] of Object.entries(result.errors)) {
            if (Array.isArray(fieldErrors)) {
              fieldErrors.forEach((error) => errorMessages.push(`${field}: ${error}`))
            } else {
              errorMessages.push(`${field}: ${fieldErrors}`)
            }
          }
          if (errorMessages.length > 0) {
            details = `\n\nDétails:\n- ${errorMessages.slice(0, 5).join('\n- ')}`
            if (errorMessages.length > 5) {
              details += `\n- ... et ${errorMessages.length - 5} autres erreurs`
            }
          }
        }
      }

      emit('importError', `${baseMsg}${details}`)
    }
  } catch (error: any) {
    console.error("Exception lors de l'import:", error)

    // Gérer les erreurs de l'API
    if (error.response?.data) {
      const result = error.response.data
      const baseMsg = result.message || "Erreur lors de l'import"
      let details = ''

      if (result.errors) {
        if (Array.isArray(result.errors)) {
          details = `\n\nDétails:\n- ${result.errors
            .slice(0, 5)
            .map((e: any) => e.message || e)
            .join('\n- ')}`
        }
      }

      emit('importError', `${baseMsg}${details}`)
    } else {
      emit('importError', error.message || "Erreur lors de l'import")
    }
  } finally {
    isLoading.value = false
    // Reset input value
    target.value = ''
    console.log("Fin de l'import")
  }
}

// Télécharger le template
const downloadTemplate = async () => {
  if (isLoading.value) return

  isLoading.value = true

  try {
    const response = await api.get(
      props.templateUrl || `${props.exportUrl || '/fiche-cotations/export'}/template`,
      {
        responseType: 'blob',
        headers: {
          Accept: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        },
      },
    )

    const blob = response.data
    const filename = `template_${props.exportFilename}.xlsx`

    ExcelService.downloadBlob(blob, filename)
    emit('templateSuccess')
  } catch (error: any) {
    console.error('Erreur lors du téléchargement du template:', error)
    emit('templateError', error.message || 'Erreur lors du téléchargement du template')
  } finally {
    isLoading.value = false
  }
}

// Déclencher la sélection de fichier
const triggerFileInput = () => {
  fileInput.value?.click()
}
</script>

<template>
  <div class="excel-manager-api">
    <!-- Input file caché -->
    <input
      ref="fileInput"
      type="file"
      :accept="importAccept"
      @change="importFromExcel"
      class="hidden"
    />

    <!-- Dropdown Menu -->
    <DropdownMenu>
      <DropdownMenuTrigger as-child>
        <Button
          :variant="buttonVariant"
          :size="buttonSize"
          :class="buttonClass"
          :disabled="isLoading"
        >
          <span
            v-if="isLoading"
            class="flex mr-1.5 iconify hugeicons--loading-03 animate-spin"
          ></span>
          <span v-else class="flex mr-1.5 iconify hugeicons--arrow-down-01"></span>
          {{ buttonText }}
        </Button>
      </DropdownMenuTrigger>
      <DropdownMenuContent>
        <DropdownMenuItem
          v-if="showImport"
          @click="triggerFileInput"
          :disabled="isLoading"
          class="flex items-center cursor-pointer"
        >
          <span class="flex mr-1.5 iconify hugeicons--upload-01"></span>
          Importer un fichier Excel
        </DropdownMenuItem>

        <DropdownMenuItem
          v-if="canShowExportExcel"
          @click="exportToExcel"
          :disabled="isLoading"
          class="flex items-center cursor-pointer"
        >
          <span class="flex mr-1.5 iconify hugeicons--download-01"></span>
          Exporter un fichier Excel
        </DropdownMenuItem>

        <DropdownMenuItem
          v-if="canShowExportPdf"
          @click="exportToPdf"
          :disabled="isLoading"
          class="flex items-center cursor-pointer"
        >
          <span class="flex mr-1.5 iconify hugeicons--download-01"></span>
          Exporter un fichier PDF
        </DropdownMenuItem>

        <DropdownMenuSeparator
          v-if="showTemplate && (showImport || canShowExportExcel || canShowExportPdf)"
        />

        <DropdownMenuItem
          v-if="showTemplate"
          @click="downloadTemplate"
          :disabled="isLoading"
          class="flex items-center cursor-pointer"
        >
          <span class="flex mr-1.5 iconify hugeicons--file-download"></span>
          Télécharger le template
        </DropdownMenuItem>
      </DropdownMenuContent>
    </DropdownMenu>
  </div>
</template>

<style scoped>
.excel-manager-api {
  display: inline-block;
}

@keyframes spin {
  from {
    transform: rotate(0deg);
  }
  to {
    transform: rotate(360deg);
  }
}

.animate-spin {
  animation: spin 1s linear infinite;
}
</style>
