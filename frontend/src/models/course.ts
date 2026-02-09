import { AcademicLevel } from './academic_level'
import { Classroom } from './classroom'
import { Filiere } from './filiere'

export interface Course {
  id: number
  label: string
  academic_level_id: string
  filiaire_id: string
  school_id: string
  cycle_id: string
  classroom_id: string
  hourly_volume: string
  max_period_1: string
  max_period_2: string
  max_period_3: string
  max_period_4: string
  max_exam_1: string
  max_exam_2: string
  created_at: string
  updated_at: string
  academic_personals?: any[]
  academic_level?: AcademicLevel
  filiaire?: Filiere
  classroom?: Classroom
}
