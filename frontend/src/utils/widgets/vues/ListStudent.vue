<template>
  <Select :model-value="displayValue" @update:model-value="updateValue">
    <SelectTrigger class="w-full">
      <SelectValue :placeholder="placeholder || 'Sélectionner un élève'" />
    </SelectTrigger>
    <SelectContent>
      <SelectItem v-if="loading" value="loading" disabled> Chargement des élèves... </SelectItem>
      <SelectItem v-else-if="error" value="error" disabled> Erreur lors du chargement </SelectItem>
      <SelectItem
        v-else-if="!normalizedStudents || normalizedStudents.length === 0"
        value="empty"
        disabled
      >
        Aucun élève disponible
      </SelectItem>
      <SelectItem v-else value="null"> Aucun élève </SelectItem>
      <SelectItem
        v-for="student in normalizedStudents"
        :key="student.id"
        :value="student.id.toString()"
      >
        {{ getStudentName(student) }}
      </SelectItem>
    </SelectContent>
  </Select>
</template>

<script setup lang="ts">
import { computed, onMounted, onUnmounted, ref, watchEffect } from 'vue'
import { useGetApi } from '@/composables/useGetApi'
import { API_ROUTES } from '@/utils/constants/api_route'
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from '@/components/ui/select'

interface Student {
  id: number
  name: string
  firstname?: string
  matricule?: string
  email?: string
}

interface Props {
  modelValue?: string | number | null
  placeholder?: string
}

interface Emits {
  (e: 'update:modelValue', value: string): void
}

const props = defineProps<Props>()
const emit = defineEmits<Emits>()

// Flag pour éviter les opérations après démontage
const isMounted = ref(true)

// API pour récupérer les élèves
const { data: studentsRaw, loading, error, fetchData } = useGetApi<any>(API_ROUTES.GET_STUDENTS)

// Normaliser les structures de réponse possibles pour obtenir un tableau d'élèves
const normalizedStudents = computed<Student[]>(() => {
  const v: any = studentsRaw.value
  if (!v) return []

  // Cas 1: tableau direct
  if (Array.isArray(v)) return v as Student[]

  // Cas 2: { students: [...] } (array) ou { data: [...] }
  if (Array.isArray(v?.students)) return v.students as Student[]
  if (Array.isArray(v?.data)) return v.data as Student[]

  // Cas 3: { students: { data: [...] } }
  if (v?.students?.data && Array.isArray(v.students.data)) return v.students.data as Student[]

  // Cas 4: objet map avec ids comme clés
  if (v && typeof v === 'object') {
    const arr = Object.values(v).filter(
      (item: any) => item && typeof item === 'object' && 'id' in item,
    )
    if (arr.length > 0) return arr as Student[]
  }

  return []
})

// Valeur affichée dans le sélecteur
const displayValue = computed(() => {
  if (!isMounted.value) return 'null'
  if (props.modelValue === null || props.modelValue === undefined || props.modelValue === '') {
    return 'null'
  }
  return props.modelValue.toString()
})

// Fonction pour mettre à jour la valeur
function updateValue(value: any) {
  if (!isMounted.value) return

  try {
    // Gérer les valeurs spéciales
    if (value === 'null' || value === 'loading' || value === 'error' || value === 'empty') {
      emit('update:modelValue', '')
      return
    }
    emit('update:modelValue', value?.toString() || '')
  } catch (err) {
    console.warn('Erreur lors de la mise à jour de la valeur:', err)
  }
}

// Fonction pour obtenir le nom complet de l'élève
const getStudentName = (student: Student): string => {
  if (!student) return 'Élève inconnu'
  try {
    const fullName = `${student.firstname || ''} ${student.name}`.trim()
    const matricule = student.matricule ? ` (${student.matricule})` : ''
    return fullName + matricule || `Élève #${student.id}`
  } catch (err) {
    return `Élève #${student.id || 'inconnu'}`
  }
}

// Charger les données au montage
onMounted(async () => {
  try {
    if (isMounted.value) {
      await fetchData()
    }
  } catch (err) {
    console.warn('Erreur lors du chargement des élèves:', err)
  }
})

// Debug aide au diagnostic
watchEffect(() => {
  if (studentsRaw.value) {
    console.log('ListStudent - raw:', studentsRaw.value)
    console.log('ListStudent - normalized:', normalizedStudents.value)
  }
  if (error.value) {
    console.error('ListStudent - error:', error.value)
  }
})

// Nettoyer lors du démontage
onUnmounted(() => {
  isMounted.value = false
})
</script>
