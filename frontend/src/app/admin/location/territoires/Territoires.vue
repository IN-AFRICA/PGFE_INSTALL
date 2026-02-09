<script setup lang="ts">
import DashLayout from '@/components/templates/DashLayout.vue'
import DashPageHeader from '@/components/templates/DashPageHeader.vue'
import { computed, onMounted, ref, watch, onBeforeUnmount, reactive } from 'vue'
import BoxPanelWrapper from '@/components/atoms/BoxPanelWrapper.vue'
import FilterBadges from '@/components/atoms/FilterBadges.vue'
import { Input } from '@/components/ui/input'
import NewTerritories from '@/components/modals/admin/location/NewTerritories.vue'
import { useGetApi } from '@/composables/useGetApi.ts'
import { API_ROUTES } from '@/utils/constants/api_route.ts'
import { eventBus } from '@/utils/eventBus.ts'
import { Popover, PopoverContent, PopoverTrigger } from '@/components/ui/popover'
import ListCountrie from '@/utils/widgets/vues/ListCountrie.vue'
import ListProvince from '@/utils/widgets/vues/ListProvince.vue'
import {
  Select,
  SelectContent,
  SelectGroup,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from '@/components/ui/select'
import { Button } from '@/components/ui/button'
import { useDeleteApi } from '@/composables/useDeleteApi.ts'
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
import { Label } from '@/components/ui/label'
import {
  Dialog,
  DialogContent,
  DialogDescription,
  DialogFooter,
  DialogHeader,
  DialogTitle,
} from '@/components/ui/dialog'
import { usePutApi } from '@/composables/usePutApi.ts'
import IconifySpinner from '@/components/ui/spinner/IconifySpinner.vue'
import TableRowActions from '@/components/molecules/TableRowActions.vue'
interface Item {
  id: number
  name: string
  province_id?: number
  country_id?: number
}
const { putData, loading: updating, error: updError, response: updResponse } = usePutApi()

const editOpen = ref(false)
const editId = ref<number | null>(null)
const editName = ref('')
const editProvinceId = ref<number | undefined>(undefined)
const editCountryId = ref<number | undefined>(undefined)

const editProvinceIdValue = computed<string | undefined>({
  get: () => (editProvinceId.value != null ? String(editProvinceId.value) : undefined),
  set: (v) => {
    editProvinceId.value = v ? Number(v) : undefined
  },
})

function openEditModal(t: Item) {
  editId.value = t.id
  editName.value = t.name
  editProvinceId.value = t.province_id

  // Find country of the province
  const p = (provinces.value || []).find((it: any) => String(it.id) === String(t.province_id))
  editCountryId.value = p ? p.country_id : undefined

  editOpen.value = true
  if (!provinces.value || provinces.value.length === 0) {
    fetchProvinces()
  }
}

async function onSubmitEdit() {
  if (!editId.value) return
  const payload: Record<string, any> = { name: editName.value.trim() }
  if (editProvinceId.value) payload.province_id = editProvinceId.value
  await putData(`${API_ROUTES.GET_TERRITORIES}/${editId.value}`, payload)
  if (updError.value) {
    showCustomToast({ message: updError.value, type: 'error' })
    return
  }
  if (updResponse.value) {
    showCustomToast({ message: 'Territoire mis à jour avec succès', type: 'success' })
    editOpen.value = false
    eventBus.emit('territoryUpdated')
  }
}

const getProvinceName = (provinceId?: number) => {
  if (!provinceId || !provinces.value) return 'N/A'
  const p = provinces.value.find((it: any) => String(it.id) === String(provinceId))
  return p ? p.name : 'N/A'
}

const getCountryNameByProvince = (provinceId?: number) => {
  if (!provinceId || !provinces.value || !countries.value) return 'N/A'
  const p = provinces.value.find((it: any) => String(it.id) === String(provinceId))
  if (!p || !p.country_id) return 'N/A'
  const c = countries.value.find((it: any) => String(it.id) === String(p.country_id))
  return c ? c.name : 'N/A'
}

const breadcrumbItems = {
  items: [
    { label: 'Accueil', href: '/', icon: 'hugeicons--home-01' },
    { label: 'Administration', href: '/admin' },
    { label: 'Territoire', isActive: true },
  ],
}

const adminGeoTags = [
  { name: 'pays', text: 'Pays', href: '/admin/countries' },
  { name: 'provinces', text: 'Provinces', href: '/admin/provinces' },
  { name: 'territoires', text: 'Territoires', href: '/admin/territoires' },
  { name: 'communes', text: 'Communes', href: '/admin/communes' },
]

const activeTagName = computed(() => 'territoires')

const query = ref('')

const filterParams = reactive({
  country_id: undefined as number | undefined,
  province_id: undefined as number | undefined,
})

const referenceData = computed(() => ({
  country_id: countries.value || [],
  province_id: provinces.value || [],
}))

const customLabels = {
  country_id: (value: any, data: any[]) => {
    const country = data?.find((c: any) => String(c.id) === String(value))
    return country ? country.name : value
  },
  province_id: (value: any, data: any[]) => {
    const province = data?.find((p: any) => String(p.id) === String(value))
    return province ? province.name : value
  },
}

const removeFilter = (key: string) => {
  if (key === 'country_id') filterParams.country_id = undefined
  if (key === 'province_id') filterParams.province_id = undefined
}

const { data: countries, fetchData: fetchCountries } = useGetApi<Item[]>(API_ROUTES.GET_COUNTRIES)
const { data: provinces, fetchData: fetchProvinces } = useGetApi<Item[]>(API_ROUTES.GET_PROVINCES)
const { data, loading, error, fetchData } = useGetApi<Item[]>(API_ROUTES.GET_TERRITORIES)

const { deleteItem, deleting, errorDelete: delError } = useDeleteApi()

const deletingId = ref<number | null>(null)

async function onDeleteTerritory(id: number, name: string) {
  deletingId.value = id
  const res = await deleteItem(`${API_ROUTES.GET_TERRITORIES}/${id}`)
  if (res) {
    showCustomToast({ message: `Territoire "${name}" supprimé avec succès.`, type: 'success' })
    eventBus.emit('territoryUpdated')
  } else {
    showCustomToast({ message: delError.value || 'Suppression impossible', type: 'error' })
  }
  deletingId.value = null
}

onMounted(() => {
  fetchCountries()
  fetchProvinces()
  reload()
  eventBus.on('territoryUpdated', onRefresh)
})

onBeforeUnmount(() => {
  eventBus.off('territoryUpdated', onRefresh)
})

function onRefresh() {
  reload()
}

watch(
  () => filterParams.country_id,
  async (id) => {
    filterParams.province_id = undefined
    if (id) await fetchProvinces({ country_id: id })
    reload()
  },
)

watch(
  () => filterParams.province_id,
  () => {
    reload()
  },
)

watch(editCountryId, (newId) => {
  if (newId) {
    fetchProvinces({ country_id: newId })
  } else {
    fetchProvinces()
  }
})

function reload() {
  const params: Record<string, any> = {}
  if (filterParams.country_id) params.country_id = filterParams.country_id
  if (filterParams.province_id) params.province_id = filterParams.province_id
  fetchData(params)
}

const filtered = computed(() => {
  const list = data.value || []
  const q = query.value.trim().toLowerCase()
  if (!q) return list
  return list.filter((it: Item) => it.name.toLowerCase().includes(q))
})
</script>

<template>
  <DashLayout :breadcrumb="breadcrumbItems" active-route="/admin" module-name="admin">
    <div class="pb-6 mx-auto w-full max-w-7xl">
      <DashPageHeader
        title="Administration"
        :tags="adminGeoTags"
        :active-tag-name="activeTagName"
      />

      <BoxPanelWrapper>
        <div class="flex items-center gap-3 justify-between">
          <div class="flex flex-1 items-center gap-2">
            <div class="relative w-full max-w-sm">
              <Input
                v-model="query"
                placeholder="Rechercher un territoire..."
                class="w-full ps-10 h-10"
              />
              <div class="absolute left-3 top-1/2 -translate-y-1/2 text-foreground-muted/70">
                <span class="iconify hugeicons--search-01 text-sm"></span>
              </div>
            </div>

            <Popover>
              <PopoverTrigger as-child>
                <Button variant="ghost" class="h-10 border bg-white gap-2">
                  <span class="iconify hugeicons--filter text-sm"></span>
                  <span>Filtre</span>
                </Button>
              </PopoverTrigger>
              <PopoverContent class="w-80">
                <div class="grid gap-4">
                  <div class="space-y-2">
                    <h4 class="font-medium leading-none">Filtrage</h4>
                  </div>
                  <div class="flex flex-col gap-3.5">
                    <ListCountrie v-model="filterParams.country_id" />
                    <ListProvince v-model="filterParams.province_id" />
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
          <div class="flex items-center gap-2">
            <NewTerritories />
          </div>
        </div>

        <div class="mt-4 flex-1 min-h-0 overflow-auto bg-white rounded-md border border-gray-100">
          <div v-if="loading" class="p-4 text-sm text-gray-600">Chargement...</div>
          <div v-else-if="error" class="p-4 text-sm text-red-600">{{ error }}</div>
          <template v-else>
            <div v-if="(filtered || []).length === 0" class="p-4 text-sm text-gray-600">
              Aucun territoire trouvé.
            </div>
            <div v-else class="w-full">
              <div
                class="px-4 py-3 bg-gray-200 border-b border-gray-100 flex items-center text-sm font-semibold text-foreground-title"
              >
                <div class="w-12">N°</div>
                <div class="flex-1">Nom du Territoire</div>
                <div class="flex-1 hidden sm:block">Province</div>
                <div class="flex-1 hidden sm:block">Pays</div>
                <div class="w-24 text-right">Actions</div>
              </div>
              <ul class="divide-y">
                <li
                  v-for="(t, index) in filtered"
                  :key="t.id"
                  class="px-4 py-3 flex items-center text-sm"
                >
                  <div class="w-12 text-gray-500">{{ index + 1 }}</div>
                  <div class="flex-1 font-medium">{{ t.name }}</div>
                  <div class="flex-1 hidden sm:block text-gray-500">
                    {{ getProvinceName(t.province_id) }}
                  </div>
                  <div class="flex-1 hidden sm:block text-gray-500">
                    {{ getCountryNameByProvince(t.province_id) }}
                  </div>
                  <div class="w-24 flex justify-end">
                    <TableRowActions>
                      <template #actions>
                        <Button size="sm" variant="outline" class="h-8" @click="openEditModal(t)">
                          <span class="iconify hugeicons--edit-01"></span>
                          <span class="sr-only">Modifier</span>
                        </Button>
                        <AlertDialog>
                          <AlertDialogTrigger as-child>
                            <Button size="sm" variant="destructive" class="h-8">
                              <span class="iconify hugeicons--delete-02"></span>
                              <span class="sr-only">Supprimer</span>
                            </Button>
                          </AlertDialogTrigger>
                          <AlertDialogContent>
                            <AlertDialogHeader>
                              <AlertDialogTitle>Supprimer ce territoire ?</AlertDialogTitle>
                              <AlertDialogDescription>
                                Action irréversible. Si des éléments dépendent de ce territoire, la
                                suppression peut être refusée.
                              </AlertDialogDescription>
                            </AlertDialogHeader>
                            <AlertDialogFooter>
                              <AlertDialogCancel>Annuler</AlertDialogCancel>
                              <Button
                                variant="destructive"
                                :disabled="deleting && deletingId === t.id"
                                @click="onDeleteTerritory(t.id, t.name)"
                              >
                                <span
                                  v-if="deleting && deletingId === t.id"
                                  class="flex items-center gap-2"
                                >
                                  <IconifySpinner size="sm" />
                                  <span>Suppression...</span>
                                </span>
                                <span v-else>Confirmer</span>
                              </Button>
                            </AlertDialogFooter>
                          </AlertDialogContent>
                        </AlertDialog>
                      </template>
                    </TableRowActions>
                  </div>
                </li>
                <Dialog v-model:open="editOpen">
                  <DialogContent class="sm:max-w-[480px]">
                    <DialogHeader>
                      <DialogTitle>Modifier un Territoire</DialogTitle>
                      <DialogDescription
                        >Mettre à jour les informations du territoire</DialogDescription
                      >
                    </DialogHeader>
                    <form @submit.prevent="onSubmitEdit">
                      <div class="grid gap-4 py-4">
                        <div class="flex flex-col space-y-1.5">
                          <Label for="name" class="text-sm font-medium">Nom</Label>
                          <Input
                            id="name"
                            v-model="editName"
                            class="h-10"
                            :disabled="updating"
                            required
                          />
                        </div>
                        <div class="flex flex-col gap-3.5">
                          <ListCountrie v-model="editCountryId" />
                          <div class="flex flex-col space-y-1.5 flex-1">
                            <Label for="province" class="text-sm font-medium">Province</Label>
                            <Select v-model="editProvinceIdValue" :disabled="updating">
                              <SelectTrigger class="h-10 w-full">
                                <SelectValue placeholder="Sélectionnez une province" />
                              </SelectTrigger>
                              <SelectContent>
                                <SelectGroup>
                                  <SelectItem
                                    v-for="p in provinces || []"
                                    :key="p.id"
                                    :value="String(p.id)"
                                  >
                                    {{ p.name }}
                                  </SelectItem>
                                </SelectGroup>
                              </SelectContent>
                            </Select>
                          </div>
                        </div>
                      </div>
                      <DialogFooter class="flex justify-end gap-2 items-center">
                        <Button
                          size="sm"
                          class="h-9"
                          variant="outline"
                          type="button"
                          :disabled="updating"
                          @click="editOpen = false"
                          >Annuler</Button
                        >
                        <Button size="sm" class="h-9" type="submit" :disabled="updating">
                          <span v-if="!updating" class="flex items-center gap-2">
                            <span class="iconify hugeicons--floppy-disk"></span>
                            <span>Enregistrer</span>
                          </span>
                          <span v-else class="flex items-center gap-2">
                            <IconifySpinner size="lg" />
                            <span>Mise à jour...</span>
                          </span>
                        </Button>
                      </DialogFooter>
                    </form>
                  </DialogContent>
                </Dialog>
              </ul>
            </div>
          </template>
        </div>
      </BoxPanelWrapper>
    </div>
  </DashLayout>
</template>

<style scoped></style>
