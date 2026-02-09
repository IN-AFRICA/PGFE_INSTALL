<template>
  <div class="flex flex-col space-y-1.5 flex-1">
    <Label for="school_year_id" class="text-sm font-medium"> Année scolaire </Label>
    <Select
      id="school_year_id"
      name="school_year_id"
      :model-value="modelValue != null ? String(modelValue) : ''"
      @update:modelValue="updateValue"
    >
      <SelectTrigger id="school_year_id" class="h-10 w-full">
        <SelectValue placeholder="Sélectionnez une année scolaire" />
      </SelectTrigger>
      <SelectContent>
        <SelectGroup>
          <SelectItem v-for="year in schoolYearsList" :key="year.id" :value="String(year.id)">
            {{ year.name }}
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
})
const emit = defineEmits(['update:modelValue'])

const { data, loading, error, fetchData } = useGetApi(API_ROUTES.GET_SCHOOL_YEARS)
fetchData()

const schoolYearsList = computed(() => {
  const v: any = data.value
  let items: any[] = []
  if (Array.isArray(v)) {
    items = v
  } else {
    items = v?.years ?? v?.data ?? []
  }
  // Filtrer les valeurs null
  return items.filter((year) => year != null && year.id != null)
})

function updateValue(value: string) {
  const num = value !== undefined && value !== null && value !== '' ? Number(value) : undefined
  emit('update:modelValue', num)
}
</script>
