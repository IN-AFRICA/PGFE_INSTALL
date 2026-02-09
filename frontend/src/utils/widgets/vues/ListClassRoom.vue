<template>
  <div class="flex flex-col space-y-1.5 flex-1">
    <Label for="class_room_id" class="text-sm font-medium">
      Salle de classe
      <SpanRequired />
    </Label>
    <Select
      id="class_room_id"
      name="class_room_id"
      :model-value="modelValue != null ? String(modelValue) : ''"
      @update:modelValue="updateValue"
    >
      <SelectTrigger id="class_room_id" class="h-10 w-full">
        <SelectValue placeholder="Sélectionnez une salle de classe" />
      </SelectTrigger>
      <SelectContent>
        <SelectGroup>
          <SelectItem v-for="room in classrooms" :key="room.id" :value="String(room.id)">
            {{ room.name }}
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
import SpanRequired from '@/components/atoms/SpanRequired.vue'

const props = defineProps({
  modelValue: [String, Number],
})
const emit = defineEmits(['update:modelValue'])

const { data, loading, error, fetchData } = useGetApi(API_ROUTES.GET_CLASSROOMS)
fetchData()

// Extraire et filtrer les données pour gérer les structures paginées et les valeurs null
const classrooms = computed(() => {
  if (!data.value) return []
  // Si c'est un tableau direct
  if (Array.isArray(data.value)) {
    return data.value.filter((room) => room != null && room.id != null)
  }
  // Si c'est une structure paginée { data: [...] }
  if (data.value && typeof data.value === 'object' && 'data' in data.value) {
    const items = (data.value as any).data
    if (Array.isArray(items)) {
      return items.filter((room) => room != null && room.id != null)
    }
  }
  return []
})

function updateValue(value: string) {
  const num = value !== undefined && value !== null && value !== '' ? Number(value) : 0
  emit('update:modelValue', num)
}
</script>
