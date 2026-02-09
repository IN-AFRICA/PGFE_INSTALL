<script setup lang="ts">
import { useRoute, useRouter } from 'vue-router'
import { computed, ref, onMounted, watch } from 'vue'
import { useAuthStore } from '@/stores/auth.ts'
import { useBulletinStore } from '@/stores/bulletin'
import { useGetApi } from '@/composables/useGetApi'
import { useAcademicLevels } from '@/composables/useAcademicLevels'
import { API_ROUTES } from '@/utils/constants/api_route'
import api from '@/services/api'
import { showCustomToast } from '@/utils/widgets/custom_toast.ts'
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
import {
  Dialog,
  DialogContent,
  DialogDescription,
  DialogFooter,
  DialogHeader,
  DialogTitle,
} from '@/components/ui/dialog'
import { Button } from '@/components/ui/button'
import { Label } from '@/components/ui/label'
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from '@/components/ui/select'
import Spinner from '@/components/ui/spinner/Spinner.vue'

// Import moved to top
import { modulesNavigation } from '@/data/navigations'
import { cn } from '@/lib/utils'
import { Tooltip, TooltipContent, TooltipTrigger, TooltipProvider } from '@/components/ui/tooltip'

const router = useRouter()
const route = useRoute()
const auth = useAuthStore()
const bulletinStore = useBulletinStore()

// Charger les Filières (Sections)
const { data: filieresData, fetchData: fetchFilieres } = useGetApi(API_ROUTES.GET_FILLIERES)

const filieresOptions = computed(() => {
  const v = filieresData.value
  if (Array.isArray(v)) return v
  if ((v as any)?.data && Array.isArray((v as any).data)) return (v as any).data
  return []
})

// Gestion des Niveaux (dépend de la filière sélectionnée)
const filiereIdForComposable = computed(() =>
  bulletinStore.selectedFiliereId === 'all' ? undefined : bulletinStore.selectedFiliereId,
)
const { levelOptions, loadLevels } = useAcademicLevels(filiereIdForComposable, undefined)

// Charger les données au montage si on ouvre le modal
onMounted(async () => {
  // Toujours charger les années scolaires car nécessaire pour le filtre
  if (!bulletinStore.schoolYears.length) {
    await bulletinStore.loadSchoolYears()
  }

  if (isBulletinPage.value) {
    await fetchReferenceData()

    // Ouvrir le modal uniquement si aucune classe n'est sélectionnée ET qu'on ne l'a pas déjà fermé manuellement dans cette session
    const configShown = sessionStorage.getItem('bulletinConfigShown')
    if (!bulletinStore.selectedClasseId && !configShown) {
      openBulletinConfig.value = true
      sessionStorage.setItem('bulletinConfigShown', 'true')
    }
  }
})

const fetchReferenceData = async () => {
  await fetchFilieres()
  await loadLevels()
}

// Filtrer les classes disponibles
const filteredClasses = computed(() => {
  let classes = bulletinStore.classes

  // Filtre par Filière (Section) - si pas 'all'
  if (bulletinStore.selectedFiliereId && bulletinStore.selectedFiliereId !== 'all') {
    classes = classes.filter((c: any) => {
      // Vérifier si la classe a une filière liée via son niveau
      const classFiliereId = c?.academic_level?.cycle?.filiaire?.id
      return String(classFiliereId) === String(bulletinStore.selectedFiliereId)
    })
  }

  // Filtre par Niveau - si pas 'all'
  if (bulletinStore.selectedLevelId && bulletinStore.selectedLevelId !== 'all') {
    classes = classes.filter(
      (c: any) => String(c.academic_level_id) === String(bulletinStore.selectedLevelId),
    )
  }

  return classes
})

// Reset level when filiere changes
watch(
  () => bulletinStore.selectedFiliereId,
  () => {
    bulletinStore.selectedLevelId = 'all'
  },
)

const props = defineProps<{
  onlySettings?: boolean
  moduleName?: keyof typeof modulesNavigation
  activeRoute?: string
}>()

// Filter items based on permission
const items = computed(() => {
  const navItems = props.moduleName ? modulesNavigation[props.moduleName] : []
  return navItems.filter((item: any) => {
    if (!item.permission) return true
    return auth.can(item.permission)
  })
})

// Show floating print button only on bulletin page
const isBulletinPage = computed(() =>
  route.path.startsWith('/apprenants/operations/bulletin-scolaire'),
)

const isPrinting = ref(false)

const handlePrint = async () => {
  if (!bulletinStore.selectedEleveId) {
    showCustomToast({
      message: 'Veuillez sélectionner un élève',
      type: 'warning',
    })
    return
  }

  isPrinting.value = true

  try {
    const params: any = { student_id: bulletinStore.selectedEleveId }

    if (bulletinStore.selectedSchoolYearId) {
      params.school_year_id = bulletinStore.selectedSchoolYearId
    }

    const response = await api.get(API_ROUTES.PRINT_BULLETIN, {
      params,
      responseType: 'blob',
    })

    const blob = new Blob([response.data], { type: 'application/pdf' })
    const url = window.URL.createObjectURL(blob)
    const link = document.createElement('a')
    link.href = url

    const studentName = bulletinStore.selectedEleve?.full_name || 'bulletin'
    link.download = `bulletin_${studentName.replace(/\s+/g, '_')}.pdf`

    document.body.appendChild(link)
    link.click()
    document.body.removeChild(link)
    window.URL.revokeObjectURL(url)

    showCustomToast({
      message: 'Bulletin téléchargé avec succès',
      type: 'success',
    })
  } catch (error: any) {
    console.error("Erreur lors de l'impression:", error)
    showCustomToast({
      message: 'Erreur lors de la génération du bulletin',
      type: 'error',
    })
  } finally {
    isPrinting.value = false
  }
}

const isLogoutDialogOpen = ref(false)
const loggingOut = ref(false)

const confirmLogout = async () => {
  try {
    loggingOut.value = true
    await new Promise((resolve) => setTimeout(resolve, 2000))
    await auth.logout()
    showCustomToast({
      message: 'Déconnexion réussie',
      type: 'success',
    })
    isLogoutDialogOpen.value = false
    router.push('/login')
  } catch (error) {
    console.error('Logout error:', error)
  } finally {
    loggingOut.value = false
  }
}

// Bulletin configuration modal state
const openBulletinConfig = ref(false)

const applyBulletinConfig = () => {
  if (bulletinStore.selectedEleveId) {
    bulletinStore.fetchBulletinData(bulletinStore.selectedEleveId)
  }
  openBulletinConfig.value = false
  showCustomToast({
    message: 'Configuration appliquée',
    type: 'success',
  })
}
</script>
<template>
  <TooltipProvider>
    <aside
      class="fixed z-50 md:left-0 md:h-screen md:top-0 md:w-32 lg:w-40 xl:w-48 md:flex md:flex-col md:justify-between md:items-center md:p-2 lg:p-4"
    >
      <div
        :class="[
          'hidden md:flex flex-col items-center gap-6',
          props.onlySettings ? 'invisible' : 'visible',
        ]"
      >
        <router-link to="/">
          <img src="/pgfe-logo.png" alt="pattern background" class="size-18 object-cover" />
        </router-link>
        <Tooltip>
          <TooltipTrigger as-child>
            <router-link
              to="/"
              aria-label="Retour à l'accueil"
              class="rounded-lg bg-white shadow-lg shadow-gray-200/20 flex flex-col md:flex-row md:items-center justify-center md:justify-start text-center md:text-left items-center gap-1 md:gap-2 px-2 py-2 md:px-3 md:py-2.5 min-h-[60px] md:min-h-[48px] w-full"
            >
              <span
                class="flex iconify hugeicons--arrow-left-02 text-lg md:text-base shrink-0"
              ></span>
              <span class="text-xs md:text-sm font-medium leading-tight line-clamp-2">Retour</span>
            </router-link>
          </TooltipTrigger>
          <TooltipContent>
            <p>Retour à l'accueil</p>
          </TooltipContent>
        </Tooltip>
      </div>
      <div v-if="!props.onlySettings" class="flex">
        <div class="menubottom flex flex-1 lg:relative">
          <ul
            class="bg-white shadow-lg shadow-gray-200/20 px-2 py-1 [--card-padding:calc(var(--spacing)*1.5)] [--card-radius:var(--radius-lg)] rounded-(--card-radius) md:py-(--card-padding) md:px-(--card-padding) flex md:flex-col items-center space-x-1 md:space-x-0 md:space-y-2"
          >
            <li v-for="item in items.slice(0, 4)" :key="item.id" class="flex-1 md:flex-none w-full">
              <Tooltip>
                <TooltipTrigger as-child>
                  <RouterLink
                    :to="item.link"
                    :class="
                      cn(
                        'flex flex-col md:flex-row md:items-center justify-center md:justify-start text-center md:text-left w-full items-center gap-1 md:gap-2 px-2 py-2 md:px-3 md:py-2.5 rounded-lg text-foreground-muted ease-linear transition-colors min-h-[60px] md:min-h-[48px]',
                        {
                          'bg-primary text-white': item.link === activeRoute,
                          'hover:bg-muted': item.link !== activeRoute,
                        },
                      )
                    "
                  >
                    <span :class="['iconify text-lg md:text-base flex shrink-0', item.icon]" />
                    <span class="text-xs md:text-sm font-medium leading-tight line-clamp-2">
                      {{ item.text }}
                    </span>
                  </RouterLink>
                </TooltipTrigger>
                <TooltipContent>
                  <p>{{ item.text }}</p>
                </TooltipContent>
              </Tooltip>
            </li>
            <li v-if="items.length >= 5" class="flex-1 md:flex-none w-full">
              <Tooltip>
                <TooltipTrigger as-child>
                  <button
                    aria-label="Afficher plus d'options"
                    :class="
                      cn(
                        'flex cursor-pointer flex-col md:flex-row md:items-center justify-center md:justify-start text-center md:text-left w-full items-center gap-1 md:gap-2 px-2 py-2 md:px-3 md:py-2.5 rounded-lg text-foreground-muted ease-linear transition-colors hover:bg-muted min-h-[60px] md:min-h-[48px]',
                      )
                    "
                  >
                    <span class="iconify hugeicons--more-02 text-lg md:text-base flex shrink-0" />
                    <span class="text-xs md:text-sm font-medium leading-tight line-clamp-2">
                      Plus d'options
                    </span>
                  </button>
                </TooltipTrigger>
                <TooltipContent>
                  <p>Plus d'options</p>
                </TooltipContent>
              </Tooltip>
            </li>
          </ul>
        </div>
      </div>
      <div
        :class="[
          'hidden md:flex flex-col items-center bg-white shadow-lg shadow-gray-200/20 px-2 py-1 [--card-padding:calc(var(--spacing)*1.5)] [--card-radius:var(--radius-lg)] rounded-(--card-radius) md:py-(--card-padding) md:px-(--card-padding) space-y-2',
          props.onlySettings ? 'border border-border/75' : '',
        ]"
      >
        <Tooltip>
          <AlertDialog v-model:open="isLogoutDialogOpen">
            <TooltipTrigger as-child>
              <AlertDialogTrigger as-child>
                <button
                  :class="
                    cn(
                      'flex flex-col md:flex-row md:items-center justify-center md:justify-start text-center md:text-left w-full items-center gap-1 md:gap-2 px-2 py-2 md:px-3 md:py-2.5 rounded-lg text-foreground-muted ease-linear transition-colors hover:bg-muted min-h-[60px] md:min-h-[48px]',
                      {},
                    )
                  "
                >
                  <span
                    :class="['iconify hugeicons--logout-05 text-lg md:text-base flex shrink-0']"
                  />
                  <span class="text-xs md:text-sm font-medium leading-tight line-clamp-2"
                    >Déconnexion</span
                  >
                </button>
              </AlertDialogTrigger>
            </TooltipTrigger>
            <TooltipContent>
              <p>Déconnexion</p>
            </TooltipContent>

            <AlertDialogContent>
              <AlertDialogHeader>
                <AlertDialogTitle>Confirmer la déconnexion</AlertDialogTitle>
                <AlertDialogDescription>
                  Voulez-vous vraiment vous déconnecter ? Cette action vous redirigera vers la page
                  de connexion.
                </AlertDialogDescription>
              </AlertDialogHeader>
              <AlertDialogFooter>
                <AlertDialogCancel :disabled="loggingOut">Annuler</AlertDialogCancel>
                <Button :disabled="loggingOut" @click="confirmLogout">
                  <span v-if="!loggingOut" class="flex items-center gap-2">
                    <span>Confirmer</span>
                  </span>
                  <span v-else class="flex items-center gap-2">
                    <span class="iconify animate-spin hugeicons--loading-03 text-xl"></span>
                    <span>Déconnexion...</span>
                  </span>
                </Button>
              </AlertDialogFooter>
            </AlertDialogContent>
          </AlertDialog>
        </Tooltip>
        <Tooltip v-if="0 > 1">
          <TooltipTrigger as-child>
            <RouterLink
              to="#"
              :class="
                cn(
                  'flex flex-col md:flex-row md:items-center justify-center md:justify-start text-center md:text-left w-full items-center gap-1 md:gap-2 px-2 py-2 md:px-3 md:py-2.5 rounded-lg text-foreground-muted ease-linear transition-colors hover:bg-muted min-h-[60px] md:min-h-[48px]',
                  {},
                )
              "
            >
              <span :class="['iconify hugeicons--setting-07 text-lg md:text-base flex shrink-0']" />
              <span class="text-xs md:text-sm font-medium leading-tight line-clamp-2"
                >Réglages</span
              >
            </RouterLink>
          </TooltipTrigger>
          <TooltipContent>
            <p>Réglages</p>
          </TooltipContent>
        </Tooltip>
      </div>
    </aside>

    <!-- Floating buttons on bulletin page -->
    <div v-if="isBulletinPage" class="fixed bottom-6 right-6 z-50 flex flex-col gap-3">
      <!-- Settings button -->
      <!-- Print button -->
      <div
        class="px-2 py-1 shadow-lg shadow-gray-200/20 bg-white [--card-padding:calc(var(--spacing)*1.5)] [--card-radius:var(--radius-lg)] rounded-(--card-radius) md:py-(--card-padding) md:px-(--card-padding)"
      >
        <Tooltip>
          <TooltipTrigger as-child>
            <button
              aria-label="Paramètres du bulletin"
              @click="openBulletinConfig = true"
              :class="
                cn(
                  'flex flex-col md:flex-row md:items-center justify-center md:justify-start text-center md:text-left w-full items-center gap-1 md:gap-2 px-2 py-2 md:px-3 md:py-2.5 rounded-lg text-foreground-muted ease-linear transition-colors hover:bg-muted min-h-[60px] md:min-h-[48px]',
                  {},
                )
              "
            >
              <span class="iconify hugeicons--settings-02 text-lg md:text-base flex shrink-0" />
              <span class="text-xs md:text-sm font-medium leading-tight line-clamp-2"
                >Paramètres</span
              >
            </button>
          </TooltipTrigger>
          <TooltipContent>
            <p>Paramètres du bulletin</p>
          </TooltipContent>
        </Tooltip>

        <Tooltip>
          <TooltipTrigger as-child>
            <button
              aria-label="Imprimer le bulletin"
              @click="handlePrint"
              :disabled="isPrinting"
              :class="
                cn(
                  'flex flex-col md:flex-row md:items-center justify-center md:justify-start text-center md:text-left w-full items-center gap-1 md:gap-2 px-2 py-2 md:px-3 md:py-2.5 rounded-lg text-foreground-muted ease-linear transition-colors min-h-[60px] md:min-h-[48px]',
                  isPrinting ? 'opacity-50 cursor-not-allowed' : 'hover:bg-muted',
                )
              "
            >
              <span
                v-if="isPrinting"
                class="iconify hugeicons--loading-03 animate-spin text-lg md:text-base flex shrink-0"
              />
              <span v-else class="iconify hugeicons--printer text-lg md:text-base flex shrink-0" />
              <span class="text-xs md:text-sm font-medium leading-tight line-clamp-2">
                {{ isPrinting ? 'Génération...' : 'Imprimer' }}
              </span>
            </button>
          </TooltipTrigger>
          <TooltipContent>
            <p>Imprimer le bulletin</p>
          </TooltipContent>
        </Tooltip>
      </div>
    </div>

    <!-- Bulletin configuration modal -->
    <Dialog v-model:open="openBulletinConfig">
      <DialogContent class="sm:max-w-[420px]">
        <DialogHeader>
          <DialogTitle>Configuration du bulletin</DialogTitle>
          <DialogDescription>
            Configurez les paramètres du bulletin pour l'affichage
          </DialogDescription>
        </DialogHeader>
        <div class="grid gap-4 py-4">
          <!-- Année Scolaire -->
          <div class="flex flex-col space-y-1.5">
            <Label for="schoolYear" class="text-sm font-medium">Année Scolaire</Label>
            <Select v-model="bulletinStore.selectedSchoolYearId">
              <SelectTrigger>
                <SelectValue placeholder="Sélectionner une année" />
              </SelectTrigger>
              <SelectContent>
                <SelectItem
                  v-for="year in bulletinStore.schoolYears"
                  :key="year.id"
                  :value="String(year.id)"
                >
                  {{ year.name }}
                </SelectItem>
              </SelectContent>
            </Select>
          </div>

          <!-- Section (Filière) -->
          <div class="flex flex-col space-y-1.5">
            <Label for="filiere" class="text-sm font-medium">Section</Label>
            <Select v-model="bulletinStore.selectedFiliereId">
              <SelectTrigger>
                <SelectValue placeholder="Toutes les sections" />
              </SelectTrigger>
              <SelectContent>
                <SelectItem value="all">Toutes les sections</SelectItem>
                <SelectItem v-for="f in filieresOptions" :key="f.id" :value="String(f.id)">
                  {{ f.name }}
                </SelectItem>
              </SelectContent>
            </Select>
          </div>

          <!-- Niveau -->
          <div class="flex flex-col space-y-1.5">
            <Label for="niveau" class="text-sm font-medium">Niveau</Label>
            <Select v-model="bulletinStore.selectedLevelId" :disabled="!levelOptions.length">
              <SelectTrigger>
                <SelectValue placeholder="Tous les niveaux" />
              </SelectTrigger>
              <SelectContent>
                <SelectItem value="all">Tous les niveaux</SelectItem>
                <SelectItem v-for="l in levelOptions" :key="l.id" :value="String(l.id)">
                  {{ l.label }}
                </SelectItem>
              </SelectContent>
            </Select>
          </div>
        </div>
        <DialogFooter class="flex justify-end gap-2 items-center">
          <Button
            size="sm"
            class="h-9"
            variant="outline"
            type="button"
            @click="openBulletinConfig = false"
          >
            <span class="iconify flex hugeicons--cancel-01 mr-1"></span>
            Fermer
          </Button>
          <Button size="sm" class="h-9" type="button" @click="applyBulletinConfig">
            <span class="iconify hugeicons--check mr-1"></span>
            Appliquer
          </Button>
        </DialogFooter>
      </DialogContent>
    </Dialog>
  </TooltipProvider>
</template>
