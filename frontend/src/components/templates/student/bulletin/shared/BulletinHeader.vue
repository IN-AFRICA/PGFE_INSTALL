<script setup lang="ts">
import type { BulletinNotes } from '@/types/BulletinType'

defineProps<{
  bulletin: BulletinNotes
}>()
</script>

<template>
  <div v-if="bulletin" class="text-center bg-gray-200 bg-opacity-50 p-4 relative border">
    <h3 class="font-bold uppercase text-base">
      BULLETIN DE LA {{ bulletin.registration.academic_level.name }} HUMANITES /
      {{ bulletin.registration.filiere.name }} <br />
      ANNEE SCOLAIRE {{ bulletin.registration.school_year.name }}
    </h3>
  </div>
</template>
