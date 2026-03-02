<script setup lang="ts">
import { ref, computed, onMounted, watch } from 'vue';
import DashLayout from '@/components/templates/DashLayout.vue';
import DashPageHeader from '@/components/templates/DashPageHeader.vue';
import BoxPanelWrapper from '@/components/atoms/BoxPanelWrapper.vue';
import { Button } from '@/components/ui/button';
import { cn } from '@/lib/utils';
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from '@/components/ui/select';
import {
  AlertDialog,
  AlertDialogAction,
  AlertDialogCancel,
  AlertDialogContent,
  AlertDialogDescription,
  AlertDialogFooter,
  AlertDialogHeader,
  AlertDialogTitle,
} from '@/components/ui/alert-dialog';
import { DAYS, TIME_SLOTS, type Day, type ScheduleEntry } from '@/types/schedule';
import { useGetApi } from '@/composables/useGetApi';
import { useDeleteApi } from '@/composables/useDeleteApi';
import { API_ROUTES } from '@/utils/constants/api_route';
import ClassFormDialog from './components/ClassFormDialog.vue';
import ScheduleCalendar from './components/ScheduleCalendar.vue';
import { Popover, PopoverContent, PopoverTrigger } from '@/components/ui/popover';
import FilterBadges from '@/components/atoms/FilterBadges.vue';
import { tagScheduleNavOperations } from '@/components/templates/schedule/tags-links';
import { useRoute } from 'vue-router';
import { reactive } from 'vue';
import { Label } from '@/components/ui/label'
import IconifySpinner from '@/components/ui/spinner/IconifySpinner.vue';
import { showCustomToast } from '@/utils/widgets/custom_toast';

const route = useRoute();
const breadcrumbItems = {
  items: [
    { label: 'Accueil', href: '/', icon: 'hugeicons--home-01' },
    { label: 'Horaire', href: '/schedule' },
    { label: 'SchoolPlanner', isActive: true },
  ],
};

const activeTab = computed(() => (route.query.tab as string) || 'school-planer');
const activeTagName = computed(() => activeTab.value);

// ======================
// FILTERS
// ======================
const MONTHS = [
  { value: '1', label: 'Janvier' },
  { value: '2', label: 'Février' },
  { value: '3', label: 'Mars' },
  { value: '4', label: 'Avril' },
  { value: '5', label: 'Mai' },
  { value: '6', label: 'Juin' },
  { value: '7', label: 'Juillet' },
  { value: '8', label: 'Août' },
  { value: '9', label: 'Septembre' },
  { value: '10', label: 'Octobre' },
  { value: '11', label: 'Novembre' },
  { value: '12', label: 'Décembre' },
];

// Fetch school years
const { data: rawYears, fetchData: fetchYears } = useGetApi(API_ROUTES.GET_SCHOOL_YEARS);
const schoolYears = computed(() => {
  if (!rawYears.value) return [];
  if (Array.isArray(rawYears.value)) return rawYears.value;
  if ((rawYears.value as any).years) return (rawYears.value as any).years;
  return (rawYears.value as any).data || [];
});

// Filters state
const filterParams = reactive({
  yearId: '',
  month: '',
  week: '',
});

// Auto-select active year
watch(schoolYears, (years) => {
  if (years.length > 0 && !filterParams.yearId) {
    const activeYear = years.find((y: any) => y.is_active === '1' || y.is_active === 1);
    if (activeYear) {
      filterParams.yearId = String(activeYear.id);
    }
  }
});

// Auto-select current month
onMounted(() => {
  fetchYears();
  const now = new Date();
  filterParams.month = String(now.getMonth() + 1);
});

// Configuration des labels pour FilterBadges
const referenceData = computed(() => ({
  yearId: schoolYears.value || [],
  month: MONTHS.map(m => ({ id: m.value, name: m.label })),
  week: weeksInMonth.value.map(w => ({ id: w.value, name: w.label })),
}));

const customLabels = {
  yearId: (value: any, data: any[]) => {
    const year = data?.find((y: any) => String(y.id) === String(value));
    return year ? `Année: ${year.name}` : value;
  },
  month: (value: any) => {
    const month = MONTHS.find((m: any) => String(m.value) === String(value));
    return month ? `Mois: ${month.label}` : value;
  },
  week: (value: any) => {
    const week = weeksInMonth.value.find((w: any) => String(w.value) === String(value));
    if (week) {
        const dateRange = week.label.match(/\((.*?)\)/);
        return `Semaine ${week.value} ${dateRange ? dateRange[0] : ''}`;
    }
    return `Semaine ${value}`;
  },
};

const removeFilter = (key: string) => {
  if (key === 'yearId') filterParams.yearId = '';
  if (key === 'month') filterParams.month = '';
  if (key === 'week') filterParams.week = '';
};

// Compute weeks for the selected month
const weeksInMonth = computed(() => {
  if (!filterParams.month) return [];
  const now = new Date();
  const year = now.getFullYear();
  const month = Number(filterParams.month) - 1;

  const firstDay = new Date(year, month, 1);
  const lastDay = new Date(year, month + 1, 0);

  const weeks: { value: string; label: string; start: Date; end: Date }[] = [];
  let weekStart = new Date(firstDay);

  const getISOWeekNumber = (d: Date) => {
    const date = new Date(d.getTime());
    date.setHours(0, 0, 0, 0);
    date.setDate(date.getDate() + 3 - (date.getDay() + 6) % 7);
    const week1 = new Date(date.getFullYear(), 0, 4);
    return 1 + Math.round(((date.getTime() - week1.getTime()) / 86400000 - 3 + (week1.getDay() + 6) % 7) / 7);
  };

  // Go to Monday of the first week
  const dayOfWeek = weekStart.getDay();
  // In JS, getDay() returns 0 for Sunday, 1 for Monday.
  // We want to force the week to start on Monday.
  const diffToMonday = dayOfWeek === 0 ? -6 : 1 - dayOfWeek;
  weekStart.setDate(weekStart.getDate() + diffToMonday);

  while (weekStart <= lastDay) {
    const weekEnd = new Date(weekStart);
    weekEnd.setDate(weekEnd.getDate() + 4); // Friday

    const startStr = `${String(weekStart.getDate()).padStart(2, '0')}/${String(weekStart.getMonth() + 1).padStart(2, '0')}`;
    const endStr = `${String(weekEnd.getDate()).padStart(2, '0')}/${String(weekEnd.getMonth() + 1).padStart(2, '0')}`;
    
    const isoWeekNum = getISOWeekNumber(weekStart);

    weeks.push({
      value: String(isoWeekNum),
      label: `Semaine ${isoWeekNum} (${startStr} - ${endStr})`,
      start: new Date(weekStart),
      end: new Date(weekEnd),
    });

    weekStart = new Date(weekStart);
    weekStart.setDate(weekStart.getDate() + 7);
  }

  return weeks;
});

// Auto-select first week when month changes
watch(() => filterParams.month, () => {
  if (weeksInMonth.value.length > 0) {
    // Try to select the current week
    const now = new Date();
    const currentWeek = weeksInMonth.value.find(w => now >= w.start && now <= w.end);
    filterParams.week = currentWeek ? currentWeek.value : weeksInMonth.value[0].value;
  } else {
    filterParams.week = '';
  }
});

// Current week data
const currentWeekData = computed(() => {
  return weeksInMonth.value.find(w => w.value === filterParams.week);
});

// Date display for title
const weekDateLabel = computed(() => {
  if (!currentWeekData.value) return '';
  const d = currentWeekData.value.start;
  return `${String(d.getDate()).padStart(2, '0')}/${String(d.getMonth() + 1).padStart(2, '0')}/${d.getFullYear()}`;
});

// All 3 filters selected?
const filtersReady = computed(() => {
  return !!filterParams.yearId && !!filterParams.month && !!filterParams.week;
});

// Week pagination
const canGoPrev = computed(() => {
  if (!filterParams.week || weeksInMonth.value.length === 0) return false;
  return Number(filterParams.week) > 1;
});

const canGoNext = computed(() => {
  if (!filterParams.week || weeksInMonth.value.length === 0) return false;
  return Number(filterParams.week) < weeksInMonth.value.length;
});

const prevWeek = () => {
  if (canGoPrev.value) {
    filterParams.week = String(Number(filterParams.week) - 1);
  }
};

const nextWeek = () => {
  if (canGoNext.value) {
    filterParams.week = String(Number(filterParams.week) + 1);
  }
};

// Helper for subject colors
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

// ======================
// SCHEDULE LOGIC
// ======================
const { data: rawSchedules, loading: loadingSchedules, fetchData: fetchSchedules } = useGetApi(API_ROUTES.GET_SCHEDULES);
const { deleteItem, deleting: deletingApi, success: deleteSuccess, errorDelete } = useDeleteApi();

const allEntries = computed<ScheduleEntry[]>(() => {
  if (!rawSchedules.value) return [];
  const list = Array.isArray(rawSchedules.value) ? rawSchedules.value : (rawSchedules.value as any).data || [];
  
  return list.map((item: any) => ({
    id: String(item.id),
    day: item.day as Day,
    startTime: item.start_time?.substring(0, 5) || '',
    endTime: item.end_time?.substring(0, 5) || '',
    subject: item.course?.label || 'Cours sans nom',
    teacher: item.academic_personal?.name || 'Inconnu',
    room: item.classroom?.name || 'Salle NC',
    academic_personal_id: item.academic_personal_id,
    course_id: item.course_id,
    classroom_id: item.classroom_id,
    school_year_id: item.school_year_id,
    week_number: item.week_number,
  }));
});

const entries = computed<ScheduleEntry[]>(() => {
  const currentWeek = filterParams.week;
  return allEntries.value.filter((item) => {
    if (!item.week_number) return true;
    return String(item.week_number) === String(currentWeek);
  });
});


// Refresh data when filters change
watch([() => filterParams.yearId, () => filterParams.month, () => filterParams.week], () => {
  if (filtersReady.value) {
    fetchSchedules({
      school_year_id: filterParams.yearId,
      // The API might support filtering by week/month, adding them as query params
      month: filterParams.month,
      week_number: filterParams.week
    });
  }
}, { immediate: true });

const formOpen = ref(false);

const handleAddClick = () => {
  if (!filtersReady.value) {
    showCustomToast({ 
      message: "Veuillez d'abord sélectionner l'année scolaire, le mois et la semaine avant d'ajouter un cours.", 
      type: 'error' 
    });
    return;
  }
  editEntry.value = null;
  formOpen.value = true;
};

const editEntry = ref<ScheduleEntry | null>(null);
const defaultDay = ref<Day>('Lundi');
const defaultTime = ref('08:00');
const deleteId = ref<string | null>(null);

const getEntry = (day: Day, time: string) =>
  entries.value.find(e => e.day === day && e.startTime <= time && e.endTime > time);

const isStart = (day: Day, time: string) =>
  entries.value.some(e => e.day === day && e.startTime === time);

const getSpan = (entry: ScheduleEntry) => {
  const startIdx = TIME_SLOTS.indexOf(entry.startTime);
  const endIdx = TIME_SLOTS.indexOf(entry.endTime);
  return Math.max(endIdx - startIdx, 1);
};

const handleCellClick = (day: Day, time: string) => {
  const existing = getEntry(day, time);
  if (existing) {
    // For now editEntry might need more fields if we want to prefill everything properly
    editEntry.value = existing;
    formOpen.value = true;
  } else {
    editEntry.value = null;
    defaultDay.value = day;
    defaultTime.value = time;
    formOpen.value = true;
  }
};

const handleSaved = () => {
  fetchSchedules({
    school_year_id: filterParams.yearId,
    month: filterParams.month,
    week_number: filterParams.week
  });
};

const confirmDelete = async () => {
  if (deleteId.value) {
    const url = API_ROUTES.DELETE_SCHEDULE(deleteId.value);
    await deleteItem(url);
    
    if (deleteSuccess.value) {
      showCustomToast({
        message: 'Cours supprimé avec succès',
        type: 'success'
      });
      fetchSchedules({
        school_year_id: filterParams.yearId,
        month: filterParams.month,
        week_number: filterParams.week
      });
      deleteId.value = null;
    } else {
      showCustomToast({
        message: errorDelete.value || 'Erreur lors de la suppression',
        type: 'error'
      });
    }
  }
};
</script>

<template>
  <DashLayout
    :breadcrumb="breadcrumbItems"
    active-route="/schedule/school-planer"
    module-name="schedule"
  >

    <div class="pb-6 mx-auto w-full max-w-7xl h-full flex flex-col">
      <DashPageHeader
        title="Horaire des cours"
        description="Gestion de l'emploi du temps scolaire"
        :tags="tagScheduleNavOperations"
        :active-tag-name="activeTagName"
      />


      <BoxPanelWrapper v-if="activeTab === 'school-planer'" class="flex-1 flex flex-col min-h-0">

        <!-- Header Row: Title, Filters, Add Button -->
        <div class="flex items-center gap-3 justify-between mb-4">
          <div class="flex flex-1 items-center gap-2">
            <h2 class="text-xl font-semibold text-foreground mr-4">Horaire du {{ weekDateLabel }}</h2>
            
            <Popover>
              <PopoverTrigger as-child>
                <Button variant="outline" size="sm" class="h-10 rounded-md bg-white border">
                  <span class="iconify hugeicons--filter mr-1.5"></span>
                  Filtre
                </Button>
              </PopoverTrigger>
              <PopoverContent class="w-80">
                <div class="grid gap-4">
                  <div class="space-y-2">
                    <h4 class="font-medium leading-none">Filtrage de l'horaire</h4>
                    <p class="text-sm text-muted-foreground">Sélectionnez les critères pour afficher l'horaire.</p>
                  </div>
                  <div class="flex flex-col gap-3.5">
                    <div class="space-y-1.5">
                      <Label class="text-xs font-medium text-muted-foreground">Année scolaire</Label>
                      <Select v-model="filterParams.yearId">
                        <SelectTrigger class="w-full truncate h-10 bg-white">
                          <SelectValue placeholder="Sélectionner" />
                        </SelectTrigger>
                        <SelectContent>
                          <SelectItem
                            v-for="y in schoolYears"
                            :key="y.id"
                            :value="String(y.id)"
                          >{{ y.name }}{{ y.is_active === '1' || y.is_active === 1 ? ' (Active)' : '' }}</SelectItem>
                        </SelectContent>
                      </Select>
                    </div>

                    <div class="space-y-1.5">
                      <Label class="text-xs font-medium text-muted-foreground">Mois</Label>
                      <Select v-model="filterParams.month">
                        <SelectTrigger class="w-full truncate h-10 bg-white">
                          <SelectValue placeholder="Sélectionner" />
                        </SelectTrigger>
                        <SelectContent>
                          <SelectItem
                            v-for="m in MONTHS"
                            :key="m.value"
                            :value="m.value"
                          >{{ m.label }}</SelectItem>
                        </SelectContent>
                      </Select>
                    </div>

                    <div class="space-y-1.5">
                      <Label class="text-xs font-medium text-muted-foreground">Semaine</Label>
                      <Select v-model="filterParams.week">
                        <SelectTrigger class="w-full truncate h-10 bg-white">
                          <SelectValue placeholder="Sélectionner" />
                        </SelectTrigger>
                        <SelectContent>
                          <SelectItem
                            v-for="w in weeksInMonth"
                            :key="w.value"
                            :value="w.value"
                          >{{ w.label }}</SelectItem>
                        </SelectContent>
                      </Select>
                    </div>
                  </div>
                </div>
              </PopoverContent>
            </Popover>

            <FilterBadges
              :filters="filterParams"
              :reference-data="referenceData"
              :custom-labels="customLabels"
              @remove-filter="removeFilter"
            />
          </div>

          <div class="ml-auto">
            <!-- Intercepting the click with a capture on a wrapper if the button is fully disabled, 
                 or we just style the button to look disabled but keep it clickable to show the error -->
            <Button 
              @click="handleAddClick" 
              size="md" 
              class="rounded-md transition-all duration-200"
              :class="{ 'opacity-50 cursor-not-allowed bg-gray-300 hover:bg-gray-300 text-gray-500 border-none': !filtersReady }"
            >
              <span class="iconify hugeicons--plus-sign mr-1.5"></span>
              Ajouter un cours
            </Button>
          </div>
        </div>


        <!-- Schedule grid or empty state -->
        <template v-if="filtersReady">
          <div v-if="loadingSchedules" class="flex gap-2 items-center justify-center py-10 bg-white h-full rounded-md text-gray-500">
            <span class="iconify animate-spin hugeicons--loading-03 text-2xl"></span>
            <span>Chargement de l'horaire...</span>
          </div>
          
          <div v-else-if="entries.length > 0" class="overflow-x-auto rounded-lg border bg-white shadow-sm">
            <table class="w-full border-collapse min-w-[1000px] table-fixed">
              <thead class="[&_tr]:border-b [&_th]:bg-gray-200 text-foreground-title">
                <tr>
                  <th class="p-3 text-sm font-medium text-muted-foreground border-b border-r w-[80px] text-center first:rounded-tl-md">Heure</th>
                  <th v-for="day in DAYS" :key="day" class="p-3 text-sm font-semibold text-foreground border-b border-r last:border-r-0 last:rounded-tr-md text-center">
                    {{ day }}
                  </th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="time in TIME_SLOTS" :key="time" class="h-24">
                  <td class="p-2 text-xs font-mono bg-gray-200 text-muted-foreground border-b border-b-white border-r text-center whitespace-nowrap font-medium">
                    {{ time }}
                  </td>
                  <template v-for="day in DAYS" :key="day">
                    <template v-if="getEntry(day, time)">
                      <td
                        v-if="isStart(day, time)"
                        :rowspan="getSpan(getEntry(day, time)!)"
                        class="border-b border-r last:border-r-0 p-0 relative"
                      >
                        <div
                          v-bind:class="cn(
                            'absolute inset-0 border-l-4 p-2 cursor-pointer transition-all hover:shadow-md group flex flex-col justify-start overflow-hidden',
                            getSubjectColor(getEntry(day, time)!.subject)
                          )"
                          @click="handleCellClick(day, time)"
                        >
                          <div class="font-bold text-sm truncate">{{ getEntry(day, time)!.subject }}</div>
                          <div v-if="getEntry(day, time)!.teacher" class="text-xs opacity-90 mt-0.5 truncate">
                             {{ getEntry(day, time)!.teacher }}
                          </div>
                          <div v-if="getEntry(day, time)!.room" class="text-xs opacity-90 truncate">
                             {{ getEntry(day, time)!.room }}
                          </div>
                          <div class="text-[10px] opacity-70 mt-auto">
                             {{ getEntry(day, time)!.startTime }} - {{ getEntry(day, time)!.endTime }}
                          </div>
                          
                          <div class="absolute bottom-1.5 right-1.5 opacity-0 group-hover:opacity-100 transition-opacity">
                            <button
                              @click.stop="deleteId = getEntry(day, time)!.id"
                              class="size-9 flex items-center justify-center rounded-md bg-white/90 hover:bg-red-50 text-red-600 shadow-sm"
                            >
                              <span class="iconify hugeicons--delete-02 size-5"></span>
                            </button>
                          </div>
                        </div>
                      </td>
                    </template>

                    <td
                      v-else
                      class="border-b border-r last:border-r-0 p-1 cursor-pointer hover:bg-gray-400 group transition-colors"
                      @click="handleCellClick(day, time)"
                    >
                      <div class="h-full rounded flex items-center justify-center min-h-[60px]">
                        <span class="iconify hugeicons--plus-sign text-muted-foreground/30 group-hover:text-white size-4 transition-colors"></span>
                      </div>
                    </td>
                  </template>
                </tr>
              </tbody>
            </table>
          </div>

          <!-- Empty state when no entries found -->
          <div v-else class="flex-1 flex flex-col items-center justify-center py-16 text-center border rounded-lg bg-gray-50/50">
            <span class="iconify hugeicons--calendar-03 size-12 text-muted-foreground/20 mb-3"></span>
            <p class="text-lg font-medium text-muted-foreground">Aucun cours programmé</p>
            <p class="text-sm text-muted-foreground/70 mt-1">
              Aucun horaire n'a été trouvé pour cette période.
            </p>
            <Button @click="editEntry = null; formOpen = true" variant="outline" size="sm" class="mt-4">
              Ajouter le premier cours
            </Button>
          </div>
        </template>

        <!-- Empty state when filters not complete -->
        <div v-else class="flex-1 flex flex-col items-center justify-center py-16 text-center">
          <span class="iconify hugeicons--calendar-03 size-16 text-muted-foreground/30 mb-4"></span>
          <p class="text-lg font-medium text-muted-foreground">Sélectionnez les filtres</p>
          <p class="text-sm text-muted-foreground/70 mt-1">
            Veuillez sélectionner l'année scolaire, le mois et la semaine pour afficher l'horaire.
          </p>
        </div>

        <!-- Week Navigation at the bottom (Pagination) -->
        <div v-if="filtersReady" class="flex justify-end items-center mt-6">
          <div class="flex items-center gap-1">
            <button
              @click="prevWeek"
              :disabled="!canGoPrev"
              class="flex items-center justify-center px-4 h-9 text-xs font-medium text-foreground-muted bg-white border border-border rounded-lg hover:bg-gray-100 hover:text-gray-700 disabled:opacity-50 transition-colors"
            >
              Précédent
            </button>
            
            <div class="flex items-center justify-center px-4 h-9 text-sm font-medium border border-primary bg-primary rounded-lg text-white min-w-[120px]">
              {{ currentWeekData?.label?.split('(')[0] || 'Semaine' }}
            </div>
            
            <button
              @click="nextWeek"
              :disabled="!canGoNext"
              class="flex items-center justify-center px-4 h-9 text-xs font-medium text-foreground-muted bg-white border border-border rounded-lg hover:bg-gray-100 hover:text-gray-700 disabled:opacity-50 transition-colors"
            >
              Suivant
            </button>
          </div>
        </div>

      </BoxPanelWrapper>


      <ScheduleCalendar v-else :entries="allEntries" />
    </div>



    <ClassFormDialog
      v-model:open="formOpen"
      :default-day="defaultDay"
      :default-time="defaultTime"
      :initial-data="editEntry"
      :default-school-year-id="filterParams.yearId"
      :default-week-number="filterParams.week"
      :available-weeks="weeksInMonth"
      @close="formOpen = false; editEntry = null"
      @saved="handleSaved"
    />


    <AlertDialog :open="!!deleteId" @update:open="deleteId = null">
      <AlertDialogContent>
        <AlertDialogHeader>
          <AlertDialogTitle>Supprimer ce cours ?</AlertDialogTitle>
          <AlertDialogDescription>
            Cette action est irréversible. Le cours sera définitivement supprimé.
          </AlertDialogDescription>
        </AlertDialogHeader>
        <AlertDialogFooter>
          <AlertDialogCancel :disabled="deletingApi">Annuler</AlertDialogCancel>
          <Button
            @click="confirmDelete"
            variant="destructive"
            class="h-10 px-4"
            :disabled="deletingApi"
          >
            <span v-if="!deletingApi">Oui, Supprimer</span>
            <span v-else class="flex items-center gap-2">
              <IconifySpinner class="text-white" />
              <span>Suppression...</span>
            </span>
          </Button>
        </AlertDialogFooter>
      </AlertDialogContent>
    </AlertDialog>
  </DashLayout>
</template>

<style scoped>
/* Add any specific styles here if needed */
</style>