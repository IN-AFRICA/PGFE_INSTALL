<script setup lang="ts">
import { computed } from 'vue'
import { useBulletinStore } from '@/stores/bulletin'
import type { Grade, BulletinSummary } from '@/types/BulletinType'

const props = defineProps<{
  grades: Grade[]
  variant?: 'standard' | 'state-exam'
  deliberations?: Array<{ decision?: string | null }>
  summary?: BulletinSummary
}>()

const bulletinStore = useBulletinStore()

const dateImpression = computed(() => {
  const today = new Date()
  return today.toLocaleDateString('fr-FR')
})

const ville = computed(() => {
  return (
    bulletinStore.bulletinNotes?.registration.school.city || bulletinStore.currentEcole?.city || ''
  )
})

const aDesNotes = computed(() => props.grades && props.grades.length > 0)

const elevePasseClasse = computed(() => {
  // Utilisation de sumary.repechage pour déterminer si l'élève a un repêchage
  // Si repechage est true => l'élève ne passe pas directement (Double/Repêchage actif)
  // Si repechage est false => l'élève passe (Passe actif)
  if (props.summary) {
    return !props.summary.repechage
  }

  // Fallback si pas de summary (ancien comportement)
  const decision = props.deliberations?.[0]?.decision
  if (decision) {
    const lowerDecision = decision.toLowerCase()
    return lowerDecision.includes('pass') || lowerDecision.includes('admis')
  }
  return false
})

const styleDecisionPasse = computed(() => {
  if (!aDesNotes.value) return ''
  return elevePasseClasse.value ? '' : 'text-decoration: line-through; opacity: 0.5;'
})

const styleDecisionDouble = computed(() => {
  if (!aDesNotes.value) return ''
  return elevePasseClasse.value ? 'text-decoration: line-through; opacity: 0.5;' : ''
})

const symboleDecisionPasse = computed(() => {
  if (!aDesNotes.value) return ''
  return elevePasseClasse.value ? '✓' : ''
})

const symboleDecisionDouble = computed(() => {
  if (!aDesNotes.value) return ''
  return !elevePasseClasse.value ? '✓' : ''
})

const fmt = (val: string | number | undefined | null): string => {
  if (val === undefined || val === null || val === '') return ''
  const num = Number(val)
  if (isNaN(num)) return String(val)
  // Enlever les zéros non significatifs après la virgule (ex: 252.0 -> 252, 87.5 -> 87.5)
  return Number(num.toFixed(1)).toString()
}

const groupedGrades = computed(() => {
  const groups: Record<number, Grade[]> = {}
  props.grades.forEach((grade) => {
    const max = grade.total_maxima
    if (!groups[max]) groups[max] = []
    groups[max].push(grade)
  })
  return Object.entries(groups).sort(([a], [b]) => Number(a) - Number(b))
})

const generalTotals = computed(() => {
  // Utiliser les totaux du backend si disponibles
  if (props.summary?.period_exam_totals) {
    const backendTotals = props.summary.period_exam_totals
    const semesterTotals = props.summary.semester_totals

    return {
      maxima: {
        P1: backendTotals.maxima.P1 || 0,
        P2: backendTotals.maxima.P2 || 0,
        E1: backendTotals.maxima.E1 || 0,
        TOT1:
          semesterTotals?.S1?.maxima ||
          (backendTotals.maxima.P1 || 0) +
            (backendTotals.maxima.P2 || 0) +
            (backendTotals.maxima.E1 || 0),
        P3: backendTotals.maxima.P3 || 0,
        P4: backendTotals.maxima.P4 || 0,
        E2: backendTotals.maxima.E2 || 0,
        TOT2:
          semesterTotals?.S2?.maxima ||
          (backendTotals.maxima.P3 || 0) +
            (backendTotals.maxima.P4 || 0) +
            (backendTotals.maxima.E2 || 0),
        TG:
          (backendTotals.maxima.P1 || 0) +
          (backendTotals.maxima.P2 || 0) +
          (backendTotals.maxima.E1 || 0) +
          (backendTotals.maxima.P3 || 0) +
          (backendTotals.maxima.P4 || 0) +
          (backendTotals.maxima.E2 || 0),
      },
      obtained: {
        P1: backendTotals.obtained.P1 || 0,
        P2: backendTotals.obtained.P2 || 0,
        E1: backendTotals.obtained.E1 || 0,
        S1:
          semesterTotals?.S1?.obtained ||
          (backendTotals.obtained.P1 || 0) +
            (backendTotals.obtained.P2 || 0) +
            (backendTotals.obtained.E1 || 0),
        P3: backendTotals.obtained.P3 || 0,
        P4: backendTotals.obtained.P4 || 0,
        E2: backendTotals.obtained.E2 || 0,
        S2:
          semesterTotals?.S2?.obtained ||
          (backendTotals.obtained.P3 || 0) +
            (backendTotals.obtained.P4 || 0) +
            (backendTotals.obtained.E2 || 0),
        TG:
          (backendTotals.obtained.P1 || 0) +
          (backendTotals.obtained.P2 || 0) +
          (backendTotals.obtained.E1 || 0) +
          (backendTotals.obtained.P3 || 0) +
          (backendTotals.obtained.P4 || 0) +
          (backendTotals.obtained.E2 || 0),
      },
    }
  }

  // Fallback: calculer à partir des grades si period_exam_totals n'est pas disponible
  const maxima = { P1: 0, P2: 0, E1: 0, TOT1: 0, P3: 0, P4: 0, E2: 0, TOT2: 0, TG: 0 }
  const obtained = { P1: 0, P2: 0, E1: 0, S1: 0, P3: 0, P4: 0, E2: 0, S2: 0, TG: 0 }

  props.grades.forEach((grade) => {
    maxima.P1 += Number(grade.maxima.P1) || 0
    maxima.P2 += Number(grade.maxima.P2) || 0
    maxima.E1 += Number(grade.maxima.E1) || 0
    maxima.TOT1 += Number(grade.sem1_maxima) || 0
    maxima.P3 += Number(grade.maxima.P3) || 0
    maxima.P4 += Number(grade.maxima.P4) || 0
    maxima.E2 += Number(grade.maxima.E2) || 0
    maxima.TOT2 += Number(grade.sem2_maxima) || 0
    maxima.TG += Number(grade.total_maxima) || 0

    obtained.P1 += Number(grade.note.P1) || 0
    obtained.P2 += Number(grade.note.P2) || 0
    obtained.E1 += Number(grade.note.E1) || 0
    obtained.S1 += Number(grade.sem1_total) || 0
    obtained.P3 += Number(grade.note.P3) || 0
    obtained.P4 += Number(grade.note.P4) || 0
    obtained.E2 += Number(grade.note.E2) || 0
    obtained.S2 += Number(grade.sem2_total) || 0
    obtained.TG += Number(grade.total_obtained) || 0
  })

  return { maxima, obtained }
})

const getPeriodPercent = (key: string): string => {
  if (!props.summary) return ''

  // Pour TG, utiliser overall_percent
  if (key === 'TG') {
    return props.summary.overall_percent ? fmt(props.summary.overall_percent) : ''
  }

  // Pour S1 et S2, utiliser semester_place
  if (key === 'S1' && props.summary.semester_place?.S1) {
    return fmt(props.summary.semester_place.S1.percent)
  }
  if (key === 'S2' && props.summary.semester_place?.S2) {
    return fmt(props.summary.semester_place.S2.percent)
  }

  // Pour P1, P2, P3, P4, E1, E2, utiliser period_exam_place
  const periodKey = key as 'P1' | 'P2' | 'P3' | 'P4' | 'E1' | 'E2'
  if (props.summary.period_exam_place?.[periodKey]) {
    return fmt(props.summary.period_exam_place[periodKey].percent)
  }

  return ''
}

const getPeriodPlace = (key: string): string => {
  if (!props.summary) return '/'

  // Pour TG, utiliser rank et class_size
  if (key === 'TG') {
    return props.summary.rank ? `${props.summary.rank} / ${props.summary.class_size}` : '/'
  }

  // Pour S1 et S2, utiliser semester_place
  if (key === 'S1' && props.summary.semester_place?.S1) {
    return (
      props.summary.semester_place.S1.label ||
      `${props.summary.semester_place.S1.rank} / ${props.summary.semester_place.S1.out_of}`
    )
  }
  if (key === 'S2' && props.summary.semester_place?.S2) {
    return (
      props.summary.semester_place.S2.label ||
      `${props.summary.semester_place.S2.rank} / ${props.summary.semester_place.S2.out_of}`
    )
  }

  // Pour P1, P2, P3, P4, E1, E2, utiliser period_exam_place
  const periodKey = key as 'P1' | 'P2' | 'P3' | 'P4' | 'E1' | 'E2'
  if (props.summary.period_exam_place?.[periodKey]) {
    return (
      props.summary.period_exam_place[periodKey].label ||
      `${props.summary.period_exam_place[periodKey].rank} / ${props.summary.period_exam_place[periodKey].out_of}`
    )
  }

  return '/'
}

const getPeriodKey = (index: number): string => {
  const keys = ['P1', 'P2', 'E1', 'TOT1', 'P3', 'P4', 'E2', 'TOT2', 'TG']
  return keys[index - 1]
}

const getNoteKey = (index: number): string => {
  const keys = ['P1', 'P2', 'E1', 'S1', 'P3', 'P4', 'E2', 'S2', 'TG']
  return keys[index - 1]
}

const getGroupMaxima = (grade: Grade, key: string) => {
  const val = grade.maxima[key as keyof typeof grade.maxima]
  if (val) return val
  if (key === 'TOT1') return grade.sem1_maxima
  if (key === 'TOT2') return grade.sem2_maxima
  if (key === 'TG') return grade.total_maxima
  return ''
}
</script>

<template>
  <div class="w-full flex flex-col justify-start items-center">
    <table class="border-collapse w-full bg-white text-[15px] text-center">
      <thead class="font-bold bg-gray-300">
        <tr>
          <th class="p-1 align-middle" rowspan="3">BRANCHES</th>
          <th class="p-1" colspan="4">PREMIER SEMESTRE</th>
          <th class="p-1" colspan="4">DEUXIEME SEMESTRE</th>
          <th class="p-1 w-15 align-middle" rowspan="3">TG</th>
          <th class="p-2 bg-black align-middle" rowspan="3"></th>
          <th class="p-1 w-20" colspan="2">Trav Journal</th>
        </tr>
        <tr>
          <th class="p-1" colspan="2">Trav Journal</th>
          <th class="p-1 w-15 align-middle" rowspan="2">Exam</th>
          <th class="p-1 w-15 align-middle" rowspan="2">TOT</th>
          <th class="p-1" colspan="2">Trav Journal</th>
          <th class="p-1 w-15 align-middle" rowspan="2">Exam</th>
          <th class="p-1 w-15 align-middle" rowspan="2">TOT</th>
          <th class="p-1 w-15 align-middle" rowspan="2">%</th>
          <th class="p-1 w-50 align-middle" rowspan="2">Sign prof</th>
        </tr>
        <tr>
          <th class="w-15">1ère P</th>
          <th class="w-15">2ème P</th>
          <th class="w-15">3ème P</th>
          <th class="w-15">4ème P</th>
        </tr>
      </thead>
      <tbody>
        <template v-for="[totalMax, groupGrades] in groupedGrades" :key="totalMax">
          <tr class="bg-gray-300 font-bold">
            <th class="p-1 text-left uppercase">MAXIMA</th>
            <td v-for="i in 9" :key="i" class="p-1">
              {{ fmt(getGroupMaxima(groupGrades[0], getPeriodKey(i))) }}
            </td>
            <td v-if="variant === 'state-exam'" colspan="4" class="bg-black"></td>

            <td class="p-1 bg-black"></td>
            <td class="bg-black" colspan="2"></td>
          </tr>

          <tr v-for="grade in groupGrades" :key="grade.id">
            <td class="text-left p-1 font-medium">{{ grade.course }}</td>
            <td class="p-1 text-center">{{ fmt(grade.note.P1) }}</td>
            <td class="p-1 text-center">{{ fmt(grade.note.P2) }}</td>
            <td class="p-1 text-center">{{ fmt(grade.note.E1) }}</td>
            <td class="p-1 bg-gray-100 font-bold text-center">
              {{ grade.sem1_total ? fmt(grade.sem1_total) : '-' }}
            </td>
            <td class="p-1 text-center">{{ fmt(grade.note.P3) }}</td>
            <td class="p-1 text-center">{{ fmt(grade.note.P4) }}</td>
            <td class="p-1 text-center">{{ fmt(grade.note.E2) }}</td>
            <td class="p-1 bg-gray-100 font-bold text-center">
              {{ grade.sem2_total ? fmt(grade.sem2_total) : '-' }}
            </td>
            <td class="p-1 bg-gray-200 font-bold text-center">{{ fmt(grade.total_obtained) }}</td>
            <template v-if="variant === 'state-exam'">
              <td class="p-1 text-[8px] font-bold">%</td>
              <td class="p-1 font-bold text-center">
                {{ grade.average_percent ? fmt(grade.average_percent) : '' }}
              </td>
              <td class="p-1"></td>
            </template>
            <template v-else>
              <td class="p-1 bg-black"></td>
              <td class="p-1 font-bold text-center">
                {{ grade.average_percent ? fmt(grade.average_percent) : '' }}
              </td>
              <td colspan="2"></td>
            </template>
          </tr>
        </template>

        <tr class="font-bold bg-gray-200">
          <td class="text-left p-1">MAXIMA GENERAUX</td>
          <td v-for="i in 9" :key="'max-gen-' + i" class="p-1 text-center">
            {{ fmt(generalTotals.maxima[getPeriodKey(i)]) || 0 }}
          </td>
          <td v-if="variant === 'state-exam'" colspan="4" class="bg-black"></td>
          <td v-else colspan="3" class="bg-black"></td>
        </tr>

        <tr class="font-bold bg-gray-100 border-black">
          <td class="text-left p-1">TOTAUX</td>
          <td v-for="i in 9" :key="'tot-gen-' + i" class="p-1 text-center">
            {{ fmt(generalTotals.obtained[getNoteKey(i)]) || 0 }}
          </td>
          <td v-if="variant === 'state-exam'" colspan="4" class="bg-black"></td>
          <td v-else colspan="3" class="bg-black"></td>
        </tr>
      </tbody>
      <tfoot>
        <tr>
          <td class="text-left p-1">POURCENTAGE</td>
          <td v-for="i in 9" :key="'pct-' + i" class="p-1 text-center">
            {{ getPeriodPercent(getNoteKey(i)) }}
          </td>
          <td class="p-1 bg-black"></td>
          <td class="p-1 text-start" rowspan="5" colspan="2">
            <ul class="w-full h-full p-4 text-xs font-bold leading-relaxed">
              <li :style="styleDecisionPasse" class="transition-all duration-300">
                PASSE (1)
                <span v-if="symboleDecisionPasse" class="ml-2 text-lg text-black">{{
                  symboleDecisionPasse
                }}</span>
              </li>
              <li :style="styleDecisionDouble" class="transition-all duration-300">
                DOUBLE (1)
                <span v-if="symboleDecisionDouble" class="ml-2 text-lg text-black">{{
                  symboleDecisionDouble
                }}</span>
              </li>
              <li class="mt-4">Le {{ dateImpression }}</li>
              <li class="mt-4">
                Le Chef d’Etablissement <br />
                Sceau de l’école
              </li>
            </ul>
          </td>
        </tr>

        <tr>
          <td class="text-left p-1">PLACE / NBRE D'ELEVES</td>
          <td v-for="i in 9" :key="'place-' + i" class="p-1 text-center">
            {{ getPeriodPlace(getNoteKey(i)) }}
          </td>
          <td class="p-1 bg-black"></td>
        </tr>

        <tr>
          <td class="text-left p-1">APPLICATION</td>
          <td v-for="i in 2" :key="'app-' + i" class="p-1 text-center"></td>
          <td class="bg-gray-200" colspan="2" rowspan="2"></td>
          <td v-for="i in 2" :key="'app2-' + i" class="p-1 text-center"></td>
          <td class="bg-gray-200" colspan="3" rowspan="2"></td>
          <td class="p-1 bg-black"></td>
        </tr>

        <tr>
          <td class="text-left p-1">CONDUITE</td>
          <td v-for="i in 4" :key="'cond-' + i" class="p-1 text-center"></td>
          <td class="p-1 bg-black"></td>
        </tr>

        <tr>
          <td class="text-left p-1">Signat. du resp.</td>
          <td v-for="i in 2" :key="'sig-' + i" class="p-1 text-center"></td>
          <td class="bg-gray-100" colspan="2"></td>
          <td v-for="i in 3" :key="'sig2-' + i" class="p-1 text-center"></td>
          <td class="bg-gray-100" colspan="2"></td>
          <td class="p-1 bg-black"></td>
        </tr>
      </tfoot>
    </table>
  </div>
</template>

<style scoped>
tr,
th,
td {
  border: 1px solid black;
}
</style>
