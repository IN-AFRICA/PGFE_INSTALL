<template>
  <div class="flex flex-col space-y-1.5 flex-1">
    <Label for="mother_id" class="text-sm font-medium"> Parents </Label>
    <Select
      id="mother_id"
      name="mother_id"
      :model-value="modelValue"
      @update:modelValue="updateValue"
    >
      <SelectTrigger id="mother_id" class="h-10 w-full bg-white">
        <SelectValue placeholder="Sélectionnez un parent" />
      </SelectTrigger>
      <SelectContent>
        <div class="p-2 flex flex-col gap-2">
          <!-- Barre de recherche -->
          <Input
            :value="parentSearch"
            placeholder="Rechercher un parent..."
            class="h-8 text-xs"
            @input="(e: any) => (parentSearch = e.target.value)"
            @keydown.stop
          />

          <!-- Bouton Nouveau Parent (Bleu Primaire) -->
          <Button
            type="button"
            class="w-full h-8 flex items-center justify-center gap-2 bg-primary text-white hover:bg-primary/90 rounded text-xs font-bold transition-all"
            @click="showModal = true"
          >
            <span class="iconify hugeicons--plus-sign text-sm"></span>
            NOUVEAU PARENT
          </Button>
        </div>

        <SelectGroup>
          <SelectItem v-for="mother in filteredData" :key="mother.id" :value="String(mother.id)">
            {{ mother.name }}
          </SelectItem>
          <div v-if="filteredData.length === 0" class="p-2 text-gray-500 text-xs text-center">
            Aucun parent trouvé
          </div>
        </SelectGroup>
      </SelectContent>
    </Select>

    <NewParentModal v-model:open="showModal" @created="handleParentCreated" />
  </div>
</template>

<script setup lang="ts">
import Label from '@/components/ui/label/Label.vue'
import Select from '@/components/ui/select/Select.vue'
import SelectContent from '@/components/ui/select/SelectContent.vue'
import SelectGroup from '@/components/ui/select/SelectGroup.vue'
import SelectItem from '@/components/ui/select/SelectItem.vue'
import SelectTrigger from '@/components/ui/select/SelectTrigger.vue'
import SelectValue from '@/components/ui/select/SelectValue.vue'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import NewParentModal from '@/components/modals/operations/NewParentModal.vue'
import { useGetApi } from '@/composables/useGetApi'
import { API_ROUTES } from '@/utils/constants/api_route'
import { defineProps, defineEmits, computed, ref } from 'vue'

const props = defineProps({
  modelValue: String,
  // Afficher une option vide "Aucun" pour les champs optionnels
  optional: { type: Boolean, default: false },
  // Exclure certains IDs déjà sélectionnés (prévention de doublons)
  excludeIds: { type: Array as () => Array<string | number>, default: () => [] },
})
const emit = defineEmits(['update:modelValue'])

const showModal = ref(false)
const parentSearch = ref('')

const { data, loading, error, fetchData } = useGetApi(API_ROUTES.GET_PARENTS)
fetchData()

const excludeSet = computed(() => new Set((props.excludeIds || []).map((v) => String(v))))
const filteredData = computed(() => {
  if (!data.value) return []
  let list = data.value.filter((p: any) => !excludeSet.value.has(String(p.id)))

  if (parentSearch.value) {
    const search = parentSearch.value.toLowerCase()
    list = list.filter((p: any) => p.name.toLowerCase().includes(search))
  }

  return list
})

function updateValue(value: string) {
  emit('update:modelValue', value)
}

async function handleParentCreated(newParent: any) {
  await fetchData()
  if (newParent?.id) {
    updateValue(String(newParent.id))
  }
}
</script>
