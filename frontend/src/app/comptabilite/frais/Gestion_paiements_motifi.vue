<script setup lang="ts">
import ComptaLayout from '@/components/templates/compta/ComptaLayout.vue'
import {
  Dialog,
  DialogContent,
  DialogDescription,
  DialogHeader,
  DialogTitle,
} from '@/components/ui/dialog'
import BoxPanelWrapper from '@/components/atoms/BoxPanelWrapper.vue'
import PaymentMotifsActions from '@/components/blocks/comptabilite/PaymentMotifsActions.vue'
import PaymentMotifsTable from '@/components/blocks/comptabilite/PaymentMotifsTable.vue'
import PaymentMotifForm from '@/components/forms/comptabilite/PaymentMotifForm.vue'
import { usePaymentMotifs } from '@/composables/usePaymentMotifs'
import { onMounted, ref } from 'vue'
import type { PaymentMotif, PaymentMotifFormData } from '@/composables/usePaymentMotifs'
import { eventBus } from '@/utils/eventBus'

// Variables réactives pour les dialogs
const isCreateDialogOpen = ref(false)
const isEditDialogOpen = ref(false)
const editingMotif = ref<PaymentMotif | null>(null)

// Utilisation du composable
const {
  // State
  searchQuery,
  selectedItems,
  motifsLoading,
  motifsError,
  createLoading,
  updateLoading,
  deleteLoading,

  // Computed
  filteredMotifs,
  selectedMotifs,
  isAllSelected,

  // Actions
  fetchMotifs,
  createPaymentMotif,
  updatePaymentMotif,
  deletePaymentMotif,
  deleteSelectedMotifs,

  // Selection management
  toggleSelection,
  toggleSelectAll,
  clearSelection,
} = usePaymentMotifs()

// Gestionnaires d'événements
const handleCreateMotif = () => {
  isCreateDialogOpen.value = true
}

const handleEditMotif = (motif: PaymentMotif) => {
  editingMotif.value = motif
  isEditDialogOpen.value = true
}

const handleDeleteMotif = async (motifId: number) => {
  const success = await deletePaymentMotif(motifId)
  if (success) {
    eventBus.emit('motifUpdated')
  }
}

const handleDeleteSelected = async () => {
  await deleteSelectedMotifs()
}

const handleExport = (format: 'excel' | 'pdf') => {
  // TODO: Implémenter l'export
  console.log('Export format:', format)
}

const handleCreateSubmit = async (data: PaymentMotifFormData) => {
  const success = await createPaymentMotif(data)
  if (success) {
    isCreateDialogOpen.value = false
    eventBus.emit('motifUpdated')
  }
}

const handleEditSubmit = async (data: PaymentMotifFormData) => {
  if (!editingMotif.value) return

  const success = await updatePaymentMotif(editingMotif.value.id, data)
  if (success) {
    isEditDialogOpen.value = false
    editingMotif.value = null
    eventBus.emit('motifUpdated')
  }
}

const handleCancelCreate = () => {
  isCreateDialogOpen.value = false
}

const handleCancelEdit = () => {
  isEditDialogOpen.value = false
  editingMotif.value = null
}

// Charger les données au montage du composant
onMounted(async () => {
  await fetchMotifs()
})

// Écouter les événements de mise à jour
eventBus.on('motifUpdated', () => {
  fetchMotifs()
})
</script>

<template>
  <ComptaLayout activeBread="Motifs de Paiement" active-tag-name="motifs-paiement" group="frais">
    <BoxPanelWrapper>
      <!-- Barre d'actions -->
      <PaymentMotifsActions
        v-model:search-query="searchQuery"
        :selected-count="selectedMotifs.length"
        :delete-loading="deleteLoading"
        @delete-selected="handleDeleteSelected"
        @export="handleExport"
        @create-motif="handleCreateMotif"
      />

      <!-- Tableau des motifs -->
      <PaymentMotifsTable
        :motifs="filteredMotifs"
        :selected-items="selectedItems"
        :loading="motifsLoading"
        :error="motifsError"
        :has-search-query="!!searchQuery"
        :is-all-selected="isAllSelected"
        @toggle-selection="toggleSelection"
        @toggle-select-all="toggleSelectAll"
        @edit-motif="handleEditMotif"
        @delete-motif="handleDeleteMotif"
      />
    </BoxPanelWrapper>

    <!-- Dialog de création -->
    <Dialog v-model:open="isCreateDialogOpen">
      <DialogContent class="sm:max-w-[600px]">
        <DialogHeader>
          <DialogTitle>Créer un nouveau motif de paiement</DialogTitle>
          <DialogDescription> Ajoutez un nouveau motif de paiement au système. </DialogDescription>
        </DialogHeader>

        <PaymentMotifForm
          mode="create"
          :loading="createLoading"
          @submit="handleCreateSubmit"
          @cancel="handleCancelCreate"
        />
      </DialogContent>
    </Dialog>

    <!-- Dialog de modification -->
    <Dialog v-model:open="isEditDialogOpen">
      <DialogContent class="sm:max-w-[600px]">
        <DialogHeader>
          <DialogTitle>Modifier le motif de paiement</DialogTitle>
          <DialogDescription> Modifiez les informations du motif de paiement. </DialogDescription>
        </DialogHeader>

        <PaymentMotifForm
          mode="edit"
          :loading="updateLoading"
          :motif="editingMotif"
          @submit="handleEditSubmit"
          @cancel="handleCancelEdit"
        />
      </DialogContent>
    </Dialog>
  </ComptaLayout>
</template>
