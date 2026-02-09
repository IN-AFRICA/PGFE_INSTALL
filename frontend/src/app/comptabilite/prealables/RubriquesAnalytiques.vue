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
import { ref } from 'vue'
import ComptaLayout from '@/components/templates/compta/ComptaLayout.vue'
import NewRubrique from '@/components/modals/compta/NewRubrique.vue'

const rubriquesAnalytiques = [
  {
    id: 1,
    code: 'RUB001',
    rubAnalytique: 'Frais de personnel',
  },
  {
    id: 2,
    code: 'RUB001',
    rubAnalytique: 'Frais de personnel',
  },
  {
    id: 3,
    code: 'RUB001',
    rubAnalytique: 'Frais de personnel',
  },
]

const page = ref(1)
const perPage = ref(15)
const total = ref(20)
</script>

<template>
  <ComptaLayout
    activeBread="Rubriques analytiques"
    active-tag-name="rubriques-analytiques"
    group="saisie"
  >
    <BoxPanelWrapper>
      <div class="flex sm:items-center gap-3 flex-col sm:flex-row sm:justify-between">
        <div class="relative flex-1">
          <Input
            type="text"
            id="search"
            name="search"
            placeholder="Rechercher une rubrique analytique..."
            class="w-full max-w-sm ps-10 border border-gray-200/40 bg-white transition-all h-10 rounded-md"
          />
          <div class="absolute left-3 top-1/2 transform -translate-y-1/2 text-foreground-muted/70">
            <span class="flex iconify hugeicons--search-01 text-sm"></span>
          </div>
        </div>
        <div class="flex flex-wrap items-center sm:justify-end gap-2.5 flex-1">
          <NewRubrique />
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
              <TableHead class="w-[20px]">
                <Checkbox class="bg-white scale-70" />
              </TableHead>
              <TableHead>Code</TableHead>
              <TableHead>Rubrique analytique</TableHead>
              <TableHead> Opérations </TableHead>
            </TableRow>
          </TableHeader>
          <TableBody>
            <TableRow v-for="item in rubriquesAnalytiques" :key="item.id">
              <TableCell class="w-[40px]">
                <Checkbox class="bg-white scale-70" />
              </TableCell>
              <TableCell class="font-semibold">{{ item.code }}</TableCell>
              <TableCell class="font-semibold">{{ item.rubAnalytique }}</TableCell>
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
