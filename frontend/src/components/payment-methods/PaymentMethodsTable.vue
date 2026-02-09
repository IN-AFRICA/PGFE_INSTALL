<template>
  <div class="border rounded-lg overflow-hidden flex flex-1 bg-white">
    <Table>
      <TableHeader>
        <TableRow>
          <TableHead class="w-12">
            <Checkbox :checked="isAllSelected" @update:checked="onToggleSelectAll" />
          </TableHead>
          <TableHead class="w-1/3">Nom</TableHead>
          <TableHead class="w-1/4">Code</TableHead>
          <TableHead class="w-1/4">Date de création</TableHead>
          <TableHead class="w-1/6 text-right">Actions</TableHead>
        </TableRow>
      </TableHeader>
      <TableBody>
        <!-- État de chargement -->
        <TableRow v-if="isLoading">
          <TableCell colspan="5" class="text-center py-8">
            <div class="flex items-center justify-center">
              <span class="iconify hugeicons--loading-03 mr-2 animate-spin"></span>
              Chargement des méthodes de paiement...
            </div>
          </TableCell>
        </TableRow>

        <!-- État d'erreur -->
        <TableRow v-else-if="error">
          <TableCell colspan="5" class="text-center py-8">
            <div class="flex items-center justify-center text-red-500">
              <span class="iconify hugeicons--alert-triangle mr-2"></span>
              {{ error }}
            </div>
          </TableCell>
        </TableRow>

        <!-- Liste vide -->
        <TableRow v-else-if="methods.length === 0">
          <TableCell colspan="5" class="text-center py-8">
            <div class="flex flex-col items-center justify-center text-gray-500">
              <span class="iconify hugeicons--credit-card text-4xl mb-2"></span>
              <p class="font-medium">Aucune méthode de paiement</p>
              <p class="text-sm">Commencez par créer votre première méthode de paiement</p>
            </div>
          </TableCell>
        </TableRow>

        <!-- Données -->
        <TableRow v-else v-for="method in methods" :key="method.id">
          <TableCell>
            <Checkbox
              :checked="selectedItems.includes(method.id)"
              @update:checked="onToggleSelection(method.id)"
            />
          </TableCell>
          <TableCell class="font-medium">{{ method.name }}</TableCell>
          <TableCell>
            <Badge variant="outline">{{ method.code }}</Badge>
          </TableCell>
          <TableCell>{{ formatDate(method.created_at) }}</TableCell>
          <TableCell class="text-right">
            <DropdownMenu>
              <DropdownMenuTrigger as-child>
                <Button variant="ghost" size="sm">
                  <span class="iconify hugeicons--more-vertical"></span>
                </Button>
              </DropdownMenuTrigger>
              <DropdownMenuContent align="end">
                <DropdownMenuItem @click="onEdit(method)">
                  <span class="iconify hugeicons--edit-02 mr-2"></span>
                  Modifier
                </DropdownMenuItem>
                <DropdownMenuItem
                  @click="onDelete(method.id)"
                  class="text-red-600 focus:text-red-600"
                >
                  <span class="iconify hugeicons--delete-02 mr-2"></span>
                  Supprimer
                </DropdownMenuItem>
              </DropdownMenuContent>
            </DropdownMenu>
          </TableCell>
        </TableRow>
      </TableBody>
    </Table>
  </div>
</template>

<script setup lang="ts">
import { Button } from '@/components/ui/button'
import { Checkbox } from '@/components/ui/checkbox'
import { Badge } from '@/components/ui/badge'
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
import type { PaymentMethod } from '@/types/PaymentMethodTypes'

interface Props {
  methods: PaymentMethod[]
  isLoading: boolean
  error: string | null
  selectedItems: number[]
  isAllSelected: boolean
}

interface Emits {
  (e: 'edit', method: PaymentMethod): void
  (e: 'delete', methodId: number): void
  (e: 'toggle-selection', methodId: number): void
  (e: 'toggle-select-all'): void
}

defineProps<Props>()
const emit = defineEmits<Emits>()

const formatDate = (dateString?: string) => {
  if (!dateString) return '-'
  try {
    return new Date(dateString).toLocaleDateString('fr-FR', {
      year: 'numeric',
      month: 'short',
      day: 'numeric',
    })
  } catch {
    return '-'
  }
}

const onEdit = (method: PaymentMethod) => {
  emit('edit', method)
}

const onDelete = (methodId: number) => {
  emit('delete', methodId)
}

const onToggleSelection = (methodId: number) => {
  emit('toggle-selection', methodId)
}

const onToggleSelectAll = () => {
  emit('toggle-select-all')
}
</script>
