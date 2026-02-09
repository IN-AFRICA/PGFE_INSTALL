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
import { Popover, PopoverContent, PopoverTrigger } from '@/components/ui/popover'
import {
  Select,
  SelectContent,
  SelectGroup,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from '@/components/ui/select'
import { Label } from '@/components/ui/label'
import FilterBadges from '@/components/atoms/FilterBadges.vue'
import ListClassRoom from '@/utils/widgets/vues/ListClassRoom.vue'
import DateFilter from '@/utils/widgets/vues/DateFilter.vue'
import { Checkbox } from '@/components/ui/checkbox'
import LayoutSaisieOperation from '@/components/templates/LayoutSaisieOperation.vue'
import BoxPanelWrapper from '@/components/atoms/BoxPanelWrapper.vue'
import { useGetApi } from '@/composables/useGetApi'
import { API_ROUTES } from '@/utils/constants/api_route'
import { useDeleteApi } from '@/composables/useDeleteApi'
import AlertMessage from '@/components/modals/AlertMessage.vue'
import { showCustomToast } from '@/utils/widgets/custom_toast'
import { computed, reactive, ref, onMounted } from 'vue'
import type { Classroom } from '@/models/classroom'
import type { School } from '@/models/school'
import type { Visit } from '@/models/visit'

const {
  data: allVisits,
  loading,
  error,
  fetchData,
} = useGetApi<Visit[]>(API_ROUTES.GET_VISITE_CLASSES)
const { data: fonctions, fetchData: fetchFonctions } = useGetApi(API_ROUTES.GET_FONCTIONS)
const { data: classrooms, fetchData: fetchClassrooms } = useGetApi(API_ROUTES.GET_CLASSROOMS)
const { deleting, errorDelete, deleteItem } = useDeleteApi()

// Charger les données au montage
onMounted(() => {
  fetchData() // Charge toutes les données (ou la première page par défaut, à voir selon l'API)
  fetchFonctions()
  fetchClassrooms()
})

// Variables réactives
const searchQuery = ref('')

// Paramètres de filtrage
const filterParams = reactive({
  classroom_id: undefined as number | undefined,
  date: undefined as string | undefined,
  professeur: undefined as string | undefined,
  visiteur: undefined as string | undefined,
  fonction_id: undefined as string | undefined,
})

// Extraire la liste des classes
const classroomList = computed(() => {
  const v: any = classrooms?.value
  if (Array.isArray(v)) return v
  return v?.data ?? []
})

// Extraire la liste des fonctions
const fonctionList = computed(() => {
  const v: any = fonctions?.value
  if (Array.isArray(v)) return v
  return v?.data ?? []
})

// Données filtrées (Client-side)
const filteredData = computed(() => {
  let result = allVisits.value || []

  // Si result est un objet paginé (ex: { data: [...] }), extraire le tableau
  if (!Array.isArray(result) && result.data) {
    result = result.data
  }
  if (!Array.isArray(result)) return []

  // Filtre Classroom
  if (filterParams.classroom_id) {
    result = result.filter(
      (v: Visit) => String(v.classroom_id) === String(filterParams.classroom_id),
    )
  }

  // Filtre Date
  if (filterParams.date) {
    result = result.filter((v: any) => v.visit_hour && v.visit_hour.startsWith(filterParams.date))
  }

  // Filtre Fonction
  if (filterParams.fonction_id) {
    result = result.filter((v: any) => String(v.fonction_id) === String(filterParams.fonction_id))
  }

  // Filtre Professeur (Recherche textuelle sur le nom du personnel)
  if (filterParams.professeur) {
    const query = filterParams.professeur.toLowerCase()
    result = result.filter(
      (v: any) => v.personal?.name && v.personal.name.toLowerCase().includes(query),
    )
  }

  // Filtre Visiteur (Recherche textuelle)
  if (filterParams.visiteur) {
    const query = filterParams.visiteur.toLowerCase()
    result = result.filter((v: any) => v.visiteur && v.visiteur.toLowerCase().includes(query))
  }

  // Filtre Recherche Globale
  if (searchQuery.value) {
    const query = searchQuery.value.toLowerCase()
    result = result.filter(
      (v: any) =>
        (v.summary && v.summary.toLowerCase().includes(query)) ||
        (v.classroom?.name && v.classroom.name.toLowerCase().includes(query)) ||
        (v.visiteur && v.visiteur.toLowerCase().includes(query)) ||
        (v.personal?.name && v.personal.name.toLowerCase().includes(query)),
    )
  }

  return result
})

// Données de référence pour les labels des filtres
const referenceData = computed(() => ({
  classroom_id: classroomList.value || [],
  date: [],
  fonction_id: fonctionList.value || [],
  professeur: [],
  visiteur: [],
}))

// Labels personnalisés pour les badges de filtre
const customLabels = {
  classroom_id: (value: any, data: any[]) => {
    const classroom = data?.find((c: any) => c.id === value)
    return classroom ? `Classe: ${classroom.name}` : value
  },
  date: (value: any) => `Date: ${value}`,
  professeur: (value: any) => `Prof: ${value}`,
  visiteur: (value: any) => `Visiteur: ${value}`,
  fonction_id: (value: any, data: any[]) => {
    const fonction = data?.find((f: any) => String(f.id) === String(value))
    return fonction ? `Fonction: ${fonction.title}` : value
  },
}

// Fonction pour retirer un filtre
const removeFilter = (key: string) => {
  if (key === 'classroom_id') filterParams.classroom_id = undefined
  if (key === 'date') filterParams.date = undefined
  if (key === 'professeur') filterParams.professeur = undefined
  if (key === 'visiteur') filterParams.visiteur = undefined
  if (key === 'fonction_id') filterParams.fonction_id = undefined
}

// Format date from visit_hour (returns only date: DD/MM/YYYY)
const formatVisitDate = (visitHour: string) => {
  if (!visitHour) return '-'
  const date = new Date(visitHour)
  return date.toLocaleDateString('fr-FR', { day: '2-digit', month: '2-digit', year: 'numeric' })
}

// Format time from visit_hour (returns only time: HH:mm)
const formatVisitTime = (visitHour: string) => {
  if (!visitHour) return '-'
  const date = new Date(visitHour)
  return date.toLocaleTimeString('fr-FR', { hour: '2-digit', minute: '2-digit' })
}

// Récupérer le titre de la fonction à partir de son ID
const getFonctionTitle = (fonctionId: string | number | null) => {
  if (!fonctionId || !fonctions.value) return '-'
  const fonction = fonctions.value.find((f: any) => f.id == fonctionId)
  return fonction?.title || '-'
}

//handleDelete
const handleDelete = async (id: number) => {
  const url = API_ROUTES.DELETE_VISITE_CLASSE.replace(':id', String(id))
  await deleteItem(url)
  fetchData() // Recharger les données après suppression
  if (errorDelete.value) {
    showCustomToast({
      message: errorDelete.value,
      type: 'error',
    })
    console.error('Erreur lors de la suppression :', errorDelete.value)
    return
  } else {
    showCustomToast({
      message: 'Visite de classe supprimée avec succès.',
      type: 'success',
    })
    console.log('Visite de classe supprimée avec succès.')
  }
}

// Convertit l'heure UTC en heure locale (format HH:mm)
const convertToLocalTime = (utcTime: string) => {
  if (!utcTime) return '—'
  const date = new Date(utcTime)
  return date.toLocaleTimeString('fr-FR', { hour: '2-digit', minute: '2-digit' })
}
</script>

<template>
  <LayoutSaisieOperation
    group="operations"
    active-tag-name="visite-classe"
    :breadcrumb="[
      { label: 'Élèves', href: '/apprenants' },
      { label: 'Opérations', href: '/apprenants/operations' },
      { label: 'Visite de Classes', href: '/apprenants/operations/visite-classe' },
    ]"
  >
    <BoxPanelWrapper>
      <div class="flex items-center gap-3 justify-between">
        <div class="flex flex-1 items-center gap-2">
          <div class="relative w-full max-w-xs">
            <Input
              type="text"
              id="search"
              name="search"
              placeholder="Rechercher..."
              v-model="searchQuery"
              class="w-full ps-10 border border-gray-200/40 bg-white transition-all h-10 rounded-md"
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
                <span class="hidden sm:flex">Filtre</span>
                <span class="iconify hugeicons--filter"></span>
              </Button>
            </PopoverTrigger>
            <PopoverContent class="w-80">
              <div class="grid gap-4">
                <div class="space-y-2">
                  <h4 class="font-medium leading-none">Filtrage</h4>
                </div>
                <div class="flex flex-col gap-3.5">
                  <ListClassRoom v-model="filterParams.classroom_id" />
                  <DateFilter v-model="filterParams.date" label="Date de visite" />

                  <!-- Filtre Fonction -->
                  <div class="space-y-1.5">
                    <Label class="text-foreground-muted font-light">Fonction</Label>
                    <Select
                      :model-value="
                        filterParams.fonction_id ? String(filterParams.fonction_id) : ''
                      "
                      @update:model-value="
                        (v) => (filterParams.fonction_id = v ? Number(v) : undefined)
                      "
                    >
                      <SelectTrigger class="w-full">
                        <SelectValue placeholder="Sélectionner une fonction" />
                      </SelectTrigger>
                      <SelectContent>
                        <SelectGroup>
                          <SelectItem v-for="f in fonctionList" :key="f.id" :value="String(f.id)">
                            {{ f.title }}
                          </SelectItem>
                        </SelectGroup>
                      </SelectContent>
                    </Select>
                  </div>

                  <!-- Filtre Professeur -->
                  <div class="space-y-1.5">
                    <Label class="text-foreground-muted font-light">Professeur</Label>
                    <Input
                      v-model="filterParams.professeur"
                      placeholder="Nom du professeur..."
                      class="h-10"
                    />
                  </div>

                  <!-- Filtre Visiteur -->
                  <div class="space-y-1.5">
                    <Label class="text-foreground-muted font-light">Visiteur</Label>
                    <Input
                      v-model="filterParams.visiteur"
                      placeholder="Nom du visiteur..."
                      class="h-10"
                    />
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
          <Button size="md" class="rounded-md max-sm:flex-1 sm:w-max" as-child>
            <RouterLink to="/apprenants/operations/visite-classe/nouvelle">
              <span class="flex iconify hugeicons--plus-sign"></span>
              <span class="hidden sm:flex">Nouvelle visite</span>
            </RouterLink>
          </Button>
        </div>
      </div>

      <!-- Loading, Deleting, Error States -->
      <div v-if="loading || deleting" class="flex justify-center items-center py-10">
        <span class="iconify animate-spin hugeicons--loading-03 text-2xl mr-2"></span>
        <span>{{ loading ? 'Chargement...' : 'Suppression en cours...' }}</span>
      </div>
      <div
        v-else-if="error || errorDelete"
        class="flex justify-center items-center py-10 text-red-600"
      >
        <span class="iconify hugeicons--alert-circle mr-2"></span>
        <span>{{ error || errorDelete }}</span>
      </div>
      <div
        v-else-if="filteredData.length === 0"
        class="mt-4 rounded-md overflow-hidden flex flex-1 bg-white p-8"
      >
        <div class="flex flex-col items-center justify-center w-full gap-3">
          <span class="iconify hugeicons--notebook text-4xl text-foreground-muted/50"></span>
          <p class="text-sm text-foreground-muted">
            {{
              searchQuery
                ? 'Aucune visite trouvée pour cette recherche'
                : 'Aucune visite de classe enregistrée'
            }}
          </p>
        </div>
      </div>
      <!-- Table -->
      <div v-else class="mt-4 rounded-md overflow-hidden flex flex-1 bg-white">
        <Table class="rounded-md bg-white">
          <TableHeader class="">
            <TableRow class="bg-primary">
              <TableHead rowspan="2" class="w-[20px]">
                <Checkbox class="bg-white scale-70" />
              </TableHead>
              <TableHead rowspan="2">Date de visite</TableHead>
              <TableHead rowspan="2">Visite de classe</TableHead>
              <TableHead class="text-center" colspan="4">Classe visitée</TableHead>
              <TableHead class="text-center" colspan="5">Appréciation détaillée</TableHead>
              <TableHead rowspan="2">Professeur</TableHead>
              <TableHead rowspan="2">Visiteur</TableHead>
              <TableHead rowspan="2">Fonction</TableHead>
              <TableHead rowspan="2">Actions</TableHead>
            </TableRow>
            <TableRow class="bg-primary border-t-primary text-white text-xs">
              <TableHead class="!rounded-tl-none">Classe</TableHead>
              <TableHead>Sujet</TableHead>
              <TableHead>Heure début</TableHead>
              <TableHead>Heure fin</TableHead>
              <TableHead>Doc prof (20pts)</TableHead>
              <TableHead>Méthode procédé(12pts)</TableHead>
              <TableHead>Matière (12pts)</TableHead>
              <TableHead>Marche leçon (25pts)</TableHead>
              <TableHead class="!rounded-tr-none">Contrôle doc élève</TableHead>
            </TableRow>
          </TableHeader>
          <TableBody>
            <TableRow v-for="visit in filteredData" :key="visit.id" class="group hover:bg-gray-50">
              <TableCell>
                <Checkbox />
              </TableCell>
              <TableCell>{{ formatVisitDate(visit.visit_hour ?? '') }}</TableCell>
              <TableCell>{{ visit.summary }}</TableCell>
              <TableCell>{{ visit.classroom?.name || '—' }}</TableCell>
              <TableCell>{{ visit.subject || '—' }}</TableCell>
              <TableCell>{{ convertToLocalTime(visit.visit_hour) || '—' }}</TableCell>
              <TableCell>{{ convertToLocalTime(visit.datefin) || '—' }}</TableCell>
              <TableCell>{{ visit.cot_doc_prof || '—' }}</TableCell>
              <TableCell>{{ visit.cot_meth_proc || '—' }}</TableCell>
              <TableCell>{{ visit.cot_matiere || '—' }}</TableCell>
              <TableCell>{{ visit.cot_march_lecon || '—' }}</TableCell>
              <TableCell>{{ visit.cot_doc_eleve || '—' }}</TableCell>
              <TableCell>{{ visit.academic_personal?.name || '—' }}</TableCell>
              <TableCell>{{ visit.visiteur || '—' }}</TableCell>
              <TableCell>{{ getFonctionTitle(visit.fonction_id) }}</TableCell>
              <TableCell>
                <div class="flex items-center gap-2 w-max">
                  <!-- Bouton "..." visible par défaut, caché au hover -->
                  <button
                    class="flex group-hover:hidden rounded-full size-8 items-center justify-center hover:bg-gray-100 transition"
                    aria-label="Plus d'actions"
                  >
                    <span
                      class="iconify hugeicons--more-vertical-circle-01"
                      aria-hidden="true"
                    ></span>
                  </button>

                  <!-- Boutons d'actions cachés par défaut, visibles au hover -->
                  <div class="hidden group-hover:flex items-center gap-2">
                    <Button
                      variant="ghost"
                      size="icon"
                      class="size-8 justify-center text-gray-500 hover:text-blue-600 hover:bg-blue-50"
                      aria-label="Modifier"
                    >
                      <RouterLink :to="`/apprenants/operations/edit-visite-classe/${visit.id}`">
                        <span class="iconify hugeicons--edit-02" aria-hidden="true"></span>
                      </RouterLink>
                    </Button>
                    <AlertMessage
                      action="danger"
                      title="Supprimer une visite de classe"
                      :message="` Vous êtes sur le point de supprimer cette visite de classe!! Êtes-vous sûr de continuer???`"
                    >
                      <template #trigger>
                        <Button
                          variant="ghost"
                          size="icon"
                          class="size-8 justify-center text-gray-500 hover:text-red-600 hover:bg-red-50"
                          aria-label="Supprimer"
                        >
                          <span class="iconify hugeicons--delete-02" aria-hidden="true"></span>
                        </Button>
                      </template>
                      <template #confirm-action-button>
                        <Button
                          variant="destructive"
                          @click="handleDelete(visit.id)"
                          size="sm"
                          aria-label="Supprimer"
                          class="h-10 px-4"
                          :disabled="deleting"
                        >
                          Oui, Supprimer
                        </Button>
                      </template>
                    </AlertMessage>
                  </div>
                </div>
              </TableCell>
            </TableRow>
          </TableBody>
        </Table>
      </div>
    </BoxPanelWrapper>
  </LayoutSaisieOperation>
</template>
