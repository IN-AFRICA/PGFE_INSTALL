<script setup lang="ts">
import { computed } from 'vue'
import { useBulletinStore } from '@/stores/bulletin'

const bulletinStore = useBulletinStore()

// Date d'impression automatique
const dateImpression = computed(() => {
  const today = new Date()
  return today.toLocaleDateString('fr-FR')
})

const ville = computed(() => {
  return (
    bulletinStore.bulletinNotes?.registration.school.city || bulletinStore.currentEcole?.city || ''
  )
})

const elevePasseClasse = computed(() => {
  // Utilisation de la décision résumée du backend (deliberation)
  const decision = bulletinStore.bulletinNotes?.summary?.deliberation
  if (decision) {
    const lowerDecision = decision.toLowerCase()
    // "passe" ou "admis" -> Passe
    return lowerDecision.includes('pass') || lowerDecision.includes('admis')
  }
  return false
})

const aDesNotes = computed(() => {
  const notes = bulletinStore.bulletinNotes
  return !!(notes && notes.grades && notes.grades.length)
})

// Styles pour barrer/cocher les mentions
const styleDecisionPasse = computed(() => {
  if (!aDesNotes.value) return 'text-decoration: none;' // Aucune modification si pas de notes
  return elevePasseClasse.value
    ? 'text-decoration: none;' // Passe: pas barré (coché)
    : 'text-decoration: line-through; opacity: 0.5;' // Double: barré
})

const styleDecisionDouble = computed(() => {
  if (!aDesNotes.value) return 'text-decoration: none;' // Aucune modification si pas de notes
  return elevePasseClasse.value
    ? 'text-decoration: line-through; opacity: 0.5;' // Passe: barré
    : 'text-decoration: none;' // Double: pas barré (coché)
})

const symboleDecisionPasse = computed(() => {
  if (!aDesNotes.value) return '' // Pas de coche si pas de notes
  return elevePasseClasse.value ? '✓' : '' // Coché si passe
})

const symboleDecisionDouble = computed(() => {
  if (!aDesNotes.value) return '' // Pas de coche si pas de notes
  return !elevePasseClasse.value ? '✓' : '' // Coché si double
})
</script>

<template>
  <div class="mt-auto p-4 border">
    <div class="flex justify-between items-end text-[9pt]">
      <div class="text-left">
        <div id="decision-passe" class="transition-opacity" :style="styleDecisionPasse">
          - L'élève passe dans la classe supérieure (1)
          <span v-if="symboleDecisionPasse" class="font-bold text-black text-xl">{{
            symboleDecisionPasse
          }}</span>
        </div>
        <div id="decision-double" class="transition-opacity" :style="styleDecisionDouble">
          - L'élève double la classe (1)
          <span v-if="symboleDecisionDouble" class="font-bold text-black text-xl">{{
            symboleDecisionDouble
          }}</span>
        </div>
        <div class="mt-5"><strong>Signature de l'élève</strong></div>
      </div>
      <div class="text-center flex-1">
        <strong>Sceau de l'Ecole</strong>
      </div>
      <div class="text-center flex-1">
        Fait à <span class="uppercase">{{ ville }}</span
        >, le
        <input
          class="w-20 border-b border-dotted border-black bg-transparent"
          type="text"
          :value="dateImpression"
          readonly
        /><br /><br />
        <strong>Chef d'Etablissement</strong><br />
        (Noms & Signature)
      </div>
    </div>
    <div class="mt-2 text-[7pt] border-t border-black pt-1">
      <div>(1) Biffer la mention inutile.</div>
      <div>NOTE IMPORTANTE : Le bulletin est sans valeur s'il est raturé ou surchargé.</div>
    </div>
    <div class="text-center italic mt-1">
      Interdiction formelle de reproduire ce bulletin sous peine des sanctions prévues par la loi.
    </div>
  </div>
</template>

<style scoped></style>
