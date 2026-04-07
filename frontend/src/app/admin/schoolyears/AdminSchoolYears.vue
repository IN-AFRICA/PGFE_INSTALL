<script setup lang="ts">
import DashLayout from '@/components/templates/DashLayout.vue'
import DashPageHeader from '@/components/templates/DashPageHeader.vue'
import BoxPanelWrapper from '@/components/atoms/BoxPanelWrapper.vue'
import { onMounted, ref, computed, onBeforeUnmount } from 'vue'
import { useGetApi } from '@/composables/useGetApi.ts'
import { usePostApi } from '@/composables/usePostApi.ts'
import { API_ROUTES } from '@/utils/constants/api_route.ts'
import { eventBus } from '@/utils/eventBus.ts'
import { useAuthStore } from '@/stores/auth.ts'
import { Button } from '@/components/ui/button'
import { showCustomToast } from '@/utils/widgets/custom_toast.ts'
import IconifySpinner from '@/components/ui/spinner/IconifySpinner.vue'
import TableRowActions from '@/components/molecules/TableRowActions.vue'
import {
  AlertDialog,
  AlertDialogTrigger,
  AlertDialogContent,
  AlertDialogHeader,
  AlertDialogTitle,
  AlertDialogDescription,
  AlertDialogFooter,
  AlertDialogCancel,
} from '@/components/ui/alert-dialog'

interface SchoolYear {
  id: number
  name: string
  is_active?: boolean
  status?: string
}

const auth = useAuthStore()
const canActivate = computed(() => auth.can('schoolyears.activate'))
const canCreate = computed(() => auth.can('schoolyears.create'))
const canUpdate = computed(() => auth.can('schoolyears.update'))
const canDelete = computed(() => auth.can('schoolyears.delete'))

const breadcrumbItems = {
  items: [
    { label: 'Accueil', href: '/', icon: 'hugeicons--home-01' },
    { label: 'Administration', href: '/admin' },
    { label: 'Années Scolaires', isActive: true },
  ],
}

const adminYearTags = [
  { name: 'schoolyears', text: 'Années Scolaires', href: '/admin/schoolyears' },
]
const activeTagName = computed(() => 'schoolyears')

const { data, loading, error, fetchData } = useGetApi<SchoolYear[]>(API_ROUTES.GET_SCHOOL_YEARS)
const { postData: activatePost, loading: activating, error: activateError } = usePostApi()

const activatingId = ref<number | null>(null)

function isActive(year: SchoolYear): boolean {
  return year.is_active === true || year.status === 'active'
}

async function onActivateYear(year: SchoolYear) {
  activatingId.value = year.id
  await activatePost(API_ROUTES.ACTIVATE_SCHOOL_YEAR(year.id), {})
  if (activateError.value) {
    showCustomToast({ message: activateError.value, type: 'error' })
  } else {
    showCustomToast({ message: `Année "${year.name}" activée avec succès`, type: 'success' })
    eventBus.emit('schoolYearsUpdated')
  }
  activatingId.value = null
}

function onRefresh() {
  fetchData()
}

onMounted(() => {
  fetchData()
  eventBus.on('schoolYearsUpdated', onRefresh)
})

onBeforeUnmount(() => {
  eventBus.off('schoolYearsUpdated', onRefresh)
})
</script>

<template>
  <DashLayout
    :breadcrumb="breadcrumbItems"
    active-route="/admin/schoolyears"
    module-name="admin"
  >
    <div class="pb-6 mx-auto w-full max-w-7xl">
      <DashPageHeader
        title="Administration"
        :tags="adminYearTags"
        :active-tag-name="activeTagName"
      />

      <BoxPanelWrapper>
        <div class="flex items-center gap-3 justify-between">
          <div class="relative flex-1">
            <!-- Pas de recherche textuelle sur les années, espace réservé pour l'alignement -->
          </div>
          <div class="flex items-center gap-2">
            <Button v-if="canCreate">
              <span class="iconify hugeicons--add-01"></span>
              <span>Nouvelle année</span>
            </Button>
          </div>
        </div>

        <div class="mt-4 flex-1 min-h-0 overflow-auto bg-white rounded-md border border-gray-100">
          <div v-if="loading" class="p-4 text-sm text-gray-600">Chargement...</div>
          <div v-else-if="error" class="p-4 text-sm text-red-600">{{ error }}</div>
          <template v-else>
            <div v-if="(data || []).length === 0" class="p-4 text-sm text-gray-600">
              Aucune année scolaire trouvée.
            </div>
            <div v-else class="w-full">
              <div
                class="px-4 py-3 bg-gray-200 border-b border-gray-100 flex items-center text-sm font-semibold text-foreground-title"
              >
                <div class="w-12">N°</div>
                <div class="flex-1">Nom de l'année</div>
                <div class="w-36">Statut</div>
                <div class="w-24 text-right">Actions</div>
              </div>
              <ul class="divide-y">
                <li
                  v-for="(year, index) in data"
                  :key="year.id"
                  class="px-4 py-3 flex items-center text-sm"
                >
                  <div class="w-12 text-gray-500">{{ index + 1 }}</div>
                  <div class="flex-1 flex items-center gap-3">
                    <span class="iconify hugeicons--calendar-03 text-primary"></span>
                    <span class="font-medium">{{ year.name }}</span>
                  </div>
                  <div class="w-36">
                    <span
                      v-if="isActive(year)"
                      class="inline-flex items-center gap-1 rounded-full bg-green-100 px-2.5 py-1 text-xs font-medium text-green-700"
                    >
                      <span class="iconify hugeicons--checkmark-circle-02 text-xs"></span>
                      Active
                    </span>
                    <span
                      v-else
                      class="inline-flex items-center gap-1 rounded-full bg-gray-100 px-2.5 py-1 text-xs font-medium text-gray-500"
                    >
                      Inactive
                    </span>
                  </div>
                  <div class="w-24 flex justify-end">
                    <TableRowActions>
                      <template #actions>
                        <!-- Activer : accessible à admin-ecole (schoolyears.activate) -->
                        <AlertDialog v-if="canActivate && !isActive(year)">
                          <AlertDialogTrigger as-child>
                            <Button
                              size="sm"
                              variant="outline"
                              class="h-8"
                              :disabled="activating && activatingId === year.id"
                            >
                              <span
                                v-if="activating && activatingId === year.id"
                                class="flex items-center gap-2"
                              >
                                <IconifySpinner size="sm" />
                              </span>
                              <span v-else class="iconify hugeicons--checkmark-circle-02"></span>
                              <span class="sr-only">Activer</span>
                            </Button>
                          </AlertDialogTrigger>
                          <AlertDialogContent>
                            <AlertDialogHeader>
                              <AlertDialogTitle>Activer cette année scolaire ?</AlertDialogTitle>
                              <AlertDialogDescription>
                                L'année <strong>{{ year.name }}</strong> deviendra l'année active.
                                Toutes les opérations seront liées à cette année.
                              </AlertDialogDescription>
                            </AlertDialogHeader>
                            <AlertDialogFooter>
                              <AlertDialogCancel>Annuler</AlertDialogCancel>
                              <Button @click="onActivateYear(year)">Confirmer</Button>
                            </AlertDialogFooter>
                          </AlertDialogContent>
                        </AlertDialog>
                        <!-- Modifier : réservé aux rôles avec schoolyears.update -->
                        <Button v-if="canUpdate" size="sm" variant="outline" class="h-8">
                          <span class="iconify hugeicons--edit-01"></span>
                          <span class="sr-only">Modifier</span>
                        </Button>
                        <!-- Supprimer : réservé aux rôles avec schoolyears.delete -->
                        <Button v-if="canDelete" size="sm" variant="destructive" class="h-8">
                          <span class="iconify hugeicons--delete-02"></span>
                          <span class="sr-only">Supprimer</span>
                        </Button>
                      </template>
                    </TableRowActions>
                  </div>
                </li>
              </ul>
            </div>
          </template>
        </div>
      </BoxPanelWrapper>
    </div>
  </DashLayout>
</template>

<style scoped></style>
