<script setup lang="ts">
import { ref } from 'vue'
import { Button } from '@/components/ui/button'

interface ExcelButtonProps {
  type: 'import' | 'export'
  data?: any[]
  filename?: string
  sheetName?: string
  buttonText?: string
  buttonVariant?: 'default' | 'destructive' | 'outline' | 'secondary' | 'ghost' | 'link'
  buttonSize?: 'default' | 'sm' | 'lg' | 'icon' | 'md'
  buttonClass?: string
  onImport?: (data: any[]) => void
  onExport?: () => void
  importAccept?: string
  disabled?: boolean
}

const props = withDefaults(defineProps<ExcelButtonProps>(), {
  filename: 'export',
  sheetName: 'Sheet1',
  buttonVariant: 'ghost',
  buttonSize: 'md',
  buttonClass: 'bg-white border border-border rounded-md',
  importAccept: '.xlsx,.xls',
  disabled: false,
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
      emit('error', 'Aucune donnée à exporter')
      return
    }

    // Dynamically import xlsx to avoid build issues if not installed
    const XLSX = await import('xlsx').catch(() => null)
    const FileSaver = await import('file-saver').catch(() => null)

    if (!XLSX || !FileSaver) {
      emit(
        'error',
        'Les dépendances Excel ne sont pas installées. Veuillez installer xlsx et file-saver.',
      )
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
  } catch (error) {
    console.error("Erreur lors de l'export Excel:", error)
    emit('error', "Erreur lors de l'export Excel")
  }
}

// Fonction pour importer un fichier Excel
const importFromExcel = (event: Event) => {
  const target = event.target as HTMLInputElement
  const file = target.files?.[0]

  if (!file) return

  const reader = new FileReader()

  reader.onload = async (e) => {
    try {
      // Dynamically import xlsx
      const XLSX = await import('xlsx').catch(() => null)

      if (!XLSX) {
        emit(
          'error',
          'Les dépendances Excel ne sont pas installées. Veuillez installer xlsx et file-saver.',
        )
        return
      }

      const data = new Uint8Array(e.target?.result as ArrayBuffer)
      const workbook = XLSX.read(data, { type: 'array' })

      // Lire la première feuille
      const firstSheetName = workbook.SheetNames[0]
      const worksheet = workbook.Sheets[firstSheetName]

      // Convertir en JSON
      const jsonData = XLSX.utils.sheet_to_json(worksheet)

      if (props.onImport) {
        props.onImport(jsonData)
      }
      emit('import', jsonData)
    } catch (error) {
      console.error("Erreur lors de l'import Excel:", error)
      emit('error', "Erreur lors de l'import Excel")
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

// Fonction pour gérer le clic
const handleClick = () => {
  if (props.type === 'import') {
    triggerFileInput()
  } else {
    exportToExcel()
  }
}

// Texte du bouton par défaut
const defaultButtonText = props.type === 'import' ? 'Importer Excel' : 'Exporter Excel'
const iconClass = props.type === 'import' ? 'hugeicons--upload-01' : 'hugeicons--download-01'
</script>

<template>
  <div class="excel-button">
    <!-- Input file caché pour l'import -->
    <input
      v-if="type === 'import'"
      ref="fileInput"
      type="file"
      :accept="importAccept"
      @change="importFromExcel"
      class="hidden"
    />

    <!-- Bouton -->
    <Button
      :variant="buttonVariant"
      :size="buttonSize"
      :class="buttonClass"
      :disabled="disabled"
      @click="handleClick"
    >
      <span class="flex mr-1.5 iconify" :class="iconClass"></span>
      {{ buttonText || defaultButtonText }}
    </Button>
  </div>
</template>

<style scoped>
.excel-button {
  display: inline-block;
}
</style>
