<script setup lang="ts">
import { useBulletinStore } from '@/stores/bulletin'
import BulletinHeader from './shared/BulletinHeader.vue'
import BulletinGradesTable from './shared/BulletinGradesTable.vue'

withDefaults(
  defineProps<{
    variant?: 'standard' | 'state-exam'
  }>(),
  {
    variant: 'standard',
  },
)

const bulletinStore = useBulletinStore()
</script>

<template>
  <div v-if="bulletinStore.bulletinNotes" class="w-full">
    <BulletinHeader :bulletin="bulletinStore.bulletinNotes" />
    <BulletinGradesTable
      :grades="bulletinStore.bulletinNotes.grades"
      :variant="variant"
      :deliberations="bulletinStore.bulletinNotes.deliberations"
      :summary="bulletinStore.bulletinNotes.summary"
    />
  </div>
</template>
