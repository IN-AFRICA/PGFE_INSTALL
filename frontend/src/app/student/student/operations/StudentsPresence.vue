<script setup lang="ts">
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import {
  Table,
  TableBody,
  TableCell,
  TableHead,
  TableHeader,
  TableRow,
} from '@/components/ui/table'
import { Checkbox } from '@/components/ui/checkbox'
import { Popover, PopoverContent, PopoverTrigger } from '@/components/ui/popover'
import {
  Select,
  SelectContent,
  SelectGroup,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from '@/components/ui/select'
import { Badge } from '@/components/ui/badge'
import LayoutSaisieOperation from '@/components/templates/LayoutSaisieOperation.vue'
import {
  DropdownMenu,
  DropdownMenuContent,
  DropdownMenuItem,
  DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu'
import BoxPanelWrapper from '@/components/atoms/BoxPanelWrapper.vue'
import { useGetApi } from '@/composables/useGetApi'
import { API_ROUTES } from '@/utils/constants/api_route'
import { ref, reactive, computed, watch, onMounted, onUnmounted } from 'vue'
import { showCustomToast } from '@/utils/widgets/custom_toast'
import { usePutApi } from '@/composables/usePutApi'
import ListClassRoom from '@/utils/widgets/vues/ListClassRoom.vue'
import NewPresenceList from '@/components/modals/NewPresenceList.vue'
import { eventBus } from '@/utils/eventBus.ts'
import { PdfService } from '@/services/pdfService.ts'
import FilterBadges from '@/components/atoms/FilterBadges.vue'

const loadingStudentId = ref<number | null>(null)
const recentlyUpdated = ref<number | null>(null)
const searchQuery = ref('')
const { loading, error, response, putData } = usePutApi()

const filterParams = reactive({
  idClasse: undefined as number | undefined,
  status: undefined as string | undefined,
  date: null as string | null,
})

// Données pour les labels des filtres
const { data: classrooms, fetchData: fetchClassrooms } = useGetApi(API_ROUTES.GET_CLASSROOMS)
fetchClassrooms()

// configuration des données de référence
const referenceData = computed(() => ({
  idClasse: classrooms.value || [],
}))

// configuration ds labels
const customLabels = {
  idClasse: (value: any, data: any[]) => {
    const classe = data?.find((c: any) => c.id === value)
    return classe ? classe.name : value
  },
  status: (value: any) => {
    const statusLabels: Record<string, string> = {
      present: 'Présent',
      absent: 'Absent Non Justifié',
      absent_justified: 'Absent Justifié',
      sick: 'Malade',
    }

    return statusLabels[value] || value
  },

  date: (value: any) => {
    return new Date(value).toLocaleDateString('fr-FR')
  },
}

// Fonction pour retirer un filtre
const removeFilter = (key: string) => {
  if (key === 'idClasse') filterParams.idClasse = undefined
  if (key === 'status') filterParams.status = undefined
  if (key === 'date') filterParams.date = null
}

// Paramètres de requête calculés pour l'API
const queryParams = computed(() => {
  const params: Record<string, any> = {}
  if (filterParams.idClasse) params.classroom_id = filterParams.idClasse
  if (filterParams.date) params.date = filterParams.date
  if (filterParams.status) params.status = filterParams.status
  return params
})

// Utiliser le bon endpoint avec useGetApi
const { data, fetchData, loading: loadingData } = useGetApi(API_ROUTES.GET_STUDENT_PRESENCE)

const filteredData = computed(() => {
  if (!data.value) return []

  if (!searchQuery.value.trim()) {
    return data.value
  }

  const query = searchQuery.value.toLowerCase()
  return data.value.filter((studentPresence: any) => {
    return (
      studentPresence.student?.toLowerCase().includes(query) ||
      studentPresence.classroom?.toLowerCase().includes(query)
    )
  })
})

const enrichedData = computed(() => {
  return filteredData.value.map((studentPresence: any) => {
    const status = getPresenceStatus(studentPresence)
    const config = getPresenceConfig(status)
    return {
      ...studentPresence,
      _status: status,
      _statusConfig: config,
    }
  })
})

// Watch pour les changements de paramètres et refetch avec des query params
watch(
  [() => filterParams.idClasse, () => filterParams.date, () => filterParams.status],
  async () => {
    console.log('Filter params changed, fetching with params:', queryParams.value)
    if (filterParams.idClasse) {
      console.log('Début du fetch avec params:', queryParams.value)
      await fetchData(queryParams.value)
      console.log('Fetch terminé')
    }
  },
  { immediate: false },
)

const handleTogglePresence = async (id: number, present: boolean) => {
  // Trouver l'item dans les données
  const item = data.value?.find((s) => s.id === id)
  if (!item) return

  // Sauvegarder l'état précédent pour rollback
  const previousState = {
    presence: item.presence,
    absent_justified: item.absent_justified,
    sick: item.sick,
  }

  // mise à jour immédiate
  item.presence = present
  if (present) {
    item.absent_justified = false
    item.sick = false
  }
  loadingStudentId.value = id

  // Appel API en arrière-plan
  const url = API_ROUTES.UPDATE_STUDENT_PRESENCE.replace(':id', String(id))
  await putData(url, {
    presence: item.presence,
    absent_justified: item.absent_justified,
    sick: item.sick,
  })

  loadingStudentId.value = null

  if (error.value) {
    // Rollback en cas d'erreur
    item.presence = previousState.presence
    item.absent_justified = previousState.absent_justified
    item.sick = previousState.sick
    showCustomToast({
      message: error.value,
      type: 'error',
    })
  } else if (response.value) {
    // Toast de succès
    showCustomToast({
      message: 'Présence mise à jour',
      type: 'success',
    })

    // Flash bleu temporaire sur la ligne
    recentlyUpdated.value = id
    setTimeout(() => {
      recentlyUpdated.value = null
    }, 1000)
  }
}

// Fonction pour déterminer le status à partir des 3 booléens
const getPresenceStatus = (item: any): string => {
  if (item.sick) return 'sick'
  if (item.absent_justified) return 'absent_justified'
  if (item.presence) return 'present'
  return 'absent' // absent non justifié
}

// Configuration des labels et couleurs pour chaque état
const getPresenceConfig = (status: string) => {
  const configs: Record<string, { label: string; color: string }> = {
    present: { label: 'Présent', color: 'bg-green-100 text-green-800 border-green-200' },
    absent: { label: 'Absent Non Justifié', color: 'bg-red-100 text-red-800 border-red-200' },
    absent_justified: {
      label: 'Absent Justifié',
      color: 'bg-orange-100 text-orange-800 border-orange-200',
    },
    sick: { label: 'Malade', color: 'bg-blue-100 text-blue-800 border-blue-200' },
  }
  return configs[status] || configs.absent
}

// Gestion du changement d'état détaillé via Select
const handleStatusChange = async (id: number, newStatus: string) => {
  // Trouver l'item dans les données
  const item = data.value?.find((s) => s.id === id)
  if (!item) return

  // Sauvegarder l'état précédent pour rollback
  const previousState = {
    presence: item.presence,
    absent_justified: item.absent_justified,
    sick: item.sick,
  }

  // Optimistic update - mise à jour immédiate selon le nouvel état
  switch (newStatus) {
    case 'present':
      item.presence = true
      item.absent_justified = false
      item.sick = false
      break
    case 'absent':
      item.presence = false
      item.absent_justified = false
      item.sick = false
      break
    case 'absent_justified':
      item.presence = false
      item.absent_justified = true
      item.sick = false
      break
    case 'sick':
      item.presence = false
      item.absent_justified = false
      item.sick = true
      break
  }

  loadingStudentId.value = id

  // Appel API en arrière-plan avec les 3 paramètres
  const url = API_ROUTES.UPDATE_STUDENT_PRESENCE.replace(':id', String(id))
  await putData(url, {
    presence: item.presence,
    absent_justified: item.absent_justified,
    sick: item.sick,
  })

  loadingStudentId.value = null

  if (error.value) {
    // Rollback en cas d'erreur
    item.presence = previousState.presence
    item.absent_justified = previousState.absent_justified
    item.sick = previousState.sick
    showCustomToast({
      message: error.value,
      type: 'error',
    })
  } else if (response.value) {
    // Toast de succès
    showCustomToast({
      message: 'Présence mise à jour',
      type: 'success',
    })

    // Flash vert temporaire sur la ligne
    recentlyUpdated.value = id
    setTimeout(() => {
      recentlyUpdated.value = null
    }, 1500)
  }
}

onMounted(() => {
  eventBus.on('presenceUpdated', () => {
    if (filterParams.idClasse) {
      fetchData(queryParams.value)
    }
  })

  // Écouter l'événement de création de présence pour synchronisation automatique
  eventBus.on('presenceCreated', (params: { classroom_id: number; date: string }) => {
    console.log('Présence créée, synchronisation:', params)
    filterParams.idClasse = params.classroom_id
    filterParams.date = params.date
  })
})

onUnmounted(() => {
  eventBus.off('presenceUpdated')
  eventBus.off('presenceCreated')
})

const getPresenceExportFilters = () => {
  const filters: Record<string, any> = {}
  if (filterParams.idClasse) filters.classroom_id = filterParams.idClasse
  if (filterParams.date) filters.date = filterParams.date
  if (filterParams.status) filters.status = filterParams.status
  return filters
}

const exportingPdf = ref(false)
const exportingExcel = ref(false)

const exportPresencePdf = async () => {
  if (exportingPdf.value) return
  exportingPdf.value = true
  try {
    const filters = getPresenceExportFilters()
    if (!filters.classroom_id && !filters.date) {
      showCustomToast?.({
        type: 'error',
        message: "Veuillez sélectionner au moins Classe ou Date avant d'exporter.",
      })
      return
    }
    await PdfService.exportAndDownloadPdf(filters)
    showCustomToast?.({ type: 'success', message: 'Export PDF démarré' })
  } catch (e: any) {
    showCustomToast?.({ type: 'error', message: e?.message || "Erreur lors de l'export PDF" })
  } finally {
    exportingPdf.value = false
  }
}

const exportPresenceExcel = async () => {
  if (exportingExcel.value) return
  exportingExcel.value = true
  try {
    const filters = getPresenceExportFilters()
    if (!filters.classroom_id && !filters.date) {
      showCustomToast?.({
        type: 'error',
        message: "Veuillez sélectionner au moins Classe ou Date avant d'exporter.",
      })
      return
    }
    await PdfService.exportAndDownloadExcel(filters)
    showCustomToast?.({ type: 'success', message: 'Export Excel démarré' })
  } catch (e: any) {
    showCustomToast?.({ type: 'error', message: e?.message || "Erreur lors de l'export Excel" })
  } finally {
    exportingExcel.value = false
  }
}
</script>

<template>
  <LayoutSaisieOperation
    active-tag-name="presences"
    group="operations"
    :breadcrumb="[
      { label: 'Élèves', href: '/apprenants' },
      { label: 'Opérations', href: '/apprenants/operations' },
      { label: 'Présence', href: '/apprenants/operations/presences' },
    ]"
  >
    <BoxPanelWrapper>
      <div class="flex items-center gap-3 justify-between">
        <div class="flex flex-1 items-center gap-2">
          <div class="relative w-full max-w-xs">
            <Input
              v-model="searchQuery"
              type="text"
              id="search"
              name="search"
              placeholder="Rechercher..."
              class="w-full max-w-xs ps-10 border border-gray-200/40 bg-white transition-all h-10 rounded-md"
            />
            <div
              class="absolute left-3 top-1/2 transform -translate-y-1/2 text-foreground-muted/70"
            >
              <span class="flex iconify hugeicons--search-01 text-sm"></span>
            </div>
          </div>
          <Popover>
            <PopoverTrigger as-child>
              <Button variant="ghost" size="sm" class="h-10 rounded-md border bg-white">
                <span class="hidden sm:flex"> Filtre</span>
                <span class="iconify hugeicons--filter">Filtre</span>
              </Button>
            </PopoverTrigger>
            <PopoverContent class="w-80">
              <div class="grid gap-4">
                <div class="space-y-2">
                  <h4 class="font-medium leading-none">Filtrage</h4>
                </div>
                <div class="flex flex-col gap-3.5">
                  <div class="space-y-1.5">
                    <Label class="text-foreground-muted font-light" for="classe">Classe</Label>
                    <!-- Classe: classroom_id -->
                    <ListClassRoom v-model="filterParams.idClasse" />
                  </div>

                  <div class="space-y-1.5">
                    <Label class="text-foreground-muted font-light" for="date">Date</Label>
                    <Input
                      id="date"
                      type="date"
                      class="h-10 bg-white"
                      v-model="filterParams.date"
                    />
                  </div>

                  <div class="space-y-1.5">
                    <Label class="text-foreground-muted font-light" for="status">Statut</Label>
                    <Select v-model="filterParams.status">
                      <SelectTrigger class="h-10 w-full">
                        <SelectValue placeholder="Sélectionner un statut" />
                      </SelectTrigger>
                      <SelectContent>
                        <SelectGroup>
                          <SelectItem value="present">
                            <div class="flex items-center gap-2">
                              <Badge
                                class="bg-green-100 text-green-800 border-green-200 px-2 py-1 text-xs font-medium border"
                              >
                                Présent
                              </Badge>
                            </div>
                          </SelectItem>
                          <SelectItem value="absent">
                            <div class="flex items-center gap-2">
                              <Badge
                                class="bg-red-100 text-red-800 border-red-200 px-2 py-1 text-xs font-medium border"
                              >
                                Absent Non Justifié
                              </Badge>
                            </div>
                          </SelectItem>
                          <SelectItem value="absent_justified">
                            <div class="flex items-center gap-2">
                              <Badge
                                class="bg-orange-100 text-orange-800 border-orange-200 px-2 py-1 text-xs font-medium border"
                              >
                                Absent Justifié
                              </Badge>
                            </div>
                          </SelectItem>
                          <SelectItem value="sick">
                            <div class="flex items-center gap-2">
                              <Badge
                                class="bg-blue-100 text-blue-800 border-blue-200 px-2 py-1 text-xs font-medium border"
                              >
                                Malade
                              </Badge>
                            </div>
                          </SelectItem>
                        </SelectGroup>
                      </SelectContent>
                    </Select>
                  </div>
                </div>
              </div>
            </PopoverContent>
          </Popover>

          <FilterBadges
            :filters="filterParams"
            :reference-data="referenceData"
            :custom-labels="customLabels"
            @remove-filter="removeFilter"
          />
        </div>
        <div class="flex flex-wrap items-center gap-2.5">
          <NewPresenceList />
          <DropdownMenu>
            <DropdownMenuTrigger as-child>
              <Button variant="ghost" size="md" class="bg-white border border-border rounded-md">
                Exporter
                <span class="iconify hugeicons--arrow-down-01" />
              </Button>
            </DropdownMenuTrigger>
            <DropdownMenuContent>
              <DropdownMenuItem
                class="flex items-center"
                @click="exportPresencePdf"
                :disabled="exportingPdf"
              >
                <span class="flex mr-1.5 iconify hugeicons--pdf-02"></span>
                Exporter pdf
              </DropdownMenuItem>
              <DropdownMenuItem
                class="flex items-center"
                @click="exportPresenceExcel"
                :disabled="exportingExcel"
              >
                <span class="flex mr-1.5 iconify hugeicons--ai-sheets"></span>
                Exporter Excel
              </DropdownMenuItem>
            </DropdownMenuContent>
          </DropdownMenu>
        </div>
      </div>
      <div class="mt-4 rounded-md overflow-hidden flex flex-1 bg-white">
        <Table class="rounded-md bg-white">
          <TableHeader>
            <TableRow>
              <TableHead class="w-[20px]">
                <Checkbox class="bg-white scale-70" />
              </TableHead>
              <TableHead> Nom complet </TableHead>
              <TableHead> Classe </TableHead>
              <TableHead> Présence </TableHead>
              <TableHead> Statut </TableHead>
            </TableRow>
          </TableHeader>
          <TableBody>
            <template v-if="loadingData">
              <TableRow>
                <TableCell colspan="5" class="text-center py-8">
                  <span class="flex justify-center items-center gap-2">
                    <span
                      class="animate-spin rounded-full h-5 w-5 border-t-2 border-b-2 border-gray-400"
                    ></span>
                    Chargement des présences...
                  </span>
                </TableCell>
              </TableRow>
            </template>
            <template v-else-if="enrichedData && enrichedData.length > 0">
              <TableRow
                v-for="studentPresence in enrichedData"
                :key="studentPresence.id"
                :class="{
                  'bg-primary-50 transition-colors duration-500':
                    recentlyUpdated === studentPresence.id,
                }"
              >
                <TableCell class="w-[40px]">
                  <Checkbox class="bg-white scale-70" />
                </TableCell>
                <TableCell>
                  {{ studentPresence.student }}
                </TableCell>
                <TableCell>
                  {{ studentPresence.classroom }}
                </TableCell>
                <TableCell>
                  <div class="flex items-center gap-2">
                    <label class="relative inline-flex items-center cursor-pointer group">
                      <input
                        type="checkbox"
                        :checked="!!studentPresence.presence"
                        @change="
                          handleTogglePresence(
                            studentPresence.id,
                            ($event.target as HTMLInputElement)?.checked,
                          )
                        "
                        class="sr-only peer"
                        :disabled="loadingStudentId === studentPresence.id"
                      />
                      <div
                        class="w-10 h-6 bg-gray-200 rounded-full peer-checked:bg-primary transition-colors duration-200 group-hover:ring-2 group-hover:ring-primary-300"
                      ></div>
                      <div
                        class="absolute left-1 top-1 w-4 h-4 bg-white rounded-full shadow transition-transform duration-200 peer-checked:translate-x-4"
                      ></div>
                      <span
                        v-if="loadingStudentId === studentPresence.id"
                        class="absolute right-[-24px]"
                      >
                        <span
                          class="animate-spin rounded-full h-4 w-4 border-t-2 border-b-2 border-primary-500"
                        ></span>
                      </span>
                    </label>
                  </div>
                </TableCell>
                <TableCell class="text-left max-w-xs">
                  <div class="flex items-center gap-2">
                    <Select
                      :model-value="studentPresence._status"
                      @update:model-value="(value) => handleStatusChange(studentPresence.id, value)"
                      :disabled="loadingStudentId === studentPresence.id"
                    >
                      <SelectTrigger class="w-[200px] h-9">
                        <SelectValue>
                          <Badge
                            :class="studentPresence._statusConfig.color"
                            class="px-2 py-1 text-xs font-medium border"
                          >
                            {{ studentPresence._statusConfig.label }}
                          </Badge>
                        </SelectValue>
                      </SelectTrigger>
                      <SelectContent>
                        <SelectItem value="present">
                          <div class="flex items-center gap-2">
                            <Badge
                              class="bg-green-100 text-green-800 border-green-200 px-2 py-1 text-xs font-medium border"
                            >
                              Présent
                            </Badge>
                          </div>
                        </SelectItem>
                        <SelectItem value="absent">
                          <div class="flex items-center gap-2">
                            <Badge
                              class="bg-red-100 text-red-800 border-red-200 px-2 py-1 text-xs font-medium border"
                            >
                              Absent Non Justifié
                            </Badge>
                          </div>
                        </SelectItem>
                        <SelectItem value="absent_justified">
                          <div class="flex items-center gap-2">
                            <Badge
                              class="bg-orange-100 text-orange-800 border-orange-200 px-2 py-1 text-xs font-medium border"
                            >
                              Absent Justifié
                            </Badge>
                          </div>
                        </SelectItem>
                        <SelectItem value="sick">
                          <div class="flex items-center gap-2">
                            <Badge
                              class="bg-blue-100 text-blue-800 border-blue-200 px-2 py-1 text-xs font-medium border"
                            >
                              Malade
                            </Badge>
                          </div>
                        </SelectItem>
                      </SelectContent>
                    </Select>
                    <span v-if="loadingStudentId === studentPresence.id">
                      <span
                        class="animate-spin rounded-full h-4 w-4 border-t-2 border-b-2 border-primary-500"
                      ></span>
                    </span>
                  </div>
                </TableCell>
              </TableRow>
            </template>
            <template v-else>
              <TableRow>
                <TableCell colspan="5" class="text-center py-8 text-gray-500">
                  Aucune donnée de présence trouvée pour les critères sélectionnés
                </TableCell>
              </TableRow>
            </template>
          </TableBody>
        </Table>
      </div>
    </BoxPanelWrapper>
  </LayoutSaisieOperation>
</template>
