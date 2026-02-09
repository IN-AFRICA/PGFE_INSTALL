<template>
  <div class="space-y-2">
    <Select :model-value="displayValue" @update:model-value="updateValue">
      <SelectTrigger>
        <SelectValue :placeholder="placeholder || 'Sélectionner un élève'" />
      </SelectTrigger>
      <SelectContent>
        <!-- Barre de recherche -->
        <div class="p-2 border-b">
          <Input
            v-model="searchQuery"
            placeholder="Rechercher un élève..."
            class="h-8"
            @click.stop
          />
        </div>

        <!-- États de chargement et d'erreur -->
        <SelectItem v-if="loading" value="loading" disabled>
          <div class="flex items-center gap-2">
            <span class="iconify hugeicons--loading-03 animate-spin text-sm"></span>
            Chargement des élèves...
          </div>
        </SelectItem>

        <SelectItem v-else-if="error" value="error" disabled>
          <div class="flex items-center gap-2 text-red-500">
            <span class="iconify hugeicons--alert-circle text-sm"></span>
            Erreur de chargement
          </div>
        </SelectItem>

        <SelectItem
          v-else-if="filteredStudents.length === 0 && !searchQuery"
          value="empty"
          disabled
        >
          Aucun élève disponible
        </SelectItem>

        <SelectItem
          v-else-if="filteredStudents.length === 0 && searchQuery"
          value="no-results"
          disabled
        >
          Aucun résultat pour "{{ searchQuery }}"
        </SelectItem>

        <!-- Option "Aucun élève" -->
        <SelectItem value="null">
          <div class="flex items-center gap-2 text-gray-500">
            <span class="iconify hugeicons--user-remove-02 text-sm"></span>
            Aucun élève
          </div>
        </SelectItem>

        <!-- Liste des élèves filtrés -->
        <SelectItem
          v-for="student in filteredStudents"
          :key="student.id"
          :value="student.id.toString()"
        >
          <div class="flex items-center gap-2">
            <span class="iconify hugeicons--user-02 text-sm text-blue-500"></span>
            <div class="flex flex-col">
              <span class="font-medium">{{ getStudentName(student) }}</span>
              <span v-if="student.matricule" class="text-xs text-gray-500">
                Matricule: {{ student.matricule }}
              </span>
            </div>
          </div>
        </SelectItem>
      </SelectContent>
    </Select>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { Input } from '@/components/ui/input'
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from '@/components/ui/select'
import { useGetApi } from '@/composables/useGetApi'
import { API_ROUTES } from '@/utils/constants/api_route'

// Interface pour les élèves
interface Student {
  id: number
  name: string
  firstname?: string
  matricule?: string
  email?: string
  phone_number?: string
}

// Props
interface Props {
  modelValue?: string | number | null
  placeholder?: string
}

const props = withDefaults(defineProps<Props>(), {
  modelValue: null,
  placeholder: 'Sélectionner un élève',
})

// Émissions
const emit = defineEmits<{
  'update:modelValue': [value: string | null]
}>()

// Variables réactives
const searchQuery = ref('')
const isMounted = ref(true)

// API pour récupérer les élèves
const {
  data: studentsData,
  loading,
  error,
  fetchData,
} = useGetApi<Student[]>(API_ROUTES.GET_STUDENTS)

// Computed pour la valeur affichée
const displayValue = computed(() => {
  if (props.modelValue === null || props.modelValue === undefined || props.modelValue === '') {
    return 'null'
  }
  return props.modelValue.toString()
})

// Fonction pour obtenir le nom complet de l'élève
const getStudentName = (student: Student): string => {
  const firstName = student.firstname || ''
  const lastName = student.name || ''
  return `${firstName} ${lastName}`.trim() || `Élève #${student.id}`
}

// Élèves filtrés par la recherche
const filteredStudents = computed(() => {
  if (!studentsData.value || !Array.isArray(studentsData.value)) {
    return []
  }

  if (!searchQuery.value.trim()) {
    return studentsData.value
  }

  const query = searchQuery.value.toLowerCase().trim()
  return studentsData.value.filter((student) => {
    const fullName = getStudentName(student).toLowerCase()
    const matricule = (student.matricule || '').toLowerCase()
    const email = (student.email || '').toLowerCase()

    return fullName.includes(query) || matricule.includes(query) || email.includes(query)
  })
})

// Fonction pour mettre à jour la valeur
function updateValue(value: any) {
  if (!isMounted.value) return

  // Gérer les valeurs spéciales
  if (
    value === 'null' ||
    value === 'loading' ||
    value === 'error' ||
    value === 'empty' ||
    value === 'no-results'
  ) {
    emit('update:modelValue', null)
    return
  }

  emit('update:modelValue', value?.toString() || null)
}

// Charger les données au montage
onMounted(async () => {
  try {
    await fetchData()
  } catch (err) {
    console.error('Erreur lors du chargement des élèves:', err)
  }
})

// Nettoyage au démontage
onUnmounted(() => {
  isMounted.value = false
})
</script>
