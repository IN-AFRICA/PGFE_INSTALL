<template>
  <div class="flex justify-between items-center text-center font-bold leading-tight border p-4">
    <img alt="Emblème RDC" class="w-20" src="@/assets/flag.png" />
    <div class="flex-grow">
      <div>REPUBLIQUE DEMOCRATIQUE DU CONGO</div>
      <div>MINISTERE DE L'EDUCATION NATIONALE ET NOUVELLE CITOYENNETE</div>
    </div>
    <img alt="Emblème Ministère" class="w-20" src="@/assets/coat.png" />
  </div>

  <div class="absolute inset-0 flex items-center justify-center opacity-20 pointer-events-none">
    <img alt="Armoiries de la RDC en filigrane" class="h-96 w-96" src="@/assets/coat.png" />
  </div>

  <div class="border p-4 text-sm">
    <div class="flex items-center justify-between">
      <span class="font-bold text-lg">N° ID.</span>
      <div class="flex gap-0.5">
        <input
          v-for="(value, index) in numeroIdArray"
          :key="index"
          :value="value"
          class="w-7 h-7 text-center border border-black text-[10pt] cursor-not-allowed pointer-events-none"
          maxlength="1"
          type="text"
          readonly
          tabindex="-1"
        />
      </div>
    </div>
  </div>

  <div class="grid grid-cols-2 text-sm relative">
    <div class="border p-4 space-y-3">
      <DottedInput
        label="PROVINCE EDUC."
        :model-value="ecoleInfo.provinceEducationnelle"
        readonly
      />

      <DottedInput label="VILLE" :model-value="ecoleInfo.ville" readonly />

      <DottedInput label="COMMUNE / TER." :model-value="ecoleInfo.commune" readonly />

      <DottedInput label="ECOLE" :model-value="ecoleInfo.name" readonly />

      <div class="mt-4">
        <NumberInputGrid label="CODE:" :values="codeEcoleArray" :count="12" readonly />
      </div>
    </div>

    <div class="border p-4 space-y-3">
      <!-- Sélection de classe -->
      <p class="flex items-center mb-2">
        <span class="mr-2">CLASSE:</span>
        <select
          :value="bulletinStore.selectedClasseId"
          @change="bulletinStore.selectClasse(($event.target as HTMLSelectElement).value)"
          class="flex-grow bg-transparent focus:outline-none border-0"
        >
          <option value="">Sélectionner une classe</option>
          <option
            v-for="classe in bulletinStore.classes.filter((c) => c)"
            :key="classe.id"
            :value="classe.id"
          >
            {{ classe?.name || 'Sans nom' }}
          </option>
        </select>
      </p>

      <!-- Sélection d'élève avec sexe -->
      <div class="flex items-center mb-2">
        <span class="mr-2">ELEVE:</span>
        <select
          :value="bulletinStore.selectedEleveId"
          @change="bulletinStore.selectEleve(($event.target as HTMLSelectElement).value)"
          class="flex-grow bg-transparent focus:outline-none border-0 mr-2"
        >
          <option value="">Sélectionner un élève</option>
          <option v-for="eleve in bulletinStore.eleves" :key="eleve.id" :value="eleve.id">
            {{ eleve.full_name }}
          </option>
        </select>
        <span class="mr-1">SEXE:</span>
        <span class="w-16">{{ studentInfo.gender || '-' }}</span>
      </div>

      <div class="flex items-center mb-2">
        <span class="mr-2">Né(e) à:</span>
        <span class="flex-grow mr-2">{{ studentInfo.birth_place || '' }}</span>
        <span class="mr-1">LE:</span>
        <span class="w-24">{{ formatDate(studentInfo.birth_date) }}</span>
      </div>

      <div class="mt-4">
        <NumberInputGrid label="N° PERM:" :values="numeroPermanentArray" :count="14" readonly />
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed, watch, onMounted } from 'vue'
import { useBulletinStore } from '@/stores/bulletin'
import DottedInput from '@/components/DottedInput.vue'
import NumberInputGrid from '@/components/NumberInputGrid.vue'

const bulletinStore = useBulletinStore()

// Charger les infos de l'école au montage
onMounted(async () => {
  if (!bulletinStore.currentEcole) {
    await bulletinStore.loadSchoolInfo()
  }
})

// Recharger le bulletin quand les filtres changent
watch([() => bulletinStore.selectedSchoolYearId, () => bulletinStore.selectedPeriod], () => {
  if (bulletinStore.selectedEleveId) {
    bulletinStore.fetchBulletinData(bulletinStore.selectedEleveId)
  }
})

// Computed pour les informations de l'école
const ecoleInfo = computed(() => {
  const school = bulletinStore.currentEcole || bulletinStore.bulletinNotes?.registration?.school
  return {
    provinceEducationnelle: school?.province?.name || '',
    ville: school?.city || '',
    commune: '', // Laisser vide comme demandé
    name: school?.name || '',
  }
})

// Computed pour les informations de l'élève
const studentInfo = computed(() => {
  const noteStudent = bulletinStore.bulletinNotes?.student
  const selectedStudent = bulletinStore.selectedEleve

  // Priorité aux données détaillées du bulletin, sinon celles de la liste d'élèves (classroom registrations)
  const student = noteStudent || selectedStudent

  return {
    gender: student?.gender ? student.gender.charAt(0).toUpperCase() : '',
    birth_place: student?.birth_place || '',
    birth_date: student?.birth_date || '',
  }
})

// Arrays pour les grilles d'input
const numeroIdArray = computed(() => {
  // Le n° ID ne doit pas afficher l'ID interne ni le matricule pour le moment
  // On affiche une grille vide
  return new Array(10).fill(' ')
})

const codeEcoleArray = computed(() => {
  const code =
    bulletinStore.bulletinNotes?.summary?.school_code ||
    bulletinStore.bulletinNotes?.registration?.school?.code ||
    bulletinStore.currentEcole?.code ||
    ''
  return code.slice(0, 12).padEnd(12, ' ').split('')
})

const numeroPermanentArray = computed(() => {
  const matricule = bulletinStore.bulletinNotes?.student?.matricule || ''
  return matricule.slice(0, 14).padEnd(14, ' ').split('')
})

const formatDate = (date: Date | string | undefined): string => {
  if (!date) return ''
  const d = typeof date === 'string' ? new Date(date) : date
  return d.toLocaleDateString('fr-FR', {
    day: '2-digit',
    month: '2-digit',
    year: 'numeric',
  })
}

// Handlers pour les grilles de numéros (lecture seule pour l'instant)
const updateNumeroId = (index: number, value: string) => {
  // Optionnel: implémenter si l'édition est autorisée
}

const updateCodeEcole = (values: string[]) => {
  // Optionnel
}

const updateNumeroPermanent = (values: string[]) => {
  // Optionnel
}
</script>

<style scoped></style>
