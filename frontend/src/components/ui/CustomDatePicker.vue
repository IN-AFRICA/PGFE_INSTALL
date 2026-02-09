<script setup lang="ts">
import {
  DateFormatter,
  type DateValue,
  getLocalTimeZone,
  CalendarDate,
} from '@internationalized/date'

import { ref, watch, computed } from 'vue'
import { cn } from '@/lib/utils'
import { Button } from '@/components/ui/button'
import { Calendar } from '@/components/ui/calendar'
import { Popover, PopoverContent, PopoverTrigger } from '@/components/ui/popover'
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from '@/components/ui/select'

// Props pour v-model
interface Props {
  modelValue?: string
}

const props = withDefaults(defineProps<Props>(), {
  modelValue: '',
})

// Émissions pour v-model
const emit = defineEmits<{
  'update:modelValue': [value: string]
}>()

const df = new DateFormatter('en-US', {
  dateStyle: 'long',
})

const months = [
  { value: 1, label: 'Janvier' },
  { value: 2, label: 'Février' },
  { value: 3, label: 'Mars' },
  { value: 4, label: 'Avril' },
  { value: 5, label: 'Mai' },
  { value: 6, label: 'Juin' },
  { value: 7, label: 'Juillet' },
  { value: 8, label: 'Août' },
  { value: 9, label: 'Septembre' },
  { value: 10, label: 'Octobre' },
  { value: 11, label: 'Novembre' },
  { value: 12, label: 'Décembre' },
]

const currentYear = new Date().getFullYear()
const years = Array.from({ length: 23 }, (_, i) => ({
  value: currentYear - 3 - i,
  label: (currentYear - 3 - i).toString(),
}))

const value = ref<DateValue>()
const selectedMonth = ref<number | null>(null)
const selectedYear = ref<number | null>(null)

// Initialiser avec la valeur du prop
if (props.modelValue) {
  const date = new Date(props.modelValue)
  if (!isNaN(date.getTime())) {
    value.value = new CalendarDate(date.getFullYear(), date.getMonth() + 1, date.getDate())
    selectedMonth.value = date.getMonth() + 1
    selectedYear.value = date.getFullYear()
  }
}

// Watcher pour émettre les changements vers le parent
watch(
  value,
  (newValue) => {
    if (newValue) {
      const date = newValue.toDate(getLocalTimeZone())
      const formattedDate = date.toISOString().split('T')[0] // Format YYYY-MM-DD
      emit('update:modelValue', formattedDate)
    } else {
      emit('update:modelValue', '')
    }
  },
  { immediate: false },
)

// Watcher pour synchroniser avec les changements du parent
watch(
  () => props.modelValue,
  (newValue) => {
    if (
      newValue &&
      newValue !== value.value?.toDate(getLocalTimeZone()).toISOString().split('T')[0]
    ) {
      const date = new Date(newValue)
      if (!isNaN(date.getTime())) {
        value.value = new CalendarDate(date.getFullYear(), date.getMonth() + 1, date.getDate())
        selectedMonth.value = date.getMonth() + 1
        selectedYear.value = date.getFullYear()
      }
    }
  },
)

function updateDateFromSelect() {
  if (selectedMonth.value && selectedYear.value) {
    const day = value.value ? value.value.day : 1
    value.value = new CalendarDate(selectedYear.value, selectedMonth.value, day)
  }
}
</script>

<template>
  <Popover>
    <PopoverTrigger as-child>
      <Button
        variant="outline"
        :class="
          cn(
            'w-full justify-start text-left font-normal relative',
            'bg-white transition-all h-10 rounded-md px-3.5',
            !value && 'text-muted-foreground',
          )
        "
      >
        <span class="flex-1">
          {{
            value
              ? value.toDate(getLocalTimeZone()).toLocaleDateString('fr-FR', {
                  day: '2-digit',
                  month: '2-digit',
                  year: 'numeric',
                })
              : 'Selectionner une date'
          }}
        </span>
        <span
          class="absolute top-1/2 -translate-y-1/2 right-3 flex iconify hugeicons--calendar-01"
        ></span>
      </Button>
    </PopoverTrigger>
    <PopoverContent class="flex w-auto flex-col gap-y-2 p-2">
      <div class="flex items-center gap-2">
        <Select class="flex-1" v-model="selectedMonth" @update:model-value="updateDateFromSelect">
          <SelectTrigger class="flex-1">
            <SelectValue placeholder="Mois" />
          </SelectTrigger>
          <SelectContent>
            <SelectItem v-for="month in months" :key="month.value" :value="month.value">
              {{ month.label }}
            </SelectItem>
          </SelectContent>
        </Select>

        <Select v-model="selectedYear" @update:model-value="updateDateFromSelect">
          <SelectTrigger>
            <SelectValue placeholder="Annee" />
          </SelectTrigger>
          <SelectContent>
            <SelectItem v-for="year in years" :key="year.value" :value="year.value">
              {{ year.label }}
            </SelectItem>
          </SelectContent>
        </Select>
      </div>
      <Calendar v-model="value" />
    </PopoverContent>
  </Popover>
</template>
