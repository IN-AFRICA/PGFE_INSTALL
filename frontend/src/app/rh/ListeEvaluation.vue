<script setup lang="ts">
import { RouterLink } from 'vue-router'
import { Input } from '@/components/ui/input'
import {
  DropdownMenu,
  DropdownMenuContent,
  DropdownMenuItem,
  DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu'
import BoxPanelWrapper from '@/components/atoms/BoxPanelWrapper.vue'
import {
  Table,
  TableBody,
  TableCell,
  TableHead,
  TableHeader,
  TableRow,
} from '@/components/ui/table'
import { Checkbox } from '@/components/ui/checkbox'
import TabPagination from '@/components/blocks/TabPagination.vue'
import { Button } from '@/components/ui/button'
import SaisieRhLayout from '@/components/templates/rh/SaisieRhLayout.vue'
import { onMounted, computed } from 'vue'
import { useGetApi } from '@/composables/useGetApi'
import { useSearch } from '@/composables/useSearch'
import { usePagination } from '@/composables/usePagination'
import { API_ROUTES } from '@/utils/constants/api_route'
import type { Evaluation } from '@/models/evaluation'

// API Calls
const {
  data: evaluationsData,
  fetchData: fetchEvaluations,
  loading: loadingEvaluations,
  error: errorEvaluations,
} = useGetApi<Evaluation[]>(API_ROUTES.GET_EVALUATIONS)

const { data: semestersData, fetchData: fetchSemesters } = useGetApi(API_ROUTES.GET_SEMESTERS)

// Search functionality
const { query } = useSearch((params: { search: string }) => {
  setAdditionalParams({ search: params.search })
  fetchEvaluations({ page: page.value, per_page: perPageCount.value, ...params })
}, 500)

// Pagination functionality
const { page, perPageCount, total, setAdditionalParams } = usePagination(fetchEvaluations, 1, 15, {
  pageParam: 'page',
  perPageParam: 'per_page',
})

onMounted(async () => {
  await Promise.all([
    fetchEvaluations({ page: page.value, per_page: perPageCount.value }),
    fetchSemesters(),
  ])
})

// Handle per page update
function onPerPageUpdate(val: number) {
  page.value = 1
  perPageCount.value = val
}

// Helper to resolve Semester
const getSemester = (id: number) => {
  const v: any = semestersData.value
  const list = Array.isArray(v) ? v : (v?.data ?? [])
  const semester = list.find((s: any) => s.id === id)
  return semester?.name || semester?.libelle || semester?.title || `S${id}`
}

// Computed Rows
const evaluation = computed(() => {
  const data = Array.isArray(evaluationsData.value)
    ? evaluationsData.value
    : (evaluationsData.value as any)?.data || []

  return data.map((item: Evaluation) => {
    const total =
      Number(item.c1_quantite_travail || '0') +
      Number(item.c2_theorie_pratique || '0') +
      Number(item.c3_determ_ress_perso || '0') +
      Number(item.c4_ponctualite || '0') +
      Number(item.c5_dr_att_posit_collab || '0')
    // Calcul du rendement global basé sur le total
    let rGlob = 'Insuffisant'
    if (total >= 80) rGlob = 'Excellent'
    else if (total >= 60) rGlob = 'Bien'
    else if (total >= 40) rGlob = 'Passable'

    return {
      id: item.id,
      date: item.created_at ? new Date(item.created_at).toLocaleDateString('fr-FR') : 'N/A',
      matricule: item.academic_personal?.matricule || 'N/A',
      personnel: item.academic_personal?.name || 'N/A',
      schoolYear: item.school_year?.name || 'N/A',
      semestre: getSemester(Number(item.semester_id)),
      c1: item.c1_quantite_travail || 0,
      c2: item.c2_theorie_pratique || 0,
      c3: item.c3_determ_ress_perso || 0,
      c4: item.c4_ponctualite || 0,
      c5: item.c5_dr_att_posit_collab || 0,
      total,
      rGlob,
      pointAmel: item.critiques || '-',
    }
  })
})
</script>

<template>
  <SaisieRhLayout activeBread="Evaluation" active-tag-name="evaluation" group="saisie">
    <BoxPanelWrapper>
      <div class="flex sm:items-center gap-3 flex-col sm:flex-row sm:justify-between">
        <div class="relative flex-1">
          <Input
            type="text"
            id="search"
            name="search"
            placeholder="Rechercher un personnel..."
            v-model="query"
            class="w-full max-w-sm ps-10 border border-gray-200/40 bg-white transition-all h-10 rounded-md"
          />
          <div class="absolute left-3 top-1/2 transform -translate-y-1/2 text-foreground-muted/70">
            <span class="flex iconify hugeicons--search-01 text-sm"></span>
          </div>
        </div>
        <div class="flex flex-wrap items-center sm:justify-end gap-2.5 flex-1">
          <Button size="md" class="rounded-md max-sm:flex-1 sm:w-max" as-child>
            <RouterLink to="/rh/saisie/nouvelle-evaluation">
              <span class="flex iconify hugeicons--plus-sign"></span>
              <span class="hidden sm:flex">Nouvelle évaluation</span>
            </RouterLink>
          </Button>
        </div>
      </div>
      <div class="mt-4 rounded-md overflow-hidden flex flex-1 bg-white">
        <Table class="rounded-md bg-white">
          <TableHeader>
            <TableRow>
              <TableHead class="w-[20px]">
                <Checkbox class="bg-white scale-70" />
              </TableHead>
              <TableHead>Date</TableHead>
              <TableHead>Matricule</TableHead>
              <TableHead>Personnel</TableHead>
              <TableHead>Ann. Scolaire</TableHead>
              <TableHead>Semestre</TableHead>
              <TableHead>C1</TableHead>
              <TableHead>C2</TableHead>
              <TableHead>C3</TableHead>
              <TableHead>C4</TableHead>
              <TableHead>C5</TableHead>
              <TableHead>Total/100</TableHead>
              <TableHead>Rend. Glob.</TableHead>
              <TableHead>Point à améliorer</TableHead>
              <!--              <TableHead>
                Opérations
              </TableHead>-->
            </TableRow>
          </TableHeader>
          <TableBody>
            <template v-if="loadingEvaluations">
              <TableRow>
                <TableCell colspan="14" class="text-center py-8">
                  Chargement des évaluations...
                </TableCell>
              </TableRow>
            </template>
            <template v-else-if="evaluation.length === 0">
              <TableRow>
                <TableCell colspan="14" class="text-center py-8 text-gray-500">
                  Aucune évaluation trouvée.
                </TableCell>
              </TableRow>
            </template>
            <template v-else>
              <TableRow v-for="item in evaluation" :key="item.id">
                <TableCell class="w-[40px]">
                  <Checkbox class="bg-white scale-70" />
                </TableCell>
                <TableCell>{{ item.date }}</TableCell>
                <TableCell>{{ item.matricule }}</TableCell>
                <TableCell>{{ item.personnel }}</TableCell>
                <TableCell>{{ item.schoolYear }}</TableCell>
                <TableCell>{{ item.semestre }}</TableCell>
                <TableCell>{{ item.c1 }}</TableCell>
                <TableCell>{{ item.c2 }}</TableCell>
                <TableCell>{{ item.c3 }}</TableCell>
                <TableCell>{{ item.c4 }}</TableCell>
                <TableCell>{{ item.c5 }}</TableCell>
                <TableCell class="font-semibold">{{ item.total }}</TableCell>
                <TableCell>{{ item.rGlob }}</TableCell>
                <TableCell class="max-w-xs truncate" :title="item.pointAmel">{{
                  item.pointAmel
                }}</TableCell>
              </TableRow>
            </template>
          </TableBody>
        </Table>
      </div>
      <TabPagination
        v-if="!loadingEvaluations && !errorEvaluations"
        v-model="page"
        :perPage="perPageCount"
        :totalItems="total"
        @update:perPage="onPerPageUpdate"
      />
    </BoxPanelWrapper>
  </SaisieRhLayout>
</template>

<style scoped></style>
