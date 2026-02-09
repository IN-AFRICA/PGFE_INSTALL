<template>
  <DashFormLayout
    title="Bulletin Scolaire"
    link-back="/apprenants/operations"
    group-route="/apprenants/operations"
    module="students"
    :breadcrumb="[
      { label: 'Élèves', href: '/apprenants' },
      { label: 'Opérations', href: '/apprenants/operations' },
      { label: 'Bulletin Scolaire', href: '#' },
    ]"
  >
    <div class="w-full">
      <div class="bg-white rounded-lg shadow-lg p-6">
        <!-- ZONE 1 avec les images du drapeau et de l'armoirie à bien positionner  -->
        <BulletinHeader />
        <!--  ZONE 2 -->
        <Bulletin v-if="shouldShowGrid" />
        <div v-else class="text-center py-8 text-gray-500">
          <p>Veuillez sélectionner une classe et un élève pour afficher le bulletin</p>
        </div>
        <!--  ZONE 3 -->
        <BulletinFoot />
        <!-- ZONE 4 -->
      </div>
    </div>
  </DashFormLayout>
</template>

<script lang="ts" setup>
import { computed, ref, onMounted } from 'vue'
import DashFormLayout from '@/components/templates/DashFormLayout.vue'
import BulletinHeader from '@/components/templates/student/BulletinHeader.vue'
import Bulletin from '@/components/templates/student/bulletin/Bulletin.vue'
import BulletinFoot from '@/components/templates/student/BulletinFoot.vue'
import { useBulletinStore } from '@/stores/bulletin'

const bulletinStore = useBulletinStore()

onMounted(async () => {
  await bulletinStore.loadClassesByEcole()
  await bulletinStore.loadSchoolYears()
})

// Vérifier si on doit afficher la grille (configuration valide)
const shouldShowGrid = computed(() => {
  return !!bulletinStore.selectedClasseId && !!bulletinStore.selectedEleveId
})
</script>

<style>
.table-bordered,
.table-bordered th,
.table-bordered td {
  border: 1px solid;
}
</style>
