<script setup lang="ts">
import { onMounted, onUnmounted } from 'vue'
import ComptaLayout from '@/components/templates/compta/ComptaLayout.vue'
import ClassComptableList from './ClassComptableList.vue'
import CategorieComptableList from './CategorieComptableList.vue'
import EditClassDialog from './EditClassDialog.vue'
import EditCategorieDialog from './EditCategorieDialog.vue'
import { useClassComptable } from './useClassComptable'
import { useCategorieComptable } from './useCategorieComptable'
import { eventBus } from '@/utils/eventBus'

// ==================== Composables ====================
const classComptable = useClassComptable()
const categorieComptable = useCategorieComptable()

// ==================== Lifecycle ====================
onMounted(async () => {
  await Promise.all([classComptable.fetchClasses(), categorieComptable.fetchCategories()])
  eventBus.on('classComptableUpdated' as never, classComptable.fetchClasses)
  eventBus.on('categorieComptableUpdated' as never, categorieComptable.fetchCategories)
})

onUnmounted(() => {
  eventBus.off('classComptableUpdated' as never, classComptable.fetchClasses)
  eventBus.off('categorieComptableUpdated' as never, categorieComptable.fetchCategories)
})
</script>

<template>
  <ComptaLayout
    activeBread="Classes et Catégories"
    active-tag-name="classes-comptables"
    group="saisie"
  >
    <div class="grid lg:grid-cols-2 gap-7">
      <!-- Section Classes Comptables -->
      <ClassComptableList
        v-model:search-query="classComptable.searchQuery.value"
        v-model:is-create-dialog-open="classComptable.isCreateDialogOpen.value"
        v-model:is-edit-dialog-open="classComptable.isEditDialogOpen.value"
        v-model:page="classComptable.page.value"
        v-model:per-page-count="classComptable.perPageCount.value"
        :filtered-classes="classComptable.filteredClasses.value"
        :classes-loading="classComptable.classesLoading.value"
        :classes-error="classComptable.classesError.value"
        :creating-class="classComptable.creatingClass.value"
        :updating-class="classComptable.updatingClass.value"
        :deleting-class="classComptable.deletingClass.value"
        :total="classComptable.total.value"
        @submit-create="classComptable.onSubmitCreate"
        @submit-edit="classComptable.onSubmitEdit"
        @delete="classComptable.handleDelete"
        @open-edit="classComptable.openEditDialog"
        @close-create="classComptable.closeCreateDialog"
        @close-edit="classComptable.closeEditDialog"
      />

      <!-- Section Catégories Comptables -->
      <CategorieComptableList
        v-model:search-query="categorieComptable.searchQuery.value"
        v-model:is-create-dialog-open="categorieComptable.isCreateDialogOpen.value"
        v-model:is-edit-dialog-open="categorieComptable.isEditDialogOpen.value"
        v-model:page="categorieComptable.page.value"
        v-model:per-page-count="categorieComptable.perPageCount.value"
        :filtered-categories="categorieComptable.filteredCategories.value"
        :categories-loading="categorieComptable.categoriesLoading.value"
        :categories-error="categorieComptable.categoriesError.value"
        :creating-categorie="categorieComptable.creatingCategorie.value"
        :updating-categorie="categorieComptable.updatingCategorie.value"
        :deleting-categorie="categorieComptable.deletingCategorie.value"
        :total="categorieComptable.total.value"
        @submit-create="categorieComptable.onSubmitCreate"
        @submit-edit="categorieComptable.onSubmitEdit"
        @delete="categorieComptable.handleDelete"
        @open-edit="categorieComptable.openEditDialog"
        @close-create="categorieComptable.closeCreateDialog"
        @close-edit="categorieComptable.closeEditDialog"
      />
    </div>

    <!-- Dialog Modification Classe -->
    <EditClassDialog
      v-model:open="classComptable.isEditDialogOpen.value"
      :loading="classComptable.updatingClass.value"
      :editing-class="classComptable.editingClass.value"
      @submit="classComptable.onSubmitEdit"
      @close="classComptable.closeEditDialog"
    />

    <!-- Dialog Modification Catégorie -->
    <EditCategorieDialog
      v-model:open="categorieComptable.isEditDialogOpen.value"
      :loading="categorieComptable.updatingCategorie.value"
      :editing-categorie="categorieComptable.editingCategorie.value"
      @submit="categorieComptable.onSubmitEdit"
      @close="categorieComptable.closeEditDialog"
    />
  </ComptaLayout>
</template>
