<template>
  <div class="flex flex-col space-y-1.5 flex-1">
    <Label for="province_id" class="text-sm font-medium"> Province </Label>
    <Select
      id="province_id"
      name="province_id"
      :model-value="modelValue != null ? String(modelValue) : ''"
      @update:modelValue="updateValue"
      :disabled="disabled"
    >
      <SelectTrigger id="province_id" class="h-10 w-full">
        <SelectValue placeholder="Sélectionnez une province" />
      </SelectTrigger>
      <SelectContent>
        <SelectGroup>
          <!-- État de chargement -->
          <SelectItem v-if="loading" value="loading" disabled>
            <div class="flex items-center gap-2">
              <span
                class="animate-spin h-4 w-4 border-2 border-gray-300 border-t-blue-600 rounded-full"
              ></span>
              Chargement des provinces...
            </div>
          </SelectItem>

          <!-- État d'erreur -->
          <div v-else-if="error" class="flex flex-row items-center gap-2 p-2">
            <div class="flex items-center gap-2 text-red-500">
              <span class="iconify hugeicons--alert-circle"></span>
              Erreur de chargement
            </div>
            <button
              type="button"
              class="flex items-center gap-1.5 px-3 py-1.5 text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 rounded-md transition-colors"
              @click.stop="fetchData()"
            >
              <span class="iconify hugeicons--refresh"></span>
              Réessayer
            </button>
          </div>

          <!-- Liste vide -->
          <SelectItem
            v-else-if="!filteredProvinces || filteredProvinces.length === 0"
            value="empty"
            disabled
          >
            Aucune province disponible
          </SelectItem>
          <SelectItem
            v-else
            v-for="province in filteredProvinces"
            :key="province.id"
            :value="String(province.id)"
          >
            {{ province.name }}
          </SelectItem>
        </SelectGroup>
      </SelectContent>
    </Select>
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
import { useGetApi } from '@/composables/useGetApi'
import type { Province } from '@/models/province'
import { API_ROUTES } from '@/utils/constants/api_route'
import { computed } from 'vue'

const props = defineProps({
  modelValue: [String, Number],
  disabled: {
    type: Boolean,
    default: false,
  },
  country_id: {
    type: [String, Number, null],
    default: null,
  },
})
const emit = defineEmits(['update:modelValue'])

const { data, loading, error, fetchData } = useGetApi<Province[]>(API_ROUTES.GET_PROVINCES)
fetchData()

function updateValue(value: any) {
  const v = value as string
  const num = v !== undefined && v !== null && v !== '' ? Number(v) : undefined
  emit('update:modelValue', num)
}
const filteredProvinces = computed(() => {
  if (!data.value) return []
  if (!props.country_id) return data.value
  return (data.value as Province[]).filter(
    (p: Province) => String(p.country_id) === String(props.country_id),
  )
})
</script>
