<script setup lang="ts">
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import {
  Table,
  TableBody,
  TableCell,
  TableHead,
  TableHeader,
  TableRow,
} from '@/components/ui/table'
import { Checkbox } from '@/components/ui/checkbox'
import {
  DropdownMenu,
  DropdownMenuContent,
  DropdownMenuItem,
  DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu'

import BoxPanelWrapper from '@/components/atoms/BoxPanelWrapper.vue'
import TabPagination from '@/components/blocks/TabPagination.vue'
import { computed, onMounted, ref } from 'vue'
import ComptaLayout from '@/components/templates/compta/ComptaLayout.vue'
import NewPlanAnalytique from '@/components/modals/compta/NewPlanAnalytique.vue'
import NewBudget from '@/components/modals/compta/NewBudget.vue'
import { useGetApi } from '@/composables/useGetApi.ts'
import { API_ROUTES } from '@/utils/constants/api_route.ts'

// Backend data
interface BudgetComptability {
  id: number
  code: string
  start_date: string // ISO date-time
  end_date: string // ISO date-time
  created_at?: string | null
  updated_at?: string | null
}

const {
  data: budgetsData,
  loading,
  error,
  fetchData: fetchBudgets,
} = useGetApi<any>(API_ROUTES.GET_BUDGET)

// Normalize various possible API response shapes
const rawBudgets = computed<BudgetComptability[]>(() => {
  const v: any = budgetsData?.value
  if (!v) return []
  if (Array.isArray(v)) return v as BudgetComptability[]
  if (Array.isArray(v?.data)) return v.data as BudgetComptability[]
  if (Array.isArray(v?.budgets)) return v.budgets as BudgetComptability[]
  if (v && typeof v === 'object') {
    const arr = Object.values(v).filter(
      (it: any) => it && typeof it === 'object' && 'code' in it && 'start_date' in it,
    )
    return arr as BudgetComptability[]
  }
  return []
})

// Map to UI model the table expects
const budgets = computed(() => {
  return rawBudgets.value.map((b) => {
    const startYear = (b.start_date || '').slice(0, 4)
    const endYear = (b.end_date || '').slice(0, 4)
    return {
      id: b.id,
      code: b.code,
      periode:
        startYear && endYear ? `${startYear}-${endYear}` : `${startYear || '-'}-${endYear || '-'}`,
      // Placeholders since backend resource doesn't provide these aggregates here
      recette_budget: 0,
      recette_execution: 0,
      recette_taux: 0,
      depense_budget: 0,
      depense_execution: 0,
      depense_taux: 0,
      statut: '',
    }
  })
})

const page = ref(1)
const perPage = ref(15)
const total = ref(20)

onMounted(() => {
  fetchBudgets()
})
</script>

<template>
  <ComptaLayout activeBread="Budget" active-tag-name="budget" group="saisie">
    <BoxPanelWrapper>
      <div class="flex sm:items-center gap-3 flex-col sm:flex-row sm:justify-between">
        <div class="relative flex-1">
          <Input
            type="text"
            id="search"
            name="search"
            placeholder="Rechercher un budget..."
            class="w-full max-w-sm ps-10 border border-gray-200/40 bg-white transition-all h-10 rounded-md"
          />
          <div class="absolute left-3 top-1/2 transform -translate-y-1/2 text-foreground-muted/70">
            <span class="flex iconify hugeicons--search-01 text-sm"></span>
          </div>
        </div>
        <div class="flex flex-wrap items-center sm:justify-end gap-2.5 flex-1">
          <NewBudget @created="fetchBudgets" />
          <!-- <DropdownMenu>
                        <DropdownMenuTrigger as-child>
                            <Button variant="ghost" size="md" class="bg-white border border-border rounded-md">
                                Exporter
                                <span class="iconify hugeicons--arrow-down-01 " />
                            </Button>
                        </DropdownMenuTrigger>
                        <DropdownMenuContent>
                            <DropdownMenuItem class="flex items-center">
                                <span class="flex mr-1.5 iconify hugeicons--pdf-02"></span>
                                Exporter pdf
                            </DropdownMenuItem>
                            <DropdownMenuItem class="flex items-center">
                                <span class="flex mr-1.5 iconify hugeicons--ai-sheets"></span>
                                Exporter Excel
                            </DropdownMenuItem>
                        </DropdownMenuContent>
                    </DropdownMenu> -->
        </div>
      </div>
      <div class="mt-4 rounded-md overflow-hidden flex flex-1 bg-white">
        <Table class="rounded-md bg-white">
          <TableHeader>
            <TableRow>
              <TableHead rowspan="2" class="w-[20px]"></TableHead>
              <TableHead rowspan="2">Code</TableHead>
              <TableHead rowspan="2">Période du budget</TableHead>
              <TableHead class="text-center" colspan="3">Recette</TableHead>
              <TableHead class="text-center" colspan="3">Dépense</TableHead>
              <TableHead rowspan="2">Operations</TableHead>
            </TableRow>
            <TableRow class="text-xs">
              <TableHead class="!rounded-tl-none text-center">Budget</TableHead>
              <TableHead class="text-center">Execution</TableHead>
              <TableHead class="text-center">Taux</TableHead>
              <TableHead class="text-center">Budget</TableHead>
              <TableHead class="text-center">Execution</TableHead>
              <TableHead class="!rounded-tr-none text-right">Taux</TableHead>
            </TableRow>
          </TableHeader>
          <TableBody>
            <TableRow v-for="item in budgets" :key="item.id">
              <TableCell class="w-[40px]">
                <Checkbox class="bg-white scale-70" />
              </TableCell>
              <TableCell class="font-semibold">{{ item.code }}</TableCell>
              <TableCell>{{ item.periode }}</TableCell>
              <TableCell class="text-center"
                >{{ item.recette_budget.toLocaleString() }} FC</TableCell
              >
              <TableCell class="text-right"
                >{{ item.recette_execution.toLocaleString() }} FC</TableCell
              >
              <TableCell class="text-right">
                <span
                  :class="{
                    'text-green-600': item.recette_taux >= 100,
                    'text-yellow-600': item.recette_taux >= 80 && item.recette_taux < 100,
                    'text-red-600': item.recette_taux < 80,
                  }"
                  class="font-medium"
                >
                  {{ item.recette_taux }}%
                </span>
              </TableCell>
              <TableCell class="text-right"
                >{{ item.depense_budget.toLocaleString() }} FC</TableCell
              >
              <TableCell class="text-right"
                >{{ item.depense_execution.toLocaleString() }} FC</TableCell
              >
              <TableCell class="text-right">
                <span
                  :class="{
                    'text-green-600': item.depense_taux <= 100,
                    'text-yellow-600': item.depense_taux > 100 && item.depense_taux <= 110,
                    'text-red-600': item.depense_taux > 110,
                  }"
                  class="font-medium"
                >
                  {{ item.depense_taux }}%
                </span>
              </TableCell>
              <TableCell>
                <div class="flex items-center gap-2 w-max">
                  <Button variant="outline" size="icon" class="size-8">
                    <span class="iconify hugeicons--edit-02"></span>
                  </Button>
                  <Button variant="destructive" size="icon" class="size-8">
                    <span class="iconify hugeicons--delete-02"></span>
                  </Button>
                </div>
              </TableCell>
            </TableRow>
          </TableBody>
        </Table>
      </div>
      <TabPagination
        v-model="page"
        :perPage="perPage"
        :totalItems="total"
        @update:perPage="(val) => (perPage = val)"
      />
    </BoxPanelWrapper>
  </ComptaLayout>
</template>
