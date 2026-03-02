<script setup lang="ts">
import { ref, computed, watch } from 'vue';
import {
  Dialog,
  DialogContent,
  DialogHeader,
  DialogTitle,
  DialogFooter,
  DialogDescription,
} from '@/components/ui/dialog';
import { Button } from '@/components/ui/button';
import { Label } from '@/components/ui/label';
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from '@/components/ui/select';
import { DAYS, TIME_SLOTS, type Day, type ScheduleEntry } from '@/types/schedule';
import { usePostApi } from '@/composables/usePostApi';
import { usePutApi } from '@/composables/usePutApi';
import { useGetApi } from '@/composables/useGetApi';
import { API_ROUTES } from '@/utils/constants/api_route';
import { showCustomToast } from '@/utils/widgets/custom_toast';
import IconifySpinner from '@/components/ui/spinner/IconifySpinner.vue';

const props = defineProps<{
  open: boolean;
  defaultDay?: Day;
  defaultTime?: string;
  initialData?: ScheduleEntry | null;
  defaultSchoolYearId?: string | number;
  defaultClassroomId?: string | number;
  defaultWeekNumber?: string | number;
  availableWeeks?: Array<{ value: string; label: string; start?: Date; end?: Date }>;
}>();

const emit = defineEmits<{
  (e: 'close'): void;
  (e: 'saved'): void;
  (e: 'update:open', value: boolean): void;
}>();

// API composables
const { postData, loading: posting, success: postSuccess, error: postError } = usePostApi();
const { putData, loading: putting, success: putSuccess, error: putError } = usePutApi();

const saving = computed(() => posting.value || putting.value);
const error = computed(() => postError.value || putError.value);
const anySuccess = computed(() => postSuccess.value || putSuccess.value);

// Fetch dropdown data
const { data: rawYears, fetchData: fetchYears } = useGetApi(API_ROUTES.GET_SCHOOL_YEARS);
const { data: rawClassrooms, fetchData: fetchClassrooms } = useGetApi(API_ROUTES.GET_CLASSROOMS);
const { data: rawPersonals, fetchData: fetchPersonals } = useGetApi(API_ROUTES.GET_ACADEMIC_PERSONALS);
const { data: rawCourses, fetchData: fetchCourses } = useGetApi(API_ROUTES.GET_COURSES);

// Computed lists
const schoolYears = computed(() => {
  if (!rawYears.value) return [];
  if (Array.isArray(rawYears.value)) return rawYears.value;
  if ((rawYears.value as any).years) return (rawYears.value as any).years;
  return (rawYears.value as any).data || [];
});

const classrooms = computed(() => {
  if (!rawClassrooms.value) return [];
  if (Array.isArray(rawClassrooms.value)) return rawClassrooms.value;
  return (rawClassrooms.value as any).data || [];
});

const academicPersonals = computed(() => {
  if (!rawPersonals.value) return [];
  if (Array.isArray(rawPersonals.value)) return rawPersonals.value;
  return (rawPersonals.value as any).data || [];
});

const courses = computed(() => {
  if (!rawCourses.value) return [];
  if (Array.isArray(rawCourses.value)) return rawCourses.value;
  const d = (rawCourses.value as any).data;
  if (Array.isArray(d)) return d;
  return [];
});

// Form state
const day = ref<Day>(props.defaultDay || 'Lundi');
const startTime = ref(props.defaultTime || '08:00');
const endTime = ref('09:00');
const schoolYearId = ref('');
const classroomId = ref('');
const academicPersonalId = ref('');
const courseId = ref('');
const weekNumber = ref('');

const isEditing = computed(() => !!props.initialData);

// Reset/Prefill form when dialog opens or initialData changes
watch(() => [props.open, props.initialData], ([isOpen, data]) => {
  if (isOpen) {
    if (data) {
      // Editing mode
      const entry = data as ScheduleEntry;
      day.value = entry.day;
      startTime.value = entry.startTime;
      endTime.value = entry.endTime;
      schoolYearId.value = String(entry.school_year_id || '');
      classroomId.value = String(entry.classroom_id || '');
      academicPersonalId.value = String(entry.academic_personal_id || '');
      courseId.value = String(entry.course_id || '');
      weekNumber.value = String(entry.week_number || 'all');
    } else {
      // Creation mode
      day.value = props.defaultDay || 'Lundi';
      startTime.value = props.defaultTime || '08:00';
      const startIdx = TIME_SLOTS.indexOf(props.defaultTime || '08:00');
      endTime.value = TIME_SLOTS[startIdx + 1] || TIME_SLOTS[TIME_SLOTS.length - 1] || '09:00';
      schoolYearId.value = String(props.defaultSchoolYearId || '');
      classroomId.value = String(props.defaultClassroomId || '');
      academicPersonalId.value = '';
      courseId.value = '';
      weekNumber.value = String(props.defaultWeekNumber || 'all');
    }

    // Fetch dropdown data
    fetchYears();
    fetchClassrooms();
    fetchPersonals();
    fetchCourses();
  }
}, { immediate: true });

const handleSave = async () => {
  // Validation
  if (!schoolYearId.value || !classroomId.value || !academicPersonalId.value || !courseId.value || !weekNumber.value) {
    showCustomToast({ message: 'Veuillez remplir tous les champs obligatoires (y compris la semaine)', type: 'error' });
    return;
  }

  if (startTime.value >= endTime.value) {
    showCustomToast({ message: "L'heure de fin doit être après l'heure de début", type: 'error' });
    return;
  }

  const payload = {
    school_year_id: Number(schoolYearId.value),
    classroom_id: Number(classroomId.value),
    academic_personal_id: Number(academicPersonalId.value),
    course_id: Number(courseId.value),
    week_number: weekNumber.value && weekNumber.value !== 'all' ? Number(weekNumber.value) : null,
    day: day.value,
    start_time: startTime.value,
    end_time: endTime.value,
    // Method spoofing for Laravel (handles servers that block real PUT)
    ...(isEditing.value ? { _method: 'PUT' } : {})
  };

  const url = isEditing.value 
    ? API_ROUTES.UPDATE_SCHEDULE(props.initialData!.id) 
    : API_ROUTES.CREATE_SCHEDULE;

  // Use postData for both cases because of method spoofing (_method: 'PUT')
  await postData(url, payload);

  if (anySuccess.value) {
    showCustomToast({ 
      message: isEditing.value ? 'Cours mis à jour avec succès' : 'Cours ajouté avec succès', 
      type: 'success' 
    });
    emit('saved');
    emit('close');
  } else if (error.value) {
    showCustomToast({ message: error.value, type: 'error' });
  }
};

const handleOpenChange = (val: boolean) => {
  if (!val) emit('close');
};
</script>

<template>
  <Dialog :open="open" @update:open="handleOpenChange">
    <DialogContent class="sm:max-w-lg">
      <DialogHeader>
        <DialogTitle>{{ isEditing ? 'Modifier le cours' : 'Ajouter un cours' }}</DialogTitle>
        <DialogDescription>
          {{ isEditing ? 'Modifiez les informations du créneau sélectionné.' : 'Remplissez les informations du nouveau cours.' }}
        </DialogDescription>
      </DialogHeader>
      <div class="grid gap-4 py-2">
        <!-- Année scolaire & Classe -->
        <div class="grid grid-cols-2 gap-4">
          <div class="space-y-2">
            <Label>Année scolaire <span class="text-red-500">*</span></Label>
            <Select v-model="schoolYearId">
              <SelectTrigger class="w-full truncate"><SelectValue placeholder="Sélectionner" /></SelectTrigger>
              <SelectContent>
                <SelectItem
                  v-for="y in schoolYears"
                  :key="y.id"
                  :value="String(y.id)"
                >{{ y.name }}</SelectItem>
              </SelectContent>
            </Select>
          </div>
          <div class="space-y-2">
            <Label>Classe <span class="text-red-500">*</span></Label>
            <Select v-model="classroomId">
              <SelectTrigger class="w-full truncate"><SelectValue placeholder="Sélectionner" /></SelectTrigger>
              <SelectContent>
                <SelectItem
                  v-for="c in classrooms"
                  :key="c.id"
                  :value="String(c.id)"
                >{{ c.name }}</SelectItem>
              </SelectContent>
            </Select>
          </div>
        </div>

        <!-- Enseignant & Cours -->
        <div class="grid grid-cols-2 gap-4">
          <div class="space-y-2">
            <Label>Enseignant <span class="text-red-500">*</span></Label>
            <Select v-model="academicPersonalId">
              <SelectTrigger class="w-full truncate"><SelectValue placeholder="Sélectionner" /></SelectTrigger>
              <SelectContent>
                <SelectItem
                  v-for="p in academicPersonals"
                  :key="p.id"
                  :value="String(p.id)"
                >{{ p.name }} {{ p.post_name }}</SelectItem>
              </SelectContent>
            </Select>
          </div>
          <div class="space-y-2">
            <Label>Cours <span class="text-red-500">*</span></Label>
            <Select v-model="courseId">
              <SelectTrigger class="w-full truncate"><SelectValue placeholder="Sélectionner" /></SelectTrigger>
              <SelectContent>
                <SelectItem
                  v-for="co in courses"
                  :key="co.id"
                  :value="String(co.id)"
                >{{ co.label }}</SelectItem>
              </SelectContent>
            </Select>
          </div>
        </div>

        <!-- Jour & Semaine -->
        <div class="grid grid-cols-2 gap-4">
          <div class="space-y-2">
            <Label>Jour <span class="text-red-500">*</span></Label>
            <Select v-model="day">
              <SelectTrigger class="w-full truncate"><SelectValue /></SelectTrigger>
              <SelectContent>
                <SelectItem v-for="d in DAYS" :key="d" :value="d">{{ d }}</SelectItem>
              </SelectContent>
            </Select>
          </div>
          <div class="space-y-2">
            <Label>Semaine du mois <span class="text-red-500">*</span></Label>
            <Select v-model="weekNumber">
              <SelectTrigger class="w-full truncate"><SelectValue placeholder="Toutes les semaines (Récurrent)" /></SelectTrigger>
              <SelectContent>
                <SelectItem value="all">Toutes les semaines (Récurrent)</SelectItem>
                <SelectItem 
                  v-for="w in availableWeeks" 
                  :key="w.value" 
                  :value="w.value"
                >
                  {{ w.label }}
                </SelectItem>
              </SelectContent>
            </Select>
          </div>
        </div>

        <!-- Heures -->
        <div class="grid grid-cols-2 gap-4">
          <div class="space-y-2">
            <Label>Heure de début <span class="text-red-500">*</span></Label>
            <Select v-model="startTime">
              <SelectTrigger class="w-full truncate"><SelectValue /></SelectTrigger>
              <SelectContent>
                <SelectItem v-for="t in TIME_SLOTS" :key="t" :value="t">{{ t }}</SelectItem>
              </SelectContent>
            </Select>
          </div>
          <div class="space-y-2">
            <Label>Heure de fin <span class="text-red-500">*</span></Label>
            <Select v-model="endTime">
              <SelectTrigger class="w-full truncate"><SelectValue /></SelectTrigger>
              <SelectContent>
                <SelectItem v-for="t in TIME_SLOTS" :key="t" :value="t">{{ t }}</SelectItem>
              </SelectContent>
            </Select>
          </div>
        </div>
      </div>
      <DialogFooter>
        <Button variant="outline" @click="emit('close')" :disabled="saving">Annuler</Button>
        <Button @click="handleSave" :disabled="saving">
          <IconifySpinner v-if="saving" class="mr-2" />
          <span v-if="saving">{{ isEditing ? 'Mise à jour...' : 'Enregistrement...' }}</span>
          <span v-else>{{ isEditing ? 'Mettre à jour' : 'Enregistrer' }}</span>
        </Button>
      </DialogFooter>
    </DialogContent>
  </Dialog>
</template>
