<template>
  <div class="flex flex-col space-y-1.5 flex-1">
    <Label for="fonction_id" class="text-sm font-medium">
      Fonction
      <SpanRequired />
    </Label>
    <Select
      id="fonction_id"
      name="fonction_id"
      :model-value="modelValue != null ? String(modelValue) : ''"
      @update:modelValue="updateValue"
    >
      <SelectTrigger id="fonction_id" class="h-10 w-full">
        <SelectValue placeholder="Sélectionnez une fonction" />
      </SelectTrigger>
      <SelectContent>
        <SelectGroup>
          <SelectItem v-for="fonction in data" :key="fonction.id" :value="String(fonction.id)">
            {{ fonction.title }}
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

const { data, loading, error, fetchData } = useGetApi(API_ROUTES.GET_FONCTIONS)
fetchData()

function updateValue(value: string) {
  const num = value !== undefined && value !== null && value !== '' ? Number(value) : 0
  emit('update:modelValue', num)
}
</script>
