<script setup lang="ts">
import LayoutGestionDisciplinaire from '@/components/templates/LayoutGestionDisciplinaire.vue'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { onMounted, ref, computed } from 'vue'
import { RouterLink } from 'vue-router'
import { showCustomToast } from '@/utils/widgets/custom_toast'
import StudentsService from '@/services/StudentsService'
import SchoolYearService from '@/services/SchoolYearService'
import FiliaireService from '@/services/FiliaireService'
import SemestreService from '@/services/SemestreService'
import StudentExitService from '@/services/StudentExitService'
import AlertMessage from '@/components/modals/AlertMessage.vue'
import { useGetApi } from '@/composables/useGetApi'

import {
  Table,
  TableBody,
  TableCell,
  TableHead,
  TableHeader,
  TableRow,
} from '@/components/ui/table'

// =================== Interface ===================
interface SortieEleve {
  id: number
  date: string
  student_id: string // String selon l'API
  filiere_id: string // String selon l'API
  school_year_id: string // String selon l'API
  semester: string
  exit_time: string
  motif: string
  created_at: string
  updated_at: string
}

// =================== Données réactives ===================
const {
  data: sorties,
  loading,
  error: sortiesError,
  fetchData: fetchStudentExits,
} = useGetApi<SortieEleve[]>('presence/student-exits')
const deleting = ref(false)
const query = ref('')

const anneesScolaires = ref([{ id: 1, label: '--------' }])
const filieres = ref([{ id: 1, name: '----------------------' }])
const semestres = ref([{ id: 1, label: '----------' }])
const students = ref([{ id: 1, full_name: '-------------' }])

// =================== Chargement à l'ouverture ===================
onMounted(() => {
  getAllSchoolYear()
  getAllFiliaire()
  getAllStudents()
  getAllSemestre()
  fetchStudentExits()
})

// =================== Fonctions ===================
const getAllSchoolYear = async () => {
  const res = await SchoolYearService.getAllSchoolYear()
  if (res) anneesScolaires.value = res?.data?.years.map((y: any) => ({ id: y.id, label: y.name }))
}

const getAllFiliaire = async () => {
  const res = await FiliaireService.getAllFiliaire()
  if (res) filieres.value = res?.data?.data.map((f: any) => ({ id: f.id, name: f.name }))
}

const getAllSemestre = async () => {
  const res = await SemestreService.getAllSemestre()
  if (res) semestres.value = res?.data?.data.map((s: any) => ({ id: s.id, label: s.name }))
}

const getAllStudents = async () => {
  const res = await StudentsService.getAllStudents()
  if (res) {
    students.value = res?.data?.students?.data.map((st: any) => ({
      id: st?.id,
      full_name: `${st?.name ?? ''} ${st?.firstname ?? ''} ${st?.lastname ?? ''}`,
    }))
  }
}

// =================== Suppression d'une sortie ===================
const deleteStudentExit = async (id: number) => {
  deleting.value = true
  try {
    await StudentExitService.deleteStudentExit(id)
    showCustomToast({ message: 'Sortie supprimée avec succès', type: 'success' })
    await fetchStudentExits()
  } catch (error) {
    showCustomToast({ message: 'Erreur lors de la suppression', type: 'error' })
  } finally {
    deleting.value = false
  }
}

// =================== Recherche ===================
const filteredSorties = computed(() => {
  const list = (sorties.value as SortieEleve[]) || []
  const q = query.value.trim().toLowerCase()
  if (!q) return list

  return list.filter((item) => {
    const student = students.value.find((s) => String(s.id) === String(item.student_id))
    const filiere = filieres.value.find((f) => String(f.id) === String(item.filiere_id))
    const annee = anneesScolaires.value.find((a) => String(a.id) === String(item.school_year_id))

    return (
      student?.full_name?.toLowerCase().includes(q) ||
      filiere?.name?.toLowerCase().includes(q) ||
      annee?.label?.toLowerCase().includes(q) ||
      item.motif?.toLowerCase().includes(q) ||
      item.semester?.toLowerCase().includes(q)
    )
  })
})

// =================== Formatage ===================
function formatReadableDate(dateString: string): string {
  if (!dateString) return 'N/A'
  const date = new Date(dateString)
  if (isNaN(date.getTime())) return 'Date invalide'
  return date.toLocaleDateString('fr-FR', {
    year: 'numeric',
    month: 'long',
    day: 'numeric',
  })
}
</script>

<template>
  <LayoutGestionDisciplinaire
    active-tag-name="index"
    :breadcrumb="[
      { label: 'Élèves', href: '/apprenants' },
      { label: 'Opérations', href: '/apprenants/operations' },
      { label: 'Gestion Disciplinaire', href: '/apprenants/operations/gestion-disciplinaire' },
      { label: 'Sorties d\'Élèves', href: '/apprenants/operations/gestion-disciplinaire/sorties' },
    ]"
  >
    <div class="flex flex-col md:flex-row gap-3 md:items-center md:justify-between">
      <div class="relative w-full md:w-96">
        <Input
          v-model="query"
          placeholder="Rechercher une sortie..."
          class="w-full ps-10 border border-gray-200/40 bg-white h-9 rounded-md"
        />
        <div class="absolute left-3 top-1/2 transform -translate-y-1/2 text-foreground-muted/70">
          <span class="flex iconify hugeicons--search-01 text-sm"></span>
        </div>
      </div>

      <Button size="md" class="rounded-md" as-child>
        <RouterLink to="/apprenants/operations/gestion-disciplinaire/sortie">
          <span class="flex iconify hugeicons--plus-sign"></span>
          <span class="hidden sm:flex">Ajouter Sortie</span>
        </RouterLink>
      </Button>
    </div>

    <!-- Message d'erreur -->
    <div v-if="sortiesError" class="mt-4 rounded-md overflow-hidden flex flex-1 bg-white p-8">
      <div class="flex flex-col items-center justify-center w-full gap-3">
        <span class="iconify hugeicons--alert-circle text-4xl text-red-500"></span>
        <p class="text-sm text-foreground-muted">{{ sortiesError }}</p>
        <Button @click="() => fetchStudentExits()" size="sm" variant="outline">
          <span class="iconify hugeicons--refresh mr-1"></span>
          Réessayer
        </Button>
      </div>
    </div>

    <!-- Liste vide -->
    <div
      v-else-if="!loading && (!sorties || sorties.length === 0)"
      class="mt-4 rounded-md overflow-hidden flex flex-1 bg-white p-8"
    >
      <div class="flex flex-col items-center justify-center w-full gap-3">
        <span class="iconify hugeicons--file-export text-4xl text-foreground-muted/50"></span>
        <p class="text-sm text-foreground-muted">
          {{
            query
              ? 'Aucune sortie trouvée pour cette recherche'
              : "Aucune sortie d'élève enregistrée"
          }}
        </p>
      </div>
    </div>

    <!-- Tableau des sorties -->
    <div v-else class="mt-4 rounded-md overflow-hidden flex flex-1 bg-white">
      <Table class="rounded-md bg-white">
        <TableHeader>
          <TableRow>
            <TableHead>Date</TableHead>
            <TableHead>Heure</TableHead>
            <TableHead>Élève</TableHead>
            <TableHead>Section</TableHead>
            <TableHead>Année scolaire</TableHead>
            <TableHead>Semestre</TableHead>
            <TableHead>Motif</TableHead>
            <TableHead>Actions</TableHead>
          </TableRow>
        </TableHeader>

        <TableBody>
          <TableRow v-if="loading">
            <TableCell colspan="8" class="text-center py-10 text-gray-500">
              <div class="flex flex-col items-center justify-center text-gray-500">
                <svg
                  class="animate-spin h-6 w-6 text-gray-400 mb-2"
                  xmlns="http://www.w3.org/2000/svg"
                  fill="none"
                  viewBox="0 0 24 24"
                >
                  <circle
                    class="opacity-25"
                    cx="12"
                    cy="12"
                    r="10"
                    stroke="currentColor"
                    stroke-width="4"
                  ></circle>
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"></path>
                </svg>
                <span>Chargement des sorties...</span>
              </div>
            </TableCell>
          </TableRow>

          <TableRow
            v-else
            v-for="item in filteredSorties"
            :key="item.id"
            class="group hover:bg-gray-50"
          >
            <TableCell>{{ formatReadableDate(item.date) }}</TableCell>
            <TableCell>{{ String(item.exit_time || '').slice(0, 5) }}</TableCell>
            <TableCell>{{
              students.find((s) => String(s.id) === String(item.student_id))?.full_name || 'N/A'
            }}</TableCell>
            <TableCell>{{
              filieres.find((f) => String(f.id) === String(item.filiere_id))?.name || 'N/A'
            }}</TableCell>
            <TableCell>{{
              anneesScolaires.find((a) => String(a.id) === String(item.school_year_id))?.label ||
              'N/A'
            }}</TableCell>
            <TableCell>{{ item.semester }}</TableCell>
            <TableCell>{{ item.motif }}</TableCell>

            <TableCell>
              <div class="flex items-center gap-2">
                <RouterLink
                  :to="`/apprenants/operations/gestion-disciplinaire/sortie?edit=${item.id}`"
                >
                  <Button
                    variant="ghost"
                    size="icon"
                    class="text-gray-500 hover:text-blue-600 hover:bg-blue-50"
                  >
                    <span class="iconify hugeicons--edit-02"></span>
                  </Button>
                </RouterLink>

                <AlertMessage
                  action="danger"
                  title="Supprimer une sortie"
                  :message="`Voulez-vous supprimer la sortie de ${students.find((s) => String(s.id) === String(item.student_id))?.full_name || 'cet apprenant'} ?`"
                >
                  <template #trigger>
                    <Button
                      variant="ghost"
                      size="icon"
                      class="text-gray-500 hover:text-red-600 hover:bg-red-50"
                      :disabled="deleting"
                    >
                      <span class="iconify hugeicons--delete-02"></span>
                    </Button>
                  </template>

                  <template #confirm-action-button>
                    <Button
                      variant="destructive"
                      @click="deleteStudentExit(item.id)"
                      size="sm"
                      class="h-10 px-4"
                      :disabled="deleting"
                    >
                      Oui, Supprimer
                    </Button>
                  </template>
                </AlertMessage>
              </div>
            </TableCell>
          </TableRow>
        </TableBody>
      </Table>
    </div>
  </LayoutGestionDisciplinaire>
</template>
