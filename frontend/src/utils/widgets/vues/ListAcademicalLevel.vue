<template>
  <div class="flex flex-col space-y-1.5 flex-1">
    <Label for="academic_level_id" class="text-sm font-medium"> Niveau scolaire </Label>
    <Select
      id="academic_level_id"
      name="academic_level_id"
      :model-value="modelValue"
      @update:modelValue="updateValue"
    >
      <SelectTrigger id="academic_level_id" class="h-10 w-full">
        <SelectValue placeholder="Sélectionnez un niveau scolaire" />
      </SelectTrigger>
      <SelectContent>
        <SelectGroup>
          <SelectItem v-for="level in levels" :key="level.id" :value="String(level.id)">
            {{ level.name }}
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
import { API_ROUTES } from '@/utils/constants/api_route'
import { defineProps, defineEmits } from 'vue'

const props = defineProps({
  modelValue: [String, Number],
  items: {
    type: Array,
    default: () => [],
  },
})
const emit = defineEmits(['update:modelValue'])

// Si des items sont fournis via props, on n'appelle pas l'API
const { data, loading, error, fetchData } = useGetApi(API_ROUTES.GET_ACADEMIC_LEVELS)

import { onMounted, watch } from 'vue'

onMounted(() => {
  if (props.items.length === 0) {
    fetchData()
  }
})

// Extraire et filtrer les données
import { computed } from 'vue'
const levels = computed(() => {
  // Priorité aux items passés en props
  if (props.items && props.items.length > 0) {
    return props.items
  }

  if (!data.value) return []
  // Si c'est un tableau direct
  if (Array.isArray(data.value)) {
    return data.value.filter((l: any) => l != null && l.id != null)
  }
  // Si c'est une structure paginée { data: [...] }
  if (data.value && typeof data.value === 'object' && 'data' in data.value) {
    const items = (data.value as any).data
    if (Array.isArray(items)) {
      return items.filter((l: any) => l != null && l.id != null)
    }
  }
  return []
})

function updateValue(value: string) {
  emit('update:modelValue', value)
}
</script>
