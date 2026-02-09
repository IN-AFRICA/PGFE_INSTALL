<script setup lang="ts">
import { ref } from 'vue'
import { Button } from '@/components/ui/button'
import {
  DropdownMenu,
  DropdownMenuContent,
  DropdownMenuItem,
  DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu'
import { showCustomToast } from '@/utils/widgets/custom_toast'

interface ExcelManagerProps {
  data?: any[]
  filename?: string
  sheetName?: string
  buttonText?: string
  buttonVariant?: 'default' | 'destructive' | 'outline' | 'secondary' | 'ghost' | 'link'
  buttonSize?: 'default' | 'sm' | 'lg' | 'icon' | 'md'
  buttonClass?: string
  onImport?: (data: any[]) => void
  onExport?: () => void
  showImport?: boolean
  showExport?: boolean
  importAccept?: string
}

const props = withDefaults(defineProps<ExcelManagerProps>(), {
  filename: 'export',
  sheetName: 'Sheet1',
  buttonText: 'Excel',
  buttonVariant: 'ghost',
  buttonSize: 'md',
  buttonClass: 'bg-white border border-border rounded-md',
  showImport: true,
  showExport: true,
  importAccept: '.xlsx,.xls',
})

const emit = defineEmits<{
  import: [data: any[]]
  export: []
  error: [message: string]
}>()

const fileInput = ref<HTMLInputElement>()

// Fonction pour exporter les données vers Excel
const exportToExcel = async () => {
  try {
    if (props.onExport) {
      props.onExport()
    }
    emit('export')

    if (!props.data || props.data.length === 0) {
      const errorMessage = '❌ Aucune donnée à exporter'
      showCustomToast({
        message: errorMessage,
        type: 'error',
      })
      emit('error', errorMessage)
      return
    }

    showCustomToast({
      message: "Préparation de l'export Excel...",
      type: 'success',
      autoClose: 2000,
    })

    // Dynamically import xlsx to avoid build issues if not installed
    const XLSX = await import('xlsx').catch(() => null)
    const FileSaver = await import('file-saver').catch(() => null)

    if (!XLSX || !FileSaver) {
      const errorMessage =
        '❌ Les dépendances Excel ne sont pas installées. Veuillez installer xlsx et file-saver.'
      showCustomToast({
        message: errorMessage,
        type: 'error',
      })
      emit('error', errorMessage)
      return
    }

    // Créer un nouveau workbook
    const wb = XLSX.utils.book_new()

    // Convertir les données en worksheet
    const ws = XLSX.utils.json_to_sheet(props.data)

    // Ajouter le worksheet au workbook
    XLSX.utils.book_append_sheet(wb, ws, props.sheetName)

    // Générer le fichier Excel
    const excelBuffer = XLSX.write(wb, { bookType: 'xlsx', type: 'array' })

    // Créer un blob et télécharger
    const blob = new Blob([excelBuffer], {
      type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
    })
    FileSaver.saveAs(blob, `${props.filename}.xlsx`)

    showCustomToast({
      message: `✅ Fichier Excel "${props.filename}.xlsx" exporté avec succès !`,
      type: 'success',
    })
  } catch (error) {
    console.error("Erreur lors de l'export Excel:", error)
    const errorMessage = "❌ Erreur lors de l'export Excel"
    showCustomToast({
      message: errorMessage,
      type: 'error',
    })
    emit('error', errorMessage)
  }
}

// Fonction pour importer un fichier Excel
const importFromExcel = (event: Event) => {
  const target = event.target as HTMLInputElement
  const file = target.files?.[0]

  if (!file) return

  showCustomToast({
    message: `Lecture du fichier "${file.name}"...`,
    type: 'success',
    autoClose: 2000,
  })

  const reader = new FileReader()

  reader.onload = async (e) => {
    try {
      // Dynamically import xlsx
      const XLSX = await import('xlsx').catch(() => null)

      if (!XLSX) {
        const errorMessage =
          '❌ Les dépendances Excel ne sont pas installées. Veuillez installer xlsx et file-saver.'
        showCustomToast({
          message: errorMessage,
          type: 'error',
        })
        emit('error', errorMessage)
        return
      }

      const data = new Uint8Array(e.target?.result as ArrayBuffer)
      const workbook = XLSX.read(data, { type: 'array' })

      // Lire la première feuille
      const firstSheetName = workbook.SheetNames[0]
      const worksheet = workbook.Sheets[firstSheetName]

      // Convertir en JSON
      const jsonData = XLSX.utils.sheet_to_json(worksheet)

      showCustomToast({
        message: `✅ Import réussi ! ${jsonData.length} ligne(s) importée(s) depuis "${file.name}"`,
        type: 'success',
      })

      if (props.onImport) {
        props.onImport(jsonData)
      }
      emit('import', jsonData)
    } catch (error) {
      console.error("Erreur lors de l'import Excel:", error)
      const errorMessage = `❌ Erreur lors de l'import du fichier "${file.name}"`
      showCustomToast({
        message: errorMessage,
        type: 'error',
      })
      emit('error', errorMessage)
    }
  }

  reader.readAsArrayBuffer(file)

  // Reset input value pour permettre de sélectionner le même fichier
  target.value = ''
}

// Fonction pour déclencher la sélection de fichier
const triggerFileInput = () => {
  fileInput.value?.click()
}
</script>

<template>
  <div class="excel-manager">
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
        <Button :variant="buttonVariant" :size="buttonSize" :class="buttonClass">
          {{ buttonText }}
          <span class="iconify hugeicons--arrow-down-01" />
        </Button>
      </DropdownMenuTrigger>
      <DropdownMenuContent>
        <DropdownMenuItem
          v-if="showImport"
          @click="triggerFileInput"
          class="flex items-center cursor-pointer"
        >
          <span class="flex mr-1.5 iconify hugeicons--upload-01"></span>
          Importer un fichier Excel
        </DropdownMenuItem>
        <DropdownMenuItem
          v-if="showExport"
          @click="exportToExcel"
          class="flex items-center cursor-pointer"
        >
          <span class="flex mr-1.5 iconify hugeicons--download-01"></span>
          Exporter un fichier Excel
        </DropdownMenuItem>
      </DropdownMenuContent>
    </DropdownMenu>
  </div>
</template>

<style scoped>
.excel-manager {
  display: inline-block;
}
</style>
