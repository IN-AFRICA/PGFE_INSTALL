<script setup lang="ts">
import DashLayout from '@/components/templates/DashLayout.vue'
import DashPageHeader from '@/components/templates/DashPageHeader.vue'
import BoxPanelWrapper from '@/components/atoms/BoxPanelWrapper.vue'
import { onMounted, ref, computed, onBeforeUnmount } from 'vue'
import { useGetApi } from '@/composables/useGetApi.ts'
import { useDeleteApi } from '@/composables/useDeleteApi.ts'
import { usePostApi } from '@/composables/usePostApi.ts'
import { usePutApi } from '@/composables/usePutApi.ts'
import { API_ROUTES } from '@/utils/constants/api_route.ts'
import { eventBus } from '@/utils/eventBus.ts'
import { useAuthStore } from '@/stores/auth.ts'
import { Input } from '@/components/ui/input'
import { Button } from '@/components/ui/button'
import { Label } from '@/components/ui/label'
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
import {
  Dialog,
  DialogContent,
  DialogDescription,
  DialogFooter,
  DialogHeader,
  DialogTitle,
} from '@/components/ui/dialog'
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from '@/components/ui/select'

interface AdminUser {
  id: number
  name: string
  email: string
  roles?: { name: string }[]
}

const auth = useAuthStore()
const canCreate = computed(() => auth.can('users.create.any') || auth.can('users.create.tiers'))
const canUpdate = computed(() => auth.can('users.update'))
const canDelete = computed(() => auth.can('users.delete'))
// Admin-ecole : rôle forcé à "tiers", super-admin peut choisir n'importe quel rôle
const forceTiers = computed(() => !auth.can('users.create.any'))

const breadcrumbItems = {
  items: [
    { label: 'Accueil', href: '/', icon: 'hugeicons--home-01' },
    { label: 'Administration', href: '/admin' },
    { label: 'Utilisateurs', isActive: true },
  ],
}

const adminUserTags = [{ name: 'users', text: 'Utilisateurs', href: '/admin/users' }]
const activeTagName = computed(() => 'users')

const query = ref('')
const { data, loading, error, fetchData } = useGetApi<AdminUser[]>(API_ROUTES.GET_ADMIN_USERS)
const { deleteItem, deleting, errorDelete: delError } = useDeleteApi()
const { postData, loading: creating, error: createError, response: createResponse } = usePostApi<any>()
const { putData, loading: updating, error: updError, response: updResponse } = usePutApi()

// Listes pour le formulaire "Lier à un personnel"
const { data: rolesData, fetchData: fetchRoles } = useGetApi<any>(API_ROUTES.GET_ROLES)
const { data: personalsData, fetchData: fetchPersonals } = useGetApi<any[]>(API_ROUTES.GET_ACADEMIC_PERSONALS)

const availableRoles = computed(() => rolesData.value?.roles || [])
// On ne filtre que les personnels qui n'ont pas encore de compte utilisateur
const availablePersonals = computed(() => {
  const all = personalsData.value || []
  return all.filter((p) => !p.user_id)
})

// Formulaire : Créer utilisateur depuis un personnel académique
const createOpen = ref(false)
const selectedPersonalId = ref<number | string>('')
const selectedPersonalRole = ref<string>('enseignant')

async function onAssignPersonal() {
  if (!selectedPersonalId.value || !selectedPersonalRole.value) {
    showCustomToast({ message: 'Veuillez sélectionner un personnel et un rôle', type: 'error' })
    return
  }
  const payload = {
    academic_personal_id: selectedPersonalId.value,
    role: selectedPersonalRole.value,
  }
  await postData(API_ROUTES.ASSIGN_ROLE_TO_PERSONAL, payload)
  if (createError.value) {
    showCustomToast({ message: createError.value, type: 'error' })
    return
  }
  showCustomToast({ message: 'Compte créé et email envoyé avec succès', type: 'success' })
  createOpen.value = false
  resetForm()
  fetchPersonals({ per_page: 500 }) // reload to remove the assigned one
  eventBus.emit('usersUpdated')
}

function resetForm() {
  selectedPersonalId.value = ''
  selectedPersonalRole.value = 'enseignant'
}

// Édition
const editOpen = ref(false)
const editId = ref<number | null>(null)
const editName = ref('')
const editEmail = ref('')
const editRole = ref('')

function openEditModal(u: AdminUser) {
  editId.value = u.id
  editName.value = u.name
  editEmail.value = u.email
  editRole.value = u.roles && u.roles.length > 0 ? u.roles[0].name : 'tiers'
  editOpen.value = true
}

async function onSubmitEdit() {
  if (!editId.value) return
  const payload: Record<string, string> = {
    name: editName.value.trim(),
    email: editEmail.value.trim(),
    role: editRole.value,
  }
  await putData(API_ROUTES.UPDATE_ADMIN_USER(editId.value), payload)
  if (updError.value) {
    showCustomToast({ message: updError.value, type: 'error' })
    return
  }
  if (updResponse.value) {
    showCustomToast({ message: 'Utilisateur mis à jour avec succès', type: 'success' })
    editOpen.value = false
    eventBus.emit('usersUpdated')
  }
}

// Suppression
const deletingId = ref<number | null>(null)

async function onDeleteUser(id: number, name: string) {
  deletingId.value = id
  const res = await deleteItem(API_ROUTES.DELETE_ADMIN_USER(id))
  if (res) {
    showCustomToast({ message: `Utilisateur "${name}" supprimé avec succès`, type: 'success' })
    eventBus.emit('usersUpdated')
  } else {
    showCustomToast({ message: delError.value || 'Suppression impossible', type: 'error' })
  }
  deletingId.value = null
}

import { watch } from 'vue'

watch(createOpen, (newVal) => {
  if (newVal) {
    if (!rolesData.value || rolesData.value.length === 0) fetchRoles()
    fetchPersonals({ per_page: 500 }) // Load personnel to assign
  }
})

const filtered = computed(() => {
  const list = data.value || []
  const q = query.value.trim().toLowerCase()
  if (!q) return list
  return list.filter(
    (u) => u.name?.toLowerCase().includes(q) || u.email?.toLowerCase().includes(q),
  )
})

function onRefresh() {
  fetchData()
}

onMounted(() => {
  fetchData()
  eventBus.on('usersUpdated', onRefresh)
})

onBeforeUnmount(() => {
  eventBus.off('usersUpdated', onRefresh)
})
</script>

<template>
  <DashLayout :breadcrumb="breadcrumbItems" active-route="/admin/users" module-name="admin">
    <div class="pb-6 mx-auto w-full max-w-7xl">
      <DashPageHeader
        title="Administration"
        :tags="adminUserTags"
        :active-tag-name="activeTagName"
      />

      <BoxPanelWrapper>
        <div class="flex items-center gap-3 justify-between">
          <div class="relative flex-1">
            <Input
              v-model="query"
              placeholder="Rechercher un utilisateur..."
              class="w-full max-w-sm ps-10 h-10"
            />
            <div class="absolute left-3 top-1/2 -translate-y-1/2 text-foreground-muted/70">
              <span class="iconify hugeicons--search-01 text-sm"></span>
            </div>
          </div>
          <div class="flex items-center gap-2">
            <Button v-if="canCreate" @click="createOpen = true">
              <span class="iconify hugeicons--user-add-01"></span>
              <span>Nouvel utilisateur</span>
            </Button>
          </div>
        </div>

        <div class="mt-4 flex-1 min-h-0 overflow-auto bg-white rounded-md border border-gray-100">
          <div v-if="loading" class="p-4 text-sm text-gray-600">Chargement...</div>
          <div v-else-if="error" class="p-4 text-sm text-red-600">{{ error }}</div>
          <template v-else>
            <div v-if="(filtered || []).length === 0" class="p-4 text-sm text-gray-600">
              Aucun utilisateur trouvé.
            </div>
            <div v-else class="w-full">
              <div
                class="px-4 py-3 bg-gray-200 border-b border-gray-100 flex items-center text-sm font-semibold text-foreground-title"
              >
                <div class="w-12">N°</div>
                <div class="flex-1">Nom</div>
                <div class="flex-1">Email</div>
                <div class="w-28">Rôle</div>
                <div class="w-24 text-right">Actions</div>
              </div>
              <ul class="divide-y">
                <li
                  v-for="(u, index) in filtered"
                  :key="u.id"
                  class="px-4 py-3 flex items-center text-sm"
                >
                  <div class="w-12 text-gray-500">{{ index + 1 }}</div>
                  <div class="flex-1 flex items-center gap-3">
                    <span class="iconify hugeicons--user text-primary"></span>
                    <span class="font-medium">{{ u.name }}</span>
                  </div>
                  <div class="flex-1 text-gray-600">{{ u.email }}</div>
                  <div class="w-28">
                    <span
                      v-for="role in u.roles"
                      :key="role.name"
                      class="inline-flex items-center rounded-full bg-primary/10 px-2 py-0.5 text-xs font-medium text-primary"
                    >
                      {{ role.name }}
                    </span>
                  </div>
                  <div class="w-24 flex justify-end">
                    <TableRowActions>
                      <template #actions>
                        <Button
                          v-if="canUpdate"
                          size="sm"
                          variant="outline"
                          class="h-8"
                          @click="openEditModal(u)"
                        >
                          <span class="iconify hugeicons--edit-01"></span>
                          <span class="sr-only">Modifier</span>
                        </Button>
                        <AlertDialog v-if="canDelete">
                          <AlertDialogTrigger as-child>
                            <Button size="sm" variant="destructive" class="h-8">
                              <span class="iconify hugeicons--delete-02"></span>
                              <span class="sr-only">Supprimer</span>
                            </Button>
                          </AlertDialogTrigger>
                          <AlertDialogContent>
                            <AlertDialogHeader>
                              <AlertDialogTitle>Supprimer cet utilisateur ?</AlertDialogTitle>
                              <AlertDialogDescription>
                                Cette action est irréversible. L'utilisateur perdra tout accès au
                                système.
                              </AlertDialogDescription>
                            </AlertDialogHeader>
                            <AlertDialogFooter>
                              <AlertDialogCancel>Annuler</AlertDialogCancel>
                              <Button
                                variant="destructive"
                                :disabled="deleting && deletingId === u.id"
                                @click="onDeleteUser(u.id, u.name)"
                              >
                                <span
                                  v-if="deleting && deletingId === u.id"
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

                <!-- Modal Édition (inline dans la liste comme Pays.vue) -->
                <Dialog v-model:open="editOpen">
                  <DialogContent class="sm:max-w-[420px]">
                    <DialogHeader>
                      <DialogTitle>Modifier un utilisateur</DialogTitle>
                      <DialogDescription>Mettre à jour les informations</DialogDescription>
                    </DialogHeader>
                    <form @submit.prevent="onSubmitEdit">
                      <div class="grid gap-4 py-4">
                        <div class="flex flex-col space-y-1.5">
                          <Label for="edit-name" class="text-sm font-medium">Nom complet</Label>
                          <Input
                            id="edit-name"
                            v-model="editName"
                            class="h-10"
                            :disabled="updating"
                            required
                          />
                        </div>
                        <div class="flex flex-col space-y-1.5">
                          <Label for="edit-email" class="text-sm font-medium">Email</Label>
                          <Input
                            id="edit-email"
                            v-model="editEmail"
                            type="email"
                            class="h-10"
                            :disabled="updating"
                            required
                          />
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

    <!-- Modal Création -->
    <Dialog v-model:open="createOpen">
      <DialogContent class="sm:max-w-[480px]">
        <DialogHeader>
          <DialogTitle>Créer un compte utilisateur</DialogTitle>
          <DialogDescription>Générer un compte de connexion pour un membre de l'école</DialogDescription>
        </DialogHeader>

        <form @submit.prevent="onAssignPersonal">
          <div class="grid gap-4 py-4">
            <div class="flex flex-col space-y-1.5">
              <Label class="text-sm font-medium">Sélectionner le personnel</Label>
              <Select v-model="selectedPersonalId" :disabled="creating">
                <SelectTrigger class="w-full h-10">
                  <SelectValue placeholder="Choisir un membre du personnel..." />
                </SelectTrigger>
                <SelectContent class="max-h-[200px]">
                  <SelectItem v-for="p in availablePersonals" :key="p.id" :value="String(p.id)">
                    {{ p.pre_name }} {{ p.name }} {{ p.post_name }} - {{ p.fonction?.name || 'Sans fonction' }}
                  </SelectItem>
                </SelectContent>
              </Select>
              <p class="text-xs text-foreground-muted mt-1">Seuls les personnels sans compte s'affichent ici.</p>
            </div>
            <div class="flex flex-col space-y-1.5 mt-2">
              <Label class="text-sm font-medium">Rôle à attribuer</Label>
              <Select v-model="selectedPersonalRole" :disabled="creating">
                <SelectTrigger class="w-full h-10">
                  <SelectValue placeholder="Choisir un rôle..." />
                </SelectTrigger>
                <SelectContent>
                  <SelectItem v-for="role in availableRoles" :key="role.id" :value="role.name">
                    <span class="capitalize">{{ role.name.replace('-', ' ') }}</span>
                  </SelectItem>
                  <!-- Fallback s'il n'y a pas de données -->
                  <SelectItem v-if="availableRoles.length === 0" value="enseignant">Enseignant</SelectItem>
                  <SelectItem v-if="availableRoles.length === 0" value="prefet">Préfet</SelectItem>
                </SelectContent>
              </Select>
            </div>
          </div>
          <DialogFooter class="flex justify-end gap-2 items-center mt-4">
            <Button size="sm" class="h-9" variant="outline" type="button" :disabled="creating" @click="createOpen = false">Annuler</Button>
            <Button size="sm" class="h-9" type="submit" :disabled="creating">
              <span v-if="!creating" class="flex items-center gap-2"><span class="iconify hugeicons--user-add-01"></span><span>Créer un compte</span></span>
              <span v-else class="flex items-center gap-2"><IconifySpinner size="lg" /><span>Création...</span></span>
            </Button>
          </DialogFooter>
        </form>
      </DialogContent>
    </Dialog>
  </DashLayout>
</template>

<style scoped></style>
