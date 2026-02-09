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
import {
  DropdownMenu,
  DropdownMenuContent,
  DropdownMenuItem,
  DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu'

import {
  AlertDialog,
  AlertDialogTrigger,
  AlertDialogContent,
  AlertDialogHeader,
  AlertDialogTitle,
  AlertDialogDescription,
  AlertDialogFooter,
  AlertDialogCancel,
  AlertDialogAction,
} from '@/components/ui/alert-dialog'

import { Checkbox } from '@/components/ui/checkbox'
import LayoutSaisieOperation from '@/components/templates/LayoutSaisieOperation.vue'
import BoxPanelWrapper from '@/components/atoms/BoxPanelWrapper.vue'
import { ref, onMounted, computed, watch } from 'vue'
import TabPagination from '@/components/blocks/TabPagination.vue'
import { useRouter } from 'vue-router'
// import AlertMessage from '@/components/modals/AlertMessage.vue';
import { useGetApi } from '@/composables/useGetApi'
import { useDeleteApi } from '@/composables/useDeleteApi'
import { API_ROUTES } from '@/utils/constants/api_route'
import { eventBus } from '@/utils/eventBus'
import { showCustomToast } from '@/utils/widgets/custom_toast'
import FilterPopover from '@/components/atoms/FilterPopover.vue'
import FilterBadges from '@/components/atoms/FilterBadges.vue'
import ListAcademicalLevel from '@/utils/widgets/vues/ListAcademicalLevel.vue'
import ListFiliere from '@/utils/widgets/vues/ListFiliere.vue'
import IconifySpinner from '@/components/ui/spinner/IconifySpinner.vue'

const page = ref(1)
const perPage = ref(15)
const router = useRouter()

// API pour récupérer les cours
const { data: courses, fetchData, loading, error, meta } = useGetApi<any[]>(API_ROUTES.GET_COURSES)
const { deleteItem, deleting: loadingDelete, errorDelete } = useDeleteApi()

const { data: levels, fetchData: fetchLevels } = useGetApi<any[]>(API_ROUTES.GET_ACADEMIC_LEVELS)
const { data: filieres, fetchData: fetchFilieres } = useGetApi<any[]>(API_ROUTES.GET_FILLIERES)
const { data: teachers, fetchData: fetchTeachers } = useGetApi<any[]>(
  API_ROUTES.GET_PERSONNELS_ACADEMIQUES,
)

// Calculer le total depuis les métadonnées de pagination
const totalItems = computed(() => meta?.value?.total ?? 0)

const levelMap = computed<Record<string, string>>(() => {
  const list = Array.isArray(levels?.value) ? (levels.value as any[]) : []
  return list.reduce(
    (acc, item) => {
      const key = String(item?.id ?? item?.value ?? item?.academic_level_id ?? '')
      const label = String(item?.name ?? item?.label ?? item?.title ?? '')
      if (key) acc[key] = label
      return acc
    },
    {} as Record<string, string>,
  )
})

const teacherMap = computed<Record<string, string>>(() => {
  const list = Array.isArray(teachers?.value) ? (teachers.value as any[]) : []
  return list.reduce(
    (acc, item) => {
      const key = String(item?.id ?? '')
      const firstname = String(item?.firstname ?? item?.first_name ?? '').trim()
      const name = String(item?.name ?? item?.last_name ?? '').trim()
      const full = [firstname, name].filter(Boolean).join(' ').trim()
      if (key) acc[key] = full || name || firstname || key
      return acc
    },
    {} as Record<string, string>,
  )
})

const filiereMap = computed<Record<string, string>>(() => {
  const list = Array.isArray(filieres?.value) ? (filieres.value as any[]) : []
  return list.reduce(
    (acc, item) => {
      const key = String(item?.id ?? item?.value ?? item?.filiere_id ?? '')
      const label = String(item?.name ?? item?.label ?? item?.title ?? '')
      if (key) acc[key] = label
      return acc
    },
    {} as Record<string, string>,
  )
})

const getLevelName = (id: string | number) => levelMap.value[String(id)] || String(id)
const getFiliereName = (id: string | number) => filiereMap.value[String(id)] || String(id)
const getTeacherFullName = (id: string | number) => teacherMap.value[String(id)] || String(id)

// Filtre les cours valides (non null/undefined)
const validCourses = computed(() => {
  if (!courses.value || !Array.isArray(courses.value)) {
    return []
  }
  return courses.value.filter((course) => course !== null && course !== undefined)
})

// Filtrage côté client (car l'API ne supporte pas tous les filtres)
const filteredCourses = computed(() => {
  let result = validCourses.value

  // Filtre par intitulé (label)
  if (filterParams.value.label && filterParams.value.label.trim() !== '') {
    const search = filterParams.value.label.toLowerCase().trim()
    result = result.filter((course) => course.label?.toLowerCase().includes(search))
  }

  // Filtre par niveau académique
  if (filterParams.value.academic_level_id) {
    result = result.filter(
      (course) => String(course.academic_level_id) === String(filterParams.value.academic_level_id),
    )
  }

  // Filtre par section/filière
  if (filterParams.value.filiaire_id) {
    result = result.filter(
      (course) => String(course.filiaire_id) === String(filterParams.value.filiaire_id),
    )
  }

  // Filtre par volume horaire
  if (filterParams.value.hourly_volume) {
    result = result.filter(
      (course) => Number(course.hourly_volume) === filterParams.value.hourly_volume,
    )
  }

  return result
})

// Charger les données au montage
onMounted(() => {
  fetchData({ page: page.value, limit: perPage.value })
  fetchLevels()
  fetchFilieres()
  fetchTeachers()
})

// Écouter les mises à jour
eventBus.on('courseUpdated', () => {
  fetchData({ page: page.value, limit: perPage.value })
})

// Recharger les données quand la page ou perPage change
watch([page, perPage], () => {
  fetchData({ page: page.value, limit: perPage.value })
})

// Paramètres de filtrage (côté client uniquement car l'API ne supporte pas ces filtres)
const filterParams = ref({
  label: '', // Recherche par intitulé
  academic_level_id: undefined as number | undefined, // Filtre par niveau
  filiaire_id: undefined as number | undefined, // Filtre par section
  hourly_volume: undefined as number | undefined, // Volume horaire
})

// Pas de debounce ni fetch car le filtrage est côté client (instantané)

// Données de référence pour les badges de filtre
const referenceData = computed(() => ({
  academic_level_id: levels.value || [],
  filiaire_id: filieres.value || [],
}))

// Labels personnalisés pour les badges de filtre
const customLabels: Record<string, (value: any, refData?: any[]) => string> = {
  label: (value) => `Cours: ${value}`,
  hourly_volume: (value) => `Heures: ${value}h`,
  academic_level_id: (value, refData) => {
    const item = refData?.find((i: any) => String(i.id) === String(value))
    return `Niveau: ${item ? item.name || item.label : value}`
  },
  filiaire_id: (value, refData) => {
    const item = refData?.find((i: any) => String(i.id) === String(value))
    return `Section: ${item ? item.name || item.label : value}`
  },
}

// Fonction pour supprimer un filtre
const removeFilter = (key: string) => {
  ;(filterParams.value as any)[key] = key === 'label' ? '' : undefined
}

function goToNewCourse() {
  console.debug('[SaisiCours] goToNewCourse clicked')
  router.push('/apprenants/saisie-prealable/cours/nouveau').catch((e) => {
    console.error('[SaisiCours] navigation error', e)
  })
}

// Handle edit
const handleEdit = (id: number) => {
  router.push(`/apprenants/saisie-prealable/cours/nouveau?edit=true&id=${id}`)
}

// Handle delete
const deletingId = ref<number | null>(null)
const handleDelete = async (id: number) => {
  deletingId.value = id
  const url = API_ROUTES.DELETE_COURSE.replace(':course', String(id))
  await deleteItem(url)
  eventBus.emit('courseUpdated')
  deletingId.value = null

  if (errorDelete.value) {
    showCustomToast({
      message: errorDelete.value,
      type: 'error',
    })
  } else {
    showCustomToast({
      message: 'Cours supprimé avec succès.',
      type: 'success',
    })
  }
}
</script>

<template>
  <LayoutSaisieOperation
    active-tag-name="cours"
    group="saisie"
    :breadcrumb="[
      { label: 'Élèves', href: '/apprenants' },
      { label: 'Saisie Préalable', href: '/apprenants/saisie-prealable' },
      { label: 'Cours', href: '/apprenants/saisie-prealable/cours' },
    ]"
  >
    <BoxPanelWrapper>
      <div class="flex items-center gap-3 justify-between">
        <div class="flex flex-1 items-center gap-2">
          <div class="relative w-full max-w-xs">
            <Input
              v-model="filterParams.label"
              type="text"
              id="search"
              name="search"
              placeholder="Rechercher un cours..."
              class="w-full ps-10 border border-gray-200/40 bg-white transition-all h-10 rounded-md"
            />
            <div
              class="absolute left-3 top-1/2 transform -translate-y-1/2 text-foreground-muted/70"
            >
              <span class="flex iconify hugeicons--search-01 text-sm"></span>
            </div>
          </div>
          <FilterPopover>
            <div class="flex flex-col space-y-1.5 flex-1">
              <label for="filter_label" class="text-sm font-medium">Intitulé du cours</label>
              <Input
                id="filter_label"
                type="text"
                v-model="filterParams.label"
                placeholder="Rechercher par intitulé..."
                class="h-10"
              />
            </div>
            <ListAcademicalLevel
              :model-value="
                filterParams.academic_level_id ? String(filterParams.academic_level_id) : ''
              "
              :items="levels || []"
              @update:model-value="
                (val) => (filterParams.academic_level_id = val ? Number(val) : undefined)
              "
            />
            <ListFiliere v-model="filterParams.filiaire_id" :items="filieres || []" />
            <div class="flex flex-col space-y-1.5 flex-1">
              <label for="hourly_volume" class="text-sm font-medium">Nombre d'heures</label>
              <Input
                id="hourly_volume"
                type="number"
                :model-value="filterParams.hourly_volume"
                @update:model-value="
                  (val) => (filterParams.hourly_volume = val ? Number(val) : undefined)
                "
                placeholder="Ex: 60"
                class="h-10"
                min="0"
              />
            </div>
          </FilterPopover>
          <FilterBadges
            :filters="filterParams"
            :reference-data="referenceData"
            :custom-labels="customLabels"
            @remove-filter="removeFilter"
          />
        </div>
        <div class="flex flex-wrap items-center gap-2.5">
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
          <Button type="button" size="md" class="rounded-md" @click="goToNewCourse">
            <span class="flex iconify hugeicons--plus-sign"></span>
            <span class="hidden sm:flex">Ajouter</span>
          </Button>
        </div>
      </div>
      <div class="mt-4 rounded-md overflow-hidden flex flex-1 bg-white">
        <!-- Fallback hidden link to ensure route is recognized -->
        <RouterLink to="/apprenants/saisie-prealable/cours/nouveau" style="display: none" />
        <Table class="rounded-md bg-white">
          <TableHeader class="bg-primary text-white text-xs uppercase">
            <TableRow>
              <TableHead rowspan="2" class="w-14">
                <Checkbox :checked="true" class="bg-white scale-70" />
              </TableHead>
              <TableHead rowspan="2">Code</TableHead>
              <TableHead rowspan="2">Intitulé du cours</TableHead>
              <TableHead rowspan="2">Niveau</TableHead>
              <TableHead rowspan="2">Section</TableHead>
              <!--              <TableHead rowspan="2">Titulaire</TableHead>-->
              <TableHead rowspan="2">Nombre d'heure</TableHead>
              <TableHead colspan="6" class="text-center px-4">Maximum</TableHead>
              <TableHead rowspan="2">Operations</TableHead>
            </TableRow>
            <TableRow class="bg-primary border-t-primary text-white text-xs uppercase">
              <TableHead class="px-4 py-2 !rounded-none">P1</TableHead>
              <TableHead class="px-4 py-2 !rounded-none">P2</TableHead>
              <TableHead class="px-4 py-2 !rounded-none">E1</TableHead>
              <TableHead class="px-4 py-2 !rounded-none">P3</TableHead>
              <TableHead class="px-4 py-2 !rounded-none">P4</TableHead>
              <TableHead class="px-4 py-2 !rounded-none">E2</TableHead>
            </TableRow>
          </TableHeader>

          <TableBody>
            <TableRow v-for="course in filteredCourses" :key="course.id">
              <TableCell class="w-14">
                <Checkbox :checked="false" class="bg-white scale-70" />
              </TableCell>
              <TableCell>{{ course.code || course.id }}</TableCell>
              <TableCell>{{ course.label }}</TableCell>
              <TableCell>{{ getLevelName(course.academic_level_id) }}</TableCell>
              <TableCell>{{ getFiliereName(course.filiaire_id) }}</TableCell>
              <!--              <TableCell>{{ course.author ? `${course.author.pre_name || ''} ${course.author.name || ''}`.trim() : 'N/A' }}</TableCell>-->

              <TableCell>{{ course.hourly_volume }}h</TableCell>
              <TableCell class="text-center px-4">{{ course.max_period_1 }}</TableCell>
              <TableCell class="text-center px-4">{{ course.max_period_2 }}</TableCell>
              <TableCell class="text-center px-4">{{ course.max_exam_1 }}</TableCell>
              <TableCell class="text-center px-4">{{ course.max_period_3 }}</TableCell>
              <TableCell class="text-center px-4">{{ course.max_period_4 }}</TableCell>
              <TableCell class="text-center px-4">{{ course.max_exam_2 }}</TableCell>
              <TableCell class="px-4">
                <div class="group flex items-center justify-between">
                  <div class="flex-1"></div>

                  <button
                    class="ml-2 group-hover:hidden rounded-full size-8 flex items-center justify-center hover:bg-gray-100 transition"
                  >
                    <span class="iconify hugeicons--more-vertical-circle-01"></span>
                  </button>

                  <div class="hidden group-hover:flex items-center gap-2 ml-2">
                    <Button
                      variant="ghost"
                      size="icon"
                      class="size-8 justify-center text-gray-500 hover:text-blue-600 hover:bg-blue-50"
                      @click="handleEdit(course.id)"
                    >
                      <span class="iconify hugeicons--edit-02"></span>
                    </Button>

                    <AlertDialog>
                      <AlertDialogTrigger as-child>
                        <Button
                          variant="ghost"
                          size="icon"
                          class="size-8 justify-center text-gray-500 hover:text-red-600 hover:bg-red-50"
                        >
                          <span class="iconify hugeicons--delete-02"></span>
                        </Button>
                      </AlertDialogTrigger>
                      <AlertDialogContent>
                        <AlertDialogHeader>
                          <AlertDialogTitle>Supprimer un cours</AlertDialogTitle>
                          <AlertDialogDescription>
                            Vous êtes sur le point de supprimer le cours '{{ course.label }}'.
                            Êtes-vous sûr de continuer?
                          </AlertDialogDescription>
                        </AlertDialogHeader>
                        <AlertDialogFooter>
                          <AlertDialogCancel :disabled="loadingDelete && deletingId === course.id"
                            >Annuler</AlertDialogCancel
                          >
                          <Button
                            :disabled="loadingDelete && deletingId === course.id"
                            @click="handleDelete(course.id)"
                          >
                            <span v-if="!loadingDelete || deletingId !== course.id">Supprimer</span>
                            <span v-else class="flex items-center gap-2">
                              <IconifySpinner class="text-white" />
                              <span>Suppression...</span>
                            </span>
                          </Button>
                        </AlertDialogFooter>
                      </AlertDialogContent>
                    </AlertDialog>
                  </div>
                </div>
              </TableCell>
            </TableRow>

            <!-- Message si aucun cours -->
            <TableRow v-if="!filteredCourses || filteredCourses.length === 0">
              <TableCell :colspan="14" class="text-center py-8 text-gray-500">
                <div v-if="loading">Chargement des cours...</div>
                <div v-else-if="error">Erreur lors du chargement: {{ error }}</div>
                <div v-else>Aucun cours trouvé</div>
              </TableCell>
            </TableRow>
          </TableBody>
        </Table>
      </div>
      <TabPagination
        v-model="page"
        :perPage="perPage"
        :totalItems="totalItems"
        @update:perPage="(val) => (perPage = val)"
      />
    </BoxPanelWrapper>
  </LayoutSaisieOperation>
</template>
