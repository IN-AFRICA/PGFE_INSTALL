import { AcademicLevel } from './academic_level'

export interface Classroom {
  id: number
  name: string
  academic_level_id: string
  indicator: string | null
  created_at: string
  updated_at: string
  titulaire_id: string | null
  academic_level: AcademicLevel | null
  titulaire: any | null
}
