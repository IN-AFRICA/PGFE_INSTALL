<script setup lang="ts">
import { ref, computed } from 'vue';
import { 
  CalendarDate, 
  today, 
  getLocalTimeZone, 
  getDayOfWeek 
} from '@internationalized/date';
import type { DateValue } from 'reka-ui';
import { Calendar } from '@/components/ui/calendar';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import BoxPanelWrapper from '@/components/atoms/BoxPanelWrapper.vue';
import { type ScheduleEntry, type Day, DAYS } from '@/types/schedule';
import { cn } from '@/lib/utils';

const getSubjectColor = (subject: string) => {
  const hash = subject.split('').reduce((acc, char) => acc + char.charCodeAt(0), 0);
  const colors = [
    'bg-blue-500/10 border-blue-500 text-blue-700',
    'bg-orange-500/10 border-orange-500 text-orange-700',
    'bg-green-500/10 border-green-500 text-green-700',
    'bg-purple-500/10 border-purple-500 text-purple-700',
    'bg-pink-500/10 border-pink-500 text-pink-700',
    'bg-red-500/10 border-red-500 text-red-700',
    'bg-cyan-500/10 border-cyan-500 text-cyan-700',
  ];
  return colors[hash % colors.length];
};

const props = defineProps<{
  entries: ScheduleEntry[];
}>();

const selectedDate = ref<DateValue>(today(getLocalTimeZone()));



const formatDate = (date: DateValue) => {
  const d = date.toDate(getLocalTimeZone());
  return new Intl.DateTimeFormat('fr-FR', { 
    day: 'numeric', 
    month: 'long', 
    year: 'numeric' 
  }).format(d);
};

const selectedDay = computed<Day | undefined>(() => {
  if (!selectedDate.value) return undefined;
  const d = selectedDate.value.toDate(getLocalTimeZone());
  const dayIndex = d.getDay(); // 0 is Sunday, 1 is Monday...

  return dayIndex >= 1 && dayIndex <= 5 ? DAYS[dayIndex - 1] : undefined;
});

const getISOWeekNumber = (d: Date) => {
  const date = new Date(d.getTime());
  date.setHours(0, 0, 0, 0);
  date.setDate(date.getDate() + 3 - (date.getDay() + 6) % 7);
  const week1 = new Date(date.getFullYear(), 0, 4);
  return 1 + Math.round(((date.getTime() - week1.getTime()) / 86400000 - 3 + (week1.getDay() + 6) % 7) / 7);
};

const dayEntries = computed(() => {
  if (!selectedDay.value || !selectedDate.value) return [];
  
  const d = selectedDate.value.toDate(getLocalTimeZone());
  const selectedWeekNum = getISOWeekNumber(d);

  return props.entries
    .filter(e => {
      // Must match the selected day (Lundi, Mardi...)
      if (e.day !== selectedDay.value) return false;
      // If it's a recurring course (null/empty), it's active. Otherwise it must match the ISO week.
      if (!e.week_number) return true;
      return String(e.week_number) === String(selectedWeekNum);
    })
    .sort((a, b) => a.startTime.localeCompare(b.startTime));
});

</script>

<template>
  <div class="grid lg:grid-cols-2 gap-7 flex-1">

    <BoxPanelWrapper class="h-full flex flex-col">
      <span class="text-gray-500 my-1.5 text-xl font-medium">Calendrier</span>
      <div class="flex-1 w-full bg-white rounded-xl border p-4 sm:p-6 custom-calendar">
        <Calendar
          :model-value="(selectedDate as any)"
          @update:model-value="selectedDate = $event as any"
          locale="fr-FR"
          class="w-full h-full p-0"
        />
      </div>

      <div class="mt-4 text-sm text-gray-400 text-center font-medium">
        Sélectionnez un jour de la semaine pour voir les cours.
      </div>
    </BoxPanelWrapper>


    <BoxPanelWrapper>
      <span class="text-gray-500 my-1.5 text-xl font-medium">
        {{ selectedDay ? selectedDay : 'Jour non ouvré' }}
      </span>
      <div class="text-sm text-muted-foreground mb-4">
        {{ selectedDate ? formatDate(selectedDate as any) : '' }}
      </div>


      <div class="space-y-4 overflow-auto flex-1 h-full">
        <template v-if="selectedDay && dayEntries.length > 0">
          <div
            v-for="entry in dayEntries"
            :key="entry.id"
            :class="cn(
              'rounded-md border-l-4 p-4 bg-white shadow-sm border',
              getSubjectColor(entry.subject)
            )"
          >
            <div class="flex items-center justify-between gap-2">
              <span class="font-bold text-base">{{ entry.subject }}</span>
              <Badge variant="outline" class="text-xs font-mono bg-white/50">
                {{ entry.startTime }} - {{ entry.endTime }}
              </Badge>
            </div>
            <div v-if="entry.teacher" class="text-sm mt-2 flex items-center gap-2">
               <span class="iconify hugeicons--teacher text-gray-400"></span>
               {{ entry.teacher }}
            </div>
            <div v-if="entry.room" class="text-sm flex items-center gap-2">
               <span class="iconify hugeicons--location-01 text-gray-400"></span>
               Salle {{ entry.room }}
            </div>
          </div>
        </template>
        
        <div v-else-if="!selectedDay" class="flex flex-col items-center justify-center py-12 text-gray-400">
           <span class="iconify hugeicons--calendar-03 size-12 mb-2"></span>
           <p>Les weekends ne sont pas inclus dans l'emploi du temps.</p>
        </div>

        <div v-else class="flex flex-col items-center justify-center py-12 text-gray-400 border-2 border-dashed rounded-lg">
           <span class="iconify hugeicons--folder-not-found size-12 mb-2 font-light"></span>
           <p>Aucun cours programmé pour ce jour.</p>
        </div>
      </div>
    </BoxPanelWrapper>
  </div>
</template>

<style scoped>
.custom-calendar :deep([data-slot="calendar"]) {
  width: 100%;
  height: 100%;
  display: flex;
  flex-direction: column;
}

.custom-calendar :deep([data-slot="calendar-header"]) {
  width: 100%;
  position: relative;
  display: flex;
  align-items: center;
  justify-content: center;
  margin-bottom: 2rem;
  padding: 0.5rem 0;
}

.custom-calendar :deep([data-slot="calendar-heading"]) {
  font-size: 1.25rem;
  font-weight: 700;
  color: #1f2937;
  text-transform: capitalize;
}

/* Force buttons to release their absolute positions from the UI kit */
.custom-calendar :deep([data-slot="calendar-prev-button"]),
.custom-calendar :deep([data-slot="calendar-next-button"]) {
  position: absolute !important;
  top: 50%;
  transform: translateY(-50%);
  size: 2.5rem !important;
  width: 2.5rem !important;
  height: 2.5rem !important;
  opacity: 1;
  background-color: #f3f4f6;
  border-radius: 0.75rem;
}

.custom-calendar :deep([data-slot="calendar-prev-button"]) {
  left: 0 !important;
}

.custom-calendar :deep([data-slot="calendar-next-button"]) {
  right: 0 !important;
}

.custom-calendar :deep(.flex.flex-col.gap-y-4) {
  flex: 1;
  width: 100%;
  display: flex !important;
}

.custom-calendar :deep([data-slot="calendar-grid-row"]) {
  display: grid !important;
  grid-template-columns: repeat(7, 1fr);
  width: 100%;
}

.custom-calendar :deep([data-slot="calendar-head-cell"]) {
  width: 100% !important;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 0;
}

.custom-calendar :deep(table) {
  width: 100%;
  height: 100%;
  display: flex;
  flex-direction: column;
}

.custom-calendar :deep(thead),
.custom-calendar :deep(tbody) {
  width: 100%;
}

.custom-calendar :deep(th) {
  width: 100%;
  font-size: 1.1rem;
  padding-bottom: 2rem;
  font-weight: 700;
  color: #4b5563;
  text-align: center;
}

.custom-calendar :deep(td) {
  padding: 0;
}

.custom-calendar :deep([data-slot="calendar-cell"]) {
  width: 100%;
  height: 100%;
}

.custom-calendar :deep([data-slot="calendar-cell-trigger"]) {
  width: 100%;
  height: 100%;
  min-height: 4.5rem;
  font-size: 1.25rem;
  font-weight: 500;
  border-radius: 0.75rem;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.2s ease-in-out;
  border: 1px solid transparent;
}

.custom-calendar :deep([data-slot="calendar-cell-trigger"]:hover) {
  background-color: #f9fafb;
  border-color: #e5e7eb;
  transform: translateY(-2px);
}

.custom-calendar :deep([data-selected][data-slot="calendar-cell-trigger"]) {
  background-color: #3b82f6 !important;
  color: white !important;
  font-weight: 700;
  box-shadow: 0 4px 6px -1px rgba(59, 130, 246, 0.3);
}
</style>
