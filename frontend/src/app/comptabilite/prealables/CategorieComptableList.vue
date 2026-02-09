<script setup lang="ts">
import { toTypedSchema } from '@vee-validate/zod'
import { Field, Form } from 'vee-validate'
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
  Dialog,
  DialogContent,
  DialogDescription,
  DialogFooter,
  DialogHeader,
  DialogTitle,
  DialogTrigger,
} from '@/components/ui/dialog'
import { Label } from '@/components/ui/label'
import BoxPanelWrapper from '@/components/atoms/BoxPanelWrapper.vue'
import TabPagination from '@/components/blocks/TabPagination.vue'
import { categorieComptableSchema } from './useCategorieComptable'
import { formatDate } from './utils'
import type { CategorieComptable } from './types'
import AlertMessage from '@/components/modals/AlertMessage.vue'

interface Props {
  searchQuery: string
  filteredCategories: CategorieComptable[]
  isCreateDialogOpen: boolean
  isEditDialogOpen: boolean
  categoriesLoading: boolean
  categoriesError: string | null
  creatingCategorie: boolean
  updatingCategorie: boolean
  deletingCategorie: boolean
  page: number
  perPageCount: number
  total: number
}

defineProps<Props>()
const emit = defineEmits([
  'update:searchQuery',
  'update:isCreateDialogOpen',
  'update:isEditDialogOpen',
  'update:page',
  'update:perPageCount',
  'submitCreate',
  'submitEdit',
  'delete',
  'openEdit',
  'closeCreate',
  'closeEdit',
])

function onPerPageUpdate(val: number) {
  emit('update:page', 1)
  emit('update:perPageCount', val)
}
</script>

<template>
  <BoxPanelWrapper>
    <div class="flex sm:items-center flex-col sm:flex-row sm:justify-between">
      <span class="text-gray-500 my-1.5 text-xl">Catégories Comptables</span>
    </div>

    <div class="flex sm:items-center gap-3 flex-col sm:flex-row sm:justify-between">
      <div class="relative flex-1">
        <Input
          :model-value="searchQuery"
          @update:model-value="emit('update:searchQuery', $event)"
          type="text"
          placeholder="Rechercher une catégorie comptable..."
          class="w-full max-w-sm ps-10 border border-gray-200/40 bg-white transition-all h-10 rounded-md"
        />
        <div class="absolute left-3 top-1/2 transform -translate-y-1/2 text-foreground-muted/70">
          <span class="flex iconify hugeicons--search-01 text-sm"></span>
        </div>
      </div>

      <div class="flex flex-wrap items-center sm:justify-end gap-2.5 flex-1">
        <Dialog :open="isCreateDialogOpen" @update:open="emit('update:isCreateDialogOpen', $event)">
          <DialogTrigger as-child>
            <Button size="md" class="rounded-md">
              <span class="iconify hugeicons--plus-sign"></span>
              <span class="hidden sm:flex">Ajouter</span>
            </Button>
          </DialogTrigger>
          <DialogContent
            class="sm:max-w-[540px]"
            @pointer-down-outside="emit('closeCreate')"
            @escape-key-down="emit('closeCreate')"
          >
            <DialogHeader>
              <DialogTitle>Ajouter une catégorie comptable</DialogTitle>
              <DialogDescription>
                Créez une nouvelle catégorie comptable en remplissant les informations ci-dessous.
              </DialogDescription>
            </DialogHeader>
            <Form
              @submit="emit('submitCreate', $event)"
              :validation-schema="toTypedSchema(categorieComptableSchema)"
              class="space-y-4"
            >
              <div class="flex flex-col space-y-1.5">
                <Label for="create_cat_name">Nom <span class="text-red-500">*</span></Label>
                <Field name="name" v-slot="{ field, errorMessage }">
                  <Input
                    v-bind="field"
                    id="create_cat_name"
                    placeholder="Ex: Actif"
                    maxlength="255"
                  />
                  <span v-if="errorMessage" class="text-sm text-red-500">{{ errorMessage }}</span>
                </Field>
              </div>
              <div class="mt-1 pb-2">
                <p class="text-sm text-foreground-muted">* Les champs marqués sont obligatoires</p>
              </div>
              <DialogFooter class="flex justify-end gap-2 items-center">
                <Button type="button" size="sm" variant="outline" @click="emit('closeCreate')">
                  <span class="iconify flex hugeicons--cancel-01 mr-1"></span>
                  Annuler
                </Button>
                <Button type="submit" size="sm" :disabled="creatingCategorie">
                  <span class="iconify flex hugeicons--floppy-disk mr-1"></span>
                  {{ creatingCategorie ? 'Enregistrement...' : 'Enregistrer' }}
                </Button>
              </DialogFooter>
            </Form>
          </DialogContent>
        </Dialog>
      </div>
    </div>

    <div class="mt-4 rounded-md overflow-hidden flex flex-1 bg-white">
      <Table class="rounded-md bg-white">
        <TableHeader>
          <TableRow>
            <TableHead>Nom</TableHead>
            <TableHead>Date de création</TableHead>
            <TableHead>Opérations</TableHead>
          </TableRow>
        </TableHeader>
        <TableBody>
          <TableRow v-if="categoriesLoading">
            <TableCell colspan="3" class="text-center py-8">
              <div class="flex items-center justify-center gap-2">
                <span class="iconify hugeicons--loading-03 animate-spin text-xl"></span>
                <span>Chargement des catégories comptables...</span>
              </div>
            </TableCell>
          </TableRow>

          <TableRow v-else-if="categoriesError">
            <TableCell colspan="3" class="text-center py-8 text-red-500">
              <div class="flex items-center justify-center gap-2">
                <span class="iconify hugeicons--alert-circle text-xl"></span>
                <span>{{ categoriesError }}</span>
              </div>
            </TableCell>
          </TableRow>

          <TableRow v-else-if="filteredCategories.length === 0">
            <TableCell colspan="3" class="text-center py-8 text-gray-500">
              <div class="flex flex-col items-center gap-2">
                <span class="iconify hugeicons--folder-open text-3xl"></span>
                <span v-if="searchQuery">Aucune catégorie trouvée pour "{{ searchQuery }}"</span>
                <span v-else>Aucune catégorie enregistrée</span>
              </div>
            </TableCell>
          </TableRow>

          <TableRow v-else v-for="item in filteredCategories" :key="item.id">
            <TableCell class="font-semibold">{{ item.name }}</TableCell>
            <TableCell>{{ formatDate(item.created_at) }}</TableCell>
            <TableCell>
              <div class="flex items-center gap-2 w-max">
                <Button
                  variant="outline"
                  size="icon"
                  class="size-8"
                  @click="emit('openEdit', item)"
                >
                  <span class="iconify hugeicons--edit-02"></span>
                </Button>
                <AlertMessage
                  action="danger"
                  title="Supprimer une classe comptable"
                  :message="`Vous êtes sur le point de supprimer la classe comptable '${item.name}'. Êtes-vous sûr de continuer?`"
                >
                  <template #trigger>
                    <Button
                      variant="destructive"
                      size="icon"
                      class="size-8"
                      :disabled="deletingCategorie"
                    >
                      <span class="iconify hugeicons--delete-02"></span>
                    </Button>
                  </template>
                  <template #confirm-action-button>
                    <Button
                      variant="destructive"
                      size="sm"
                      class="h-10 px-4"
                      @click.stop="emit('delete', item.id)"
                      :disabled="deletingCategorie"
                    >
                      Oui, Supprimer
                    </Button>
                  </template>
                </AlertMessage>
              </div>
            </TableCell>
          </TableRow>
        </TableBody>
      </Table>
    </div>

    <TabPagination
      v-if="!categoriesLoading && !categoriesError"
      :model-value="page"
      @update:model-value="emit('update:page', $event)"
      :perPage="perPageCount"
      :totalItems="total"
      @update:perPage="onPerPageUpdate"
    />
  </BoxPanelWrapper>
</template>
