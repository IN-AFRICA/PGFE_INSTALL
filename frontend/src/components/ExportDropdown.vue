<template>
  <DropdownMenu>
    <DropdownMenuTrigger as-child>
      <Button
        variant="ghost"
        size="md"
        class="bg-white border border-border rounded-md"
        :disabled="loading"
      >
        <template v-if="loading">
          <span class="iconify hugeicons--loading-03 animate-spin mr-2" />
          Chargement...
        </template>
        <template v-else>
          Exporter
          <span class="iconify hugeicons--arrow-down-01 ml-1.5" />
        </template>
      </Button>
    </DropdownMenuTrigger>
    <DropdownMenuContent>
      <DropdownMenuItem
        v-if="!hideOptions.pdf"
        class="flex items-center"
        @click.prevent="handleExport('pdf')"
        :disabled="loading"
      >
        <span class="flex mr-1.5 iconify hugeicons--pdf-02"></span>
        Exporter PDF
      </DropdownMenuItem>

      <DropdownMenuItem
        v-if="!hideOptions.excel"
        class="flex items-center"
        @click.prevent="handleExport('excel')"
        :disabled="loading"
      >
        <span class="flex mr-1.5 iconify hugeicons--ai-sheets"></span>
        Exporter Excel
      </DropdownMenuItem>
    </DropdownMenuContent>
  </DropdownMenu>
</template>

<script setup lang="ts">
import {
  DropdownMenu,
  DropdownMenuContent,
  DropdownMenuItem,
  DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu'
import { Button } from '@/components/ui/button'
const props = defineProps({
  loading: {
    type: Boolean,
    default: false,
  },
  onExport: {
    type: Function,
    required: true,
  },
  hideOptions: {
    type: Object,
    default: () => ({ pdf: false, excel: false }),
  },
})

const handleExport = (format: 'pdf' | 'excel') => {
  if (props.onExport) {
    props.onExport(format)
  }
}
</script>
