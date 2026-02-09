<template>
  <div class="flex flex-col space-y-1.5 flex-1">
    <Label for="filiaire_id" class="text-sm font-medium"> Section </Label>
    <Select
      id="filiaire_id"
      name="filiaire_id"
      :model-value="modelValue != null ? String(modelValue) : ''"
      @update:modelValue="updateValue"
    >
      <SelectTrigger id="filiaire_id" class="h-10 w-full">
        <SelectValue placeholder="Sélectionnez une section" />
      </SelectTrigger>
      <SelectContent>
        <SelectGroup>
          <SelectItem v-for="filiere in filieres" :key="filiere.id" :value="String(filiere.id)">
            {{ filiere.name }}
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
import { defineProps, defineEmits, computed } from 'vue'

const props = defineProps({
  modelValue: [String, Number],
  items: {
    type: Array,
    default: () => [],
  },
})
const emit = defineEmits(['update:modelValue'])

const { data, loading, error, fetchData } = useGetApi(API_ROUTES.GET_FILLIERES)

import { onMounted } from 'vue'

onMounted(() => {
  if (props.items.length === 0) {
    fetchData()
  }
})

// Extraire et filtrer les données pour gérer les structures paginées et les valeurs null
const filieres = computed(() => {
  // Priorité aux items passés en props
  if (props.items && props.items.length > 0) {
    return props.items
  }

  if (!data.value) return []
  // Si c'est un tableau direct
  if (Array.isArray(data.value)) {
    return data.value.filter((f) => f != null && f.id != null)
  }
  // Si c'est une structure paginée { data: [...] }
  if (data.value && typeof data.value === 'object' && 'data' in data.value) {
    const items = (data.value as any).data
    if (Array.isArray(items)) {
      return items.filter((f) => f != null && f.id != null)
    }
  }
  return []
})

function updateValue(value: string) {
  const num = value !== undefined && value !== null && value !== '' ? Number(value) : 0
  emit('update:modelValue', num)
}
</script>
