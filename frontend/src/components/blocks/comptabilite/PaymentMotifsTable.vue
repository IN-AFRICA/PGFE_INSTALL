<template>
  <div class="border rounded-lg overflow-hidden flex flex-1 bg-white">
    <Table>
      <TableHeader>
        <TableRow>
          <TableHead class="w-12">
            <Checkbox :checked="isAllSelected" @update:checked="$emit('toggle-select-all')" />
          </TableHead>
          <TableHead>Nom</TableHead>
          <TableHead>Code</TableHead>
          <TableHead>Type de frais</TableHead>
          <TableHead>Description</TableHead>
          <TableHead>Date de création</TableHead>
          <TableHead class="text-right">Actions</TableHead>
        </TableRow>
      </TableHeader>
      <TableBody>
        <!-- État de chargement -->
        <TableRow v-if="loading">
          <TableCell colspan="7" class="text-center py-8">
            <div class="flex items-center justify-center">
              <span class="iconify hugeicons--loading-03 mr-2 animate-spin"></span>
              Chargement des motifs de paiement...
            </div>
          </TableCell>
        </TableRow>

        <!-- État d'erreur -->
        <TableRow v-else-if="error">
          <TableCell colspan="7" class="text-center py-8">
            <div class="flex items-center justify-center text-red-500">
              <span class="iconify hugeicons--alert-triangle mr-2"></span>
              {{ error }}
            </div>
          </TableCell>
        </TableRow>

        <!-- Liste vide -->
        <TableRow v-else-if="motifs.length === 0">
          <TableCell colspan="7" class="text-center py-8">
            <div class="flex flex-col items-center justify-center text-gray-500">
              <span class="iconify hugeicons--note-01 text-4xl mb-2"></span>
              <p class="font-medium">
                {{ hasSearchQuery ? 'Aucun motif trouvé' : 'Aucun motif de paiement' }}
              </p>
              <p class="text-sm">
                {{
                  hasSearchQuery
                    ? "Essayez avec d'autres mots-clés"
                    : 'Commencez par créer votre premier motif de paiement'
                }}
              </p>
            </div>
          </TableCell>
        </TableRow>

        <!-- Données -->
        <TableRow v-else v-for="motif in motifs" :key="motif.id">
          <TableCell>
            <Checkbox
              :checked="selectedItems.includes(motif.id)"
              @update:checked="$emit('toggle-selection', motif.id)"
            />
          </TableCell>
          <TableCell class="font-medium">{{ motif.name }}</TableCell>
          <TableCell>
            <Badge variant="outline">{{ motif.code }}</Badge>
          </TableCell>
          <TableCell>
            <Badge variant="secondary">{{ getFeeTypeName(motif) }}</Badge>
          </TableCell>
          <TableCell class="max-w-xs truncate" :title="motif.description">
            {{ motif.description }}
          </TableCell>
          <TableCell>{{ formatDate(motif.created_at) }}</TableCell>
          <TableCell class="text-right">
            <DropdownMenu>
              <DropdownMenuTrigger as-child>
                <Button variant="ghost" size="sm">
                  <span class="iconify hugeicons--more-vertical"></span>
                </Button>
              </DropdownMenuTrigger>
              <DropdownMenuContent align="end">
                <DropdownMenuItem @click="$emit('edit-motif', motif)">
                  <span class="iconify hugeicons--edit-02 mr-2"></span>
                  Modifier
                </DropdownMenuItem>
                <DropdownMenuItem
                  @click="$emit('delete-motif', motif.id)"
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
import { Badge } from '@/components/ui/badge'
import { Button } from '@/components/ui/button'
import { Checkbox } from '@/components/ui/checkbox'
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
import type { PaymentMotif } from '@/composables/usePaymentMotifs'

interface Props {
  motifs: PaymentMotif[]
  selectedItems: number[]
  loading?: boolean
  error?: string | null
  hasSearchQuery?: boolean
  isAllSelected?: boolean
}

interface Emits {
  (e: 'toggle-selection', motifId: number): void
  (e: 'toggle-select-all'): void
  (e: 'edit-motif', motif: PaymentMotif): void
  (e: 'delete-motif', motifId: number): void
}

withDefaults(defineProps<Props>(), {
  loading: false,
  error: null,
  hasSearchQuery: false,
  isAllSelected: false,
})

defineEmits<Emits>()

// Utilitaires
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

const getFeeTypeName = (motif: PaymentMotif) => {
  return motif.fee_type?.name || 'Non défini'
}
</script>
