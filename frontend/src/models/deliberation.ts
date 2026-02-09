import { Course } from './course'
import { PeriodNote } from './periode_note'
import { Student } from './students'

export interface Deliberation {
  id: number
  student: Student
  classroom: string
  filiaire: string
  academic_level: string
  cycle: string
  school_year: string
  school: string
  course: Course
  is_validated: string // "0" ou "1" (string, pas number)
  mention: string
  cotations: Cotation[]
  note: PeriodNote // Notes directement dans l'objet
  pourcentage: number // Pourcentage calculé par le backend
  moyenne_note: number // Moyenne calculée par le backend
  repechage: boolean // Indicateur de repêchage
  created_at: string
  updated_at: string
}
