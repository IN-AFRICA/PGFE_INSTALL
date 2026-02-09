<template>
  <div class="flex flex-col space-y-1.5 flex-1">
    <Label for="academic_personal_id" class="text-sm font-medium">
      Personnel scolaire
      <SpanRequired />
    </Label>
    <Select
      id="academic_personal_id"
      name="academic_personal_id"
      :model-value="modelValue != null ? String(modelValue) : ''"
      @update:modelValue="updateValue"
    >
      <SelectTrigger id="academic_personal_id" class="h-10 w-full">
        <SelectValue placeholder="Sélectionnez un personnel scolaire" />
      </SelectTrigger>
      <SelectContent>
        <SelectGroup>
          <SelectItem v-for="personnel in data" :key="personnel.id" :value="String(personnel.id)">
            {{ personnel.name }}
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
import SpanRequired from '@/components/atoms/SpanRequired.vue'

const props = defineProps({
  modelValue: [String, Number],
})
const emit = defineEmits(['update:modelValue'])

const { data, loading, error, fetchData } = useGetApi(API_ROUTES.GET_PERSONNELS_ACADEMIQUES)
fetchData()

function updateValue(value: string) {
  const num = value !== undefined && value !== null && value !== '' ? Number(value) : 0
  emit('update:modelValue', num)
}
</script>
